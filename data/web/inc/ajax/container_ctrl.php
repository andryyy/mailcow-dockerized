<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';
if (!isset($_SESSION['mailcow_cc_role']) || $_SESSION['mailcow_cc_role'] != 'admin') {
	exit();
}

if (preg_match('/^[a-z\-]{0,}-mailcow/', $_GET['service'])) {
  if ($_GET['action'] == "start") {
    header('Content-Type: text/html; charset=utf-8');
    $retry = 0;
    while (docker('info', $_GET['service'])['State']['Running'] != 1 && $retry <= 3) {
      $response = docker('post', $_GET['service'], 'start');
      $response = json_decode($response, true);
      $last_response = ($response['type'] == "success") ? '<b><span class="pull-right text-success">OK</span></b>' : '<b><span class="pull-right text-danger">Error: ' . $response['msg'] . '</span></b>';
      if ($response['type'] == "success") {
        break;
      }
      usleep(1500000);
      $retry++;
    }
    echo (!isset($last_response)) ? '<b><span class="pull-right text-warning">Already running</span></b>' : $last_response;
  }
  if ($_GET['action'] == "stop") {
    header('Content-Type: text/html; charset=utf-8');
    $retry = 0;
    while (docker('info', $_GET['service'])['State']['Running'] == 1 && $retry <= 3) {
      $response = docker('post', $_GET['service'], 'stop');
      $response = json_decode($response, true);
      $last_response = ($response['type'] == "success") ? '<b><span class="pull-right text-success">OK</span></b>' : '<b><span class="pull-right text-danger">Error: ' . $response['msg'] . '</span></b>';
      if ($response['type'] == "success") {
        break;
      }
      usleep(1500000);
      $retry++;
    }
    echo (!isset($last_response)) ? '<b><span class="pull-right text-warning">Not running</span></b>' : $last_response;
  }
  if ($_GET['action'] == "restart") {
    header('Content-Type: text/html; charset=utf-8');
    $response = docker('post', $_GET['service'], 'restart');
    $response = json_decode($response, true);
    $last_response = ($response['type'] == "success") ? '<b><span class="pull-right text-success">OK</span></b>' : '<b><span class="pull-right text-danger">Error: ' . $response['msg'] . '</span></b>';
    echo (!isset($last_response)) ? '<b><span class="pull-right text-warning">Cannot restart container</span></b>' : $last_response;
  }
  if ($_GET['action'] == "logs") {
    $lines = (empty($_GET['lines']) || !is_numeric($_GET['lines'])) ? 1000 : $_GET['lines'];
    header('Content-Type: text/plain; charset=utf-8');
    print_r(preg_split('/\n/', docker('logs', $_GET['service'], $lines)));
  }
}

?>
