<?php

function array_diff_key_recursive (array $arr1, array $arr2) {
  $diff = array_diff_key($arr1, $arr2);
  $intersect = array_intersect_key($arr1, $arr2);

  foreach ($intersect as $k => $v) {
    if (is_array($arr1[$k]) && is_array($arr2[$k])) {
      $d = array_diff_key_recursive($arr1[$k], $arr2[$k]);

      if ($d) {
        $diff[$k] = $d;
      }
    }
  }

  return $diff;
}

// target lang
$targetLang = $argv[1];

if(empty($targetLang)) {
  die('Please specify target lang as the first argument, to which you want to add missing keys from master lang (EN). Use the lowercase name,
  for example `sk` for the Slovak language'."\n");
}

// load master lang
$masterLang = file_get_contents(__DIR__.'/../data/web/lang/lang.en.json');
$masterLang = json_decode($masterLang, true);

// load target lang
$lang = file_get_contents(__DIR__.'/../data/web/lang/lang.'.$targetLang.'.json');
$lang = json_decode($lang, true);

// compare lang keys
$result = array_diff_key_recursive($masterLang, $lang);

if(empty($result)) {
  die('No new keys were added. Looks like target lang is up to date.'."\n");
}

foreach($result as $key => $val) {
  // check if section key exists in target lang
  if(array_key_exists($key, $lang)) {
    // add only missing section keys
    foreach ($val as $k => $v) {
      $lang[$key][$k] = $v;
    }
    // sort keys
    ksort($lang[$key]);
  } else {
    // add whole section
    $lang[$key] = $val;
    ksort($lang);
  }
}

$lang = json_encode($lang, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
file_put_contents(__DIR__.'/../data/web/lang/lang.'.$targetLang.'.json', $lang);

echo 'Following new lang keys were added and need translation:'."\n";
print_r($result);
