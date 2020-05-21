<?php
session_start();
header("Content-Type: application/json");
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';
if (!isset($_SESSION['mailcow_cc_role'])) {
  exit();
}

function rrmdir($src) {
  $dir = opendir($src);
  while(false !== ( $file = readdir($dir)) ) {
    if (( $file != '.' ) && ( $file != '..' )) {
      $full = $src . '/' . $file;
      if ( is_dir($full) ) {
        rrmdir($full);
      }
      else {
        unlink($full);
      }
    }
  }
  closedir($dir);
  rmdir($src);
}
function addAddresses(&$list, $mail, $headerName) {
  $addresses = $mail->getAddresses($headerName);
  foreach ($addresses as $address) {
    $list[] = array('address' => $address['address'], 'type' => $headerName);
  }
}

if (!empty($_GET['id']) && ctype_alnum($_GET['id'])) {
  $tmpdir = '/tmp/' . $_GET['id'] . '/';
  $mailc = quarantine('details', $_GET['id']);
  if (strlen($mailc['msg']) > 10485760) {
    echo json_encode(array('error' => 'Message size exceeds 10 MiB.'));
    exit;
  }
  if (!empty($mailc['msg'])) {
    // Init message array
    $data = array();
    // Init parser
    $mail_parser = new PhpMimeMailParser\Parser();
    $html2text = new Html2Text\Html2Text();
    // Load msg to parser
    $mail_parser->setText($mailc['msg']);

    // Get mail recipients
    {
      $recipientsList = array();
      addAddresses($recipientsList, $mail_parser, 'to');
      addAddresses($recipientsList, $mail_parser, 'cc');
      addAddresses($recipientsList, $mail_parser, 'bcc');
      $data['recipients'] = $recipientsList;
    }

    // Get rspamd score
    $data['score'] = $mailc['score'];
    // Get rspamd symbols
    $data['symbols'] = json_decode($mailc['symbols']);
    // Get text/plain content
    $data['text_plain'] = $mail_parser->getMessageBody('text');
    // Get html content and convert to text
    $data['text_html'] = $html2text->convert($mail_parser->getMessageBody('html'));
    if (empty($data['text_plain']) && empty($data['text_html'])) {
      // Failed to parse content, try raw
      $text = trim(substr($mailc['msg'], strpos($mailc['msg'], "\r\n\r\n") + 1));
      // Only return html->text
      $data['text_plain'] = 'Parser failed, assuming HTML';
      $data['text_html'] = $html2text->convert($text);
    }
    (empty($data['text_plain'])) ? $data['text_plain'] = '-' : null;
    // Get subject
    $data['subject'] = $mail_parser->getHeader('subject');
    (empty($data['subject'])) ? $data['subject'] = '-' : null;
    // Get attachments
    if (is_dir($tmpdir)) {
      rrmdir($tmpdir);
    }
    mkdir('/tmp/' . $_GET['id']);
    $mail_parser->saveAttachments($tmpdir, true);
    $atts = $mail_parser->getAttachments(true);
    if (count($atts) > 0) {
      foreach ($atts as $key => $val) {
        $data['attachments'][$key] = array(
          // Index
          // 0 => file name
          // 1 => mime type
          // 2 => file size
          // 3 => vt link by sha256
          $val->getFilename(),
          $val->getContentType(),
          filesize($tmpdir . $val->getFilename()),
          'https://www.virustotal.com/file/' . hash_file('SHA256', $tmpdir . $val->getFilename()) . '/analysis/'
        );
      }
    }
    if (isset($_GET['eml'])) {
      $dl_filename = filter_var($data['subject'], FILTER_SANITIZE_STRING);
      $dl_filename = strlen($dl_filename) > 30 ? substr($dl_filename,0,30) : $dl_filename;
      header('Pragma: public');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Cache-Control: private', false);
      header('Content-Type: message/rfc822');
      header('Content-Disposition: attachment; filename="'. $dl_filename . '.eml";');
      header('Content-Transfer-Encoding: binary');
      header('Content-Length: ' . strlen($mailc['msg']));
      echo $mailc['msg'];
      exit;
    }
    if (isset($_GET['att'])) {
      if ($_SESSION['acl']['quarantine_attachments'] == 0) {
        exit(json_encode('Forbidden'));
      }
      $dl_id = intval($_GET['att']);
      $dl_filename = filter_var($data['attachments'][$dl_id][0], FILTER_SANITIZE_STRING);
      $dl_filename = strlen($dl_filename) > 30 ? substr($dl_filename,0,30) : $dl_filename;
      if (!is_dir($tmpdir . $dl_filename) && file_exists($tmpdir . $dl_filename)) {
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: ' . $data['attachments'][$dl_id][1]);
        header('Content-Disposition: attachment; filename="'. $dl_filename . '";');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . $data['attachments'][$dl_id][2]);
        readfile($tmpdir . $dl_filename);
        exit;
      }
    }
    echo json_encode($data);
  }
}
?>
