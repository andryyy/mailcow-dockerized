<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';
$AuthUsers = array("admin", "domainadmin", "user");
if (!isset($_SESSION['mailcow_cc_role']) OR !in_array($_SESSION['mailcow_cc_role'], $AuthUsers)) {
	header('Location: /');
	exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/header.inc.php';
?>
<div class="container">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><?=$lang['edit']['title'];?></h3>
				</div>
				<div class="panel-body">
<?php
if (isset($_SESSION['mailcow_cc_role'])) {
  if ($_SESSION['mailcow_cc_role'] == "admin"  || $_SESSION['mailcow_cc_role'] == "domainadmin") {
    if (isset($_GET["alias"]) &&
      !empty($_GET["alias"])) {
        $alias = html_entity_decode(rawurldecode($_GET["alias"]));
        $result = mailbox('get', 'alias_details', $alias);
        if (!empty($result)) {
        ?>
          <h4><?=$lang['edit']['alias'];?></h4>
          <br />
          <form class="form-horizontal" data-id="editalias" role="form" method="post">
            <input type="hidden" value="0" name="active">
            <input type="hidden" value="0" name="sogo_visible">
            <div class="form-group">
              <label class="control-label col-sm-2" for="address"><?=$lang['edit']['alias'];?></label>
              <div class="col-sm-10">
                <input class="form-control" type="text" name="address" value="<?=htmlspecialchars($result['address']);?>" />
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="goto"><?=$lang['edit']['target_address'];?></label>
              <div class="col-sm-10">
                <textarea id="textarea_alias_goto" class="form-control" autocapitalize="none" autocorrect="off" rows="10" id="goto" name="goto" required><?= (!preg_match('/^(null|ham|spam)@localhost$/i', $result['goto'])) ? str_replace(',', ', ', htmlspecialchars($result['goto'])) : null; ?></textarea>
                <div class="checkbox">
                  <label><input class="goto_checkbox" type="checkbox" value="1" name="goto_null" <?= ($result['goto'] == "null@localhost") ? "checked" : null; ?>> <?=$lang['add']['goto_null'];?></label>
                </div>
                <div class="checkbox">
                  <label><input class="goto_checkbox" type="checkbox" value="1" name="goto_spam" <?= ($result['goto'] == "spam@localhost") ? "checked" : null; ?>> <?=$lang['add']['goto_spam'];?></label>
                </div>
                <div class="checkbox">
                  <label><input class="goto_checkbox" type="checkbox" value="1" name="goto_ham" <?= ($result['goto'] == "ham@localhost") ? "checked" : null; ?>> <?=$lang['add']['goto_ham'];?></label>
                </div>
                <hr>
                <div class="checkbox">
                  <label><input type="checkbox" value="1" name="sogo_visible" <?php if (isset($result['sogo_visible_int']) && $result['sogo_visible_int']=="1") { echo "checked"; }; ?>> <?=$lang['edit']['sogo_visible'];?></label>
                </div>
                <p class="help-block"><?=$lang['edit']['sogo_visible_info'];?></p>
              </div>
            </div>
            <hr>
            <div class="form-group">
              <label class="control-label col-sm-2" for="private_"><?=$lang['edit']['private_comment'];?></label>
              <div class="col-sm-10">
                <input maxlength="160" class="form-control" type="text" name="private_comment" value="<?=htmlspecialchars($result['private_comment']);?>" />
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="public_comment"><?=$lang['edit']['public_comment'];?></label>
              <div class="col-sm-10">
                <input maxlength="160" class="form-control" type="text" name="public_comment" value="<?=htmlspecialchars($result['public_comment']);?>" />
              </div>
            </div>
            <hr>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="active" <?php if (isset($result['active_int']) && $result['active_int']=="1") { echo "checked"; }; ?>> <?=$lang['edit']['active'];?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-success" data-action="edit_selected" data-id="editalias" data-item="<?=htmlspecialchars($alias);?>" data-api-url='edit/alias' data-api-attr='{}' href="#"><?=$lang['edit']['save'];?></button>
              </div>
            </div>
          </form>
        <?php
        }
        else {
        ?>
          <div class="alert alert-info" role="alert"><?=$lang['info']['no_action'];?></div>
        <?php
        }
    }
    elseif (isset($_GET['domainadmin'])) {
      $domain_admin = $_GET["domainadmin"];
      $result = domain_admin('details', $domain_admin);
      if (!empty($result)) {
      ?>
      <h4><?=$lang['edit']['domain_admin'];?></h4>
      <br />
      <form class="form-horizontal" data-id="editdomainadmin" role="form" method="post">
        <input type="hidden" value="0" name="active">
        <div class="form-group">
          <label class="control-label col-sm-2" for="username_new"><?=$lang['edit']['username'];?></label>
          <div class="col-sm-10">
            <input class="form-control" type="text" name="username_new" value="<?=htmlspecialchars($domain_admin);?>" />
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-2" for="domains"><?=$lang['edit']['domains'];?></label>
          <div class="col-sm-10">
            <select data-live-search="true" class="full-width-select" name="domains" multiple required>
            <?php
            foreach ($result['selected_domains'] as $domain):
            ?>
              <option selected><?=htmlspecialchars($domain);?></option>
            <?php
            endforeach;
            foreach ($result['unselected_domains'] as $domain):
            ?>
              <option><?=htmlspecialchars($domain);?></option>
            <?php
            endforeach;
            ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-2" for="password"><?=$lang['edit']['password'];?></label>
          <div class="col-sm-10">
          <input type="password" data-hibp="true" class="form-control" name="password" placeholder="">
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-2" for="password2"><?=$lang['edit']['password_repeat'];?></label>
          <div class="col-sm-10">
          <input type="password" class="form-control" name="password2">
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <div class="checkbox">
            <label><input type="checkbox" value="1" name="active" <?php if (isset($result['active_int']) && $result['active_int']=="1") { echo "checked"; }; ?>> <?=$lang['edit']['active'];?></label>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <div class="checkbox">
            <label><input type="checkbox" value="1" name="disable_tfa"> <?=$lang['tfa']['disable_tfa'];?></label>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <button class="btn btn-success" data-action="edit_selected" data-api-reload-location="/admin" data-id="editdomainadmin" data-item="<?=$domain_admin;?>" data-api-url='edit/domain-admin' data-api-attr='{}' href="#"><?=$lang['edit']['save'];?></button>
          </div>
        </div>
      </form>
      <form data-id="daacl" class="form-inline well" method="post">
        <div class="row">
          <div class="col-sm-1">
            <p class="help-block">ACL</p>
          </div>
          <div class="col-sm-10">
            <div class="form-group">
              <select id="da_acl" name="da_acl" size="10" multiple>
              <?php
              $da_acls = acl('get', 'domainadmin', $domain_admin);
              foreach ($da_acls as $acl => $val):
                ?>
                <option value="<?=$acl;?>" <?=($val == 1) ? 'selected' : null;?>><?=$lang['acl'][$acl];?></option>
                <?php
              endforeach;
              ?>
              </select>
            </div>
            <div class="form-group">
              <button class="btn btn-default" data-action="edit_selected" data-id="daacl" data-item="<?=htmlspecialchars($domain_admin);?>" data-api-url='edit/da-acl' data-api-attr='{}' href="#"><?=$lang['admin']['save'];?></button>
            </div>
          </div>
        </div>
      </form>
      <?php
      }
      else {
      ?>
        <div class="alert alert-info" role="alert"><?=$lang['info']['no_action'];?></div>
      <?php
      }
    }
    elseif (isset($_GET['admin'])) {
      $admin = $_GET["admin"];
      $result = admin('details', $admin);
      if (!empty($result)) {
      ?>
      <h4><?=$lang['edit']['domain_admin'];?></h4>
      <br />
      <form class="form-horizontal" data-id="editadmin" role="form" method="post">
        <input type="hidden" value="0" name="active">
        <div class="form-group">
          <label class="control-label col-sm-2" for="username_new"><?=$lang['edit']['username'];?></label>
          <div class="col-sm-10">
            <input class="form-control" type="text" name="username_new" value="<?=htmlspecialchars($admin);?>" />
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-2" for="password"><?=$lang['edit']['password'];?></label>
          <div class="col-sm-10">
          <input type="password" data-hibp="true" class="form-control" name="password" placeholder="">
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-2" for="password2"><?=$lang['edit']['password_repeat'];?></label>
          <div class="col-sm-10">
          <input type="password" class="form-control" name="password2">
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <div class="checkbox">
            <label><input type="checkbox" value="1" name="active" <?php if (isset($result['active_int']) && $result['active_int']=="1") { echo "checked"; }; ?>> <?=$lang['edit']['active'];?></label>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <div class="checkbox">
            <label><input type="checkbox" value="1" name="disable_tfa"> <?=$lang['tfa']['disable_tfa'];?></label>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <button class="btn btn-success" data-action="edit_selected" data-api-reload-location="/admin" data-id="editadmin" data-item="<?=$admin;?>" data-api-url='edit/admin' data-api-attr='{}' href="#"><?=$lang['edit']['save'];?></button>
          </div>
        </div>
      </form>
      <?php
      }
      else {
      ?>
        <div class="alert alert-info" role="alert"><?=$lang['info']['no_action'];?></div>
      <?php
      }
    }
    elseif (isset($_GET['domain']) &&
      is_valid_domain_name($_GET["domain"]) &&
      !empty($_GET["domain"])) {
        $domain = $_GET["domain"];
        $result = mailbox('get', 'domain_details', $domain);
        $rl = ratelimit('get', 'domain', $domain);
        $rlyhosts = relayhost('get');
        if (!empty($result)) {
        ?>
          <h4><?=$lang['edit']['domain'];?></h4>
          <form data-id="editdomain" class="form-horizontal" role="form" method="post">
            <input type="hidden" value="0" name="active">
            <input type="hidden" value="0" name="backupmx">
            <input type="hidden" value="0" name="gal">
            <input type="hidden" value="0" name="relay_all_recipients">
            <div class="form-group">
              <label class="control-label col-sm-2" for="description"><?=$lang['edit']['description'];?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="description" value="<?=htmlspecialchars($result['description']);?>">
              </div>
            </div>
            <?php
            if ($_SESSION['mailcow_cc_role'] == "admin") {
            ?>
            <div class="form-group">
              <label class="control-label col-sm-2" for="aliases"><?=$lang['edit']['max_aliases'];?></label>
              <div class="col-sm-10">
                <input type="number" class="form-control" name="aliases" value="<?=intval($result['max_num_aliases_for_domain']);?>">
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="mailboxes"><?=$lang['edit']['max_mailboxes'];?></label>
              <div class="col-sm-10">
                <input type="number" class="form-control" name="mailboxes" value="<?=intval($result['max_num_mboxes_for_domain']);?>">
              </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-2" for="defquota"><?=$lang['edit']['mailbox_quota_def'];?></label>
                <div class="col-sm-10">
                    <input type="number" class="form-control" name="defquota" value="<?=intval($result['def_quota_for_mbox'] / 1048576);?>">
                </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="maxquota"><?=$lang['edit']['max_quota'];?></label>
              <div class="col-sm-10">
                <input type="number" class="form-control" name="maxquota" value="<?=intval($result['max_quota_for_mbox'] / 1048576);?>">
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="quota"><?=$lang['edit']['domain_quota'];?></label>
              <div class="col-sm-10">
                <input type="number" class="form-control" name="quota" value="<?=intval($result['max_quota_for_domain'] / 1048576);?>">
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="quota"><?=$lang['edit']['relayhost'];?></label>
              <div class="col-sm-10">
                <select data-live-search="true" name="relayhost" class="form-control">
                  <?php
                  foreach ($rlyhosts as $rlyhost) {
                  ?>
                  <option value="<?=$rlyhost['id'];?>" <?=($result['relayhost'] == $rlyhost['id']) ? 'selected' : null;?>>ID <?=$rlyhost['id'];?>: <?=$rlyhost['hostname'];?> (<?=$rlyhost['username'];?>)</option>
                  <?php
                  }
                  ?>
                  <option value="" <?=($result['relayhost'] == "0") ? 'selected' : null;?>>None</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2"><?=$lang['edit']['backup_mx_options'];?></label>
              <div class="col-sm-10">
                <div class="checkbox">
                  <label><input type="checkbox" value="1" name="backupmx" <?=(isset($result['backupmx_int']) && $result['backupmx_int']=="1") ? "checked" : null;?>> <?=$lang['edit']['relay_domain'];?></label>
                  <br />
                  <label><input type="checkbox" value="1" name="relay_all_recipients" <?=(isset($result['relay_all_recipients_int']) && $result['relay_all_recipients_int']=="1") ? "checked" : null;?>> <?=$lang['edit']['relay_all'];?></label>
                  <p><?=$lang['edit']['relay_all_info'];?></p>
                </div>
              </div>
            </div>
            <?php
            }
            ?>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                  <label><input type="checkbox" value="1" name="gal" <?=(isset($result['gal_int']) && $result['gal_int']=="1") ? "checked" : null;?>> <?=$lang['edit']['gal'];?></label>
                  <small class="help-block"><?=$lang['edit']['gal_info'];?></small>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                  <label><input type="checkbox" value="1" name="active" <?=(isset($result['active_int']) && $result['active_int']=="1") ? "checked" : null;?> <?=($_SESSION['mailcow_cc_role'] == "admin") ? null : "disabled";?>> <?=$lang['edit']['active'];?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-success" data-action="edit_selected" data-id="editdomain" data-item="<?=$domain;?>" data-api-url='edit/domain' data-api-attr='{}' href="#"><?=$lang['admin']['save'];?></button>
              </div>
            </div>
          </form>
          <?php
          if (!empty($dkim = dkim('details', $domain))) {
          ?>
          <hr>
          <div class="row">
            <div class="col-xs-2">
              <p>Domain: <strong><?=htmlspecialchars($result['domain_name']);?></strong> (<?=$dkim['dkim_selector'];?>._domainkey)</p>
            </div>
            <div class="col-xs-10">
              <pre><?=$dkim['dkim_txt'];?></pre>
            </div>
          </div>
          <?php
          }
          ?>
      <hr>
      <form data-id="domratelimit" class="form-inline well" method="post">
        <div class="form-group">
          <label class="control-label"><?=$lang['acl']['ratelimit'];?></label>
          <input name="rl_value" type="number" value="<?=(!empty($rl['value'])) ? $rl['value'] : null;?>" autocomplete="off" class="form-control" placeholder="disabled">
        </div>
        <div class="form-group">
          <select name="rl_frame" class="form-control">
            <option value="s" <?=(isset($rl['frame']) && $rl['frame'] == 's') ? 'selected' : null;?>>msgs / second</option>
            <option value="m" <?=(isset($rl['frame']) && $rl['frame'] == 'm') ? 'selected' : null;?>>msgs / minute</option>
            <option value="h" <?=(isset($rl['frame']) && $rl['frame'] == 'h') ? 'selected' : null;?>>msgs / hour</option>
          </select>
        </div>
        <div class="form-group">
          <button data-acl="<?=$_SESSION['acl']['ratelimit'];?>" class="btn btn-default" data-action="edit_selected" data-id="domratelimit" data-item="<?=$domain;?>" data-api-url='edit/rl-domain' data-api-attr='{}' href="#"><?=$lang['admin']['save'];?></button>
        </div>
      </form>
      <hr>
      <div class="row">
        <div class="col-sm-6">
          <h4><?=$lang['user']['spamfilter_wl'];?></h4>
          <p><?=$lang['user']['spamfilter_wl_desc'];?></p>
          <div class="table-responsive">
            <table class="table table-striped table-condensed" id="wl_policy_domain_table"></table>
          </div>
          <div class="mass-actions-user">
            <div class="btn-group" data-acl="<?=$_SESSION['acl']['spam_policy'];?>">
              <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="policy_wl_domain" href="#"><span class="glyphicon glyphicon-check" aria-hidden="true"></span> <?=$lang['mailbox']['toggle_all'];?></a>
              <a class="btn btn-sm btn-danger" data-action="delete_selected" data-id="policy_wl_domain" data-api-url='delete/domain-policy' href="#"><?=$lang['mailbox']['remove'];?></a></li>
            </div>
          </div>
          <form class="form-inline" data-id="add_wl_policy_domain">
            <div class="input-group" data-acl="<?=$_SESSION['acl']['spam_policy'];?>">
              <input type="text" class="form-control" name="object_from" placeholder="*@example.org" required>
              <span class="input-group-btn">
                <button class="btn btn-default" data-action="add_item" data-id="add_wl_policy_domain" data-api-url='add/domain-policy' data-api-attr='{"domain":"<?= $domain; ?>","object_list":"wl"}' href="#"><?=$lang['user']['spamfilter_table_add'];?></button>
              </span>
            </div>
          </form>
        </div>
        <div class="col-sm-6">
          <h4><?=$lang['user']['spamfilter_bl'];?></h4>
          <p><?=$lang['user']['spamfilter_bl_desc'];?></p>
          <div class="table-responsive">
            <table class="table table-striped table-condensed" id="bl_policy_domain_table"></table>
          </div>
          <div class="mass-actions-user">
            <div class="btn-group" data-acl="<?=$_SESSION['acl']['spam_policy'];?>">
              <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="policy_bl_domain" href="#"><span class="glyphicon glyphicon-check" aria-hidden="true"></span> <?=$lang['mailbox']['toggle_all'];?></a>
              <a class="btn btn-sm btn-danger" data-action="delete_selected" data-id="policy_bl_domain" data-api-url='delete/domain-policy' href="#"><?=$lang['mailbox']['remove'];?></a></li>
            </div>
          </div>
          <form class="form-inline" data-id="add_bl_policy_domain">
            <div class="input-group" data-acl="<?=$_SESSION['acl']['spam_policy'];?>">
              <input type="text" class="form-control" name="object_from" placeholder="*@example.org" required>
              <span class="input-group-btn">
                <button class="btn btn-default" data-action="add_item" data-id="add_bl_policy_domain" data-api-url='add/domain-policy' data-api-attr='{"domain":"<?= $domain; ?>","object_list":"bl"}' href="#"><?=$lang['user']['spamfilter_table_add'];?></button>
              </span>
            </div>
          </form>
        </div>
      </div>
          <?php
        }
        else {
        ?>
          <div class="alert alert-info" role="alert"><?=$lang['info']['no_action'];?></div>
        <?php
        }
    }
    elseif (isset($_GET['oauth2client']) &&
      is_numeric($_GET["oauth2client"]) &&
      !empty($_GET["oauth2client"])) {
        $oauth2client = $_GET["oauth2client"];
        $result = oauth2('details', 'client', $oauth2client);
        if (!empty($result)) {
        ?>
          <h4>OAuth2</h4>
          <form data-id="oauth2client" class="form-horizontal" role="form" method="post">
            <div class="form-group">
              <label class="control-label col-sm-2" for="client_id"><?=$lang['edit']['client_id'];?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="client_id" id="client_id" value="<?=htmlspecialchars($result['client_id']);?>" disabled>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="client_secret"><?=$lang['edit']['client_secret'];?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="client_secret" id="client_secret" value="<?=htmlspecialchars($result['client_secret']);?>" disabled>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="scope"><?=$lang['edit']['scope'];?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="scope" id="scope" value="<?=htmlspecialchars($result['scope']);?>" disabled>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="redirect_uri"><?=$lang['edit']['redirect_uri'];?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="redirect_uri" id="redirect_uri" value="<?=htmlspecialchars($result['redirect_uri']);?>">
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-default" data-action="edit_selected" data-id="oauth2client" data-item="<?=$oauth2client;?>" data-api-url='edit/oauth2-client' data-api-attr='{}' href="#"><?=$lang['admin']['save'];?></button>
              </div>
            </div>
          </form>
          <?php
        }
        else {
        ?>
          <div class="alert alert-info" role="alert"><?=$lang['info']['no_action'];?></div>
        <?php
        }
    }
    elseif (isset($_GET['aliasdomain']) &&
      is_valid_domain_name(html_entity_decode(rawurldecode($_GET["aliasdomain"]))) &&
      !empty($_GET["aliasdomain"])) {
        $alias_domain = html_entity_decode(rawurldecode($_GET["aliasdomain"]));
        $result = mailbox('get', 'alias_domain_details', $alias_domain);
        $rl = ratelimit('get', 'domain', $alias_domain);
        if (!empty($result)) {
        ?>
          <h4><?=$lang['edit']['edit_alias_domain'];?></h4>
          <form class="form-horizontal" data-id="editaliasdomain" role="form" method="post">
            <input type="hidden" value="0" name="active">
            <div class="form-group">
              <label class="control-label col-sm-2" for="target_domain"><?=$lang['edit']['target_domain'];?></label>
              <div class="col-sm-10">
                <select class="full-width-select" data-live-search="true" id="addSelectDomain" name="target_domain" required>
                <?php
                foreach (mailbox('get', 'domains') as $domain):
                ?>
                  <option <?=($result['target_domain'] != $domain) ?: 'selected';?>><?=htmlspecialchars($domain);?></option>
                <?php
                endforeach;
                ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                  <label><input type="checkbox" value="1" name="active" <?=(isset($result['active_int']) && $result['active_int']=="1") ?  "checked" : null ?>> <?=$lang['edit']['active'];?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-success" data-action="edit_selected" data-id="editaliasdomain" data-item="<?=$alias_domain;?>" data-api-url='edit/alias-domain' data-api-attr='{}' href="#"><?=$lang['edit']['save'];?></button>
              </div>
            </div>
          </form>
          <hr>
          <form data-id="domratelimit" class="form-inline well" method="post">
            <div class="form-group">
              <label class="control-label"><?=$lang['acl']['ratelimit'];?></label>
              <input name="rl_value" type="number" value="<?=(!empty($rl['value'])) ? $rl['value'] : null;?>" autocomplete="off" class="form-control" placeholder="disabled">
            </div>
            <div class="form-group">
              <select name="rl_frame" class="form-control">
                <option value="s" <?=(isset($rl['frame']) && $rl['frame'] == 's') ? 'selected' : null;?>>msgs / second</option>
                <option value="m" <?=(isset($rl['frame']) && $rl['frame'] == 'm') ? 'selected' : null;?>>msgs / minute</option>
                <option value="h" <?=(isset($rl['frame']) && $rl['frame'] == 'h') ? 'selected' : null;?>>msgs / hour</option>
              </select>
            </div>
            <div class="form-group">
              <button class="btn btn-default" data-action="edit_selected" data-id="domratelimit" data-item="<?=$alias_domain;?>" data-api-url='edit/rl-domain' data-api-attr='{}' href="#"><?=$lang['admin']['save'];?></button>
            </div>
          </form>
          <?php
          if (!empty($dkim = dkim('details', $alias_domain))) {
          ?>
          <hr>
          <div class="row">
            <div class="col-xs-2">
              <p>Domain: <strong><?=htmlspecialchars($result['alias_domain']);?></strong> (<?=$dkim['dkim_selector'];?>._domainkey)</p>
            </div>
            <div class="col-xs-10">
            <pre><?=$dkim['dkim_txt'];?></pre>
            </div>
          </div>
          <?php
          }
        }
        else {
        ?>
          <div class="alert alert-info" role="alert"><?=$lang['info']['no_action'];?></div>
        <?php
        }
    }
    elseif (isset($_GET['mailbox']) && filter_var(html_entity_decode(rawurldecode($_GET["mailbox"])), FILTER_VALIDATE_EMAIL) && !empty($_GET["mailbox"])) {
      $mailbox = html_entity_decode(rawurldecode($_GET["mailbox"]));
      $result = mailbox('get', 'mailbox_details', $mailbox);
      $rl = ratelimit('get', 'mailbox', $mailbox);
      $quarantine_notification = mailbox('get', 'quarantine_notification', $mailbox);
      if (!empty($result)) {
        ?>
        <h4><?=$lang['edit']['mailbox'];?></h4>
        <form class="form-horizontal" data-id="editmailbox" role="form" method="post">
          <input type="hidden" value="default" name="sender_acl">
          <input type="hidden" value="0" name="active">
          <input type="hidden" value="0" name="force_pw_update">
          <input type="hidden" value="0" name="sogo_access">
          <div class="form-group">
            <label class="control-label col-sm-2" for="name"><?=$lang['edit']['full_name'];?></label>
            <div class="col-sm-10">
            <input type="text" class="form-control" name="name" value="<?=htmlspecialchars($result['name'], ENT_QUOTES, 'UTF-8');?>">
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="quota"><?=$lang['edit']['quota_mb'];?>
              <br /><span id="quotaBadge" class="badge">max. <?=intval($result['max_new_quota'] / 1048576)?> MiB</span>
            </label>
            <div class="col-sm-10">
              <input type="number" name="quota" style="width:100%" min="0" max="<?=intval($result['max_new_quota'] / 1048576);?>" value="<?=intval($result['quota']) / 1048576;?>" class="form-control">
              <small class="help-block">0 = ∞</small>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="sender_acl"><?=$lang['edit']['sender_acl'];?></label>
            <div class="col-sm-10">
              <select data-live-search="true" data-width="100%" style="width:100%" id="editSelectSenderACL" name="sender_acl" size="10" multiple>
              <?php
              $sender_acl_handles = mailbox('get', 'sender_acl_handles', $mailbox);

              foreach ($sender_acl_handles['sender_acl_domains']['ro'] as $domain):
                ?>
                <option data-subtext="Admin" value="<?=htmlspecialchars($domain);?>" disabled selected><?=htmlspecialchars(sprintf($lang['edit']['dont_check_sender_acl'], $domain));?></option>
                <?php
              endforeach;

              foreach ($sender_acl_handles['sender_acl_addresses']['ro'] as $alias):
                ?>
              <option data-subtext="Admin" disabled selected><?=htmlspecialchars($alias);?></option>
                <?php
              endforeach;

              foreach ($sender_acl_handles['fixed_sender_aliases'] as $alias):
                ?>
                <option data-subtext="Alias" disabled selected><?=htmlspecialchars($alias);?></option>
                <?php
              endforeach;

              foreach ($sender_acl_handles['sender_acl_domains']['rw'] as $domain):
                ?>
                <option value="<?=htmlspecialchars($domain);?>" selected><?=htmlspecialchars(sprintf($lang['edit']['dont_check_sender_acl'], $domain));?></option>
                <?php
              endforeach;

              foreach ($sender_acl_handles['sender_acl_domains']['selectable'] as $domain):
                ?>
                <option value="<?=htmlspecialchars($domain);?>"><?=htmlspecialchars(sprintf($lang['edit']['dont_check_sender_acl'], $domain));?></option>
                <?php
              endforeach;

              foreach ($sender_acl_handles['sender_acl_addresses']['rw'] as $address):
                ?>
                  <option selected><?=htmlspecialchars($address);?></option>
                <?php
              endforeach;

              foreach ($sender_acl_handles['sender_acl_addresses']['selectable'] as $address):
                ?>
                  <option><?=htmlspecialchars($address);?></option>
                <?php
              endforeach;

              // Generated here, but used in extended_sender_acl
              if (!empty($sender_acl_handles['external_sender_aliases'])) {
                $ext_sender_acl = implode(', ', $sender_acl_handles['external_sender_aliases']);
              }
              else {
                $ext_sender_acl = '';
              }

              ?>
              </select>
              <div style="display:none" id="sender_acl_disabled"><?=$lang['edit']['sender_acl_disabled'];?></div>
              <small class="help-block"><?=$lang['edit']['sender_acl_info'];?></small>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="sender_acl"><?=$lang['user']['quarantine_notification'];?></label>
            <div class="col-sm-10">
            <div class="btn-group" data-acl="<?=$_SESSION['acl']['quarantine_notification'];?>">
              <button type="button" class="btn btn-sm btn-default <?=($quarantine_notification == "never") ? "active" : null;?>"
                data-action="edit_selected"
                data-item="<?= htmlentities($mailbox); ?>"
                data-id="quarantine_notification"
                data-api-url='edit/quarantine_notification'
                data-api-attr='{"quarantine_notification":"never"}'><?=$lang['user']['never'];?></button>
              <button type="button" class="btn btn-sm btn-default <?=($quarantine_notification == "hourly") ? "active" : null;?>"
                data-action="edit_selected"
                data-item="<?= htmlentities($mailbox); ?>"
                data-id="quarantine_notification"
                data-api-url='edit/quarantine_notification'
                data-api-attr='{"quarantine_notification":"hourly"}'><?=$lang['user']['hourly'];?></button>
              <button type="button" class="btn btn-sm btn-default <?=($quarantine_notification == "daily") ? "active" : null;?>"
                data-action="edit_selected"
                data-item="<?= htmlentities($mailbox); ?>"
                data-id="quarantine_notification"
                data-api-url='edit/quarantine_notification'
                data-api-attr='{"quarantine_notification":"daily"}'><?=$lang['user']['daily'];?></button>
              <button type="button" class="btn btn-sm btn-default <?=($quarantine_notification == "weekly") ? "active" : null;?>"
                data-action="edit_selected"
                data-item="<?= htmlentities($mailbox); ?>"
                data-id="quarantine_notification"
                data-api-url='edit/quarantine_notification'
                data-api-attr='{"quarantine_notification":"weekly"}'><?=$lang['user']['weekly'];?></button>
            </div>
            <div style="display:none" id="user_acl_q_notify_disabled"><?=$lang['edit']['user_acl_q_notify_disabled'];?></div>
            <p class="help-block"><small><?=$lang['user']['quarantine_notification_info'];?></small></p>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="password"><?=$lang['edit']['password'];?></label>
            <div class="col-sm-10">
            <input type="password" data-hibp="true" class="form-control" name="password" placeholder="<?=$lang['edit']['unchanged_if_empty'];?>">
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="password2"><?=$lang['edit']['password_repeat'];?></label>
            <div class="col-sm-10">
            <input type="password" class="form-control" name="password2">
            </div>
          </div>
          <div data-acl="<?=$_SESSION['acl']['extend_sender_acl'];?>" class="form-group">
            <label class="control-label col-sm-2" for="extended_sender_acl"><?=$lang['edit']['extended_sender_acl'];?></label>
            <div class="col-sm-10">
            <input type="text" class="form-control" name="extended_sender_acl" value="<?=empty($ext_sender_acl) ? '' : $ext_sender_acl; ?>" placeholder="user1@example.com, user2@example.org, @example.com, ...">
            <small class="help-block"><?=$lang['edit']['extended_sender_acl_info'];?></small>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <div class="checkbox">
              <label><input type="checkbox" value="1" name="active" <?=($result['active_int']=="1") ? "checked" : null;?>> <?=$lang['edit']['active'];?></label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <div class="checkbox">
              <label><input type="checkbox" value="1" name="force_pw_update" <?=($result['attributes']['force_pw_update']=="1") ? "checked" : null;?>> <?=$lang['edit']['force_pw_update'];?></label>
              <small class="help-block"><?=$lang['edit']['force_pw_update_info'];?></small>
              </div>
            </div>
          </div>
          <div data-acl="<?=$_SESSION['acl']['sogo_access'];?>" class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <div class="checkbox">
              <label><input type="checkbox" value="1" name="sogo_access" <?=($result['attributes']['sogo_access']=="1") ? "checked" : null;?>> <?=$lang['edit']['sogo_access'];?></label>
              <small class="help-block"><?=$lang['edit']['sogo_access_info'];?></small>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <button class="btn btn-success" data-action="edit_selected" data-id="editmailbox" data-item="<?=htmlspecialchars($result['username']);?>" data-api-url='edit/mailbox' data-api-attr='{}' href="#"><?=$lang['edit']['save'];?></button>
            </div>
          </div>
        </form>
        <hr>
        <form data-id="mboxratelimit" class="form-inline well" method="post">
          <div class="row">
            <div class="col-sm-1">
              <p class="help-block"><?=$lang['acl']['ratelimit'];?></p>
            </div>
            <div class="col-sm-10">
              <div class="form-group">
                <input name="rl_value" type="number" autocomplete="off" value="<?=(!empty($rl['value'])) ? $rl['value'] : null;?>" class="form-control" placeholder="disabled">
              </div>
              <div class="form-group">
                <select name="rl_frame" class="form-control">
                  <option value="s" <?=(isset($rl['frame']) && $rl['frame'] == 's') ? 'selected' : null;?>>msgs / second</option>
                  <option value="m" <?=(isset($rl['frame']) && $rl['frame'] == 'm') ? 'selected' : null;?>>msgs / minute</option>
                  <option value="h" <?=(isset($rl['frame']) && $rl['frame'] == 'h') ? 'selected' : null;?>>msgs / hour</option>
                </select>
              </div>
              <div class="form-group">
                <button class="btn btn-default" data-action="edit_selected" data-id="mboxratelimit" data-item="<?=htmlspecialchars($mailbox);?>" data-api-url='edit/rl-mbox' data-api-attr='{}' href="#"><?=$lang['admin']['save'];?></button>
              </div>
              <p class="help-block"><?=$lang['edit']['mbox_rl_info'];?></p>
            </div>
          </div>
        </form>
        <form data-id="useracl" class="form-inline well" method="post">
          <div class="row">
            <div class="col-sm-1">
              <p class="help-block">ACL</p>
            </div>
            <div class="col-sm-10">
              <div class="form-group">
                <select id="user_acl" name="user_acl" size="10" multiple>
                <?php
                $user_acls = acl('get', 'user', $mailbox);
                foreach ($user_acls as $acl => $val):
                  ?>
                  <option value="<?=$acl;?>" <?=($val == 1) ? 'selected' : null;?>><?=$lang['acl'][$acl];?></option>
                  <?php
                endforeach;
                ?>
                </select>
              </div>
              <div class="form-group">
                <button class="btn btn-default" data-action="edit_selected" data-id="useracl" data-item="<?=htmlspecialchars($mailbox);?>" data-api-url='edit/user-acl' data-api-attr='{}' href="#"><?=$lang['admin']['save'];?></button>
              </div>
            </div>
          </div>
        </form>
      <?php
      }
    }
    elseif (isset($_GET['relayhost']) && is_numeric($_GET["relayhost"]) && !empty($_GET["relayhost"])) {
        $relayhost = intval($_GET["relayhost"]);
        $result = relayhost('details', $relayhost);
        if (!empty($result)) {
          ?>
          <h4><?=$lang['edit']['resource'];?></h4>
          <form class="form-horizontal" role="form" method="post" data-id="editrelayhost">
            <input type="hidden" value="0" name="active">
            <div class="form-group">
              <label class="control-label col-sm-2" for="hostname"><?=$lang['add']['hostname'];?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="hostname" value="<?=htmlspecialchars($result['hostname'], ENT_QUOTES, 'UTF-8');?>" required>
                <p class="help-block"><?=$lang['add']['relayhost_wrapped_tls_info'];?></p>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="username"><?=$lang['add']['username'];?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="username" value="<?=htmlspecialchars($result['username'], ENT_QUOTES, 'UTF-8');?>">
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="password"><?=$lang['add']['password'];?></label>
              <div class="col-sm-10">
                <input type="password" data-hibp="true" class="form-control" name="password" value="<?=htmlspecialchars($result['password'], ENT_QUOTES, 'UTF-8');?>">
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="active" <?=($result['active_int']=="1") ? "checked" : null;?>> <?=$lang['edit']['active'];?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-success" data-action="edit_selected" data-id="editrelayhost" data-item="<?=htmlspecialchars($result['id']);?>" data-api-url='edit/relayhost' data-api-attr='{}' href="#"><?=$lang['edit']['save'];?></button>
              </div>
            </div>
          </form>
        <?php
        }
        else {
        ?>
          <div class="alert alert-info" role="alert"><?=$lang['info']['no_action'];?></div>
        <?php
        }
    }
    elseif (isset($_GET['transport']) && is_numeric($_GET["transport"]) && !empty($_GET["transport"])) {
        $transport = intval($_GET["transport"]);
        $result = transport('details', $transport);
        if (!empty($result)) {
          ?>
          <h4><?=$lang['edit']['resource'];?></h4>
          <form class="form-horizontal" role="form" method="post" data-id="edittransport">
            <input type="hidden" value="0" name="active">
            <div class="form-group">
              <label class="control-label col-sm-2" for="destination"><?=$lang['add']['destination'];?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="destination" value="<?=htmlspecialchars($result['destination'], ENT_QUOTES, 'UTF-8');?>" required>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="nexthop"><?=$lang['edit']['nexthop'];?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="nexthop" value="<?=htmlspecialchars($result['nexthop'], ENT_QUOTES, 'UTF-8');?>" required>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="username"><?=$lang['add']['username'];?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="username" value="<?=htmlspecialchars($result['username'], ENT_QUOTES, 'UTF-8');?>">
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="password"><?=$lang['add']['password'];?></label>
              <div class="col-sm-10">
                <input type="password" data-hibp="true" class="form-control" name="password" value="<?=htmlspecialchars($result['password'], ENT_QUOTES, 'UTF-8');?>">
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="active" <?=($result['active_int']=="1") ? "checked" : null;?>> <?=$lang['edit']['active'];?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-success" data-action="edit_selected" data-id="edittransport" data-item="<?=htmlspecialchars($result['id']);?>" data-api-url='edit/transport' data-api-attr='{}' href="#"><?=$lang['edit']['save'];?></button>
              </div>
            </div>
          </form>
        <?php
        }
        else {
        ?>
          <div class="alert alert-info" role="alert"><?=$lang['info']['no_action'];?></div>
        <?php
        }
    }
    elseif (isset($_GET['resource']) && filter_var(html_entity_decode(rawurldecode($_GET["resource"])), FILTER_VALIDATE_EMAIL) && !empty($_GET["resource"])) {
        $resource = html_entity_decode(rawurldecode($_GET["resource"]));
        $result = mailbox('get', 'resource_details', $resource);
        if (!empty($result)) {
          ?>
          <h4><?=$lang['edit']['resource'];?></h4>
          <form class="form-horizontal" role="form" method="post" data-id="editresource">
            <input type="hidden" value="0" name="active">
            <div class="form-group">
              <label class="control-label col-sm-2" for="description"><?=$lang['add']['description'];?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="description" value="<?=htmlspecialchars($result['description'], ENT_QUOTES, 'UTF-8');?>" required>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="domain"><?=$lang['edit']['kind'];?></label>
              <div class="col-sm-10">
                <select name="kind" title="<?=$lang['edit']['select'];?>" required>
                  <option value="location" <?=($result['kind'] == "location") ? "selected" : null;?>>Location</option>
                  <option value="group" <?=($result['kind'] == "group") ? "selected" : null;?>>Group</option>
                  <option value="thing" <?=($result['kind'] == "thing") ? "selected" : null;?>>Thing</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="multiple_bookings_select"><?=$lang['add']['multiple_bookings'];?></label>
              <div class="col-sm-10">
                <select name="multiple_bookings_select" id="editSelectMultipleBookings" title="<?=$lang['add']['select'];?>" required>
                  <option value="0" <?=($result['multiple_bookings'] == 0) ? "selected" : null;?>><?=$lang['mailbox']['booking_0'];?></option>
                  <option value="-1" <?=($result['multiple_bookings'] == -1) ? "selected" : null;?>><?=$lang['mailbox']['booking_lt0'];?></option>
                  <option value="custom" <?=($result['multiple_bookings'] >= 1) ? "selected" : null;?>><?=$lang['mailbox']['booking_custom'];?></option>
                </select>
                <div style="display:none" id="multiple_bookings_custom_div">
                  <hr>
                  <input type="number" class="form-control" name="multiple_bookings_custom" id="multiple_bookings_custom" value="<?=($result['multiple_bookings'] >= 1) ? $result['multiple_bookings'] : null;?>">
                </div>
                <input type="hidden" name="multiple_bookings" id="multiple_bookings">
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="active" <?=($result['active_int']=="1") ? "checked" : null;?>> <?=$lang['edit']['active'];?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-success" data-action="edit_selected" data-id="editresource" data-item="<?=htmlspecialchars($result['name']);?>" data-api-url='edit/resource' data-api-attr='{}' href="#"><?=$lang['edit']['save'];?></button>
              </div>
            </div>
          </form>
        <?php
        }
        else {
        ?>
          <div class="alert alert-info" role="alert"><?=$lang['info']['no_action'];?></div>
        <?php
        }
    }
    elseif (isset($_GET['bcc']) && !empty($_GET["bcc"])) {
        $bcc = intval($_GET["bcc"]);
        $result = bcc('details', $bcc);
        if (!empty($result)) {
          ?>
          <h4><?=$lang['mailbox']['bcc_map'];?></h4>
          <br />
          <form class="form-horizontal" data-id="editbcc" role="form" method="post">
            <input type="hidden" value="0" name="active">
            <div class="form-group">
              <label class="control-label col-sm-2" for="bcc_dest"><?=$lang['mailbox']['bcc_destination'];?></label>
              <div class="col-sm-10">
                <input value="<?=$result['bcc_dest'];?>" type="text" class="form-control" name="bcc_dest" id="bcc_dest">
                <small><?=$lang['edit']['bcc_dest_format'];?></small>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="type"><?=$lang['mailbox']['bcc_map_type'];?></label>
              <div class="col-sm-10">
                <select id="addFilterType" name="type" id="type" required>
                  <option value="sender" <?=($result['type'] == 'sender') ? 'selected' : null;?>><?=$lang['mailbox']['bcc_sender_map'];?></option>
                  <option value="rcpt" <?=($result['type'] == 'rcpt') ? 'selected' : null;?>><?=$lang['mailbox']['bcc_rcpt_map'];?></option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="active" <?php if (isset($result['active_int']) && $result['active_int']=="1") { echo "checked"; }; ?>> <?=$lang['edit']['active'];?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-success" data-action="edit_selected" data-id="editbcc" data-item="<?=$bcc;?>" data-api-url='edit/bcc' data-api-attr='{}' href="#"><?=$lang['edit']['save'];?></button>
              </div>
            </div>
          </form>
        <?php
        }
        else {
        ?>
          <div class="alert alert-info" role="alert"><?=$lang['info']['no_action'];?></div>
        <?php
        }
    }
    elseif (isset($_GET['recipient_map']) &&
      !empty($_GET["recipient_map"]) &&
      $_SESSION['mailcow_cc_role'] == "admin") {
        $map = intval($_GET["recipient_map"]);
        $result = recipient_map('details', $map);
        if (substr($result['recipient_map_old'], 0, 1) == '@') {
          $result['recipient_map_old'] = substr($result['recipient_map_old'], 1);
        }
        if (!empty($result)) {
          ?>
          <h4><?=$lang['mailbox']['recipient_map']?>: <?=$result['recipient_map_old'];?></h4>
          <br />
          <form class="form-horizontal" data-id="edit_recipient_map" role="form" method="post">
            <input type="hidden" value="0" name="active">
            <div class="form-group">
              <label class="control-label col-sm-2" for="recipient_map_new"><?=$lang['mailbox']['recipient_map_old'];?></label>
              <div class="col-sm-10">
                <input value="<?=$result['recipient_map_old'];?>" type="text" class="form-control" name="recipient_map_old" id="recipient_map_old">
                <small><?=$lang['mailbox']['recipient_map_old_info'];?></small>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="recipient_map_new"><?=$lang['mailbox']['recipient_map_new'];?></label>
              <div class="col-sm-10">
                <input value="<?=$result['recipient_map_new'];?>" type="text" class="form-control" name="recipient_map_new" id="recipient_map_new">
                <small><?=$lang['mailbox']['recipient_map_new_info'];?></small>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="active" <?php if (isset($result['active_int']) && $result['active_int']=="1") { echo "checked"; }; ?>> <?=$lang['edit']['active'];?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-success" data-action="edit_selected" data-id="edit_recipient_map" data-item="<?=$map;?>" data-api-url='edit/recipient_map' data-api-attr='{}' href="#"><?=$lang['edit']['save'];?></button>
              </div>
            </div>
          </form>
        <?php
        }
        else {
        ?>
          <div class="alert alert-info" role="alert"><?=$lang['info']['no_action'];?></div>
        <?php
        }
    }
    elseif (isset($_GET['tls_policy_map']) &&
      !empty($_GET["tls_policy_map"]) &&
      $_SESSION['mailcow_cc_role'] == "admin") {
        $map = intval($_GET["tls_policy_map"]);
        $result = tls_policy_maps('details', $map);
        if (!empty($result)) {
          ?>
          <h4><?=$lang['mailbox']['tls_policy_maps']?>: <?=$result['dest'];?></h4>
          <br />
          <form class="form-horizontal" data-id="edit_tls_policy_maps" role="form" method="post">
            <input type="hidden" value="0" name="active">
            <div class="form-group">
              <label class="control-label col-sm-2" for="dest"><?=$lang['mailbox']['tls_map_dest'];?></label>
              <div class="col-sm-10">
                <input value="<?=$result['dest'];?>" type="text" class="form-control" name="dest" id="dest">
                <small><?=$lang['mailbox']['tls_map_dest_info'];?></small>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="policy"><?=$lang['mailbox']['tls_map_policy'];?></label>
              <div class="col-sm-10">
              <select class="full-width-select" name="policy" required>
                <option value="none" <?=($result['policy'] != 'none') ?: 'selected';?>>none</option>
                <option value="may" <?=($result['policy'] != 'may') ?: 'selected';?>>may</option>
                <option value="encrypt" <?=($result['policy'] != 'encrypt') ?: 'selected';?>>encrypt</option>
                <option value="dane" <?=($result['policy'] != 'dane') ?: 'selected';?>>dane-only</option>
                <option value="dane-only" <?=($result['policy'] != 'dane-only') ?: 'selected';?>>dane-only</option>
                <option value="fingerprint" <?=($result['policy'] != 'fingerprint') ?: 'selected';?>>fingerprint</option>
                <option value="verify" <?=($result['policy'] != 'verify') ?: 'selected';?>>verify</option>
                <option value="secure" <?=($result['policy'] != 'secure') ?: 'selected';?>>secure</option>
              </select>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="parameters"><?=$lang['mailbox']['tls_map_parameters'];?></label>
              <div class="col-sm-10">
                <input value="<?=$result['parameters'];?>" type="text" class="form-control" name="parameters" id="parameters">
                <small><?=$lang['mailbox']['tls_map_parameters_info'];?></small>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="active" <?php if (isset($result['active_int']) && $result['active_int']=="1") { echo "checked"; }; ?>> <?=$lang['edit']['active'];?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-success" data-action="edit_selected" data-id="edit_tls_policy_maps" data-item="<?=$map;?>" data-api-url='edit/tls-policy-map' data-api-attr='{}' href="#"><?=$lang['edit']['save'];?></button>
              </div>
            </div>
          </form>
        <?php
        }
        else {
        ?>
          <div class="alert alert-info" role="alert"><?=$lang['info']['no_action'];?></div>
        <?php
        }
    }
  }
  if ($_SESSION['mailcow_cc_role'] == "admin"  || $_SESSION['mailcow_cc_role'] == "domainadmin" || $_SESSION['mailcow_cc_role'] == "user") {
    if (isset($_GET['syncjob']) &&
      is_numeric($_GET['syncjob'])) {
        $id = $_GET["syncjob"];
        $result = mailbox('get', 'syncjob_details', $id);
        if (!empty($result)) {
        ?>
          <h4><?=$lang['edit']['syncjob'];?></h4>
          <form class="form-horizontal" data-id="editsyncjob" role="form" method="post">
            <input type="hidden" value="0" name="delete2duplicates">
            <input type="hidden" value="0" name="delete1">
            <input type="hidden" value="0" name="delete2">
            <input type="hidden" value="0" name="automap">
            <input type="hidden" value="0" name="skipcrossduplicates">
            <input type="hidden" value="0" name="active">
            <input type="hidden" value="0" name="subscribeall">
            <div class="form-group">
              <label class="control-label col-sm-2" for="host1"><?=$lang['edit']['hostname'];?></label>
              <div class="col-sm-10">
              <input type="text" class="form-control" name="host1" id="host1" value="<?=htmlspecialchars($result['host1'], ENT_QUOTES, 'UTF-8');?>">
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="port1">Port</label>
              <div class="col-sm-10">
              <input type="number" class="form-control" name="port1" id="port1" min="1" max="65535" value="<?=htmlspecialchars($result['port1'], ENT_QUOTES, 'UTF-8');?>">
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="user1"><?=$lang['edit']['username'];?></label>
              <div class="col-sm-10">
              <input type="text" class="form-control" name="user1" id="user1" value="<?=htmlspecialchars($result['user1'], ENT_QUOTES, 'UTF-8');?>">
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="password1"><?=$lang['edit']['password'];?></label>
              <div class="col-sm-10">
              <input type="password" class="form-control" name="password1" id="password1" value="<?=htmlspecialchars($result['password1'], ENT_QUOTES, 'UTF-8');?>">
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="enc1"><?=$lang['edit']['encryption'];?></label>
              <div class="col-sm-10">
                <select id="enc1" name="enc1">
                  <option <?=($result['enc1'] == "TLS") ? "selected" : null;?>>TLS</option>
                  <option <?=($result['enc1'] == "SSL") ? "selected" : null;?>>SSL</option>
                  <option <?=($result['enc1'] == "PLAIN") ? "selected" : null;?>>PLAIN</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="mins_interval"><?=$lang['edit']['mins_interval'];?></label>
              <div class="col-sm-10">
                <input type="number" class="form-control" name="mins_interval" min="1" max="3600" value="<?=htmlspecialchars($result['mins_interval'], ENT_QUOTES, 'UTF-8');?>" required>
                <small class="help-block">1-3600</small>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="subfolder2"><?=$lang['edit']['subfolder2'];?></label>
              <div class="col-sm-10">
              <input type="text" class="form-control" name="subfolder2" id="subfolder2" value="<?=htmlspecialchars($result['subfolder2'], ENT_QUOTES, 'UTF-8');?>">
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="maxage"><?=$lang['edit']['maxage'];?></label>
              <div class="col-sm-10">
              <input type="number" class="form-control" name="maxage" id="maxage" min="0" max="32000" value="<?=htmlspecialchars($result['maxage'], ENT_QUOTES, 'UTF-8');?>">
              <small class="help-block">0-32000</small>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="maxbytespersecond"><?=$lang['edit']['maxbytespersecond'];?></label>
              <div class="col-sm-10">
              <input type="number" class="form-control" name="maxbytespersecond" id="maxbytespersecond" min="0" max="125000000" value="<?=htmlspecialchars($result['maxbytespersecond'], ENT_QUOTES, 'UTF-8');?>">
              <small class="help-block">0-125000000</small>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="timeout1"><?=$lang['add']['timeout1'];?></label>
              <div class="col-sm-10">
              <input type="number" class="form-control" name="timeout1" id="timeout1" min="1" max="32000" value="<?=htmlspecialchars($result['timeout1'], ENT_QUOTES, 'UTF-8');?>">
              <small class="help-block">1-32000</small>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="timeout2"><?=$lang['add']['timeout2'];?></label>
              <div class="col-sm-10">
              <input type="number" class="form-control" name="timeout2" id="timeout2" min="1" max="32000" value="<?=htmlspecialchars($result['timeout2'], ENT_QUOTES, 'UTF-8');?>">
              <small class="help-block">1-32000</small>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="exclude"><?=$lang['edit']['exclude'];?></label>
              <div class="col-sm-10">
              <input type="text" class="form-control" name="exclude" id="exclude" value="<?=htmlspecialchars($result['exclude'], ENT_QUOTES, 'UTF-8');?>">
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="custom_params"><?=$lang['add']['custom_params'];?></label>
              <div class="col-sm-10">
              <input type="text" class="form-control" name="custom_params" id="custom_params" value="<?=htmlspecialchars($result['custom_params'], ENT_QUOTES, 'UTF-8');?>" placeholder="--dry --some-param=xy --other-param=yx">
              <small class="help-block"><?=$lang['add']['custom_params_hint'];?></small>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="delete2duplicates" <?=($result['delete2duplicates']=="1") ? "checked" : "";?>> <?=$lang['edit']['delete2duplicates'];?> (--delete2duplicates)</label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="delete1" <?=($result['delete1']=="1") ? "checked" : "";?>> <?=$lang['edit']['delete1'];?> (--delete1)</label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="delete2" <?=($result['delete2']=="1") ? "checked" : "";?>> <?=$lang['edit']['delete2'];?> (--delete2)</label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="automap" <?=($result['automap']=="1") ? "checked" : "";?>> <?=$lang['edit']['automap'];?> (--automap)</label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="skipcrossduplicates" <?=($result['skipcrossduplicates']=="1") ? "checked" : "";?>> <?=$lang['edit']['skipcrossduplicates'];?> (--skipcrossduplicates)</label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="subscribeall" <?=($result['subscribeall']=="1") ? "checked" : "";?>> <?=$lang['add']['subscribeall'];?> (--subscribeall)</label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="active" <?=($result['active_int']=="1") ? "checked" : "";?>> <?=$lang['edit']['active'];?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-success" data-action="edit_selected" data-id="editsyncjob" data-item="<?=htmlspecialchars($result['id']);?>" data-api-url='edit/syncjob' data-api-attr='{}' href="#"><?=$lang['edit']['save'];?></button>
              </div>
            </div>
          </form>
        <?php
        }
        else {
        ?>
          <div class="alert alert-info" role="alert"><?=$lang['info']['no_action'];?></div>
        <?php
        }
      }
    elseif (isset($_GET['filter']) &&
      is_numeric($_GET['filter'])) {
        $id = $_GET["filter"];
        $result = mailbox('get', 'filter_details', $id);
        if (!empty($result)) {
        ?>
          <h4>Filter</h4>
          <form class="form-horizontal" data-id="editfilter" role="form" method="post">
            <input type="hidden" value="0" name="active">
            <div class="form-group">
              <label class="control-label col-sm-2" for="script_desc"><?=$lang['edit']['sieve_desc'];?></label>
              <div class="col-sm-10">
              <input type="text" class="form-control" name="script_desc" id="script_desc" value="<?=htmlspecialchars($result['script_desc'], ENT_QUOTES, 'UTF-8');?>" required maxlength="255">
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="filter_type"><?=$lang['edit']['sieve_type'];?></label>
              <div class="col-sm-10">
                <select id="addFilterType" name="filter_type" id="filter_type" required>
                  <option value="prefilter" <?=($result['filter_type'] == 'prefilter') ? 'selected' : null;?>>Prefilter</option>
                  <option value="postfilter" <?=($result['filter_type'] == 'postfilter') ? 'selected' : null;?>>Postfilter</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="script_data">Script:</label>
              <div class="col-sm-10">
                <textarea spellcheck="false" autocorrect="off" autocapitalize="none" class="form-control textarea-code" rows="20" id="script_data" name="script_data" required><?=$result['script_data'];?></textarea>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="active" <?=($result['active_int']=="1") ? "checked" : "";?>> <?=$lang['edit']['active'];?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-success" data-action="edit_selected" data-id="editfilter" data-item="<?=htmlspecialchars($result['id']);?>" data-api-url='edit/filter' data-api-attr='{}' href="#"><?=$lang['edit']['validate_save'];?></button>
              </div>
            </div>
          </form>
        <?php
        }
        else {
        ?>
          <div class="alert alert-info" role="alert"><?=$lang['info']['no_action'];?></div>
        <?php
        }
    }
  }
}
else {
?>
	<div class="alert alert-danger" role="alert"><?=$lang['danger']['access_denied'];?></div>
<?php
}
?>
				</div>
			</div>
		</div>
	</div>
<a href="<?=$_SESSION['return_to'];?>">&#8592; <?=$lang['edit']['previous'];?></a>
</div> <!-- /container -->
<script type='text/javascript'>
<?php
$lang_user = json_encode($lang['user']);
echo "var lang_user = ". $lang_user . ";\n";
echo "var table_for_domain = '". ((isset($domain)) ? $domain : null) . "';\n";
echo "var csrf_token = '". $_SESSION['CSRF']['TOKEN'] . "';\n";
echo "var pagination_size = '". $PAGINATION_SIZE . "';\n";
?>
</script>
<?php
$js_minifier->add('/web/js/site/edit.js');
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/footer.inc.php';
?>
