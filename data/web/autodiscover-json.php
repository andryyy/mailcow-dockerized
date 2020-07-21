<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/vars.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.inc.php';
$default_autodiscover_config = $autodiscover_config;
if(file_exists('inc/vars.local.inc.php')) {
  include_once 'inc/vars.local.inc.php';
}
$autodiscover_config = array_merge($default_autodiscover_config, $autodiscover_config);

header('Content-type: application/json');
if (strtolower($_GET['Protocol']) == 'activesync' && getenv('SKIP_SOGO') != "y") {
  echo '{"Protocol":"ActiveSync","Url":"' . $autodiscover_config['activesync']['url'] . '"}';
}
elseif (strtolower($_GET['Protocol']) == 'autodiscoverv1') {
  echo '{"Protocol":"AutodiscoverV1","Url":"https://' . $_SERVER['HTTP_HOST'] . '/Autodiscover/Autodiscover.xml"}';
}
else {
  http_response_code(400);
  echo '{"ErrorCode":"InvalidProtocol","ErrorMessage":"The given protocol value \u0027' . $_GET['Protocol'] . '\u0027 is invalid. Supported values are \u0027ActiveSync,AutodiscoverV1\u0027"}';
}
?>
