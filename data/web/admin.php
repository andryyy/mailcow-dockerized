<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';

if (isset($_SESSION['mailcow_cc_role']) && $_SESSION['mailcow_cc_role'] == "admin") {
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/header.inc.php';
$_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
$tfa_data = get_tfa();
if (!isset($_SESSION['gal']) && $license_cache = $redis->Get('LICENSE_STATUS_CACHE')) {
  $_SESSION['gal'] = json_decode($license_cache, true);
}
?>
<div class="container">

  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#tab-access" aria-controls="tab-access" role="tab" data-toggle="tab"><?=$lang['admin']['access'];?></a></li>
    <li role="presentation"><a href="#tab-config" aria-controls="tab-config" role="tab" data-toggle="tab"><?=$lang['admin']['configuration'];?></a></li>
    <li role="presentation"><a href="#tab-routing" aria-controls="tab-config" role="tab" data-toggle="tab"><?=$lang['admin']['routing'];?></a></li>
    <li role="presentation"><a href="#tab-sys-mails" aria-controls="tab-sys-mails" role="tab" data-toggle="tab"><?=$lang['admin']['sys_mails'];?></a></li>
    <li role="presentation"><a href="#tab-mailq" aria-controls="tab-mailq" role="tab" data-toggle="tab"><?=$lang['admin']['queue_manager'];?></a></li>
  </ul>

  <div class="row">
  <div class="col-md-12">
  <div class="tab-content" style="padding-top:20px">
  <div role="tabpanel" class="tab-pane active" id="tab-access">
    <div class="panel panel-danger">
      <div class="panel-heading"><?=$lang['admin']['admin_details'];?></div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-striped table-condensed" id="adminstable"></table>
        </div>
        <div class="mass-actions-admin">
          <div class="btn-group">
            <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="admins" href="#"><span class="glyphicon glyphicon-check" aria-hidden="true"></span> <?=$lang['mailbox']['toggle_all'];?></a>
            <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a data-action="edit_selected" data-id="admins" data-api-url='edit/admin' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
              <li><a data-action="edit_selected" data-id="admins" data-api-url='edit/admin' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
              <li role="separator" class="divider"></li>
              <li><a data-action="edit_selected" data-id="admins" data-api-url='edit/admin' data-api-attr='{"disable_tfa":"1"}' href="#"><?=$lang['tfa']['disable_tfa'];?></a></li>
              <li role="separator" class="divider"></li>
              <li><a data-action="delete_selected" data-id="admins" data-api-url='delete/admin' href="#"><?=$lang['mailbox']['remove'];?></a></li>
            </ul>
            <a class="btn btn-sm btn-success" data-id="add_admin" data-toggle="modal" data-target="#addAdminModal" href="#"><span class="glyphicon glyphicon-plus"></span> <?=$lang['admin']['add_admin'];?></a>
          </div>
        </div>
        <legend style="margin-top:20px">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="margin-bottom: -5px;">
          <path d="M17.81 4.47c-.08 0-.16-.02-.23-.06C15.66 3.42 14 3 12.01 3c-1.98 0-3.86.47-5.57 1.41-.24.13-.54.04-.68-.2-.13-.24-.04-.55.2-.68C7.82 2.52 9.86 2 12.01 2c2.13 0 3.99.47 6.03 1.52.25.13.34.43.21.67-.09.18-.26.28-.44.28zM3.5 9.72c-.1 0-.2-.03-.29-.09-.23-.16-.28-.47-.12-.7.99-1.4 2.25-2.5 3.75-3.27C9.98 4.04 14 4.03 17.15 5.65c1.5.77 2.76 1.86 3.75 3.25.16.22.11.54-.12.7-.23.16-.54.11-.7-.12-.9-1.26-2.04-2.25-3.39-2.94-2.87-1.47-6.54-1.47-9.4.01-1.36.7-2.5 1.7-3.4 2.96-.08.14-.23.21-.39.21zm6.25 12.07c-.13 0-.26-.05-.35-.15-.87-.87-1.34-1.43-2.01-2.64-.69-1.23-1.05-2.73-1.05-4.34 0-2.97 2.54-5.39 5.66-5.39s5.66 2.42 5.66 5.39c0 .28-.22.5-.5.5s-.5-.22-.5-.5c0-2.42-2.09-4.39-4.66-4.39-2.57 0-4.66 1.97-4.66 4.39 0 1.44.32 2.77.93 3.85.64 1.15 1.08 1.64 1.85 2.42.19.2.19.51 0 .71-.11.1-.24.15-.37.15zm7.17-1.85c-1.19 0-2.24-.3-3.1-.89-1.49-1.01-2.38-2.65-2.38-4.39 0-.28.22-.5.5-.5s.5.22.5.5c0 1.41.72 2.74 1.94 3.56.71.48 1.54.71 2.54.71.24 0 .64-.03 1.04-.1.27-.05.53.13.58.41.05.27-.13.53-.41.58-.57.11-1.07.12-1.21.12zM14.91 22c-.04 0-.09-.01-.13-.02-1.59-.44-2.63-1.03-3.72-2.1-1.4-1.39-2.17-3.24-2.17-5.22 0-1.62 1.38-2.94 3.08-2.94 1.7 0 3.08 1.32 3.08 2.94 0 1.07.93 1.94 2.08 1.94s2.08-.87 2.08-1.94c0-3.77-3.25-6.83-7.25-6.83-2.84 0-5.44 1.58-6.61 4.03-.39.81-.59 1.76-.59 2.8 0 .78.07 2.01.67 3.61.1.26-.03.55-.29.64-.26.1-.55-.04-.64-.29-.49-1.31-.73-2.61-.73-3.96 0-1.2.23-2.29.68-3.24 1.33-2.79 4.28-4.6 7.51-4.6 4.55 0 8.25 3.51 8.25 7.83 0 1.62-1.38 2.94-3.08 2.94s-3.08-1.32-3.08-2.94c0-1.07-.93-1.94-2.08-1.94s-2.08.87-2.08 1.94c0 1.71.66 3.31 1.87 4.51.95.94 1.86 1.46 3.27 1.85.27.07.42.35.35.61-.05.23-.26.38-.47.38z"/>
        </svg> <?=$lang['tfa']['tfa'];?></legend>
        <div class="row">
          <div class="col-sm-3 col-xs-5 text-right"><?=$lang['tfa']['tfa'];?>:</div>
          <div class="col-sm-9 col-xs-7">
            <p id="tfa_pretty"><?=$tfa_data['pretty'];?></p>
              <div id="tfa_additional">
                <?php if (!empty($tfa_data['additional'])):
                foreach ($tfa_data['additional'] as $key_info): ?>
                <form style="display:inline;" method="post">
                  <input type="hidden" name="unset_tfa_key" value="<?=$key_info['id'];?>" />
                  <div style="padding:4px;margin:4px" class="label label-<?=($_SESSION['tfa_id'] == $key_info['id']) ? 'success' : 'default'; ?>">
                  <?=$key_info['key_id'];?>
                  <a href="#" style="font-weight:bold;color:white" onClick="$(this).closest('form').submit()">[<?=strtolower($lang['admin']['remove']);?>]</a>
                  </div>
                </form>
                <?php endforeach;
                endif;?>
              </div>
              <br />
          </div>
        </div>
        <div class="row">
          <div class="col-sm-3 col-xs-5 text-right"><?=$lang['tfa']['set_tfa'];?>:</div>
          <div class="col-sm-9 col-xs-7">
            <select data-width="fit" id="selectTFA" class="selectpicker" title="<?=$lang['tfa']['select'];?>">
              <option value="yubi_otp"><?=$lang['tfa']['yubi_otp'];?></option>
              <option value="u2f"><?=$lang['tfa']['u2f'];?></option>
              <option value="totp"><?=$lang['tfa']['totp'];?></option>
              <option value="none"><?=$lang['tfa']['none'];?></option>
            </select>
          </div>
        </div>

        <legend data-target="#license" class="arrow-toggle" unselectable="on" data-toggle="collapse">
          <span style="font-size:12px" class="arrow rotate glyphicon glyphicon-menu-down"></span> <?=$lang['admin']['guid_and_license'];?>
        </legend>
        <div id="license" class="collapse in">
        <form class="form-horizontal" autocapitalize="none" autocorrect="off" role="form" method="post">
          <div class="form-group">
            <label class="control-label col-sm-3" for="guid"><?=$lang['admin']['guid'];?>:</label>
            <div class="col-sm-9">
              <div class="input-group">
                <span class="input-group-addon">
                  <span class="glyphicon <?=(isset($_SESSION['gal']['valid']) && $_SESSION['gal']['valid'] === "true") ? 'glyphicon-heart text-danger' : 'glyphicon-remove';?>" aria-hidden="true"></span>
                </span>
                <input type="text" id="guid" class="form-control" value="<?=license('guid');?>" readonly>
              </div>
              <p class="help-block">
                <?=$lang['admin']['customer_id'];?>: <?=(isset($_SESSION['gal']['c'])) ? $_SESSION['gal']['c'] : '?';?> -
                <?=$lang['admin']['service_id'];?>: <?=(isset($_SESSION['gal']['s'])) ? $_SESSION['gal']['s'] : '?';?>
              </p>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
              <p class="help-block"><?=$lang['admin']['license_info'];?></p>
              <div class="btn-group">
                <button class="btn btn-sm btn-success" name="license_validate_now" type="submit" href="#"><?=$lang['admin']['validate_license_now'];?></button>
              </div>
            </div>
          </div>
        </form>
        </div>

        <legend data-target="#api" class="arrow-toggle" unselectable="on" data-toggle="collapse">
          <span style="font-size:12px" class="arrow rotate glyphicon glyphicon-menu-down"></span> API
        </legend>
        <?php
        $api = admin_api('get');
        ?>
        <div id="api" class="collapse">
        <form class="form-horizontal" autocapitalize="none" autocorrect="off" role="form" method="post">
          <div class="form-group">
            <label class="control-label col-sm-3" for="allow_from"><?=$lang['admin']['api_allow_from'];?>:</label>
            <div class="col-sm-9">
              <textarea class="form-control" rows="5" name="allow_from" id="allow_from" required><?=htmlspecialchars($api['allow_from']);?></textarea>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-3" for="admin_api_key"><?=$lang['admin']['api_key'];?>:</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" placeholder="-" value="<?=htmlspecialchars($api['api_key']);?>" readonly>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
              <label>
                <input type="checkbox" name="active" <?=($api['active'] == 1) ? 'checked' : null;?>> <?=$lang['admin']['activate_api'];?>
              </label>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
              <p class="help-block"><?=$lang['admin']['api_info'];?></p>
              <div class="btn-group">
                <button class="btn btn-default" name="admin_api" type="submit" href="#"><span class="glyphicon glyphicon-check"></span> <?=$lang['admin']['save'];?></button>
                <button class="btn btn-info" name="admin_api_regen_key" type="submit" href="#"><?=$lang['admin']['regen_api_key'];?></button>
              </div>
            </div>
          </div>
        </form>
        </div>

      </div>
    </div>

    <div class="panel panel-default">
    <div class="panel-heading"><?=$lang['admin']['domain_admins'];?></div>
        <div class="panel-body">
          <div class="table-responsive">
            <table class="table table-striped table-condensed" id="domainadminstable"></table>
          </div>
          <div class="mass-actions-admin">
            <div class="btn-group">
              <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="domain_admins" href="#"><span class="glyphicon glyphicon-check" aria-hidden="true"></span> <?=$lang['mailbox']['toggle_all'];?></a>
              <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a data-action="edit_selected" data-id="domain_admins" data-api-url='edit/domain-admin' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                <li><a data-action="edit_selected" data-id="domain_admins" data-api-url='edit/domain-admin' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                <li role="separator" class="divider"></li>
                <li><a data-action="edit_selected" data-id="domain_admins" data-api-url='edit/domain-admin' data-api-attr='{"disable_tfa":"1"}' href="#"><?=$lang['tfa']['disable_tfa'];?></a></li>
                <li role="separator" class="divider"></li>
                <li><a data-action="delete_selected" data-id="domain_admins" data-api-url='delete/domain-admin' href="#"><?=$lang['mailbox']['remove'];?></a></li>
              </ul>
              <a class="btn btn-sm btn-success" data-id="add_domain_admin" data-toggle="modal" data-target="#addDomainAdminModal" href="#"><span class="glyphicon glyphicon-plus"></span> <?=$lang['admin']['add_domain_admin'];?></a>
            </div>
          </div>
        </div>
    </div>

    <div class="panel panel-default">
    <div class="panel-heading">OAuth2 Apps</div>
        <div class="panel-body">
          <p><?=$lang['admin']['oauth2_info'];?></p>
          <div class="table-responsive">
            <table class="table table-striped" id="oauth2clientstable"></table>
          </div>
          <div class="mass-actions-admin">
            <div class="btn-group">
              <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="oauth2_clients" href="#"><span class="glyphicon glyphicon-check" aria-hidden="true"></span> <?=$lang['mailbox']['toggle_all'];?></a>
              <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a data-action="delete_selected" data-id="oauth2_clients" data-api-url='delete/oauth2-client' href="#"><?=$lang['mailbox']['remove'];?></a></li>
                <li role="separator" class="divider"></li>
                <li><a data-action="edit_selected" data-id="oauth2_clients" data-api-url='edit/oauth2-client' data-api-attr='{"revoke_tokens":"1"}' href="#"><?=$lang['admin']['oauth2_revoke_tokens'];?></a></li>
                <li role="separator" class="divider"></li>
                <li><a data-action="edit_selected" data-id="oauth2_clients" data-api-url='edit/oauth2-client' data-api-attr='{"renew_secret":"1"}' href="#"><?=$lang['admin']['oauth2_renew_secret'];?></a></li>
              </ul>
              <a class="btn btn-sm btn-success" data-id="add_oauth2_client" data-toggle="modal" data-target="#addOAuth2ClientModal" href="#"><span class="glyphicon glyphicon-plus"></span> Add OAuth2 client</a>
            </div>
          </div>
        </div>
    </div>
  
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">Rspamd UI</h3>
      </div>
      <div class="panel-body">
        <div class="row">
          <div class="col-sm-9">
          <form class="form-horizontal" autocapitalize="none" data-id="admin" autocorrect="off" role="form" method="post">
            <div class="form-group">
              <div class="col-sm-offset-3 col-sm-9">
                <label>
                  <a href="/rspamd/" target="_blank"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span> Rspamd UI</a>
                </label>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-3" for="rspamd_ui_pass"><?=$lang['admin']['password'];?>:</label>
              <div class="col-sm-9">
              <input type="password" class="form-control" name="rspamd_ui_pass" required>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-3" for="rspamd_ui_pass2"><?=$lang['admin']['password_repeat'];?>:</label>
              <div class="col-sm-9">
              <input type="password" class="form-control" name="rspamd_ui_pass2" required>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-3 col-sm-9">
                <button type="submit" class="btn btn-default" id="rspamd_ui" name="rspamd_ui" href="#"><span class="glyphicon glyphicon-check"></span> <?=$lang['admin']['save'];?></button>
              </div>
            </div>
          </form>
          </div>
          <div class="col-sm-3">
            <img class="img-responsive" src="/img/rspamd_logo.png" alt="Rspamd UI" />
          </div>
        </div>
      </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-routing">
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['relayhosts'];?></div>
      <div class="panel-body">
        <p style="margin-bottom:40px"><?=$lang['admin']['relayhosts_hint'];?></p>
        <div class="table-responsive">
          <table class="table table-striped table-condensed" id="relayhoststable"></table>
        </div>
        <div class="mass-actions-admin">
          <div class="btn-group btn-group-sm">
            <button type="button" id="toggle_multi_select_all" data-id="rlyhosts" class="btn btn-default"><?=$lang['mailbox']['toggle_all'];?></button>
            <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a data-action="edit_selected" data-id="rlyhosts" data-api-url='edit/relayhost' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
              <li><a data-action="edit_selected" data-id="rlyhosts" data-api-url='edit/relayhost' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
              <li role="separator" class="divider"></li>
              <li><a data-action="delete_selected" data-id="rlyhosts" data-api-url='delete/relayhost' href="#"><?=$lang['admin']['remove'];?></a></li>
            </ul>
          </div>
        </div>
        <legend><?=$lang['admin']['add_relayhost'];?></legend>
        <p class="help-block"><?=$lang['admin']['add_relayhost_hint'];?></p>
        <div class="row">
          <div class="col-md-6">
            <form class="form" data-id="rlyhost" role="form" method="post">
              <div class="form-group">
                <label for="hostname"><?=$lang['admin']['host'];?></label>
                <input class="form-control input-sm" name="hostname" placeholder='host:25, host, [host]:25, [0.0.0.0]:25' required>
              </div>
              <div class="form-group">
                <label for="username"><?=$lang['admin']['username'];?></label>
                <input class="form-control input-sm" name="username">
              </div>
              <div class="form-group">
                <label for="password"><?=$lang['admin']['password'];?></label>
                <input class="form-control input-sm" name="password">
              </div>
              <button class="btn btn-default" data-action="add_item" data-id="rlyhost" data-api-url='add/relayhost' data-api-attr='{}' href="#"><span class="glyphicon glyphicon-plus"></span> <?=$lang['admin']['add'];?></button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['transport_maps'];?></div>
      <div class="panel-body">
        <p style="margin-bottom:40px"><?=$lang['admin']['transports_hint'];?></p>
        <div class="table-responsive">
          <table class="table table-striped table-condensed" id="transportstable"></table>
        </div>
        <div class="mass-actions-admin">
          <div class="btn-group btn-group-sm">
            <button type="button" id="toggle_multi_select_all" data-id="transports" class="btn btn-default"><?=$lang['mailbox']['toggle_all'];?></button>
            <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a data-action="edit_selected" data-id="transports" data-api-url='edit/transport' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
              <li><a data-action="edit_selected" data-id="transports" data-api-url='edit/transport' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
              <li role="separator" class="divider"></li>
              <li><a data-action="delete_selected" data-id="transports" data-api-url='delete/transport' href="#"><?=$lang['admin']['remove'];?></a></li>
            </ul>
          </div>
        </div>
        <legend><?=$lang['admin']['add_transport'];?></legend>
        <p class="help-block"><?=$lang['admin']['add_transports_hint'];?></p>
        <div class="row">
          <div class="col-md-6">
            <form class="form" data-id="transport" role="form" method="post">
              <div class="form-group">
                <label for="destination"><?=$lang['admin']['destination'];?></label>
                <input class="form-control input-sm" name="destination" placeholder='<?=$lang['admin']['transport_dest_format'];?>' required>
              </div>
              <div class="form-group">
                <label for="nexthop"><?=$lang['admin']['nexthop'];?></label>
                <input class="form-control input-sm" name="nexthop" placeholder='host:25, host, [host]:25, [0.0.0.0]:25' required>
              </div>
              <div class="form-group">
                <label for="username"><?=$lang['admin']['username'];?></label>
                <input class="form-control input-sm" name="username">
              </div>
              <div class="form-group">
                <label for="password"><?=$lang['admin']['password'];?></label>
                <input class="form-control" name="password">
              </div>
              <!-- <div class="form-group">
                <label>
                  <input type="checkbox" name="lookup_mx" value="1"> <?=$lang['admin']['lookup_mx'];?>
                </label>
              </div> -->
              <div class="form-group">
                <label>
                  <input type="checkbox" name="active" value="1"> <?=$lang['admin']['active'];?>
                </label>
              </div>
              <p class="help-block"><?=$lang['admin']['credentials_transport_warning'];?></p>
              <button class="btn btn-default" data-action="add_item" data-id="transport" data-api-url='add/transport' data-api-attr='{}' href="#"><span class="glyphicon glyphicon-plus"></span> <?=$lang['admin']['add'];?></button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-config">
    <div class="row">
    <div id="sidebar-admin" class="col-sm-2 hidden-xs">
      <div id="scrollbox" class="list-group">
        <a href="#dkim" class="list-group-item"><?=$lang['admin']['dkim_keys'];?></a>
        <a href="#fwdhosts" class="list-group-item"><?=$lang['admin']['forwarding_hosts'];?></a>
        <a href="#f2bparams" class="list-group-item"><?=$lang['admin']['f2b_parameters'];?></a>
        <a href="#quarantine" class="list-group-item"><?=$lang['admin']['quarantine'];?></a>
        <a href="#quota" class="list-group-item"><?=$lang['admin']['quota_notifications'];?></a>
        <a href="#rsettings" class="list-group-item"><?=$lang['admin']['rspamd_settings_map'];?></a>
        <a href="#customize" class="list-group-item"><?=$lang['admin']['customize'];?></a>
        <a href="#top" class="list-group-item" style="border-top:1px dashed #dadada">↸ <?=$lang['admin']['to_top'];?></a>
      </div>
    </div>
    <div class="col-sm-10">
    <span class="anchor" id="dkim"></span>
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['dkim_keys'];?></div>
      <div class="panel-body">
        <div class="mass-actions-admin">
          <div class="btn-group btn-group-sm">
            <button type="button" id="toggle_multi_select_all" data-id="dkim" class="btn btn-default"><?=$lang['mailbox']['toggle_all'];?></button>
            <button type="button" data-action="delete_selected" name="delete_selected" data-id="dkim" data-api-url="delete/dkim" class="btn btn-danger"><?=$lang['admin']['remove'];?></button>
          </div>
        </div>
        <?php
        foreach(mailbox('get', 'domains') as $domain) {
            if (!empty($dkim = dkim('details', $domain))) {
              $dkim_domains[] = $domain;
              ($GLOBALS['SHOW_DKIM_PRIV_KEYS'] === true) ?: $dkim['privkey'] = base64_encode('Please set $SHOW_DKIM_PRIV_KEYS to true to show DKIM private keys.');
          ?>
            <div class="row">
              <div class="col-md-1"><input type="checkbox" data-id="dkim" name="multi_select" value="<?=$domain;?>" /></div>
              <div class="col-md-3">
                <p><?=$lang['admin']['domain'];?>: <strong><?=htmlspecialchars($domain);?></strong>
                  <p class="dkim-label"><span class="label label-success"><?=$lang['admin']['dkim_key_valid'];?></span></p>
                  <p class="dkim-label"><span class="label label-primary"><?=$lang['admin']['dkim_domains_selector'];?> '<?=$dkim['dkim_selector'];?>'</span></p>
                  <p class="dkim-label"><span class="label label-info"><?=$dkim['length'];?> bit</span></p>
                </p>
              </div>
              <div class="col-md-8">
                  <pre><?=$dkim['dkim_txt'];?></pre>
                  <p data-toggle="modal" data-target="#showDKIMprivKey" id="dkim_priv" style="cursor:pointer;margin-top:-8pt" data-priv-key="<?=$dkim['privkey'];?>"><small>↪ <?=$lang['admin']['dkim_private_key'];?></small></p>
              </div>
              <hr class="visible-xs visible-sm">
            </div>
          <?php
          }
          else {
          ?>
          <div class="row">
            <div class="col-md-1"><input class="dkim_missing" type="checkbox" data-id="dkim" name="multi_select" value="<?=$domain;?>" disabled /></div>
            <div class="col-md-3">
              <p><?=$lang['admin']['domain'];?>: <strong><?=htmlspecialchars($domain);?></strong><br /><span class="label label-danger"><?=$lang['admin']['dkim_key_missing'];?></span></p>
            </div>
            <div class="col-md-8"><pre>-</pre></div>
              <hr class="visible-xs visible-sm">
          </div>
          <?php
          }
          foreach(mailbox('get', 'alias_domains', $domain) as $alias_domain) {
            if (!empty($dkim = dkim('details', $alias_domain))) {
              $dkim_domains[] = $alias_domain;
              ($GLOBALS['SHOW_DKIM_PRIV_KEYS'] === true) ?: $dkim['privkey'] = base64_encode('Please set $SHOW_DKIM_PRIV_KEYS to true to show DKIM private keys.');
            ?>
              <div class="row">
              <div class="col-md-1"><input type="checkbox" data-id="dkim" name="multi_select" value="<?=$alias_domain;?>" /></div>
                <div class="col-md-2 col-md-offset-1">
                  <p><small>↳ Alias-Domain: <strong><?=htmlspecialchars($alias_domain);?></strong></small>
                    <p class="dkim-label"><span class="label label-success"><?=$lang['admin']['dkim_key_valid'];?></span></p>
                    <p class="dkim-label"><span class="label label-primary">Selector '<?=$dkim['dkim_selector'];?>'</span></p>
                    <p class="dkim-label"><span class="label label-info"><?=$dkim['length'];?> bit</span></p>
                </p>
                </div>
                <div class="col-md-8">
                  <pre><?=$dkim['dkim_txt'];?></pre>
                  <p data-toggle="modal" data-target="#showDKIMprivKey" id="dkim_priv" style="cursor:pointer;margin-top:-8pt" data-priv-key="<?=$dkim['privkey'];?>"><small>↪ Private key</small></p>
                </div>
              <hr class="visible-xs visible-sm">
              </div>
            <?php
            }
            else {
            ?>
            <div class="row">
              <div class="col-md-1"><input class="dkim_missing" type="checkbox" data-id="dkim" name="multi_select" value="<?=$alias_domain;?>" disabled /></div>
              <div class="col-md-2 col-md-offset-1">
                <p><small>↳ Alias-Domain: <strong><?=htmlspecialchars($alias_domain);?></strong><br /></small><span class="label label-danger"><?=$lang['admin']['dkim_key_missing'];?></span></p>
              </div>
              <div class="col-md-8"><pre>-</pre></div>
              <hr class="visible-xs visible-sm">
            </div>
            <?php
            }
          }
        }
        foreach(dkim('blind') as $blind) {
          if (!empty($dkim = dkim('details', $blind))) {
            $dkim_domains[] = $blind;
            ($GLOBALS['SHOW_DKIM_PRIV_KEYS'] === true) ?: $dkim['privkey'] = base64_encode('Please set $SHOW_DKIM_PRIV_KEYS to true to show DKIM private keys.');
          ?>
            <div class="row">
              <div class="col-md-1"><input type="checkbox" data-id="dkim" name="multi_select" value="<?=$blind;?>" /></div>
              <div class="col-md-3">
                <p><?=$lang['admin']['domain'];?>: <strong><?=htmlspecialchars($blind);?></strong>
                  <p class="dkim-label"><span class="label label-warning"><?=$lang['admin']['dkim_key_unused'];?></span></p>
                  <p class="dkim-label"><span class="label label-primary">Selector '<?=$dkim['dkim_selector'];?>'</span></p>
                  <p class="dkim-label"><span class="label label-info"><?=$dkim['length'];?> bit</span></p>
                </p>
                </div>
                <div class="col-md-8">
                  <pre><?=$dkim['dkim_txt'];?></pre>
                  <p data-toggle="modal" data-target="#showDKIMprivKey" id="dkim_priv" style="cursor:pointer;margin-top:-8pt" data-priv-key="<?=$dkim['privkey'];?>"><small>↪ Private key</small></p>
                </div>
                <hr class="visible-xs visible-sm">
            </div>
          <?php
          }
        }
        ?>

        <legend style="margin-top:40px"><?=$lang['admin']['dkim_add_key'];?></legend>
        <form class="form" data-id="dkim" role="form" method="post">
          <div class="form-group">
            <label for="domain"><?=$lang['admin']['domain_s'];?></label>
            <input class="form-control input-sm" id="dkim_add_domains" name="domains" placeholder="example.org, example.com" required>
            <small>↪ <a href="#" id="dkim_missing_keys"><?=$lang['admin']['dkim_domains_wo_keys'];?></a></small>
          </div>
          <div class="form-group">
            <label for="domain"><?=$lang['admin']['dkim_domains_selector'];?></label>
            <input class="form-control input-sm" name="dkim_selector" value="dkim" required>
          </div>
          <div class="form-group">
            <select data-width="200px" data-style="btn btn-default btn-sm" class="form-control" id="key_size" name="key_size" title="<?=$lang['admin']['dkim_key_length'];?>" required>
              <option data-subtext="bits">1024</option>
              <option data-subtext="bits">2048</option>
            </select>
          </div>
          <button class="btn btn-sm btn-default" data-action="add_item" data-id="dkim" data-api-url='add/dkim' data-api-attr='{}' href="#"><span class="glyphicon glyphicon-plus"></span> <?=$lang['admin']['add'];?></button>
        </form>

        <legend data-target="#import_dkim" style="margin-top:40px;cursor:pointer" class="arrow-toggle" unselectable="on" data-toggle="collapse">
          <span style="font-size:12px" class="arrow rotate glyphicon glyphicon-menu-down"></span> <?=$lang['admin']['import_private_key'];?>
        </legend>
        <div id="import_dkim" class="collapse">
        <form class="form" data-id="dkim_import" role="form" method="post">
          <div class="form-group">
            <label for="domain"><?=$lang['admin']['domain'];?>:</label>
            <input class="form-control input-sm" name="domain" placeholder="example.org" required>
          </div>
          <div class="form-group">
            <label for="domain"><?=$lang['admin']['dkim_domains_selector'];?>:</label>
            <input class="form-control input-sm" name="dkim_selector" value="dkim" required>
          </div>
          <div class="form-group">
            <label for="private_key_file"><?=$lang['admin']['private_key'];?>: (RSA PKCS#8)</label>
            <textarea class="form-control input-sm" rows="10" name="private_key_file" id="private_key_file" required placeholder="-----BEGIN RSA KEY-----"></textarea>
          </div>
          <button class="btn btn-sm btn-default" data-action="add_item" data-id="dkim_import" data-api-url='add/dkim_import' data-api-attr='{}' href="#"><span class="glyphicon glyphicon-plus"></span> <?=$lang['admin']['import'];?></button>
        </form>
        </div>

        <legend data-target="#duplicate_dkim" style="margin-top:40px;cursor:pointer" class="arrow-toggle" unselectable="on" data-toggle="collapse">
          <span style="font-size:12px" class="arrow rotate glyphicon glyphicon-menu-down"></span> <?=$lang['admin']['duplicate_dkim'];?>
        </legend>
        <div id="duplicate_dkim" class="collapse">
        <form class="form-horizontal" data-id="dkim_duplicate" role="form" method="post">
          <div class="form-group">
            <label class="control-label col-sm-2" for="from_domain"><?=$lang['admin']['dkim_from'];?>:</label>
            <div class="col-sm-10">
            <select data-style="btn btn-default btn-sm"
              data-live-search="true"
              data-id="dkim_duplicate"
              title="<?=$lang['admin']['dkim_from_title'];?>"
              name="from_domain" id="from_domain" class="full-width-select form-control" required>
              <?php
              foreach ($dkim_domains as $dkim) {
              ?>
              <option value="<?=$dkim;?>"><?=$dkim;?></option>
              <?php
              }
              ?>
            </select>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="to_domain"><?=$lang['admin']['dkim_to'];?>:</label>
            <div class="col-sm-10">
            <select
              data-live-search="true"
              data-style="btn btn-default btn-sm"
              data-id="dkim_duplicate"
              title="<?=$lang['admin']['dkim_to_title'];?>"
              name="to_domain" id="to_domain" class="full-width-select form-control" multiple required>
              <?php
              foreach (array_merge(mailbox('get', 'domains'), mailbox('get', 'alias_domains')) as $domain) {
              ?>
              <option value="<?=$domain;?>"><?=$domain;?></option>
              <?php
              }
              ?>
            </select>
            </div>
          </div>
          <button class="btn btn-sm btn-default" data-action="add_item" data-id="dkim_duplicate" data-api-url='add/dkim_duplicate' data-api-attr='{}' href="#"><span class="glyphicon glyphicon-duplicate"></span> <?=$lang['admin']['duplicate'];?></button>
        </form>
        </div>

      </div>
    </div>

    <span class="anchor" id="fwdhosts"></span>
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['forwarding_hosts'];?></div>
      <div class="panel-body">
        <p style="margin-bottom:40px"><?=$lang['admin']['forwarding_hosts_hint'];?></p>
        <div class="table-responsive">
          <table class="table table-striped table-condensed" id="forwardinghoststable"></table>
        </div>
        <div class="mass-actions-admin">
          <div class="btn-group btn-group-sm">
            <button type="button" id="toggle_multi_select_all" data-id="fwdhosts" class="btn btn-default"><?=$lang['mailbox']['toggle_all'];?></button>
            <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a data-action="edit_selected" data-id="fwdhosts" data-api-url='edit/fwdhost' data-api-attr='{"keep_spam":"0"}' href="#">Enable spam filter</a></li>
              <li><a data-action="edit_selected" data-id="fwdhosts" data-api-url='edit/fwdhost' data-api-attr='{"keep_spam":"1"}' href="#">Disable spam filter</a></li>
              <li role="separator" class="divider"></li>
              <li><a data-action="delete_selected" data-id="fwdhosts" data-api-url='delete/fwdhost' href="#"><?=$lang['admin']['remove'];?></a></li>
            </ul>
          </div>
        </div>
        <legend><?=$lang['admin']['add_forwarding_host'];?></legend>
        <p class="help-block"><?=$lang['admin']['forwarding_hosts_add_hint'];?></p>
        <form class="form" data-id="fwdhost" role="form" method="post">
          <div class="form-group">
            <label for="hostname"><?=$lang['admin']['host'];?></label>
            <input class="form-control" name="hostname" placeholder="example.org" required>
          </div>
          <div class="form-group">
            <select data-width="200px" class="form-control" id="filter_spam" name="filter_spam" title="<?=$lang['user']['spamfilter'];?>" required>
              <option value="1"><?=$lang['admin']['active'];?></option>
              <option value="0"><?=$lang['admin']['inactive'];?></option>
            </select>
          </div>
          <button class="btn btn-default" data-action="add_item" data-id="fwdhost" data-api-url='add/fwdhost' data-api-attr='{}' href="#"><span class="glyphicon glyphicon-plus"></span> <?=$lang['admin']['add'];?></button>
        </form>
      </div>
    </div>

    <span class="anchor" id="f2bparams"></span>
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['f2b_parameters'];?></div>
      <div class="panel-body">
      <?php
      $f2b_data = fail2ban('get');
      ?>
        <form class="form" data-id="f2b" role="form" method="post">
          <div class="form-group">
            <label for="ban_time"><?=$lang['admin']['f2b_ban_time'];?>:</label>
            <input type="number" class="form-control" name="ban_time" value="<?=$f2b_data['ban_time'];?>" required>
          </div>
          <div class="form-group">
            <label for="max_attempts"><?=$lang['admin']['f2b_max_attempts'];?>:</label>
            <input type="number" class="form-control" name="max_attempts" value="<?=$f2b_data['max_attempts'];?>" required>
          </div>
          <div class="form-group">
            <label for="retry_window"><?=$lang['admin']['f2b_retry_window'];?>:</label>
            <input type="number" class="form-control" name="retry_window" value="<?=$f2b_data['retry_window'];?>" required>
          </div>
          <div class="form-group">
            <label for="netban_ipv4"><?=$lang['admin']['f2b_netban_ipv4'];?>:</label>
            <div class="input-group">
              <span class="input-group-addon">/</span>
              <input type="number" class="form-control" name="netban_ipv4" value="<?=$f2b_data['netban_ipv4'];?>" required>
            </div>
          </div>
          <div class="form-group">
            <label for="netban_ipv6"><?=$lang['admin']['f2b_netban_ipv6'];?>:</label>
            <div class="input-group">
              <span class="input-group-addon">/</span>
              <input type="number" class="form-control" name="netban_ipv6" value="<?=$f2b_data['netban_ipv6'];?>" required>
            </div>
          </div>
          <p class="help-block"><?=$lang['admin']['f2b_list_info'];?></p>
          <div class="form-group">
            <label for="whitelist"><?=$lang['admin']['f2b_whitelist'];?>:</label>
            <textarea class="form-control" name="whitelist" rows="5"><?=$f2b_data['whitelist'];?></textarea>
          </div>
          <div class="form-group">
            <label for="blacklist"><?=$lang['admin']['f2b_blacklist'];?>:</label>
            <textarea class="form-control" name="blacklist" rows="5"><?=$f2b_data['blacklist'];?></textarea>
          </div>
          <div class="btn-group">
            <button class="btn btn-default" data-action="edit_selected" data-item="self" data-id="f2b" data-api-url='edit/fail2ban' data-api-attr='{}' href="#"><span class="glyphicon glyphicon-check"></span> <?=$lang['admin']['save'];?></button>
            <a href="#" role="button" class="btn btn-default" data-toggle="modal" data-container="netfilter-mailcow" data-target="#RestartContainer"><span class="glyphicon glyphicon-refresh"></span> <?= $lang['header']['restart_netfilter']; ?></a>
          </div>
        </form>
        <hr>
        <p class="help-block"><?=$lang['admin']['ban_list_info'];?></p>
        <?php
        if (empty($f2b_data['active_bans']) && empty($f2b_data['perm_bans'])):
        ?>
        <i><?=$lang['admin']['no_active_bans'];?></i>
        <?php
        endif;
        if (!empty($f2b_data['active_bans'])):
          foreach ($f2b_data['active_bans'] as $active_bans):
          ?>
          <p><span class="label label-info" style="padding:4px;font-size:85%;"><span class="glyphicon glyphicon-filter"></span> <?=$active_bans['network'];?> (<?=$active_bans['banned_until'];?>) - 
            <?php
            if ($active_bans['queued_for_unban'] == 0):
            ?>
            <a data-action="edit_selected" data-item="<?=$active_bans['network'];?>" data-id="f2b-quick" data-api-url='edit/fail2ban' data-api-attr='{"action":"unban"}' href="#">[<?=$lang['admin']['queue_unban'];?>]</a>
            <a data-action="edit_selected" data-item="<?=$active_bans['network'];?>" data-id="f2b-quick" data-api-url='edit/fail2ban' data-api-attr='{"action":"whitelist"}' href="#">[whitelist]</a>
            <a data-action="edit_selected" data-item="<?=$active_bans['network'];?>" data-id="f2b-quick" data-api-url='edit/fail2ban' data-api-attr='{"action":"blacklist"}' href="#">[blacklist]</a>
            <?php
            else:
            ?>
            <i><?=$lang['admin']['unban_pending'];?></i>
            <?php
            endif;
            ?>
          </span></p>
          <?php
          endforeach;
          ?>
          <hr>
          <?php
        endif;
        if (!empty($f2b_data['perm_bans'])):
          foreach ($f2b_data['perm_bans'] as $perm_bans):
          ?>
          <span class="label label-danger" style="padding: 0.1em 0.4em 0.1em;"><span class="glyphicon glyphicon-filter"></span> <?=$perm_bans?></span>
          <?php
          endforeach;
        endif;
        ?>
      </div>
    </div>

    <span class="anchor" id="quarantine"></span>
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['quarantine'];?></div>
      <div class="panel-body">
       <?php $q_data = quarantine('settings');?>
        <form class="form" data-id="quarantine" role="form" method="post">
          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label for="retention_size"><?=$lang['admin']['quarantine_retention_size'];?></label>
                <input type="number" class="form-control" name="retention_size" value="<?=$q_data['retention_size'];?>" placeholder="0" required>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label for="max_size"><?=$lang['admin']['quarantine_max_size'];?></label>
                <input type="number" class="form-control" name="max_size" value="<?=$q_data['max_size'];?>" placeholder="0" required>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label for="max_age"><?=$lang['admin']['quarantine_max_age'];?></label>
                <input type="number" class="form-control" name="max_age" value="<?=$q_data['max_age'];?>" min="1" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="sender"><?=$lang['admin']['quarantine_notification_sender'];?>:</label>
                <input type="text" class="form-control" name="sender" value="<?=htmlspecialchars($q_data['sender']);?>" placeholder="quarantine@localhost">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="subject"><?=$lang['admin']['quarantine_notification_subject'];?>:</label>
                <input type="text" class="form-control" name="subject" value="<?=htmlspecialchars($q_data['subject']);?>" placeholder="Spam Quarantine Notification">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              <legend data-target="#quarantine_template" style="cursor:pointer" class="arrow-toggle" unselectable="on" data-toggle="collapse">
                <span style="font-size:12px" class="arrow rotate glyphicon glyphicon-menu-down"></span> <?=$lang['admin']['quarantine_notification_html'];?>
              </legend>
              <div id="quarantine_template" class="collapse" >
                <textarea autocorrect="off" spellcheck="false" autocapitalize="none" class="form-control textarea-code" rows="20" name="html_tmpl"><?=$q_data['html_tmpl'];?></textarea>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="release_format"><?=$lang['admin']['quarantine_release_format'];?>:</label>
                <select data-width="100%" name="release_format" class="selectpicker" title="<?=$lang['tfa']['select'];?>">
                  <option <?=($q_data['release_format'] == 'raw') ? 'selected' : null;?> value="raw"><?=$lang['admin']['quarantine_release_format_raw'];?></option>
                  <option <?=($q_data['release_format'] == 'attachment') ? 'selected' : null;?> value="attachment"><?=$lang['admin']['quarantine_release_format_att'];?></option>
                </select>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="exclude_domains"><?=$lang['admin']['quarantine_exclude_domains'];?>:</label><br />
                <select data-width="100%" name="exclude_domains" class="selectpicker" title="<?=$lang['tfa']['select'];?>" multiple>
                <?php
                foreach (array_merge(mailbox('get', 'domains'), mailbox('get', 'alias_domains')) as $domain):
                ?>
                  <option <?=(in_array($domain, $q_data['exclude_domains'])) ? 'selected' : null;?>><?=htmlspecialchars($domain);?></option>
                <?php
                endforeach;
                ?>
                </select>
              </div>
            </div>
          </div>
          <button class="btn btn-default" data-action="edit_selected" data-item="self" data-id="quarantine" data-api-url='edit/quarantine' data-api-attr='{"action":"settings"}' href="#"><span class="glyphicon glyphicon-check"></span> <?=$lang['admin']['save'];?></button>
        </form>
      </div>
    </div>

    <span class="anchor" id="quota"></span>
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['quota_notifications'];?></div>
      <div class="panel-body">
      <p><?=$lang['admin']['quota_notifications_info'];?></p>
       <?php $qw_data = quota_notification('get');?>
      <form class="form" role="form" data-id="quota_notification" method="post">
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label for="sender"><?=$lang['admin']['quarantine_notification_sender'];?>:</label>
              <input type="text" class="form-control" name="sender" value="<?=htmlspecialchars($qw_data['sender']);?>" placeholder="quota-warning@localhost">
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="subject"><?=$lang['admin']['quarantine_notification_subject'];?>:</label>
              <input type="text" class="form-control" name="subject" value="<?=htmlspecialchars($qw_data['subject']);?>" placeholder="Quota warning">
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <legend data-target="#quota_template" style="cursor:pointer" class="arrow-toggle" unselectable="on" data-toggle="collapse">
              <span style="font-size:12px" class="arrow rotate glyphicon glyphicon-menu-down"></span> <?=$lang['admin']['quarantine_notification_html'];?>
            </legend>
            <div id="quota_template" class="collapse" >
              <!-- <small><?=$lang['admin']['quota_notifications_vars'];?></small><br><br>-->
              <textarea autocorrect="off" spellcheck="false" autocapitalize="none" class="form-control textarea-code collapse in" rows="20" name="html_tmpl"><?=$qw_data['html_tmpl'];?></textarea>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-10">
            <div class="form-group">
              <br>
              <a type="button" class="btn btn-sm btn-success" data-action="edit_selected"
                data-item="quota_notification"
                data-id="quota_notification"
                data-api-url='edit/quota_notification'
                data-api-attr='{}'><?=$lang['user']['save_changes'];?></a>
            </div>
          </div>
        </div>
      </form>
      </div>
    </div>

    <span class="anchor" id="rsettings"></span>
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['rspamd_settings_map'];?></div>
      <div class="panel-body">
      <legend data-target="#active_settings_map" style="cursor:pointer" class="arrow-toggle" unselectable="on" data-toggle="collapse">
        <span style="font-size:12px" class="arrow rotate glyphicon glyphicon-menu-down"></span> <?=$lang['admin']['active_rspamd_settings_map'];?>
      </legend>
      <div id="active_settings_map" class="collapse" >
        <textarea autocorrect="off" spellcheck="false" autocapitalize="none" class="form-control textarea-code" rows="20" name="settings_map" readonly><?=file_get_contents('http://nginx:8081/settings.php');?></textarea>
      </div>
      <br>
      <?php $rsettings = rsettings('get'); ?>
        <form class="form" data-id="rsettings" role="form" method="post">
          <div class="row">
            <div class="col-sm-3">
              <div class="list-group">
                <?php
                if (empty($rsettings)):
                ?>
                  <span class="list-group-item"><em><?=$lang['admin']['rsetting_none'];?></em></span>
                <?php
                else:
                foreach ($rsettings as $rsetting):
                  $rsetting_details = rsettings('details', $rsetting['id']);
                ?>
                  <a href="#<?=$rsetting_details['id'];?>" class="list-group-item list-group-item-<?=($rsetting_details['active_int'] == '1') ? 'success' : ''; ?>" data-dont-remember="1" data-toggle="tab"><?=$rsetting_details['desc'];?> (ID #<?=$rsetting['id'];?>)</a>
                <?php
                endforeach;
                endif;
                ?>
                  <a href="#" class="list-group-item list-group-item-default"
                    data-id="add_domain_admin"
                    data-toggle="modal"
                    data-dont-remember="1"
                    data-target="#addRsettingModal"
                    data-toggle="tab"><?=$lang['admin']['rsetting_add_rule'];?></a>
              </div>
            </div>
            <div class="col-sm-9">
              <div class="tab-content">
                <?php
                if (empty($rsettings)):
                ?>
                <div id="none" class="tab-pane active">
                  <p class="help-block"><?=$lang['admin']['rsetting_none'];?></p>
                </div>
                <?php
                else:
                ?>
                <div id="none" class="tab-pane active">
                  <p class="help-block"><?=$lang['admin']['rsetting_no_selection'];?></p>
                </div>
                <?php
                foreach ($rsettings as $rsetting):
                  $rsetting_details = rsettings('details', $rsetting['id']);
                ?>
                <div id="<?=$rsetting_details['id'];?>" class="tab-pane">
                  <form class="form" data-id="rsettings" role="form" method="post">
                    <input type="hidden" name="active" value="0">
                    <div class="form-group">
                      <label for="desc"><?=$lang['admin']['rsetting_desc'];?>:</label>
                      <input type="text" class="form-control" name="desc" value="<?=htmlspecialchars($rsetting_details['desc']);?>">
                    </div>
                    <div class="form-group">
                      <label for="content"><?=$lang['admin']['rsetting_content'];?>:</label>
                      <textarea class="form-control" name="content" rows="10"><?=htmlspecialchars($rsetting_details['content']);?></textarea>
                    </div>
                    <div class="form-group">
                      <label>
                        <input type="checkbox" name="active" value="1" <?=($rsetting_details['active_int'] == 1) ? 'checked' : null;?>> <?=$lang['admin']['active'];?>
                      </label>
                    </div>
                    <button class="btn btn-default" data-action="edit_selected" data-item="<?=$rsetting_details['id'];?>" data-id="rsettings" data-api-url='edit/rsetting' data-api-attr='{}' href="#"><span class="glyphicon glyphicon-check"></span> <?=$lang['admin']['save'];?></button>
                    <button class="btn btn-danger" data-action="delete_selected" data-item="<?=$rsetting_details['id'];?>" data-id="rsettings" data-api-url="delete/rsetting" data-api-attr='{}' href="#"><?=$lang['admin']['remove'];?></button>
                  </form>
                </div>
                <?php
                endforeach;
                endif;
                ?>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <span class="anchor" id="customize"></span>
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['customize'];?></div>
      <div class="panel-body">
        <legend><?=$lang['admin']['change_logo'];?></legend>
        <p class="help-block"><?=$lang['admin']['logo_info'];?></p>
        <form class="form-inline" role="form" method="post" enctype="multipart/form-data">
          <p>
            <input type="file" name="main_logo" class="filestyle" data-buttonName="btn-default" data-buttonText="Select" accept="image/gif, image/jpeg, image/pjpeg, image/x-png, image/png, image/svg+xml">
            <button name="submit_main_logo" type="submit" class="btn btn-default"><span class="glyphicon glyphicon-cloud-upload"></span> <?=$lang['admin']['upload'];?></button>
          </p>
        </form>
        <?php
        if ($main_logo = customize('get', 'main_logo')):
          $specs = customize('get', 'main_logo_specs');
        ?>
        <div class="row">
          <div class="col-sm-3">
            <div class="thumbnail">
              <img class="img-thumbnail" src="<?=$main_logo;?>" alt="mailcow logo">
              <div class="caption">
                <span class="label label-info"><?=$specs['geometry']['width'];?>x<?=$specs['geometry']['height'];?> px</span>
                <span class="label label-info"><?=$specs['mimetype'];?></span>
                <span class="label label-info"><?=$specs['fileSize'];?></span>
              </div>
            </div>
            <hr>
            <form class="form-inline" role="form" method="post">
              <p><button name="reset_main_logo" type="submit" class="btn btn-xs btn-default"><?=$lang['admin']['reset_default'];?></button></p>
            </form>
          </div>
        </div>
        <?php
        endif;
        ?>
        <legend><?=$lang['admin']['app_links'];?></legend>
        <p class="help-block"><?=$lang['admin']['merged_vars_hint'];?></p>
        <form class="form-inline" data-id="app_links" role="form" method="post">
          <table class="table table-condensed" style="white-space: nowrap;" id="app_link_table">
            <tr>
              <th><?=$lang['admin']['app_name'];?></th>
              <th><?=$lang['admin']['link'];?></th>
              <th>&nbsp;</th>
            </tr>
            <?php
            $app_links = customize('get', 'app_links');
            foreach ($app_links as $row) {
              foreach ($row as $key => $val):
            ?>
            <tr>
              <td><input class="input-sm form-control" data-id="app_links" type="text" name="app" required value="<?=$key;?>"></td>
              <td><input class="input-sm form-control" data-id="app_links" type="text" name="href" required value="<?=$val;?>"></td>
              <td><a href="#" role="button" class="btn btn-xs btn-default" type="button"><?=$lang['admin']['remove_row'];?></a></td>
            </tr>
            <?php
              endforeach;
            }
            foreach ($MAILCOW_APPS as $app):
            ?>
            <tr>
              <td><input class="input-sm form-control" value="<?=htmlspecialchars($app['name']);?>" disabled></td>
              <td><input class="input-sm form-control" value="<?=htmlspecialchars($app['link']);?>" disabled></td>
              <td>&nbsp;</td>
            </tr>
            <?php
            endforeach;
            ?>
          </table>
          <p><div class="btn-group">
            <button class="btn btn-sm btn-default" data-action="edit_selected" data-item="admin" data-id="app_links" data-reload="no" data-api-url='edit/app_links' data-api-attr='{}' href="#"><span class="glyphicon glyphicon-check"></span> <?=$lang['admin']['save'];?></button>
            <button class="btn btn-sm btn-default" type="button" id="add_app_link_row"><?=$lang['admin']['add_row'];?></button>
          </div></p>
        </form>
        <legend data-target="#ui_texts" style="padding-top:20px" unselectable="on"><?=$lang['admin']['ui_texts'];?></legend>
        <div id="ui_texts">
        <?php
        $ui_texts = customize('get', 'ui_texts');
        ?>
          <form class="form" data-id="uitexts" role="form" method="post">
            <div class="form-group">
              <label for="title_name"><?=$lang['admin']['title_name'];?>:</label>
              <input type="text" class="form-control" name="title_name" placeholder="mailcow UI" value="<?=$ui_texts['title_name'];?>">
            </div>
            <div class="form-group">
              <label for="main_name"><?=$lang['admin']['main_name'];?>:</label>
              <input type="text" class="form-control" name="main_name" placeholder="mailcow UI" value="<?=$ui_texts['main_name'];?>">
            </div>
            <div class="form-group">
              <label for="apps_name"><?=$lang['admin']['apps_name'];?>:</label>
              <input type="text" class="form-control" name="apps_name" placeholder="mailcow Apps" value="<?=$ui_texts['apps_name'];?>">
            </div>
            <div class="form-group">
              <label for="help_text"><?=$lang['admin']['help_text'];?>:</label>
              <textarea class="form-control" id="help_text" name="help_text" rows="7"><?=$ui_texts['help_text'];?></textarea>
            </div>
            <div class="form-group">
              <label for="ui_footer"><?=$lang['admin']['ui_footer'];?>:</label>
              <textarea class="form-control" id="ui_footer" name="ui_footer" rows="7"><?=$ui_texts['ui_footer'];?></textarea>
            </div>
            <button class="btn btn-default" data-action="edit_selected" data-item="ui" data-id="uitexts" data-api-url='edit/ui_texts' data-api-attr='{}' href="#"><span class="glyphicon glyphicon-check"></span> <?=$lang['admin']['save'];?></button>
          </form>
        </div>
      </div>
    </div>
    </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-sys-mails">
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['sys_mails'];?></div>
      <div class="panel-body">
        <form class="form-horizontal" autocapitalize="none" data-id="admin" autocorrect="off" role="form" method="post">
          <div class="form-group">
            <label class="control-label col-sm-2" for="mass_from"><?=$lang['admin']['from'];?>:</label>
            <div class="col-sm-10">
              <input type="email" class="form-control" name="mass_from" value="noreply@<?=getenv('MAILCOW_HOSTNAME');;?>" required>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="mass_subject"><?=$lang['admin']['subject'];?>:</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" name="mass_subject" required>
            </div>
          </div>
          <?php
          $domains = array_merge(mailbox('get', 'domains'), mailbox('get', 'alias_domains'));
          if (!empty($domains)) {
            foreach ($domains as $domain) {
              foreach (mailbox('get', 'mailboxes', $domain) as $mailbox) {
                $mailboxes[] = $mailbox;
              }
            }
          }
          ?>
          <div class="form-group">
            <label class="control-label col-sm-2" for="mass_subject"><?=$lang['admin']['include_exclude'];?>:
              <p class="help-block"><?=$lang['admin']['include_exclude_info'];?></p>
            </label>
            <div class="col-sm-5">
              <label class="control-label" for="mass_exclude"><?=$lang['admin']['excludes'];?>:</label>
              <select id="mass_exclude" name="mass_exclude[]" data-live-search="true" data-width="100%"  size="30" multiple>
              <?php
              if (!empty($mailboxes)) {
                foreach (array_filter($mailboxes) as $mailbox):
                ?>
                <option><?=htmlspecialchars($mailbox);?></option>
                <?php
                endforeach;
              }
              ?>
              </select>
            </div>
            <div class="col-sm-5">
              <label class="control-label" for="mass_include"><?=$lang['admin']['includes'];?>:</label>
              <select id="mass_include" name="mass_include[]" data-live-search="true" data-width="100%"  size="30" multiple>
              <?php
              if (!empty($mailboxes)) {
                foreach (array_filter($mailboxes) as $mailbox):
                ?>
                <option><?=htmlspecialchars($mailbox);?></option>
                <?php
                endforeach;
              }
              ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="mass_text"><?=$lang['admin']['text'];?>:</label>
            <div class="col-sm-10">
              <textarea class="form-control" rows="10" name="mass_text" id="mass_text" required></textarea>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <label>
                <input type="checkbox" id="mass_disarm"> <?=$lang['admin']['activate_send'];?>
              </label>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <button class="btn btn-default" type="submit" id="mass_send" name="mass_send" disabled><span class="glyphicon glyphicon-envelope"></span> <?=$lang['admin']['send'];?></button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-mailq">
    <div class="panel panel-default">
      <div class="panel-heading">
        <?=$lang['admin']['queue_manager'];?> <span class="badge badge-info table-lines"></span>
        <div class="btn-group pull-right">
          <button class="btn btn-xs btn-default refresh_table" data-draw="draw_queue" data-table="queuetable"><?=$lang['admin']['refresh'];?></button>
        </div>
      </div>
      <div class="panel-body">
      <div class="table-responsive">
        <table class="table table-striped table-condensed" id="queuetable"></table>
      </div>
      <div class="mass-actions-admin">
        <div class="btn-group">
          <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="mailqitems" href="#"><span class="glyphicon glyphicon-check" aria-hidden="true"></span> <?=$lang['mailbox']['toggle_all'];?></a>
          <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a data-toggle="tooltip" title="postqueue -i" data-action="edit_selected" data-id="mailqitems" data-api-url='edit/mailq' data-api-attr='{"action":"deliver"}' href="#"><?=$lang['admin']['queue_deliver_mail'];?></a></li>
            <li><a data-toggle="tooltip" title="postsuper -H" data-action="edit_selected" data-id="mailqitems" data-api-url='edit/mailq' data-api-attr='{"action":"unhold"}' href="#"><?=$lang['admin']['queue_unhold_mail'];?></a></li>
            <li><a data-toggle="tooltip" title="postsuper -h" data-action="edit_selected" data-id="mailqitems" data-api-url='edit/mailq' data-api-attr='{"action":"hold"}' href="#"><?=$lang['admin']['queue_hold_mail'];?></a></li>
            <li role="separator" class="divider"></li>
            <li><a data-toggle="tooltip" title="postsuper -d" data-action="delete_selected" data-id="mailqitems" data-api-url='delete/mailq' href="#"><?=$lang['mailbox']['remove'];?></a></li>
          </ul>
          <a class="btn btn-sm btn-primary"
            data-action="edit_selected"
            data-item="mailqitems-all"
            data-api-url='edit/mailq'
            data-api-attr='{"action":"flush"}'
            data-toggle="tooltip" title="postqueue -f"
            href="#"><span class="glyphicon glyphicon-check" aria-hidden="true"></span> <?=$lang['admin']['flush_queue'];?></a>
          <a class="btn btn-sm btn-danger"
            id="super_delete"
            data-action="edit_selected"
            data-item="mailqitems-all"
            data-api-url='edit/mailq'
            data-api-attr='{"action":"super_delete"}'
            data-toggle="tooltip" title="postsuper -d ALL"
            href="#"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span> <?=$lang['admin']['delete_queue'];?></a>
        </div>
      </div>
      </div>
    </div>
  </div>

  </div> <!-- /tab-content -->
  </div> <!-- /col-md-12 -->
  </div> <!-- /row -->
</div> <!-- /container -->
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/modals/admin.php';
?>
<script type='text/javascript'>
<?php
$lang_admin = json_encode($lang['admin']);
echo "var lang = ". $lang_admin . ";\n";
echo "var admin_username = '". $_SESSION['mailcow_cc_username'] . "';\n";
echo "var csrf_token = '". $_SESSION['CSRF']['TOKEN'] . "';\n";
echo "var pagination_size = '". $PAGINATION_SIZE . "';\n";
echo "var log_pagination_size = '". $LOG_PAGINATION_SIZE . "';\n";
?>
</script>
<?php
$js_minifier->add('/web/js/site/admin.js');
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/footer.inc.php';
} else {
	header('Location: /');
	exit();
}
?>
