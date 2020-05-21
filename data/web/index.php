<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';

if (isset($_SESSION['mailcow_cc_role']) && isset($_SESSION['oauth2_request'])) {
  $oauth2_request = $_SESSION['oauth2_request'];
  unset($_SESSION['oauth2_request']);
  header('Location: ' . $oauth2_request);
  exit();
}
elseif (isset($_SESSION['mailcow_cc_role']) && $_SESSION['mailcow_cc_role'] == 'admin') {
  header('Location: /admin');
  exit();
}
elseif (isset($_SESSION['mailcow_cc_role']) && $_SESSION['mailcow_cc_role'] == 'domainadmin') {
  header('Location: /mailbox');
  exit();
}
elseif (isset($_SESSION['mailcow_cc_role']) && $_SESSION['mailcow_cc_role'] == 'user') {
  header('Location: /user');
  exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/header.inc.php';
$_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
$_SESSION['index_query_string'] = $_SERVER['QUERY_STRING'];

?>
<div class="container">
  <div class="row">
    <div class="col-md-offset-3 col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> <?= $lang['login']['login']; ?></div>
        <div class="panel-body">
          <div class="text-center mailcow-logo"><img src="<?=($main_logo = customize('get', 'main_logo')) ? $main_logo : '/img/cow_mailcow.svg';?>" alt="mailcow"></div>
          <?php if (!empty($UI_TEXTS['ui_announcement_text']) && in_array($UI_TEXTS['ui_announcement_type'], array('info', 'warning', 'danger')) && $UI_TEXTS['ui_announcement_active'] == 1) { ?>
          <div class="alert alert-<?=$UI_TEXTS['ui_announcement_type'];?>"><?=$UI_TEXTS['ui_announcement_text'];?></div>
          <?php } ?>
          <legend><?= isset($_SESSION['oauth2_request']) ? $lang['oauth2']['authorize_app'] : $UI_TEXTS['main_name'];?></legend>
            <?php
            if (strpos($_SESSION['index_query_string'], 'mobileconfig') !== false) {
            ?>
            <div class="alert alert-info"><?= $lang['login']['mobileconfig_info']; ?></div>
            <?php
            }
            ?>
            <form method="post" autofill="off">
            <div class="form-group">
              <label class="sr-only" for="login_user"><?= $lang['login']['username']; ?></label>
              <div class="input-group">
                <div class="input-group-addon"><i class="glyphicon glyphicon-user"></i></div>
                <input name="login_user" autocorrect="off" autocapitalize="none" type="<?=(strpos($_SESSION['index_query_string'], 'mobileconfig') !== false) ? 'email' : 'text';?>" id="login_user" class="form-control" placeholder="<?= $lang['login']['username']; ?>" required="" autofocus="">
              </div>
            </div>
            <div class="form-group">
              <label class="sr-only" for="pass_user"><?= $lang['login']['password']; ?></label>
              <div class="input-group">
                <div class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></div>
                <input name="pass_user" type="password" id="pass_user" class="form-control" placeholder="<?= $lang['login']['password']; ?>" required="">
              </div>
            </div>
            <div class="form-group">
              <button type="submit" class="btn btn-success" value="Login"><?= $lang['login']['login']; ?></button>
              <?php if(!isset($_SESSION['oauth2_request'])) { ?>
              <div class="btn-group pull-right">
                <button type="button" <?=(isset($_SESSION['mailcow_locale']) && count($AVAILABLE_LANGUAGES) === 1) ? 'disabled="true"' : '' ?> class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="lang-sm lang-lbl" lang="<?= $_SESSION['mailcow_locale']; ?>"></span> <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  <?php
                  foreach ($AVAILABLE_LANGUAGES as $language) {
                  ?>
                  <li<?= ($_SESSION['mailcow_locale'] == $language) ? ' class="active"' : ''; ?>><a href="?<?= http_build_query(array_merge($_GET, array('lang' => $language))) ?>"><span class="lang-xs lang-lbl-full" lang="<?= $language; ?>"></span></a></li>
                  <?php } ?>
                </ul>
              </div>
              <?php } ?>
            </div>
            </form>
            <?php
            if (isset($_SESSION['ldelay']) && $_SESSION['ldelay'] != '0') {
            ?>
            <p><div class="alert alert-info"><?= sprintf($lang['login']['delayed'], $_SESSION['ldelay']); ?></b></div></p>
            <?php } ?>
          <?php if(!isset($_SESSION['oauth2_request'])) { ?>
            <legend><span class="glyphicon glyphicon-link" aria-hidden="true"></span> <?=$UI_TEXTS['apps_name'];?></legend>
            <?php
            if (!empty($MAILCOW_APPS)) {
              foreach ($MAILCOW_APPS as $app) {
                if (getenv('SKIP_SOGO') == "y" && preg_match('/^\/SOGo/i', $app['link'])) { continue; }
              ?>
                <a href="<?= htmlspecialchars($app['link']); ?>" role="button" style="margin-bottom:3pt" title="<?= htmlspecialchars($app['description']); ?>" class="btn btn-primary"><?= htmlspecialchars($app['name']); ?></a>&nbsp;
              <?php
              }
              $app_links = customize('get', 'app_links');
              if (!empty($app_links)) {
                foreach ($app_links as $row) {
                  foreach ($row as $key => $val) {
                ?>
                  <a href="<?= htmlspecialchars($val); ?>" role="button" style="margin-bottom:3pt" class="btn btn-primary"><?= htmlspecialchars($key); ?></a>&nbsp;
                <?php 
                  }
                }
              }
            }
          }
          ?>
        </div>
      </div>
    </div>
    <?php if(!isset($_SESSION['oauth2_request'])) { ?>
    <div class="col-md-offset-3 col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading">
          <a data-toggle="collapse" href="#collapse1"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span> <?= $lang['start']['help']; ?></a>
        </div>
        <div id="collapse1" class="panel-collapse collapse">
          <div class="panel-body">
            <?php if ($UI_TEXTS['help_text']) { ?>
            <p><?=$UI_TEXTS['help_text'];?></p>
            <?php } else { ?>
            <p><span style="border-bottom: 1px dotted #999;"><?=$UI_TEXTS['main_name'];?></span></p>
            <p><?= $lang['start']['mailcow_panel_detail']; ?></p>
            <p><span style="border-bottom: 1px dotted #999;"><?=$UI_TEXTS['apps_name'];?></span></p>
            <p><?= $lang['start']['mailcow_apps_detail']; ?></p>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
    <?php } ?>
  </div>
</div><!-- /.container -->
<?php
$js_minifier->add('/web/js/site/index.js');
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/footer.inc.php';
