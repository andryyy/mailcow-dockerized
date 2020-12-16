-- Thanks to https://raw.githubusercontent.com/fatalbanana

local lua_maps = require 'lua_maps'
local rspamd_regexp = require 'rspamd_regexp'
local rspamd_util = require 'rspamd_util'

local ivm_sendgrid_ids = lua_maps.map_add_from_ucl(
  'https://www.invaluement.com/spdata/sendgrid-id-dnsbl.txt',
  'set',
  'Invaluement Service Provider DNSBL: Sendgrid IDs'
)

local ivm_sendgrid_envfromdomains = lua_maps.map_add_from_ucl(
  'https://www.invaluement.com/spdata/sendgrid-envelopefromdomain-dnsbl.txt',
  'set',
  'Invaluement Service Provider DNSBL: Sendgrid envelope domains'
)

local cb_id = rspamd_config:register_symbol({
  name = 'IVM_SENDGRID',
  callback = function(task)
    -- Is it Sendgrid?
    local sg_hdr = task:get_header('X-SG-EID')
    if not sg_hdr then return end

    -- Get original envelope from
    local env_from = task:get_from{'smtp', 'orig'}
    if not env_from then return end

    -- Check normalised domain in domains list
    if ivm_sendgrid_envfromdomains and ivm_sendgrid_envfromdomains:get_key(rspamd_util.get_tld(env_from[1].domain)) then
      task:insert_result('IVM_SENDGRID_DOMAIN', 1.0)
    end

    -- Check ID in ID list
    local lp_re = rspamd_regexp.create_cached([[^bounces\+(\d+)-]])
    local res = lp_re:search(env_from[1].user, true, true)
    if not res then return end
    if ivm_sendgrid_ids and ivm_sendgrid_ids:get_key(res[1][2]) then
      task:insert_result('IVM_SENDGRID_ID', 1.0)
    end
  end,
  description = 'Invaluement Service Provider DNSBL: Sendgrid',
  type = 'callback',
})

rspamd_config:register_symbol({
  name = 'IVM_SENDGRID_DOMAIN',
  parent = cb_id,
  group = 'ivmspdnsbl',
  score = 8.0,
  type = 'virtual',
})

rspamd_config:register_symbol({
  name = 'IVM_SENDGRID_ID',
  parent = cb_id,
  group = 'ivmspdnsbl',
  score = 8.0,
  type = 'virtual',
})
