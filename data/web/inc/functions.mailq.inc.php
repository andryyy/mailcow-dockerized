<?php
function mailq($_action, $_data = null) {
  if ($_SESSION['mailcow_cc_role'] != "admin") {
    $_SESSION['return'][] = array(
      'type' => 'danger',
      'log' => array(__FUNCTION__, $_action, $_data),
      'msg' => 'access_denied'
    );
    return false;
  }
  function process_mailq_output($returned_output, $_action, $_data) {
    if ($returned_output !== NULL) {
      if ($_action == 'cat') {
        logger(array('return' => array(
          array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_data),
            'msg' => 'queue_cat_success'
          )
        )));
        return $returned_output;
      }
      else {
        if (isset($returned_output['type']) && $returned_output['type'] == 'danger') {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data),
            'msg' => 'Error: ' . $returned_output['msg']
          );
        }
        if (isset($returned_output['type']) && $returned_output['type'] == 'success') {
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_data),
            'msg' => 'queue_command_success'
          );
        }
      }
    }
    else {
      $_SESSION['return'][] = array(
        'type' => 'danger',
        'log' => array(__FUNCTION__, $_action, $_data),
        'msg' => 'unknown'
      );
    }
  }
  if ($_action == 'get') {
    $mailq_lines = docker('post', 'postfix-mailcow', 'exec', array('cmd' => 'mailq', 'task' => 'list'));
    $lines = 0;
    // Hard limit to 10000 items
    foreach (preg_split("/((\r?\n)|(\r\n?))/", $mailq_lines) as $mailq_item) if ($lines++ < 10000) {
      if (empty($mailq_item) || $mailq_item == '1') {
        continue;
      }
      $mq_line = json_decode($mailq_item, true);
      if ($mq_line !== NULL) {
        $rcpts = array();
        foreach ($mq_line['recipients'] as $rcpt) {
          if (isset($rcpt['delay_reason'])) {
            $rcpts[] = $rcpt['address'] . ' (' . $rcpt['delay_reason'] . ')';
          }
          else {
            $rcpts[] = $rcpt['address'];
          }
        }
        if (!empty($rcpts)) {
          $mq_line['recipients'] = $rcpts;
        }
        $line[] = $mq_line;
      }
    }
    if (!isset($line) || empty($line)) {
      return '[]';
    }
    else {
      return json_encode($line);
    }
  }
  elseif ($_action == 'delete') {
    if (!is_array($_data['qid'])) {
      $qids = array();
      $qids[] = $_data['qid'];
    }
    else {
      $qids = $_data['qid'];
    }
    $docker_return = docker('post', 'postfix-mailcow', 'exec', array('cmd' => 'mailq', 'task' => 'delete', 'items' => $qids));
    process_mailq_output(json_decode($docker_return, true), $_action, $_data);
  }
  elseif ($_action == 'cat') {
    if (!is_array($_data['qid'])) {
      $qids = array();
      $qids[] = $_data['qid'];
    }
    else {
      $qids = $_data['qid'];
    }
    $docker_return = docker('post', 'postfix-mailcow', 'exec', array('cmd' => 'mailq', 'task' => 'cat', 'items' => $qids));
    return process_mailq_output($docker_return, $_action, $_data);
  }
  elseif ($_action == 'edit') {
    if (in_array($_data['action'], array('hold', 'unhold', 'deliver'))) {
      if (!is_array($_data['qid'])) {
        $qids = array();
        $qids[] = $_data['qid'];
      }
      else {
        $qids = $_data['qid'];
      }
      if (!empty($qids)) {
        $docker_return = docker('post', 'postfix-mailcow', 'exec', array('cmd' => 'mailq', 'task' => $_data['action'], 'items' => $qids));
        process_mailq_output(json_decode($docker_return, true), $_action, $_data);
      }
    }
    if (in_array($_data['action'], array('flush', 'super_delete'))) {
      $docker_return = docker('post', 'postfix-mailcow', 'exec', array('cmd' => 'mailq', 'task' => $_data['action']));
      process_mailq_output(json_decode($docker_return, true), $_action, $_data);
    }
  }
}
