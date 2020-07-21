<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';
if (quarantine('hash_details', $_GET['hash']) === false && !isset($_POST)) {
  header('Location: /admin');
  exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/header.inc.php';
if (preg_match("/^([a-f0-9]{64})$/", $_POST['quick_release']) || preg_match("/^([a-f0-9]{64})$/", $_POST['quick_delete'])) {
?>
<div class="container">
  <div class="row">
    <div class="col-md-offset-2 col-md-8">
      <div class="panel panel-default">
        <div class="panel-heading"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> <?= $lang['header']['quarantine']; ?></div>
        <div class="panel-body">
          <legend><?=(isset($_POST['quick_release'])) ? $lang['quarantine']['release'] : $lang['quarantine']['remove'];?></legend>
            <p><?=$lang['quarantine']['qhandler_success'];?></p>
        </div>
      </div>
    </div>
  </div> <!-- /row -->
</div> <!-- /container -->
<?php
}
elseif (in_array($_GET['action'], array('release', 'delete'))) {
  if (preg_match("/^([a-f0-9]{64})$/", $_GET['hash'])) {
    if ($_GET['action'] == "release"):
?>
<div class="container">
  <div class="row">
    <div class="col-md-offset-2 col-md-8">
      <div class="panel panel-default">
        <div class="panel-heading"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> <?= $lang['header']['quarantine']; ?></div>
        <div class="panel-body">
          <legend data-hash="<?=$_GET['hash'];?>"><?=$lang['quarantine']['release'];?></legend>
            <div id="qid_error" style="display:none" class="alert alert-danger"></div>
            <div class="form-group">
              <label for="qid_detail_symbols"><h4><?=$lang['quarantine']['rspamd_result'];?>:</h4></label>
              <p><?=$lang['quarantine']['spam_score'];?>: <span id="qid_detail_score"></span></p>
              <p id="qid_detail_symbols"></p>
            </div>
            <div class="form-group">
              <label for="qid_detail_subj"><h4><?=$lang['quarantine']['subj'];?>:</h4></label>
              <p id="qid_detail_subj"></p>
            </div>
            <div class="form-group">
              <label for="qid_detail_hfrom"><h4><?=$lang['quarantine']['sender_header'];?>:</h4></label>
              <p><span class="mail-address-item" id="qid_detail_hfrom"></span></p>
            </div>
            <div class="form-group">
              <label for="qid_detail_efrom"><h4><?=$lang['quarantine']['sender'];?>:</h4></label>
              <p><span class="mail-address-item" id="qid_detail_efrom"></span></p>
            </div>
            <div class="form-group">
              <label for="qid_detail_recipients"><h4><?=$lang['quarantine']['recipients'];?>:</h4></label>
              <p id="qid_detail_recipients"></p>
            </div>
            <form method="post" autofill="off">
            <div class="form-group">
              <button type="submit" class="btn btn-success" name="quick_release" value="<?=$_GET['hash'];?>"><?= $lang['tfa']['confirm']; ?></button>
            </div>
            </form>
        </div>
      </div>
    </div>
  </div> <!-- /row -->
</div> <!-- /container -->
<?php
    elseif ($_GET['action'] == "delete"):
?>
<div class="container">
  <div class="row">
    <div class="col-md-offset-2 col-md-8">
      <div class="panel panel-default">
        <div class="panel-heading"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> <?= $lang['header']['quarantine']; ?></div>
        <div class="panel-body">
          <legend data-hash="<?=$_GET['hash'];?>"><?=$lang['quarantine']['remove'];?></legend>
            <div id="qid_error" style="display:none" class="alert alert-danger"></div>
            <div class="form-group">
              <label for="qid_detail_symbols"><h4><?=$lang['quarantine']['rspamd_result'];?>:</h4></label>
              <p><?=$lang['quarantine']['spam_score'];?>: <span id="qid_detail_score"></span></p>
              <p id="qid_detail_symbols"></p>
            </div>
            <div class="form-group">
              <label for="qid_detail_subj"><h4><?=$lang['quarantine']['subj'];?>:</h4></label>
              <p id="qid_detail_subj"></p>
            </div>
            <div class="form-group">
              <label for="qid_detail_hfrom"><h4><?=$lang['quarantine']['sender_header'];?>:</h4></label>
              <p><span class="mail-address-item" id="qid_detail_hfrom"></span></p>
            </div>
            <div class="form-group">
              <label for="qid_detail_efrom"><h4><?=$lang['quarantine']['sender'];?>:</h4></label>
              <p><span class="mail-address-item" id="qid_detail_efrom"></span></p>
            </div>
            <div class="form-group">
              <label for="qid_detail_recipients"><h4><?=$lang['quarantine']['recipients'];?>:</h4></label>
              <p id="qid_detail_recipients"></p>
            </div>
            <form method="post" autofill="off">
            <div class="form-group">
              <button type="submit" class="btn btn-success" name="quick_delete" value="<?=$_GET['hash'];?>"><?= $lang['tfa']['confirm']; ?></button>
            </div>
            </form>
        </div>
      </div>
    </div>
  </div> <!-- /row -->
</div> <!-- /container -->
<?php
    endif;
  }
}
$js_minifier->add('/web/js/site/qhandler.js');
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/footer.inc.php';
?>
