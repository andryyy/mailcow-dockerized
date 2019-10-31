<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';

if (!isset($_SESSION['mailcow_cc_role'])) {
  $_SESSION['oauth2_request'] = $_SERVER['REQUEST_URI'];
  header('Location: /?oauth');
}

$request = OAuth2\Request::createFromGlobals();
$response = new OAuth2\Response();

if (!$oauth2_server->validateAuthorizeRequest($request, $response)) {
  $response->send();
  exit();
}

if (!isset($_POST['authorized'])):
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/header.inc.php';

?>
<div class="container">
  <div class="panel panel-default">
    <div class="panel-heading"><?=$lang['oauth2']['authorize_app'];?></div>
    <div class="panel-body">
      <?php
      if ($_SESSION['mailcow_cc_role'] != 'user'):
      $request = '';
      ?>
      <p><?=$lang['oauth2']['access_denied'];?></p>
      <?php
      else:
      ?>
      <p><?=$lang['oauth2']['scope_ask_permission'];?>:</p>
      <dl class="dl-horizontal">
        <dt><?=$lang['oauth2']['profile'];?></dt>
        <dd><?=$lang['oauth2']['profile_desc'];?></dd>
      </dl>
      <form class="form-horizontal" autocapitalize="none" autocorrect="off" role="form" method="post">
        <div class="form-group">
          <div class="col-sm-10 text-center">
            <button class="btn btn-success" name="authorized" type="submit" value="1"><?=$lang['oauth2']['permit'];?></button>
            <a href="#" class="btn btn-default" onclick="window.history.back()" role="button"><?=$lang['oauth2']['deny'];?></a>
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['CSRF']['TOKEN'];?>">
          </div>
        </div>
      </form>
      <?php
      endif;
      ?>
    </div>
  </div>
</div> <!-- /container -->
<script src="../js/authorize.js"></script>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/footer.inc.php';
exit();
endif;

// print the authorization code if the user has authorized your client
$is_authorized = ($_POST['authorized'] == '1');
$oauth2_server->handleAuthorizeRequest($request, $response, $is_authorized, $_SESSION['mailcow_cc_username']);
if ($is_authorized) {
  unset($_SESSION['oauth2_request']);
  header('Location: ' . $response->getHttpHeader('Location'));
  exit;
}
