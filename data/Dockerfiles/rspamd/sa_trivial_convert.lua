local fun = require "fun"
local rspamd_logger = require "rspamd_logger"
local util = require "rspamd_util"
local lua_util = require "lua_util"
local rspamd_regexp = require "rspamd_regexp"
local ucl = require "ucl"

local complicated = {}
local rules = {}
local scores = {}

local function words_to_re(words, start)
  return table.concat(fun.totable(fun.drop_n(start, words)), " ");
end

local function split(str, delim)
  local result = {}

  if not delim then
    delim = '[^%s]+'
  end

  for token in string.gmatch(str, delim) do
    table.insert(result, token)
  end

  return result
end

local function handle_header_def(hline, cur_rule)
  --Now check for modifiers inside header's name
  local hdrs = split(hline, '[^|]+')
  local hdr_params = {}
  local cur_param = {}
  -- Check if an re is an ordinary re
  local ordinary = true

  for _,h in ipairs(hdrs) do
    if h == 'ALL' or h == 'ALL:raw' then
      ordinary = false
    else
      local args = split(h, '[^:]+')
      cur_param['strong'] = false
      cur_param['raw'] = false
      cur_param['header'] = args[1]

      if args[2] then
        -- We have some ops that are required for the header, so it's not ordinary
        ordinary = false
      end

      fun.each(function(func)
          if func == 'addr' then
            cur_param['function'] = function(str)
              local addr_parsed = util.parse_addr(str)
              local ret = {}
              if addr_parsed then
                for _,elt in ipairs(addr_parsed) do
                  if elt['addr'] then
                    table.insert(ret, elt['addr'])
                  end
                end
              end

              return ret
            end
          elseif func == 'name' then
            cur_param['function'] = function(str)
              local addr_parsed = util.parse_addr(str)
              local ret = {}
              if addr_parsed then
                for _,elt in ipairs(addr_parsed) do
                  if elt['name'] then
                    table.insert(ret, elt['name'])
                  end
                end
              end

              return ret
            end
          elseif func == 'raw' then
            cur_param['raw'] = true
          elseif func == 'case' then
            cur_param['strong'] = true
          else
            rspamd_logger.warnx(rspamd_config, 'Function %1 is not supported in %2',
              func, cur_rule['symbol'])
          end
        end, fun.tail(args))

        -- Some header rules require splitting to check of multiple headers
        if cur_param['header'] == 'MESSAGEID' then
          -- Special case for spamassassin
          ordinary = false
        elseif cur_param['header'] == 'ToCc' then
          ordinary = false
        else
          table.insert(hdr_params, cur_param)
        end
    end

    cur_rule['ordinary'] = ordinary and (not (#hdr_params > 1))
    cur_rule['header'] = hdr_params
  end
end

local function process_sa_conf(f)
  local cur_rule = {}
  local valid_rule = false

  local function insert_cur_rule()
   if not rules[cur_rule.type] then
     rules[cur_rule.type] = {}
   end

   local target = rules[cur_rule.type]

   if cur_rule.type == 'header' then
     if not cur_rule.header[1].header then
      rspamd_logger.errx(rspamd_config, 'bad rule definition: %1', cur_rule)
      return
     end
     if not target[cur_rule.header[1].header] then
       target[cur_rule.header[1].header] = {}
     end
     target = target[cur_rule.header[1].header]
   end

   if not cur_rule['symbol'] then
     rspamd_logger.errx(rspamd_config, 'bad rule definition: %1', cur_rule)
     return
   end
   target[cur_rule['symbol']] = cur_rule
   cur_rule = {}
   valid_rule = false
  end

  local function parse_score(words)
    if #words == 3 then
      -- score rule <x>
      return tonumber(words[3])
    elseif #words == 6 then
      -- score rule <x1> <x2> <x3> <x4>
      -- we assume here that bayes and network are enabled and select <x4>
      return tonumber(words[6])
    else
      rspamd_logger.errx(rspamd_config, 'invalid score for %1', words[2])
    end

    return 0
  end

  local skip_to_endif = false
  local if_nested = 0
  for l in f:lines() do
    (function ()
    l = lua_util.rspamd_str_trim(l)
    -- Replace bla=~/re/ with bla =~ /re/ (#2372)
    l = l:gsub('([^%s])%s*([=!]~)%s*([^%s])', '%1 %2 %3')

    if string.len(l) == 0 or string.sub(l, 1, 1) == '#' then
      return
    end

    -- Unbalanced if/endif
    if if_nested < 0 then if_nested = 0 end
    if skip_to_endif then
      if string.match(l, '^endif') then
        if_nested = if_nested - 1

        if if_nested == 0 then
          skip_to_endif = false
        end
      elseif string.match(l, '^if') then
        if_nested = if_nested + 1
      elseif string.match(l, '^else') then
        -- Else counterpart for if
        skip_to_endif = false
      end
      table.insert(complicated, l)
      return
    else
      if string.match(l, '^ifplugin') then
        skip_to_endif = true
        if_nested = if_nested + 1
        table.insert(complicated, l)
      elseif string.match(l, '^if !plugin%(') then
         skip_to_endif = true
         if_nested = if_nested + 1
        table.insert(complicated, l)
      elseif string.match(l, '^if') then
        -- Unknown if
        skip_to_endif = true
        if_nested = if_nested + 1
        table.insert(complicated, l)
      elseif string.match(l, '^else') then
        -- Else counterpart for if
        skip_to_endif = true
        table.insert(complicated, l)
      elseif string.match(l, '^endif') then
        if_nested = if_nested - 1
        table.insert(complicated, l)
      end
    end

    -- Skip comments
    local words = fun.totable(fun.take_while(
      function(w) return string.sub(w, 1, 1) ~= '#' end,
      fun.filter(function(w)
          return w ~= "" end,
      fun.iter(split(l)))))

    if words[1] == "header" then
      -- header SYMBOL Header ~= /regexp/
      if valid_rule then
        insert_cur_rule()
      end
      if words[4] and (words[4] == '=~' or words[4] == '!~') then
        cur_rule['type'] = 'header'
        cur_rule['symbol'] = words[2]

        if words[4] == '!~' then
          table.insert(complicated, l)
          return
        end

        cur_rule['re_expr'] = words_to_re(words, 4)
        local unset_comp = string.find(cur_rule['re_expr'], '%s+%[if%-unset:')
        if unset_comp then
          table.insert(complicated, l)
          return
        end

        cur_rule['re'] = rspamd_regexp.create(cur_rule['re_expr'])

        if not cur_rule['re'] then
          rspamd_logger.warnx(rspamd_config, "Cannot parse regexp '%1' for %2",
            cur_rule['re_expr'], cur_rule['symbol'])
          table.insert(complicated, l)
          return
        else
          handle_header_def(words[3], cur_rule)
          if not cur_rule['ordinary'] then
            table.insert(complicated, l)
            return
          end
        end

        valid_rule = true
      else
        table.insert(complicated, l)
        return
      end
    elseif words[1] == "body" then
      -- body SYMBOL /regexp/
      if valid_rule then
        insert_cur_rule()
      end

      cur_rule['symbol'] = words[2]
      if words[3] and (string.sub(words[3], 1, 1) == '/'
          or string.sub(words[3], 1, 1) == 'm') then
        cur_rule['type'] = 'sabody'
        cur_rule['re_expr'] = words_to_re(words, 2)
        cur_rule['re'] = rspamd_regexp.create(cur_rule['re_expr'])
        if cur_rule['re'] then

          valid_rule = true
        end
      else
        -- might be function
        table.insert(complicated, l)
        return
      end
    elseif words[1] == "rawbody" then
      -- body SYMBOL /regexp/
      if valid_rule then
        insert_cur_rule()
      end

      cur_rule['symbol'] = words[2]
      if words[3] and (string.sub(words[3], 1, 1) == '/'
          or string.sub(words[3], 1, 1) == 'm') then
        cur_rule['type'] = 'sarawbody'
        cur_rule['re_expr'] = words_to_re(words, 2)
        cur_rule['re'] = rspamd_regexp.create(cur_rule['re_expr'])
        if cur_rule['re'] then
          valid_rule = true
        end
      else
        table.insert(complicated, l)
        return
      end
    elseif words[1] == "full" then
      -- body SYMBOL /regexp/
      if valid_rule then
        insert_cur_rule()
      end

      cur_rule['symbol'] = words[2]

      if words[3] and (string.sub(words[3], 1, 1) == '/'
          or string.sub(words[3], 1, 1) == 'm') then
        cur_rule['type'] = 'message'
        cur_rule['re_expr'] = words_to_re(words, 2)
        cur_rule['re'] = rspamd_regexp.create(cur_rule['re_expr'])
        cur_rule['raw'] = true
        if cur_rule['re'] then
          valid_rule = true
        end
      else
        table.insert(complicated, l)
        return
      end
    elseif words[1] == "uri" then
      -- uri SYMBOL /regexp/
      if valid_rule then
        insert_cur_rule()
      end
      cur_rule['type'] = 'uri'
      cur_rule['symbol'] = words[2]
      cur_rule['re_expr'] = words_to_re(words, 2)
      cur_rule['re'] = rspamd_regexp.create(cur_rule['re_expr'])
      if cur_rule['re'] and cur_rule['symbol'] then
        valid_rule = true
      else
        table.insert(complicated, l)
        return
      end
    elseif words[1] == "meta" then
      -- meta SYMBOL expression
      if valid_rule then
        insert_cur_rule()
      end
      table.insert(complicated, l)
      return
    elseif words[1] == "describe" and valid_rule then
      cur_rule['description'] = words_to_re(words, 2)
    elseif words[1] == "score" then
      scores[words[2]] = parse_score(words)
    else
      table.insert(complicated, l)
      return
    end
    end)()
  end
  if valid_rule then
    insert_cur_rule()
  end
end

for _,matched in ipairs(arg) do
  local f = io.open(matched, "r")
  if f then
    rspamd_logger.messagex(rspamd_config, 'loading SA rules from %s', matched)
    process_sa_conf(f)
  else
    rspamd_logger.errx(rspamd_config, "cannot open %1", matched)
  end
end

local multimap_conf = {}

local function handle_rule(what, syms, hdr)
  local mtype
  local filter
  local fname
  local header
  local sym = what:upper()
  if what == 'sabody' then
    mtype = 'content'
    fname = 'body_re.map'
    filter = 'oneline'
  elseif what == 'sarawbody' then
    fname = 'raw_body_re.map'
    mtype = 'content'
    filter = 'rawtext'
  elseif what == 'full' then
    fname = 'full_re.map'
    mtype = 'content'
    filter = 'full'
  elseif what == 'uri' then
    fname = 'uri_re.map'
    mtype = 'url'
    filter = 'full'
  elseif what == 'header' then
    fname = ('hdr_' .. hdr .. '_re.map'):lower()
    mtype = 'header'
    header = hdr
    sym = sym .. '_' .. hdr:upper()
  else
    rspamd_logger.errx('unknown type: %s', what)
    return
  end
  local conf = {
    type = mtype,
    filter = filter,
    symbol = 'SA_MAP_AUTO_' .. sym,
    regexp = true,
    map = fname,
    header = header,
    symbols = {}
  }
  local re_file = io.open(fname, 'w')

  for k,r in pairs(syms) do
    local score = 0.0
    if scores[k] then
      score = scores[k]
    end
    re_file:write(string.format('/%s/ %s:%f\n', tostring(r.re), k, score))
    table.insert(conf.symbols, k)
  end

  re_file:close()

  multimap_conf[sym:lower()] = conf
  rspamd_logger.messagex('stored %s regexp in %s', sym:lower(), fname)
end

for k,v in pairs(rules) do
  if k == 'header' then
    for h,r in pairs(v) do
      handle_rule(k, r, h)
    end
  else
    handle_rule(k, v)
  end
end

local out = ucl.to_format(multimap_conf, 'ucl')
local mmap_conf = io.open('auto_multimap.conf', 'w')
mmap_conf:write(out)
mmap_conf:close()
rspamd_logger.messagex('stored multimap conf in %s', 'auto_multimap.conf')

local sa_remain = io.open('auto_sa.conf', 'w')
fun.each(function(l) 
  sa_remain:write(l)
  sa_remain:write('\n')
end, fun.filter(function(l) return not string.match(l, '^%s+$') end, complicated))
sa_remain:close()
rspamd_logger.messagex('stored sa remains conf in %s', 'auto_sa.conf')
