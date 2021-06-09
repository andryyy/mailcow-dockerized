<?php
error_reporting(0);
function get_spf_allowed_hosts($check_domain, $expand_ipv6 = false) {
	$hosts = array();
	
	$records = dns_get_record($check_domain, DNS_TXT);
	foreach ($records as $record)
	{
		$txt = explode(' ', $record['entries'][0]);
		if (array_shift($txt) != 'v=spf1') // only handle SPF records
			continue;
		
		foreach ($txt as $mech)
		{
			$qual = substr($mech, 0, 1);
			if ($qual == '-' || $qual == '~') // only handle pass or neutral records
				continue(2);
			
			if ($qual == '+' || $qual == '?')
				$mech = substr($mech, 1); // remove the qualifier
			
			if (strpos($mech, '=') !== FALSE) // handle a modifier
			{
				$mod = explode('=', $mech);
				if ($mod[0] == 'redirect') // handle a redirect
				{
					$hosts = get_spf_allowed_hosts($mod[1],true);
					return $hosts;
				}
			}
			else
			{
				unset($cidr);
				// reset domain to check_domain
				$domain = $check_domain;
				if (strpos($mech, ':') !== FALSE) // handle a domain specification
				{
					$split = explode(':', $mech);
					$mech = array_shift($split);
					$domain = implode(':', $split);
					if (strpos($domain, '/') !== FALSE) // remove CIDR specification
					{
						$split = explode('/', $domain);
						$domain = $split[0];
						$cidr = $split[1];
					}
				}
				
				$new_hosts = array();
        if ($mech == 'include' && $check_domain != $domain) // handle an inclusion
				{
					$new_hosts = get_spf_allowed_hosts($domain);
				}
				elseif ($mech == 'a') // handle a mechanism
				{
					$new_hosts = get_a_hosts($domain);
				}
				elseif ($mech == 'mx') // handle mx mechanism
				{
					$new_hosts = get_mx_hosts($domain);
				}
				elseif ($mech == 'ip4' || $mech == 'ip6') // handle ip mechanism
				{
					$new_hosts = array($domain);
				}
				
				if (isset($cidr)) // add CIDR specification if present
				{
					foreach ($new_hosts as &$host)
					{
						$host .= '/' . $cidr;
					}
					unset($host);
				}
				
				$hosts = array_unique(array_merge($hosts,$new_hosts), SORT_REGULAR);
			}
		}
	}
	foreach ($hosts as &$host) {
		if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			if ($expand_ipv6 === true) {
				$hex = unpack("H*hex", inet_pton($host));
				$host = substr(preg_replace("/([A-f0-9]{4})/", "$1:", $hex['hex']), 0, -1);
			}
			else {
				$host = $host;
			}
		}
	}
	return $hosts;
}


function get_mx_hosts($domain)
{
	$hosts = array();
  try {
    $mx_records = dns_get_record($domain, DNS_MX);
    if ($mx_records) {
      foreach ($mx_records as $mx_record) {
        $new_hosts = get_a_hosts($mx_record['target']);
        $hosts = array_unique(array_merge($hosts,$new_hosts), SORT_REGULAR);
      }
    }
  }
  catch (Exception $e) {
    if ($e->getMessage() !== 'dns_get_record(): A temporary server error occurred.') {
      throw $e;
    }
    $mx_records = false;
  }
	return $hosts;
}

function get_a_hosts($domain)
{
	$hosts = array();
	
	$a_records = dns_get_record($domain, DNS_A);
	foreach ($a_records as $a_record)
	{
		$hosts[] = $a_record['ip'];
	}
	$a_records = dns_get_record($domain, DNS_AAAA);
	foreach ($a_records as $a_record) {
    $hosts[] = $a_record['ipv6'];
	}
	
	return $hosts;
}

function get_outgoing_hosts_best_guess($domain)
{
	// try the SPF record to get hosts that are allowed to send outgoing mails for this domain
	$hosts = get_spf_allowed_hosts($domain);
	if ($hosts) return $hosts;
	
	// try the MX record to get mail servers for this domain
	$hosts = get_mx_hosts($domain);
	if ($hosts) return $hosts;
	
	// fall back to the A record to get the host name for this domain
	return get_a_hosts($domain);
}
?>
