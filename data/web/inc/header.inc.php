<!DOCTYPE html>
<html lang="<?= $_SESSION['mailcow_locale'] ?>">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
  <meta name="theme-color" content="#F5D76E"/>
  <meta http-equiv="Referrer-Policy" content="same-origin">
  <title><?=$UI_TEXTS['title_name'];?></title>
  <?php
    if (preg_match("/mailbox/i", $_SERVER['REQUEST_URI'])) {
      $css_minifier->add('/web/css/site/mailbox.css');
    }
    if (preg_match("/admin/i", $_SERVER['REQUEST_URI'])) {
      $css_minifier->add('/web/css/site/admin.css');
    }
    if (preg_match("/user/i", $_SERVER['REQUEST_URI'])) {
      $css_minifier->add('/web/css/site/user.css');
    }
    if (preg_match("/edit/i", $_SERVER['REQUEST_URI'])) {
      $css_minifier->add('/web/css/site/edit.css');
    }
    if (preg_match("/quarantine/i", $_SERVER['REQUEST_URI'])) {
      $css_minifier->add('/web/css/site/quarantine.css');
    }
    if (preg_match("/debug/i", $_SERVER['REQUEST_URI'])) {
      $css_minifier->add('/web/css/site/debug.css');
    }
    if ($_SERVER['REQUEST_URI'] == '/') {
      $css_minifier->add('/web/css/site/index.css');
    }

  $hash = $css_minifier->getDataHash();
  $CSSPath = '/tmp/' . $hash . '.css';
  if(!file_exists($CSSPath)) {
    $css_minifier->minify($CSSPath);
    cleanupCSS($hash);
  }
  ?>
  <link rel="stylesheet" href="/cache/<?=basename($CSSPath)?>">
  <?php if (strtolower(trim($DEFAULT_THEME)) != "lumen") { ?>
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootswatch/3.3.7/<?= strtolower(trim($DEFAULT_THEME)); ?>/bootstrap.min.css">
  <?php } ?>
  <link rel="shortcut icon" href="/favicon.png" type="image/png">
  <link rel="icon" href="/favicon.png" type="image/png">
</head>
<body id="top">
  <div class="overlay"></div>
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container-fluid">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/"><img alt="mailcow-logo" src="<?=($main_logo = customize('get', 'main_logo')) ? $main_logo : '/img/cow_mailcow.svg';?>"></a>
      </div>
      <div id="navbar" class="navbar-collapse collapse">
        <ul class="nav navbar-nav navbar-right">
          <?php
          if (isset($_SESSION['mailcow_locale'])) {
          ?>
          <li class="dropdown<?=(isset($_SESSION['mailcow_locale']) && count($AVAILABLE_LANGUAGES) === 1) ? ' lang-link-disabled"' : '' ?>">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><span class="lang-sm lang-lbl" lang="<?= $_SESSION['mailcow_locale']; ?>"></span><span class="caret"></span></a>
            <ul class="dropdown-menu" role="menu">
              <?php
              foreach ($AVAILABLE_LANGUAGES as $language) {
              ?>
              <li<?= ($_SESSION['mailcow_locale'] == $language) ? ' class="active"' : ''; ?>><a href="?<?= http_build_query(array_merge($_GET, array('lang' => $language))); ?>"><span class="lang-xs lang-lbl-full" lang="<?= $language; ?>"></span></a></li>
              <?php
              }
              ?>
            </ul>
          </li>
          <?php
          }
          if (isset($_SESSION['mailcow_cc_role'])) {
          ?>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?= $lang['header']['mailcow_settings']; ?> <span class="caret"></span></a>
            <ul class="dropdown-menu" role="menu">
              <?php
              if (isset($_SESSION['mailcow_cc_role'])) {
                if ($_SESSION['mailcow_cc_role'] == 'admin') {
                ?>
                  <li<?= (preg_match("/admin/i", $_SERVER['REQUEST_URI'])) ? ' class="active"' : ''; ?>><a href="/admin"><?= $lang['header']['administration']; ?></a></li>
                  <li<?= (preg_match("/debug/i", $_SERVER['REQUEST_URI'])) ? ' class="active"' : ''; ?>><a href="/debug"><?= $lang['header']['debug']; ?></a></li>
                <?php
                }
                if ($_SESSION['mailcow_cc_role'] == 'admin' || $_SESSION['mailcow_cc_role'] == 'domainadmin') {
                ?>
                  <li<?= (preg_match("/mailbox/i", $_SERVER['REQUEST_URI'])) ? ' class="active"' : ''; ?>><a href="/mailbox"><?= $lang['header']['mailboxes']; ?></a></li>
                <?php } if ($_SESSION['mailcow_cc_role'] != 'admin') { ?>
                  <li<?= (preg_match("/user/i", $_SERVER['REQUEST_URI'])) ? ' class="active"' : ''; ?>><a href="/user"><?= $lang['header']['user_settings']; ?></a></li>
                <?php
                }
              }
              ?>
            </ul>
          </li>
          <?php if (isset($_SESSION['mailcow_cc_role'])) { ?>
          <li<?= (preg_match("/quarantine/i", $_SERVER['REQUEST_URI'])) ? ' class="active"' : ''; ?>><a href="/quarantine"><span class="glyphicon glyphicon-briefcase"></span> <?= $lang['header']['quarantine']; ?></a></li>
          <?php } if ($_SESSION['mailcow_cc_role'] == 'admin' && getenv('SKIP_SOGO') != "y") { ?>
          <li><a href data-toggle="modal" data-container="sogo-mailcow" data-target="#RestartContainer"><span class="glyphicon glyphicon-refresh"></span> <?= $lang['header']['restart_sogo']; ?></a></li>
          <?php } ?>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><span class="glyphicon glyphicon-link"></span> <?= $lang['header']['apps']; ?> <span class="caret"></span></a>
            <ul class="dropdown-menu" role="menu">
            <?php foreach ($MAILCOW_APPS as $app) {
              if (getenv('SKIP_SOGO') == "y" && preg_match('/^\/SOGo/i', $app['link'])) { continue; }
            ?>
              <li title="<?= htmlspecialchars($app['description']); ?>"><a href="<?= htmlspecialchars($app['link']); ?>"><?= htmlspecialchars($app['name']); ?></a></li>
            <?php
            }
            $app_links = customize('get', 'app_links');
            if ($app_links) {
              foreach ($app_links as $row) {
                foreach ($row as $key => $val) {
              ?>
              <li><a href="<?= htmlspecialchars($val); ?>"><?= htmlspecialchars($key); ?></a></li>
              <?php
                }
              }
            }
            ?>
            </ul>
          </li>
          <?php } if (!isset($_SESSION['dual-login']) && isset($_SESSION['mailcow_cc_username'])) { ?>
            <li class="logged-in-as"><a href="#" onclick="logout.submit()"><b class="username-lia"><?= htmlspecialchars($_SESSION['mailcow_cc_username']); ?></b> <span class="glyphicon glyphicon-log-out"></span></a></li>
          <?php } elseif (isset($_SESSION['dual-login'])) { ?>
            <li class="logged-in-as"><a href="#" onclick="logout.submit()"><b class="username-lia"><?= htmlspecialchars($_SESSION['mailcow_cc_username']); ?> <span class="text-info">(<?= htmlspecialchars($_SESSION['dual-login']['username']); ?>)</span> </b><span class="glyphicon glyphicon-log-out"></span></a></li>
          <?php } if (!preg_match('/y|yes/i', getenv('MASTER'))) { ?>
            <li class="text-warning slave-info">[ slave ]</li>
          <?php } ?>
        </ul>
      </div><!--/.nav-collapse -->
    </div><!--/.container-fluid -->
  </nav>
  <form action="/" method="post" id="logout"><input type="hidden" name="logout"></form>
  <?php if (!empty($UI_TEXTS['ui_announcement_text']) &&
    in_array($UI_TEXTS['ui_announcement_type'], array('info', 'warning', 'danger')) &&
    $UI_TEXTS['ui_announcement_active'] == 1 &&
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) != '/') { ?>
  <div class="container">
    <div class="alert alert-<?=$UI_TEXTS['ui_announcement_type'];?>"><?=$UI_TEXTS['ui_announcement_text'];?></div>
  </div>
  <?php } ?>
