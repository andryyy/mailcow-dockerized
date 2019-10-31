<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/spf.inc.php';

define('state_good', '<span class="glyphicon glyphicon-ok text-success"></span>');
define('state_missing', '<span class="glyphicon glyphicon-remove text-danger"></span>');
define('state_nomatch', "?");
define('state_optional', " <sup>2</sup>");

if (isset($_SESSION['mailcow_cc_role']) && ($_SESSION['mailcow_cc_role'] == "admin"|| $_SESSION['mailcow_cc_role'] == "domainadmin")) {

$domains = mailbox('get', 'domains');
$alias_domains = array();
foreach($domains as $dn) {
  $alias_domains = array_merge($alias_domains, mailbox('get', 'alias_domains', $dn));
}
$domains = array_merge($domains, $alias_domains);

if (isset($_GET['domain'])) {
  if (is_valid_domain_name($_GET['domain'])) {
    if (in_array($_GET['domain'], $domains)) {
      $domain = $_GET['domain'];
    }
    else {
      echo "No such domain in context";
      exit();
    }
  }
  else {
    echo "Invalid domain name";
    exit();
  }
}

$ch = curl_init('http://ip4.mailcow.email');
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
curl_setopt($ch, CURLOPT_VERBOSE, false);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
$ip = curl_exec($ch);
curl_close($ch);

$ch = curl_init('http://ip6.mailcow.email');
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V6);
curl_setopt($ch, CURLOPT_VERBOSE, false);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
$ip6 = curl_exec($ch);
curl_close($ch);

$ptr = implode('.', array_reverse(explode('.', $ip))) . '.in-addr.arpa';
if (!empty($ip6)) {
  $ip6_full = str_replace('::', str_repeat(':', 9-substr_count($ip6, ':')), $ip6);
  $ip6_full = str_replace('::', ':0:', $ip6_full);
  $ip6_full = str_replace('::', ':0:', $ip6_full);
  $ptr6 = '';
  foreach (explode(':', $ip6_full) as $part) {
    $ptr6 .= str_pad($part, 4, '0', STR_PAD_LEFT);
  }
  $ptr6 = implode('.', array_reverse(str_split($ptr6, 1))) . '.ip6.arpa';
}

$https_port = strpos($_SERVER['HTTP_HOST'], ':');
if ($https_port === FALSE) {
  $https_port = 443;
}
else {
  $https_port = substr($_SERVER['HTTP_HOST'], $https_port+1);
}

if (!isset($autodiscover_config['sieve'])) {
  $autodiscover_config['sieve'] = array('server' => $mailcow_hostname, 'port' => array_pop(explode(':', getenv('SIEVE_PORT'))));
}

// Init records array
$spf_link = '<a href="https://en.wikipedia.org/wiki/Sender_Policy_Framework" target="_blank">SPF Record Syntax</a><br />';
$dmarc_link = '<a href="https://www.kitterman.com/dmarc/assistant.html" target="_blank">DMARC Assistant</a>';

$records = array();
if ($_SESSION['mailcow_cc_role'] == "admin") {
  $records[] = array(
    $mailcow_hostname,
    'A',
    $ip
  );
  $records[] = array(
    $ptr,
    'PTR',
    $mailcow_hostname
  );
  if (!empty($ip6)) {
    $records[] = array(
      $mailcow_hostname,
      'AAAA',
      $ip6
    );
    $records[] = array(
      $ptr6,
      'PTR',
      $mailcow_hostname
    );
  }

  $services = [
    [$autodiscover_config['smtp']['server'], 25, 1],
  ];

  if (!in_array($domain, $alias_domains)) {
    $services[] = [$mailcow_hostname,                       $https_port,                             null];
    $services[] = [$autodiscover_config['pop3']['server'],  $autodiscover_config['pop3']['tlsport'], 1];
    $services[] = [$autodiscover_config['imap']['server'],  $autodiscover_config['imap']['tlsport'], 1];
    $services[] = [$autodiscover_config['smtp']['server'],  $autodiscover_config['smtp']['port'],    null];
    $services[] = [$autodiscover_config['smtp']['server'],  $autodiscover_config['smtp']['tlsport'], 1];
    $services[] = [$autodiscover_config['imap']['server'],  $autodiscover_config['imap']['port'],    null];
    $services[] = [$autodiscover_config['pop3']['server'],  $autodiscover_config['pop3']['port'],    null];
    $services[] = [$autodiscover_config['sieve']['server'], $autodiscover_config['sieve']['port'],   1];
  }

  foreach($services as list($server,$port,$starttls)) {
    foreach(['aRSA','aECDSA'] as $ciphers) {
      $digest = generate_tlsa_digest($server, $port, $starttls, $ciphers);
      if($digest !== false) { // Check if negotation failed (because e.g. RSA or ECDSA isn't supported)
        $records[] = array(
          '_'.$port.'._tcp.'.$server,
          'TLSA',
          $digest
        );
      }
    }
  }
}
$records[] = array(
  $domain,
  'MX',
  $mailcow_hostname
);
if (!in_array($domain, $alias_domains)) {
  $records[] = array(
    'autodiscover.'.$domain,
    'CNAME',
    $mailcow_hostname
  );
  $records[] = array(
    '_autodiscover._tcp.'.$domain,
    'SRV',
    $mailcow_hostname.
    ' '.$https_port
  );
  $records[] = array(
    'autoconfig.'.$domain,
    'CNAME',
    $mailcow_hostname
  );
}
$records[] = array(
  $domain,
  'TXT',
  $spf_link,
  state_optional
);
$records[] = array(
  '_dmarc.'.$domain,
  'TXT',
  $dmarc_link,
  state_optional
);

if (!empty($dkim = dkim('details', $domain))) {
  $records[] = array(
    $dkim['dkim_selector'] . '._domainkey.' . $domain,
    'TXT',
    $dkim['dkim_txt']
  );
}
if (!in_array($domain, $alias_domains)) {
  $current_records = dns_get_record('_pop3._tcp.' . $domain, DNS_SRV);
  if (count($current_records) == 0 || $current_records[0]['target'] != '') {
    if ($autodiscover_config['pop3']['tlsport'] != '110') {
      $records[] = array(
        '_pop3._tcp.' . $domain,
        'SRV',
        $autodiscover_config['pop3']['server'] . ' ' . $autodiscover_config['pop3']['tlsport']
      );
    }
  }
  else {
    $records[] = array(
      '_pop3._tcp.' . $domain,
      'SRV',
      '. 0'
    );
  }
  $current_records = dns_get_record('_pop3s._tcp.' . $domain, DNS_SRV);
  if (count($current_records) == 0 || $current_records[0]['target'] != '') {
    if ($autodiscover_config['pop3']['port'] != '995') {
      $records[] = array(
        '_pop3s._tcp.' . $domain,
        'SRV',
        $autodiscover_config['pop3']['server'] . ' ' . $autodiscover_config['pop3']['port']
      );
    }
  }
  else {
    $records[] = array(
      '_pop3s._tcp.' . $domain,
      'SRV',
      '. 0'
    );
  }
  if ($autodiscover_config['imap']['tlsport'] != '143') {
    $records[] = array(
      '_imap._tcp.' . $domain,
      'SRV',
      $autodiscover_config['imap']['server'] . ' ' . $autodiscover_config['imap']['tlsport']
    );
  }
  if ($autodiscover_config['imap']['port'] != '993') {
    $records[] = array(
      '_imaps._tcp.' . $domain,
      'SRV',
      $autodiscover_config['imap']['server'] . ' ' . $autodiscover_config['imap']['port']
    );
  }
  if ($autodiscover_config['smtp']['tlsport'] != '587') {
    $records[] = array(
      '_submission._tcp.' . $domain,
      'SRV',
      $autodiscover_config['smtp']['server'] . ' ' . $autodiscover_config['smtp']['tlsport']
    );
  }
  if ($autodiscover_config['smtp']['port'] != '465') {
    $records[] = array(
      '_smtps._tcp.' . $domain,
      'SRV',
      $autodiscover_config['smtp']['server'] . ' ' . $autodiscover_config['smtp']['port']
    );
  }
  if ($autodiscover_config['sieve']['port'] != '4190') {
    $records[] = array(
      '_sieve._tcp.' . $domain,
      'SRV',
      $autodiscover_config['sieve']['server'] . ' ' . $autodiscover_config['sieve']['port']
    );
  }
}

$record_types = array(
  'A' => DNS_A,
  'AAAA' => DNS_AAAA,
  'CNAME' => DNS_CNAME,
  'MX' => DNS_MX,
  'PTR' => DNS_PTR,
  'SRV' => DNS_SRV,
  'TXT' => DNS_TXT,
);
$data_field = array(
  'A' => 'ip',
  'AAAA' => 'ipv6',
  'CNAME' => 'target',
  'MX' => 'target',
  'PTR' => 'target',
  'SRV' => 'data',
  'TLSA' => 'data',
  'TXT' => 'txt',
);
?>
<div class="table-responsive" id="dnstable">
  <table class="table table-striped">
    <tr>
      <th><?=$lang['diagnostics']['dns_records_name'];?></th>
      <th><?=$lang['diagnostics']['dns_records_type'];?></th>
      <th><?=$lang['diagnostics']['dns_records_data'];?></th>
      <th><?=$lang['diagnostics']['dns_records_status'];?></th>
    </tr>
<?php
foreach ($records as $record) {
  $record[1] = strtoupper($record[1]);
  $state = state_missing;
  if ($record[1] == 'TLSA') {
    $currents = dns_get_record($record[0], 52, $_, $_, TRUE);
    foreach ($currents as &$current) {
      $current['type'] = 'TLSA';
      $current['cert_usage'] = hexdec(bin2hex($current['data']{0}));
      $current['selector'] = hexdec(bin2hex($current['data']{1}));
      $current['match_type'] = hexdec(bin2hex($current['data']{2}));
      $current['cert_data'] = bin2hex(substr($current['data'], 3));
      $current['data'] = $current['cert_usage'] . ' ' . $current['selector'] . ' ' . $current['match_type'] . ' ' . $current['cert_data'];
    }
    unset($current);
  }
  else {
    $currents = dns_get_record($record[0], $record_types[$record[1]]);
    if ($record[1] == 'SRV') {
      foreach ($currents as &$current) {
        if ($current['target'] == '') {
          $current['target'] = '.';
          $current['port'] = '0';
        }
        $current['data'] = $current['target'] . ' ' . $current['port'];
      }
      unset($current);
    }
    elseif ($record[1] == 'TXT') {
      foreach ($currents as &$current) {
        unset($current);
      }
      unset($current);
    }
  }

  if ($record[1] == 'CNAME' && count($currents) == 0) {
    // A and AAAA are also valid instead of CNAME
    $a = dns_get_record($record[0], DNS_A);
    $cname = dns_get_record($record[2], DNS_A);
    if (count($a) > 0 && count($cname) > 0) {
      if ($a[0]['ip'] == $cname[0]['ip']) {
        $currents = array(array('host' => $record[0], 'class' => 'IN', 'type' => 'CNAME', 'target' => $record[2]));
        $aaaa = dns_get_record($record[0], DNS_AAAA);
        $cname = dns_get_record($record[2], DNS_AAAA);
        if (count($aaaa) == 0 || count($cname) == 0 || $aaaa[0]['ipv6'] != $cname[0]['ipv6']) {
          $currents[0]['target'] = $aaaa[0]['ipv6'] . ' <sup>1</sup>';
        }
      }
      else {
        $currents = array(array('host' => $record[0], 'class' => 'IN', 'type' => 'CNAME', 'target' => $a[0]['ip'] . ' <sup>1</sup>'));
      }
    }
  }

  foreach ($currents as $i => &$current) {
    if ($current['type'] == 'TXT' &&
      stripos($current['txt'], 'v=dmarc') === 0 &&
      $record[2] == $dmarc_link) {
        $current['txt'] = str_replace(' ', '', $current['txt']);
        $state = $current[$data_field[$current['type']]] . state_optional;
    }
    elseif ($current['type'] == 'TXT' &&
      stripos($current['txt'], 'v=spf') === 0 &&
      $record[2] == $spf_link) {
        $state = state_nomatch;
        $rslt = get_spf_allowed_hosts($record[0]);
        if(in_array($ip, $rslt) && in_array($ip6, $rslt)){
            $state = state_good;
        }
        $state .= '<br />' . $current[$data_field[$current['type']]].state_optional;
    }
    elseif ($current['type'] == 'TXT' &&
      stripos($current['txt'], 'v=dkim') === 0 &&
      stripos($record[2], 'v=dkim') === 0) {
        preg_match('/v=DKIM1;.*k=rsa;.*p=(.*)/i', $current[$data_field[$current['type']]], $dkim_matches_current);
        preg_match('/v=DKIM1;.*k=rsa;.*p=(.*)/i', $record[2], $dkim_matches_good);
        if ($dkim_matches_current[1] == $dkim_matches_good[1]) {
          $state = state_good;
        }
    }
    elseif ($current['type'] != 'TXT' &&
      isset($data_field[$current['type']]) && $state != state_good) {
        // Ignore $current TLSA record if it already matches another one in $records.
        // E.g. hide found TLSA records for an RSA certificate in the status column
        // of missing TLSA records for an ECDSA certificate.
        if($current['type'] == 'TLSA' && $record[1] == 'TLSA') {
          foreach($records as $other_record) {
            if($record != $other_record &&
              $other_record[1] == 'TLSA' &&
              $other_record[0] == $current['host'] &&
              $other_record[2] == $current[$data_field[$current['type']]]) {
              unset($currents[$i]);
              continue 2;
            }
          }
        }

        $state = state_nomatch;
        if ($current[$data_field[$current['type']]] == $record[2]) {
          $state = state_good;
        }
    }
  }
  unset($current);

  if (isset($record[3]) &&
    $record[3] == state_optional &&
    ($state == state_missing || $state == state_nomatch)) {
      $state = state_optional;
  }

  if ($state == state_nomatch) {
    $state = array();
    foreach ($currents as $current) {
      $state[] = $current[$data_field[$current['type']]];
    }
    $state = implode('<br />', $state);
  }
  echo sprintf('<tr>
    <td>%s</td>
    <td>%s</td>
    <td class="dns-found">%s</td>
    <td class="dns-recommended">%s</td>
  </tr>', $record[0], $record[1], $record[2], $state);
}
?>
  </table>
</div>
<p class="help-block">
<sup>1</sup> <?=$lang['diagnostics']['cname_from_a'];?><br />
<sup>2</sup> <?=$lang['diagnostics']['optional'];?>
</p>
<?php
} else {
  echo "Session invalid";
  exit();
}
?>
