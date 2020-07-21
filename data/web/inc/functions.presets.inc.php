<?php
function presets($_action, $_kind) {
  switch ($_action) {
    case 'get':
      if ($_SESSION['mailcow_cc_role'] != "admin" && $_SESSION['mailcow_cc_role'] != "domainadmin") {
        return false;
      }
      $presets = array();
      $kind = strtolower(trim($_kind));
      $lang_base = 'admin';
      $presets_path = __DIR__ . '/presets/' . $kind;
      if (!in_array($kind, ['rspamd', 'sieve'], true)) {
        return array();
      }
      if ($kind === 'sieve') {
        $lang_base = 'mailbox';
      }
      foreach (glob($presets_path . '/*.yml') as $filename) {
        $presets[] = getPresetFromFilePath($filename, $lang_base);
      }
      return $presets;
    break;
  }
  return array();
}
function getPresetFromFilePath($filePath, $lang_base) {
  global $lang;
  $preset = Spyc::YAMLLoad($filePath);
  $preset = ['name' => basename($filePath, '.yml')] + $preset;
  /* get translated headlines */
  if (isset($preset['headline']) && strpos($preset['headline'], 'lang.') === 0) {
    $langTextName = trim(substr($preset['headline'], 5));
    if (isset($lang[$lang_base][$langTextName])) {
      $preset['headline'] = $lang[$lang_base][$langTextName];
    }
  }
  return $preset;
}
