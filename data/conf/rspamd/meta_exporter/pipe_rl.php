<?php
// File size is limited by Nginx site to 10M
// To speed things up, we do not include prerequisites
header('Content-Type: text/plain');
require_once "vars.inc.php";
// Do not show errors, we log to using error_log
ini_set('error_reporting', 0);
// Init Redis
$redis = new Redis();
try {
  if (!empty(getenv('REDIS_SLAVEOF_IP'))) {
    $redis->connect(getenv('REDIS_SLAVEOF_IP'), getenv('REDIS_SLAVEOF_PORT'));
  }
  else {
    $redis->connect('redis-mailcow', 6379);
  }
}
catch (Exception $e) {
  exit;
}

$raw_data_content = file_get_contents('php://input');
$raw_data_decoded = json_decode($raw_data_content, true);

$data['time'] = time();
$data['rcpt'] = implode(', ', $raw_data_decoded['rcpt']);
$data['from'] = $raw_data_decoded['from'];
$data['user'] = $raw_data_decoded['user'];
$symbol_rl_key = array_search('RATELIMITED', array_column($raw_data_decoded['symbols'], 'name'));
$data['rl_info'] = implode($raw_data_decoded['symbols'][$symbol_rl_key]['options']);
preg_match('/(.+)\((.+)\)/i', $data['rl_info'], $rl_matches);
if (!empty($rl_matches[1]) && !empty($rl_matches[2])) {
  $data['rl_name'] = $rl_matches[1];
  $data['rl_hash'] = $rl_matches[2];
}
else {
  $data['rl_name'] = 'err';
  $data['rl_hash'] = 'err';
}
$data['qid'] = $raw_data_decoded['qid'];
$data['ip'] = $raw_data_decoded['ip'];
$data['message_id'] = $raw_data_decoded['message_id'];
$data['header_subject'] = implode(' ', $raw_data_decoded['header_subject']);
$data['header_from'] = implode(', ', $raw_data_decoded['header_from']);

$redis->lpush('RL_LOG', json_encode($data));
exit;

