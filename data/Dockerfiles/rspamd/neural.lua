--[[
Copyright (c) 2016, Vsevolod Stakhov <vsevolod@highsecure.ru>

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
]]--


if confighelp then
  return
end

local rspamd_logger = require "rspamd_logger"
local rspamd_util = require "rspamd_util"
local rspamd_kann = require "rspamd_kann"
local lua_redis = require "lua_redis"
local lua_util = require "lua_util"
local fun = require "fun"
local lua_settings = require "lua_settings"
local meta_functions = require "lua_meta"
local ts = require("tableshape").types
local lua_verdict = require "lua_verdict"
local N = "neural"

-- Module vars
local default_options = {
  train = {
    max_trains = 1000,
    max_epoch = 1000,
    max_usages = 10,
    max_iterations = 25, -- Torch style
    mse = 0.001,
    autotrain = true,
    train_prob = 1.0,
    learn_threads = 1,
    learning_rate = 0.01,
  },
  watch_interval = 60.0,
  lock_expire = 600,
  learning_spawned = false,
  ann_expire = 60 * 60 * 24 * 2, -- 2 days
  symbol_spam = 'NEURAL_SPAM',
  symbol_ham = 'NEURAL_HAM',
}

local redis_profile_schema = ts.shape{
  digest = ts.string,
  symbols = ts.array_of(ts.string),
  version = ts.number,
  redis_key = ts.string,
  distance = ts.number:is_optional(),
}

-- Rule structure:
-- * static config fields (see `default_options`)
-- * prefix - name or defined prefix
-- * settings - table of settings indexed by settings id, -1 is used when no settings defined

-- Rule settings element defines elements for specific settings id:
-- * symbols - static symbols profile (defined by config or extracted from symcache)
-- * name - name of settings id
-- * digest - digest of all symbols
-- * ann - dynamic ANN configuration loaded from Redis
-- * train - train data for ANN (e.g. the currently trained ANN)

-- Settings ANN table is loaded from Redis and represents dynamic profile for ANN
-- Some elements are directly stored in Redis, ANN is, in turn loaded dynamically
-- * version - version of ANN loaded from redis
-- * redis_key - name of ANN key in Redis
-- * symbols - symbols in THIS PARTICULAR ANN (might be different from set.symbols)
-- * distance - distance between set.symbols and set.ann.symbols
-- * ann - kann object

local settings = {
  rules = {},
  prefix = 'rn', -- Neural network default prefix
  max_profiles = 3, -- Maximum number of NN profiles stored
}

local module_config = rspamd_config:get_all_opt("neural")
if not module_config then
  -- Legacy
  module_config = rspamd_config:get_all_opt("fann_redis")
end


-- Lua script that checks if we can store a new training vector
-- Uses the following keys:
-- key1 - ann key
-- key2 - spam or ham
-- key3 - maximum trains
-- key4 - sampling coin (as Redis scripts do not allow math.random calls)
-- returns 1 or 0 + reason: 1 - allow learn, 0 - not allow learn
local redis_lua_script_can_store_train_vec = [[
  local prefix = KEYS[1]
  local locked = redis.call('HGET', prefix, 'lock')
  if locked then return {tostring(-1),'locked by another process till: ' .. locked} end
  local nspam = 0
  local nham = 0
  local lim = tonumber(KEYS[3])
  local coin = tonumber(KEYS[4])

  local ret = redis.call('LLEN', prefix .. '_spam')
  if ret then nspam = tonumber(ret) end
  ret = redis.call('LLEN', prefix .. '_ham')
  if ret then nham = tonumber(ret) end

  if KEYS[2] == 'spam' then
    if nspam <= lim then
      if nspam > nham then
        -- Apply sampling
        local skip_rate = 1.0 - nham / (nspam + 1)
        if coin < skip_rate then
          return {tostring(-(nspam)),'sampled out with probability ' .. tostring(skip_rate)}
        end
      end
      return {tostring(nspam),'can learn'}
    else -- Enough learns
      return {tostring(-(nspam)),'too many spam samples'}
    end
  else
    if nham <= lim then
      if nham > nspam then
        -- Apply sampling
        local skip_rate = 1.0 - nspam / (nham + 1)
        if coin < skip_rate then
          return {tostring(-(nham)),'sampled out with probability ' .. tostring(skip_rate)}
        end
      end
      return {tostring(nham),'can learn'}
    else
      return {tostring(-(nham)),'too many ham samples'}
    end
  end

  return {tostring(-1),'bad input'}
]]
local redis_can_store_train_vec_id = nil

-- Lua script to invalidate ANNs by rank
-- Uses the following keys
-- key1 - prefix for keys
-- key2 - number of elements to leave
local redis_lua_script_maybe_invalidate = [[
  local card = redis.call('ZCARD', KEYS[1])
  local lim = tonumber(KEYS[2])
  if card > lim then
    local to_delete = redis.call('ZRANGE', KEYS[1], 0, card - lim - 1)
    for _,k in ipairs(to_delete) do
      local tb = cjson.decode(k)
      redis.call('DEL', tb.redis_key)
      -- Also train vectors
      redis.call('DEL', tb.redis_key .. '_spam')
      redis.call('DEL', tb.redis_key .. '_ham')
    end
    redis.call('ZREMRANGEBYRANK', KEYS[1], 0, card - lim - 1)
    return to_delete
  else
    return {}
  end
]]
local redis_maybe_invalidate_id = nil

-- Lua script to invalidate ANN from redis
-- Uses the following keys
-- key1 - prefix for keys
-- key2 - current time
-- key3 - key expire
-- key4 - hostname
local redis_lua_script_maybe_lock = [[
  local locked = redis.call('HGET', KEYS[1], 'lock')
  local now = tonumber(KEYS[2])
  if locked then
    locked = tonumber(locked)
    local expire = tonumber(KEYS[3])
    if now > locked and (now - locked) < expire then
      return {tostring(locked), redis.call('HGET', KEYS[1], 'hostname')}
    end
  end
  redis.call('HSET', KEYS[1], 'lock', tostring(now))
  redis.call('HSET', KEYS[1], 'hostname', KEYS[4])
  return 1
]]
local redis_maybe_lock_id = nil

-- Lua script to save and unlock ANN in redis
-- Uses the following keys
-- key1 - prefix for ANN
-- key2 - prefix for profile
-- key3 - compressed ANN
-- key4 - profile as JSON
-- key5 - expire in seconds
-- key6 - current time
-- key7 - old key
local redis_lua_script_save_unlock = [[
  local now = tonumber(KEYS[6])
  redis.call('ZADD', KEYS[2], now, KEYS[4])
  redis.call('HSET', KEYS[1], 'ann', KEYS[3])
  redis.call('DEL', KEYS[1] .. '_spam')
  redis.call('DEL', KEYS[1] .. '_ham')
  redis.call('HDEL', KEYS[1], 'lock')
  redis.call('HDEL', KEYS[7], 'lock')
  redis.call('EXPIRE', KEYS[1], tonumber(KEYS[5]))
  return 1
]]
local redis_save_unlock_id = nil

local redis_params

local function load_scripts(params)
  redis_can_store_train_vec_id = lua_redis.add_redis_script(redis_lua_script_can_store_train_vec,
    params)
  redis_maybe_invalidate_id = lua_redis.add_redis_script(redis_lua_script_maybe_invalidate,
    params)
  redis_maybe_lock_id = lua_redis.add_redis_script(redis_lua_script_maybe_lock,
    params)
  redis_save_unlock_id = lua_redis.add_redis_script(redis_lua_script_save_unlock,
    params)
end

local function result_to_vector(task, profile)
  if not profile.zeros then
    -- Fill zeros vector
    local zeros = {}
    for i=1,meta_functions.count_metatokens() do
      zeros[i] = 0.0
    end
    for _,_ in ipairs(profile.symbols) do
      zeros[#zeros + 1] = 0.0
    end
    profile.zeros = zeros
  end

  local vec = lua_util.shallowcopy(profile.zeros)
  local mt = meta_functions.rspamd_gen_metatokens(task)

  for i,v in ipairs(mt) do
    vec[i] = v
  end

  task:process_ann_tokens(profile.symbols, vec, #mt, 0.1)

  return vec
end

-- Used to generate new ANN key for specific profile
local function new_ann_key(rule, set, version)
  local ann_key = string.format('%s_%s_%s_%s_%s', settings.prefix,
      rule.prefix, set.name, set.digest:sub(1, 8), tostring(version))

  return ann_key
end

-- Extract settings element for a specific settings id
local function get_rule_settings(task, rule)
  local sid = task:get_settings_id() or -1

  local set = rule.settings[sid]

  if not set then return nil end

  while type(set) == 'number' do
    -- Reference to another settings!
    set = rule.settings[set]
  end

  return set
end

-- Generate redis prefix for specific rule and specific settings
local function redis_ann_prefix(rule, settings_name)
  -- We also need to count metatokens:
  local n = meta_functions.version
  return string.format('%s_%s_%d_%s',
      settings.prefix, rule.prefix, n, settings_name)
end

-- Creates and stores ANN profile in Redis
local function new_ann_profile(task, rule, set, version)
  local ann_key = new_ann_key(rule, set, version)

  local profile = {
    symbols = set.symbols,
    redis_key = ann_key,
    version = version,
    digest = set.digest,
    distance = 0 -- Since we are using our own profile
  }

  local ucl = require "ucl"
  local profile_serialized = ucl.to_format(profile, 'json-compact', true)

  local function add_cb(err, _)
    if err then
      rspamd_logger.errx(task, 'cannot store ANN profile for %s:%s at %s : %s',
          rule.prefix, set.name, profile.redis_key, err)
    else
      rspamd_logger.infox(task, 'created new ANN profile for %s:%s, data stored at prefix %s',
          rule.prefix, set.name, profile.redis_key)
    end
  end

  lua_redis.redis_make_request(task,
      rule.redis,
      nil,
      true, -- is write
      add_cb, --callback
      'ZADD', -- command
      {set.prefix, tostring(rspamd_util.get_time()), profile_serialized}
  )

  return profile
end


-- ANN filter function, used to insert scores based on the existing symbols
local function ann_scores_filter(task)

  for _,rule in pairs(settings.rules) do
    local sid = task:get_settings_id() or -1
    local ann
    local profile

    local set = get_rule_settings(task, rule)
    if set then
      if set.ann then
        ann = set.ann.ann
        profile = set.ann
      else
        lua_util.debugm(N, task, 'no ann loaded for %s:%s',
            rule.prefix, set.name)
      end
    else
      lua_util.debugm(N, task, 'no ann defined in %s for settings id %s',
          rule.prefix, sid)
    end

    if ann then
      local vec = result_to_vector(task, profile)

      local score
      local out = ann:apply1(vec)
      score = out[1]

      local symscore = string.format('%.3f', score)
      lua_util.debugm(N, task, '%s:%s:%s ann score: %s',
          rule.prefix, set.name, set.ann.version, symscore)

      if score > 0 then
        local result = score
        task:insert_result(rule.symbol_spam, result, symscore)
      else
        local result = -(score)
        task:insert_result(rule.symbol_ham, result, symscore)
      end
    end
  end
end

local function create_ann(n, nlayers)
    -- We ignore number of layers so far when using kann
  local nhidden = math.floor((n + 1) / 2)
  local t = rspamd_kann.layer.input(n)
  t = rspamd_kann.transform.relu(t)
  t = rspamd_kann.transform.tanh(rspamd_kann.layer.dense(t, nhidden));
  t = rspamd_kann.layer.cost(t, 1, rspamd_kann.cost.mse)
  return rspamd_kann.new.kann(t)
end


local function ann_push_task_result(rule, task, verdict, score, set)
  local train_opts = rule.train
  local learn_spam, learn_ham
  local skip_reason = 'unknown'

  if train_opts.autotrain then
    if train_opts.spam_score then
      learn_spam = score >= train_opts.spam_score

      if not learn_spam then
        skip_reason = string.format('score < spam_score: %f < %f',
            score, train_opts.spam_score)
      end
    else
      learn_spam = verdict == 'spam' or verdict == 'junk'

      if not learn_spam then
        skip_reason = string.format('verdict: %s',
            verdict)
      end
    end

    if train_opts.ham_score then
      learn_ham = score <= train_opts.ham_score
      if not learn_ham then
        skip_reason = string.format('score > ham_score: %f > %f',
            score, train_opts.ham_score)
      end
    else
      learn_ham = verdict == 'ham'

      if not learn_ham then
        skip_reason = string.format('verdict: %s',
            verdict)
      end
    end
  else
    -- Train by request header
    local hdr = task:get_request_header('ANN-Train')

    if hdr then
      if hdr:lower() == 'spam' then
        learn_spam = true
      elseif hdr:lower() == 'ham' then
        learn_ham = true
      else
        skip_reason = string.format('no explicit header')
      end
    end
  end


  if learn_spam or learn_ham then
    local learn_type
    if learn_spam then learn_type = 'spam' else learn_type = 'ham' end

    local function can_train_cb(err, data)
      if not err and type(data) == 'table' then
        local nsamples,reason = tonumber(data[1]),data[2]

        if nsamples >= 0 then
          local coin = math.random()

          if coin < 1.0 - train_opts.train_prob then
            rspamd_logger.infox(task, 'probabilistically skip sample: %s', coin)
            return
          end

          local vec = result_to_vector(task, set)

          local str = rspamd_util.zstd_compress(table.concat(vec, ';'))
          local target_key = set.ann.redis_key .. '_' .. learn_type

          local function learn_vec_cb(_err)
            if _err then
              rspamd_logger.errx(task, 'cannot store train vector for %s:%s: %s',
                  rule.prefix, set.name, _err)
            else
              lua_util.debugm(N, task,
                  "add train data for ANN rule " ..
                      "%s:%s, save %s vector of %s elts in %s key; %s bytes compressed",
                  rule.prefix, set.name, learn_type, #vec, target_key, #str)
            end
          end

          lua_redis.redis_make_request(task,
              rule.redis,
              nil,
              true, -- is write
              learn_vec_cb, --callback
              'LPUSH', -- command
              { target_key, str } -- arguments
          )
        else
          -- Negative result returned
          rspamd_logger.infox(task, "cannot learn %s ANN %s:%s; redis_key: %s: %s (%s vectors stored)",
              learn_type, rule.prefix, set.name, set.ann.redis_key, reason, -tonumber(nsamples))
        end
      else
        if err then
          rspamd_logger.errx(task, 'cannot check if we can train %s:%s : %s',
              rule.prefix, set.name, err)
        else
          rspamd_logger.errx(task, 'cannot check if we can train %s:%s : type of Redis key %s is %s, expected table' ..
              'please remove this key from Redis manually if you perform upgrade from the previous version',
              rule.prefix, set.name, set.ann.redis_key, type(data))
        end
      end
    end

    -- Check if we can learn
    if set.can_store_vectors then
      if not set.ann then
        -- Need to create or load a profile corresponding to the current configuration
        set.ann = new_ann_profile(task, rule, set, 0)
        lua_util.debugm(N, task,
            'requested new profile for %s, set.ann is missing',
            set.name)
      end

      lua_redis.exec_redis_script(redis_can_store_train_vec_id,
          {task = task, is_write = true},
          can_train_cb,
          {
            set.ann.redis_key,
            learn_type,
            tostring(train_opts.max_trains),
            tostring(math.random()),
          })
    else
      lua_util.debugm(N, task,
          'do not push data: train condition not satisfied; reason: not checked existing ANNs')
    end
  else
    lua_util.debugm(N, task,
        'do not push data to key %s: train condition not satisfied; reason: %s',
        (set.ann or {}).redis_key,
        skip_reason)
  end
end

--- Offline training logic

-- Closure generator for unlock function
local function gen_unlock_cb(rule, set, ann_key)
  return function (err)
    if err then
      rspamd_logger.errx(rspamd_config, 'cannot unlock ANN %s:%s at %s from redis: %s',
          rule.prefix, set.name, ann_key, err)
    else
      lua_util.debugm(N, rspamd_config, 'unlocked ANN %s:%s at %s',
          rule.prefix, set.name, ann_key)
    end
  end
end

-- This function is intended to extend lock for ANN during training
-- It registers periodic that increases locked key each 30 seconds unless
-- `set.learning_spawned` is set to `true`
local function register_lock_extender(rule, set, ev_base, ann_key)
  rspamd_config:add_periodic(ev_base, 30.0,
      function()
        local function redis_lock_extend_cb(_err, _)
          if _err then
            rspamd_logger.errx(rspamd_config, 'cannot lock ANN %s from redis: %s',
                ann_key, _err)
          else
            rspamd_logger.infox(rspamd_config, 'extend lock for ANN %s for 30 seconds',
                ann_key)
          end
        end

        if set.learning_spawned then
          lua_redis.redis_make_request_taskless(ev_base,
              rspamd_config,
              rule.redis,
              nil,
              true, -- is write
              redis_lock_extend_cb, --callback
              'HINCRBY', -- command
              {ann_key, 'lock', '30'}
          )
        else
          lua_util.debugm(N, rspamd_config, "stop lock extension as learning_spawned is false")
          return false -- do not plan any more updates
        end

        return true
      end
  )
end

-- This function receives training vectors, checks them, spawn learning and saves ANN in Redis
local function spawn_train(worker, ev_base, rule, set, ann_key, ham_vec, spam_vec)
  -- Check training data sanity
  -- Now we need to join inputs and create the appropriate test vectors
  local n = #set.symbols +
      meta_functions.rspamd_count_metatokens()

  -- Now we can train ann
  local train_ann = create_ann(n, 3)

  if #ham_vec + #spam_vec < rule.train.max_trains / 2 then
    -- Invalidate ANN as it is definitely invalid
    -- TODO: add invalidation
    assert(false)
  else
    local inputs, outputs = {}, {}

    -- Used to show sparsed vectors in a convenient format (for debugging only)
    local function debug_vec(t)
      local ret = {}
      for i,v in ipairs(t) do
        if v ~= 0 then
          ret[#ret + 1] = string.format('%d=%.2f', i, v)
        end
      end

      return ret
    end

    -- Make training set by joining vectors
    -- KANN automatically shuffles those samples
    -- 1.0 is used for spam and -1.0 is used for ham
    -- It implies that output layer can express that (e.g. tanh output)
    for _,e in ipairs(spam_vec) do
      inputs[#inputs + 1] = e
      outputs[#outputs + 1] = {1.0}
      --rspamd_logger.debugm(N, rspamd_config, 'spam vector: %s', debug_vec(e))
    end
    for _,e in ipairs(ham_vec) do
      inputs[#inputs + 1] = e
      outputs[#outputs + 1] = {-1.0}
      --rspamd_logger.debugm(N, rspamd_config, 'ham vector: %s', debug_vec(e))
    end

    -- Called in child process
    local function train()
      local log_thresh = rule.train.max_iterations / 10
      local seen_nan = false

      local function train_cb(iter, train_cost, value_cost)
        if (iter * (rule.train.max_iterations / log_thresh)) % (rule.train.max_iterations) == 0 then
          if train_cost ~= train_cost and not seen_nan then
            -- We have nan :( try to log lot's of stuff to dig into a problem
            seen_nan = true
            rspamd_logger.errx(rspamd_config, 'ANN %s:%s: train error: observed nan in error cost!; value cost = %s',
                rule.prefix, set.name,
                value_cost)
            for i,e in ipairs(inputs) do
              lua_util.debugm(N, rspamd_config, 'train vector %s -> %s',
                  debug_vec(e), outputs[i][1])
            end
          end

          rspamd_logger.infox(rspamd_config,
              "ANN %s:%s: learned from %s redis key in %s iterations, error: %s, value cost: %s",
              rule.prefix, set.name,
              ann_key,
              iter,
              train_cost,
              value_cost)
        end
      end

      train_ann:train1(inputs, outputs, {
        lr = rule.train.learning_rate,
        max_epoch = rule.train.max_iterations,
        cb = train_cb,
      })

      if not seen_nan then
        local out = train_ann:save()
        return out
      else
        return nil
      end
    end

    set.learning_spawned = true

    local function redis_save_cb(err)
      if err then
        rspamd_logger.errx(rspamd_config, 'cannot save ANN %s:%s to redis key %s: %s',
            rule.prefix, set.name, ann_key, err)
        lua_redis.redis_make_request_taskless(ev_base,
            rspamd_config,
            rule.redis,
            nil,
            false, -- is write
            gen_unlock_cb(rule, set, ann_key), --callback
            'HDEL', -- command
            {ann_key, 'lock'}
        )
      else
        rspamd_logger.infox(rspamd_config, 'saved ANN %s:%s to redis: %s',
            rule.prefix, set.name, set.ann.redis_key)
      end
    end

    local function ann_trained(err, data)
      set.learning_spawned = false
      if err then
        rspamd_logger.errx(rspamd_config, 'cannot train ANN %s:%s : %s',
            rule.prefix, set.name, err)
        lua_redis.redis_make_request_taskless(ev_base,
            rspamd_config,
            rule.redis,
            nil,
            true, -- is write
            gen_unlock_cb(rule, set, ann_key), --callback
            'HDEL', -- command
            {ann_key, 'lock'}
        )
      else
        local ann_data = rspamd_util.zstd_compress(data)
        if not set.ann then
          set.ann = {
            symbols = set.symbols,
            distance = 0,
            digest = set.digest,
            redis_key = ann_key,
          }
        end
        -- Deserialise ANN from the child process
        ann_trained = rspamd_kann.load(data)
        local version = (set.ann.version or 0) + 1
        set.ann.version = version
        set.ann.ann = ann_trained
        set.ann.symbols = set.symbols
        set.ann.redis_key = new_ann_key(rule, set, version)

        local profile = {
          symbols = set.symbols,
          digest = set.digest,
          redis_key = set.ann.redis_key,
          version = version
        }

        local ucl = require "ucl"
        local profile_serialized = ucl.to_format(profile, 'json-compact', true)

        rspamd_logger.infox(rspamd_config,
            'trained ANN %s:%s, %s bytes; redis key: %s (old key %s)',
            rule.prefix, set.name, #data, set.ann.redis_key, ann_key)

        lua_redis.exec_redis_script(redis_save_unlock_id,
            {ev_base = ev_base, is_write = true},
            redis_save_cb,
            {profile.redis_key,
             redis_ann_prefix(rule, set.name),
             ann_data,
             profile_serialized,
             tostring(rule.ann_expire),
             tostring(os.time()),
             ann_key, -- old key to unlock...
            })
      end
    end

    worker:spawn_process{
      func = train,
      on_complete = ann_trained,
      proctitle = string.format("ANN train for %s/%s", rule.prefix, set.name),
    }
  end
  -- Spawn learn and register lock extension
  set.learning_spawned = true
  register_lock_extender(rule, set, ev_base, ann_key)
end

-- Utility to extract and split saved training vectors to a table of tables
local function process_training_vectors(data)
  return fun.totable(fun.map(function(tok)
    local _,str = rspamd_util.zstd_decompress(tok)
    return fun.totable(fun.map(tonumber, lua_util.str_split(tostring(str), ';')))
  end, data))
end

-- This function does the following:
-- * Tries to lock ANN
-- * Loads spam and ham vectors
-- * Spawn learning process
local function do_train_ann(worker, ev_base, rule, set, ann_key)
  local spam_elts = {}
  local ham_elts = {}

  local function redis_ham_cb(err, data)
    if err or type(data) ~= 'table' then
      rspamd_logger.errx(rspamd_config, 'cannot get ham tokens for ANN %s from redis: %s',
        ann_key, err)
      -- Unlock on error
      lua_redis.redis_make_request_taskless(ev_base,
        rspamd_config,
        rule.redis,
        nil,
        true, -- is write
          gen_unlock_cb(rule, set, ann_key), --callback
        'HDEL', -- command
        {ann_key, 'lock'}
      )
    else
      -- Decompress and convert to numbers each training vector
      ham_elts = process_training_vectors(data)
      spawn_train(worker, ev_base, rule, set, ann_key, ham_elts, spam_elts)
    end
  end

  -- Spam vectors received
  local function redis_spam_cb(err, data)
    if err or type(data) ~= 'table' then
      rspamd_logger.errx(rspamd_config, 'cannot get spam tokens for ANN %s from redis: %s',
        ann_key, err)
      -- Unlock ANN on error
      lua_redis.redis_make_request_taskless(ev_base,
        rspamd_config,
        rule.redis,
        nil,
        true, -- is write
          gen_unlock_cb(rule, set, ann_key), --callback
        'HDEL', -- command
        {ann_key, 'lock'}
      )
    else
      -- Decompress and convert to numbers each training vector
      spam_elts = process_training_vectors(data)
      -- Now get ham vectors...
      lua_redis.redis_make_request_taskless(ev_base,
        rspamd_config,
        rule.redis,
        nil,
        false, -- is write
        redis_ham_cb, --callback
        'LRANGE', -- command
        {ann_key .. '_ham', '0', '-1'}
      )
    end
  end

  local function redis_lock_cb(err, data)
    if err then
      rspamd_logger.errx(rspamd_config, 'cannot call lock script for ANN %s from redis: %s',
        ann_key, err)
    elseif type(data) == 'number' and data == 1 then
      -- ANN is locked, so we can extract SPAM and HAM vectors and spawn learning
      lua_redis.redis_make_request_taskless(ev_base,
        rspamd_config,
        rule.redis,
        nil,
        false, -- is write
        redis_spam_cb, --callback
        'LRANGE', -- command
        {ann_key .. '_spam', '0', '-1'}
      )

      rspamd_logger.infox(rspamd_config, 'lock ANN %s:%s (key name %s) for learning',
        rule.prefix, set.name, ann_key)
    else
      local lock_tm = tonumber(data[1])
      rspamd_logger.infox(rspamd_config, 'do not learn ANN %s:%s (key name %s), ' ..
          'locked by another host %s at %s', rule.prefix, set.name, ann_key,
          data[2], os.date('%c', lock_tm))
    end
  end

  -- Check if we are already learning this network
  if set.learning_spawned then
    rspamd_logger.infox(rspamd_config, 'do not learn ANN %s, already learning another ANN',
        ann_key)
    return
  end

  -- Call Redis script that tries to acquire a lock
  -- This script returns either a boolean or a pair {'lock_time', 'hostname'} when
  -- ANN is locked by another host (or a process, meh)
  lua_redis.exec_redis_script(redis_maybe_lock_id,
    {ev_base = ev_base, is_write = true},
    redis_lock_cb,
      {
        ann_key,
        tostring(os.time()),
        tostring(rule.watch_interval * 2),
        rspamd_util.get_hostname()
    })
end

-- This function loads new ann from Redis
-- This is based on `profile` attribute.
-- ANN is loaded from `profile.redis_key`
-- Rank of `profile` key is also increased, unfortunately, it means that we need to
-- serialize profile one more time and set its rank to the current time
-- set.ann fields are set according to Redis data received
local function load_new_ann(rule, ev_base, set, profile, min_diff)
  local ann_key = profile.redis_key

  local function data_cb(err, data)
    if err then
      rspamd_logger.errx(rspamd_config, 'cannot get ANN data from key: %s; %s',
          ann_key, err)
    else
      if type(data) == 'string' then
        local _err,ann_data = rspamd_util.zstd_decompress(data)
        local ann

        if _err or not ann_data then
          rspamd_logger.errx(rspamd_config, 'cannot decompress ANN for %s from Redis key %s: %s',
              rule.prefix .. ':' .. set.name, ann_key, _err)
          return
        else
          ann = rspamd_kann.load(ann_data)

          if ann then
            set.ann = {
              digest = profile.digest,
              version = profile.version,
              symbols = profile.symbols,
              distance = min_diff,
              redis_key = profile.redis_key
            }

            local ucl = require "ucl"
            local profile_serialized = ucl.to_format(profile, 'json-compact', true)
            set.ann.ann = ann -- To avoid serialization

            local function rank_cb(_, _)
              -- TODO: maybe add some logging
            end
            -- Also update rank for the loaded ANN to avoid removal
            lua_redis.redis_make_request_taskless(ev_base,
                rspamd_config,
                rule.redis,
                nil,
                true, -- is write
                rank_cb, --callback
                'ZADD', -- command
                {set.prefix, tostring(rspamd_util.get_time()), profile_serialized}
            )
            rspamd_logger.infox(rspamd_config, 'loaded ANN for %s:%s from %s; %s bytes compressed; version=%s',
                rule.prefix, set.name, ann_key, #ann_data, profile.version)
          else
            rspamd_logger.errx(rspamd_config, 'cannot deserialize ANN for %s:%s from Redis key %s',
                rule.prefix, set.name, ann_key)
          end
        end
      else
        lua_util.debugm(N, rspamd_config, 'no ANN for %s:%s in Redis key %s',
            rule.prefix, set.name, ann_key)
      end
    end
  end
  lua_redis.redis_make_request_taskless(ev_base,
      rspamd_config,
      rule.redis,
      nil,
      false, -- is write
      data_cb, --callback
      'HGET', -- command
      {ann_key, 'ann'} -- arguments
  )
end

-- Used to check an element in Redis serialized as JSON
-- for some specific rule + some specific setting
-- This function tries to load more fresh or more specific ANNs in lieu of
-- the existing ones.
-- Use this function to load ANNs as `callback` parameter for `check_anns` function
local function process_existing_ann(_, ev_base, rule, set, profiles)
  local my_symbols = set.symbols
  local min_diff = math.huge
  local sel_elt

  for _,elt in fun.iter(profiles) do
    if elt and elt.symbols then
      local dist = lua_util.distance_sorted(elt.symbols, my_symbols)
      -- Check distance
      if dist < #my_symbols * .3 then
        if dist < min_diff then
          min_diff = dist
          sel_elt = elt
        end
      end
    end
  end

  if sel_elt then
    -- We can load element from ANN
    if set.ann then
      -- We have an existing ANN, probably the same...
      if set.ann.digest == sel_elt.digest then
        -- Same ANN, check version
        if set.ann.version < sel_elt.version then
          -- Load new ann
          rspamd_logger.infox(rspamd_config, 'ann %s is changed, ' ..
              'our version = %s, remote version = %s',
              rule.prefix .. ':' .. set.name,
              set.ann.version,
              sel_elt.version)
          load_new_ann(rule, ev_base, set, sel_elt, min_diff)
        else
          lua_util.debugm(N, rspamd_config, 'ann %s is not changed, ' ..
              'our version = %s, remote version = %s',
              rule.prefix .. ':' .. set.name,
              set.ann.version,
              sel_elt.version)
        end
      else
        -- We have some different ANN, so we need to compare distance
        if set.ann.distance > min_diff then
          -- Load more specific ANN
          rspamd_logger.infox(rspamd_config, 'more specific ann is available for %s, ' ..
              'our distance = %s, remote distance = %s',
              rule.prefix .. ':' .. set.name,
              set.ann.distance,
              min_diff)
          load_new_ann(rule, ev_base, set, sel_elt, min_diff)
        else
          lua_util.debugm(N, rspamd_config, 'ann %s is not changed or less specific, ' ..
              'our distance = %s, remote distance = %s',
              rule.prefix .. ':' .. set.name,
              set.ann.distance,
              min_diff)
        end
      end
    else
      -- We have no ANN, load new one
      load_new_ann(rule, ev_base, set, sel_elt, min_diff)
    end
  end
end


-- This function checks all profiles and selects if we can train our
-- ANN. By our we mean that it has exactly the same symbols in profile.
-- Use this function to train ANN as `callback` parameter for `check_anns` function
local function maybe_train_existing_ann(worker, ev_base, rule, set, profiles)
  local my_symbols = set.symbols
  local sel_elt

  for _,elt in fun.iter(profiles) do
    if elt and elt.symbols then
      local dist = lua_util.distance_sorted(elt.symbols, my_symbols)
      -- Check distance
      if dist == 0 then
        sel_elt = elt
        break
      end
    end
  end

  if sel_elt then
    -- We have our ANN and that's train vectors, check if we can learn
    local ann_key = sel_elt.redis_key

    lua_util.debugm(N, rspamd_config, "check if ANN %s needs to be trained",
        ann_key)

    -- Create continuation closure
    local redis_len_cb_gen = function(cont_cb, what, is_final)
      return function(err, data)
        if err then
          rspamd_logger.errx(rspamd_config,
              'cannot get ANN %s trains %s from redis: %s', what, ann_key, err)
        elseif data and type(data) == 'number' or type(data) == 'string' then
          if tonumber(data) and tonumber(data) >= rule.train.max_trains then
            if is_final then
              rspamd_logger.debugm(N, rspamd_config,
                  'can start ANN %s learn as it has %s learn vectors; %s required, after checking %s vectors',
                  ann_key, tonumber(data), rule.train.max_trains, what)
            else
              rspamd_logger.debugm(N, rspamd_config,
                  'checked %s vectors in ANN %s: %s vectors; %s required, need to check other class vectors',
                  what, ann_key, tonumber(data), rule.train.max_trains)
            end
            cont_cb()
          else
            rspamd_logger.debugm(N, rspamd_config,
                'cannot learn ANN %s now: there are not enough %s learn vectors (has %s vectors; %s required)',
                ann_key, what, tonumber(data), rule.train.max_trains)
          end
        end
      end

    end

    local function initiate_train()
      rspamd_logger.infox(rspamd_config,
          'need to learn ANN %s after %s required learn vectors',
          ann_key, rule.train.max_trains)
      do_train_ann(worker, ev_base, rule, set, ann_key)
    end

    -- Spam vector is OK, check ham vector length
    local function check_ham_len()
      lua_redis.redis_make_request_taskless(ev_base,
          rspamd_config,
          rule.redis,
          nil,
          false, -- is write
          redis_len_cb_gen(initiate_train, 'ham', true), --callback
          'LLEN', -- command
          {ann_key .. '_ham'}
      )
    end

    lua_redis.redis_make_request_taskless(ev_base,
        rspamd_config,
        rule.redis,
        nil,
        false, -- is write
        redis_len_cb_gen(check_ham_len, 'spam', false), --callback
        'LLEN', -- command
        {ann_key .. '_spam'}
    )
  end
end

-- Used to deserialise ANN element from a list
local function load_ann_profile(element)
  local ucl = require "ucl"

  local parser = ucl.parser()
  local res,ucl_err = parser:parse_string(element)
  if not res then
    rspamd_logger.warnx(rspamd_config, 'cannot parse ANN from redis: %s',
        ucl_err)
    return nil
  else
    local profile = parser:get_object()
    local checked,schema_err = redis_profile_schema:transform(profile)
    if not checked then
      rspamd_logger.errx(rspamd_config, "cannot parse profile schema: %s", schema_err)

      return nil
    end
    return checked
  end
end

-- Function to check or load ANNs from Redis
local function check_anns(worker, cfg, ev_base, rule, process_callback, what)
  for _,set in pairs(rule.settings) do
    local function members_cb(err, data)
      if err then
        rspamd_logger.errx(cfg, 'cannot get ANNs list from redis: %s',
            err)
        set.can_store_vectors = true
      elseif type(data) == 'table' then
        lua_util.debugm(N, cfg, '%s: process element %s:%s',
            what, rule.prefix, set.name)
        process_callback(worker, ev_base, rule, set, fun.map(load_ann_profile, data))
        set.can_store_vectors = true
      end
    end

    if type(set) == 'table' then
      -- Extract all profiles for some specific settings id
      -- Get the last `max_profiles` recently used
      -- Select the most appropriate to our profile but it should not differ by more
      -- than 30% of symbols
      lua_redis.redis_make_request_taskless(ev_base,
          cfg,
          rule.redis,
          nil,
          false, -- is write
          members_cb, --callback
          'ZREVRANGE', -- command
          {set.prefix, '0', tostring(settings.max_profiles)} -- arguments
      )
    end
  end -- Cycle over all settings

  return rule.watch_interval
end

-- Function to clean up old ANNs
local function cleanup_anns(rule, cfg, ev_base)
  for _,set in pairs(rule.settings) do
    local function invalidate_cb(err, data)
      if err then
        rspamd_logger.errx(cfg, 'cannot exec invalidate script in redis: %s',
            err)
      elseif type(data) == 'table' then
        for _,expired in ipairs(data) do
          local profile = load_ann_profile(expired)
          rspamd_logger.infox(cfg, 'invalidated ANN for %s; redis key: %s; version=%s',
              rule.prefix .. ':' .. set.name,
              profile.redis_key,
              profile.version)
        end
      end
    end

    if type(set) == 'table' then
      lua_redis.exec_redis_script(redis_maybe_invalidate_id,
          {ev_base = ev_base, is_write = true},
          invalidate_cb,
          {set.prefix, tostring(settings.max_profiles)})
    end
  end
end

local function ann_push_vector(task)
  if task:has_flag('skip') then
    lua_util.debugm(N, task, 'do not push data for skipped task')
    return
  end
  if not settings.allow_local and lua_util.is_rspamc_or_controller(task) then
    lua_util.debugm(N, task, 'do not push data for manual scan')
    return
  end

  local verdict,score = lua_verdict.get_specific_verdict(N, task)

  if verdict == 'passthrough' then
    lua_util.debugm(N, task, 'ignore task as its verdict is %s(%s)',
        verdict, score)

    return
  end

  if score ~= score then
    lua_util.debugm(N, task, 'ignore task as its score is nan (%s verdict)',
        verdict)

    return
  end

  for _,rule in pairs(settings.rules) do
    local set = get_rule_settings(task, rule)

    if set then
      ann_push_task_result(rule, task, verdict, score, set)
    else
      lua_util.debugm(N, task, 'settings not found in rule %s', rule.prefix)
    end

  end
end


-- This function is used to adjust profiles and allowed setting ids for each rule
-- It must be called when all settings are already registered (e.g. at post-init for config)
local function process_rules_settings()
  local function process_settings_elt(rule, selt)
    local profile = rule.profile[selt.name]
    if profile then
      -- Use static user defined profile
      -- Ensure that we have an array...
      lua_util.debugm(N, rspamd_config, "use static profile for %s (%s): %s",
          rule.prefix, selt.name, profile)
      if not profile[1] then profile = lua_util.keys(profile) end
      selt.symbols = profile
    else
      lua_util.debugm(N, rspamd_config, "use dynamic cfg based profile for %s (%s)",
          rule.prefix, selt.name)
    end

    local function filter_symbols_predicate(sname)
      local fl = rspamd_config:get_symbol_flags(sname)
      if fl then
        fl = lua_util.list_to_hash(fl)

        return not (fl.nostat or fl.idempotent or fl.skip)
      end

      return false
    end

    -- Generic stuff
    table.sort(fun.totable(fun.filter(filter_symbols_predicate, selt.symbols)))

    selt.digest = lua_util.table_digest(selt.symbols)
    selt.prefix = redis_ann_prefix(rule, selt.name)

    lua_redis.register_prefix(selt.prefix, N,
        string.format('NN prefix for rule "%s"; settings id "%s"',
            rule.prefix, selt.name), {
          persistent = true,
          type = 'zlist',
        })
    -- Versions
    lua_redis.register_prefix(selt.prefix .. '_\\d+', N,
        string.format('NN storage for rule "%s"; settings id "%s"',
            rule.prefix, selt.name), {
          persistent = true,
          type = 'hash',
        })
    lua_redis.register_prefix(selt.prefix .. '_\\d+_spam', N,
        string.format('NN learning set (spam) for rule "%s"; settings id "%s"',
            rule.prefix, selt.name), {
          persistent = true,
          type = 'list',
        })
    lua_redis.register_prefix(selt.prefix .. '_\\d+_ham', N,
        string.format('NN learning set (spam) for rule "%s"; settings id "%s"',
            rule.prefix, selt.name), {
          persistent = true,
          type = 'list',
        })
  end

  for k,rule in pairs(settings.rules) do
    if not rule.allowed_settings then
      rule.allowed_settings = {}
    elseif rule.allowed_settings == 'all' then
      -- Extract all settings ids
      rule.allowed_settings = lua_util.keys(lua_settings.all_settings())
    end

    -- Convert to a map <setting_id> -> true
    rule.allowed_settings = lua_util.list_to_hash(rule.allowed_settings)

    -- Check if we can work without settings
    if k == 'default' or type(rule.default) ~= 'boolean' then
      rule.default = true
    end

    rule.settings = {}

    if rule.default then
      local default_settings = {
        symbols = lua_settings.default_symbols(),
        name = 'default'
      }

      process_settings_elt(rule, default_settings)
      rule.settings[-1] = default_settings -- Magic constant, but OK as settings are positive int32
    end

    -- Now, for each allowed settings, we store sorted symbols + digest
    -- We set table rule.settings[id] -> { name = name, symbols = symbols, digest = digest }
    for s,_ in pairs(rule.allowed_settings) do
      -- Here, we have a name, set of symbols and
      local settings_id = s
      if type(settings_id) ~= 'number' then
        settings_id = lua_settings.numeric_settings_id(s)
      end
      local selt = lua_settings.settings_by_id(settings_id)

      local nelt = {
        symbols = selt.symbols, -- Already sorted
        name = selt.name
      }

      process_settings_elt(rule, nelt)
      for id,ex in pairs(rule.settings) do
        if type(ex) == 'table' then
          if nelt and lua_util.distance_sorted(ex.symbols, nelt.symbols) == 0 then
            -- Equal symbols, add reference
            lua_util.debugm(N, rspamd_config,
                'added reference from settings id %s to %s; same symbols',
                nelt.name, ex.name)
            rule.settings[settings_id] = id
            nelt = nil
          end
        end
      end

      if nelt then
        rule.settings[settings_id] = nelt
        lua_util.debugm(N, rspamd_config, 'added new settings id %s(%s) to %s',
            nelt.name, settings_id, rule.prefix)
      end
    end
  end
end

redis_params = lua_redis.parse_redis_server('neural')

if not redis_params then
  redis_params = lua_redis.parse_redis_server('fann_redis')
end

-- Initialization part
if not (module_config and type(module_config) == 'table') or not redis_params then
  rspamd_logger.infox(rspamd_config, 'Module is unconfigured')
  lua_util.disable_module(N, "redis")
  return
end

local rules = module_config['rules']

if not rules then
  -- Use legacy configuration
  rules = {}
  rules['default'] = module_config
end

local id = rspamd_config:register_symbol({
  name = 'NEURAL_CHECK',
  type = 'postfilter,nostat',
  priority = 6,
  callback = ann_scores_filter
})

settings = lua_util.override_defaults(settings, module_config)
settings.rules = {} -- Reset unless validated further in the cycle

-- Check all rules
for k,r in pairs(rules) do
  local rule_elt = lua_util.override_defaults(default_options, r)
  rule_elt['redis'] = redis_params
  rule_elt['anns'] = {} -- Store ANNs here

  if not rule_elt.prefix then
    rule_elt.prefix = k
  end
  if not rule_elt.name then
    rule_elt.name = k
  end
  if rule_elt.train.max_train then
    rule_elt.train.max_trains = rule_elt.train.max_train
  end

  if not rule_elt.profile then rule_elt.profile = {} end

  rspamd_logger.infox(rspamd_config, "register ann rule %s", k)
  settings.rules[k] = rule_elt
  rspamd_config:set_metric_symbol({
    name = rule_elt.symbol_spam,
    score = 0.0,
    description = 'Neural network SPAM',
    group = 'neural'
  })
  rspamd_config:register_symbol({
    name = rule_elt.symbol_spam,
    type = 'virtual,nostat',
    parent = id
  })

  rspamd_config:set_metric_symbol({
    name = rule_elt.symbol_ham,
    score = -0.0,
    description = 'Neural network HAM',
    group = 'neural'
  })
  rspamd_config:register_symbol({
    name = rule_elt.symbol_ham,
    type = 'virtual,nostat',
    parent = id
  })
end

rspamd_config:register_symbol({
  name = 'NEURAL_LEARN',
  type = 'idempotent,nostat,explicit_disable',
  priority = 5,
  callback = ann_push_vector
})

-- Add training scripts
for _,rule in pairs(settings.rules) do
  load_scripts(rule.redis)
  -- We also need to deal with settings
  rspamd_config:add_post_init(process_rules_settings)
  -- This function will check ANNs in Redis when a worker is loaded
  rspamd_config:add_on_load(function(cfg, ev_base, worker)
    if worker:is_scanner() then
      rspamd_config:add_periodic(ev_base, 0.0,
          function(_, _)
            return check_anns(worker, cfg, ev_base, rule, process_existing_ann,
                'try_load_ann')
          end)
    end

    if worker:is_primary_controller() then
      -- We also want to train neural nets when they have enough data
      rspamd_config:add_periodic(ev_base, 0.0,
          function(_, _)
            -- Clean old ANNs
            cleanup_anns(rule, cfg, ev_base)
            return check_anns(worker, cfg, ev_base, rule, maybe_train_existing_ann,
                'try_train_ann')
          end)
    end
  end)
end
