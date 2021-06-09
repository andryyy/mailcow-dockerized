<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';

if (isset($_SESSION['mailcow_cc_role']) && $_SESSION['mailcow_cc_role'] == "admin") {
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/header.inc.php';
$_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
$tfa_data = get_tfa();
$fido2_data = fido2(array("action" => "get_friendly_names"));
if (!isset($_SESSION['gal']) && $license_cache = $redis->Get('LICENSE_STATUS_CACHE')) {
  $_SESSION['gal'] = json_decode($license_cache, true);
}
?>
<div class="container">

  <ul class="nav nav-tabs" role="tablist">
    <li class="dropdown active">
      <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['admin']['access'];?><span class="caret"></span></a>
      <ul class="dropdown-menu">
        <li class="active" data-dont-remember="1" role="presentation"><a href="#tab-config-admins" aria-controls="tab-config-admins" role="tab" data-toggle="tab"><?=$lang['admin']['admins'];?></a></li>
        <!-- <li role="presentation"><a href="#tab-config-ldap-admins" aria-controls="tab-config-ldap-admins" role="tab" data-toggle="tab"><?=$lang['admin']['admins_ldap'];?></a></li> -->
        <li role="presentation"><a href="#tab-config-oauth2" aria-controls="tab-config-oauth2" role="tab" data-toggle="tab">OAuth2 Apps</a></li>
        <li role="presentation"><a href="#tab-config-rspamd" aria-controls="tab-config-rspamd" role="tab" data-toggle="tab">Rspamd UI</a></li>
      </ul>
    </li>

    <li class="dropdown">
      <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['admin']['configuration'];?><span class="caret"></span></a>
      <ul class="dropdown-menu">
        <li role="presentation"><a href="#tab-config-dkim" aria-controls="tab-config-dkim" role="tab" data-toggle="tab"><?=$lang['admin']['dkim_keys'];?></a></li>
        <li role="presentation"><a href="#tab-config-fwdhosts" aria-controls="tab-config-fwdhosts" role="tab" data-toggle="tab"><?=$lang['admin']['forwarding_hosts'];?></a></li>
        <li role="presentation"><a href="#tab-config-f2b" aria-controls="tab-config-f2b" role="tab" data-toggle="tab"><?=$lang['admin']['f2b_parameters'];?></a></li>
        <li role="presentation"><a href="#tab-config-quarantine" aria-controls="tab-config-quarantine" role="tab" data-toggle="tab"><?=$lang['admin']['quarantine'];?></a></li>
        <li role="presentation"><a href="#tab-config-quota" aria-controls="tab-config-quota" role="tab" data-toggle="tab"><?=$lang['admin']['quota_notifications'];?></a></li>
        <li role="presentation"><a href="#tab-config-rsettings" aria-controls="tab-config-rsettings" role="tab" data-toggle="tab"><?=$lang['admin']['rspamd_settings_map'];?></a></li>
        <li role="presentation"><a href="#tab-config-password-policy" aria-controls="tab-config-password-policy" role="tab" data-toggle="tab"><?=$lang['admin']['password_policy'];?></a></li>
        <li role="presentation"><a href="#tab-config-customize" aria-controls="tab-config-customize" role="tab" data-toggle="tab"><?=$lang['admin']['customize'];?></a></li>
      </ul>
    </li>
    <li role="presentation"><a href="#tab-routing" aria-controls="tab-routing" role="tab" data-toggle="tab"><?=$lang['admin']['routing'];?></a></li>
    <li role="presentation"><a href="#tab-sys-mails" aria-controls="tab-sys-mails" role="tab" data-toggle="tab"><?=$lang['admin']['sys_mails'];?></a></li>
    <li role="presentation"><a href="#tab-mailq" aria-controls="tab-mailq" role="tab" data-toggle="tab"><?=$lang['admin']['queue_manager'];?></a></li>
    <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['admin']['rspamd_global_filters'];?>
      <span class="caret"></span></a>
      <ul class="dropdown-menu">
        <li role="presentation"><a href="#tab-globalfilter-regex" aria-controls="tab-globalfilter-regex" role="tab" data-toggle="tab"><?=$lang['admin']['regex_maps'];?></a></li>
      </ul>
    </li>
  </ul>

  <div class="row">
  <div class="col-md-12">
  <div class="tab-content" style="padding-top:20px">
  <div role="tabpanel" class="tab-pane active" id="tab-config-admins">
    <div class="panel panel-danger">
      <div class="panel-heading"><?=$lang['admin']['admin_details'];?></div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-striped table-condensed" id="adminstable"></table>
        </div>
        <div class="mass-actions-admin">
          <div class="btn-group">
            <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="admins" href="#"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></a>
            <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a data-action="edit_selected" data-id="admins" data-api-url='edit/admin' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
              <li><a data-action="edit_selected" data-id="admins" data-api-url='edit/admin' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
              <li role="separator" class="divider"></li>
              <li><a data-action="edit_selected" data-id="admins" data-api-url='edit/admin' data-api-attr='{"disable_tfa":"1"}' href="#"><?=$lang['tfa']['disable_tfa'];?></a></li>
              <li role="separator" class="divider"></li>
              <li><a data-action="delete_selected" data-id="admins" data-api-url='delete/admin' href="#"><?=$lang['mailbox']['remove'];?></a></li>
            </ul>
            <a class="btn btn-sm btn-success" data-id="add_admin" data-toggle="modal" data-target="#addAdminModal" href="#"><i class="bi bi-person-plus-fill"></i> <?=$lang['admin']['add_admin'];?></a>
          </div>
        </div>

        <? // TFA ?>
        <legend style="margin-top:20px">
          <?=$lang['tfa']['tfa'];?>
        </legend>
        <div class="row">
          <div class="col-sm-3 col-xs-5 text-right"><?=$lang['tfa']['tfa'];?>:</div>
          <div class="col-sm-9 col-xs-7">
            <p id="tfa_pretty"><?=$tfa_data['pretty'];?></p>
              <div id="tfa_keys">
                <?php
                if (!empty($tfa_data['additional'])) {
                  foreach ($tfa_data['additional'] as $key_info) {
                ?>
                <form style="display:inline;" method="post">
                  <input type="hidden" name="unset_tfa_key" value="<?=$key_info['id'];?>">
                  <div style="padding:4px;margin:4px" class="label label-keys label-<?=($_SESSION['tfa_id'] == $key_info['id']) ? 'success' : 'default'; ?>">
                  <?=$key_info['key_id'];?>
                  <a href="#" style="font-weight:bold;color:white" onClick="$(this).closest('form').submit()">[<?=$lang['admin']['remove'];?>]</a>
                  </div>
                </form>
                <?php
                  }
                }
                ?>
              </div>
              <br>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-3 col-xs-5 text-right"><?=$lang['tfa']['set_tfa'];?>:</div>
          <div class="col-sm-9 col-xs-7">
            <select data-style="btn btn-sm dropdown-toggle bs-placeholder btn-default" data-width="fit" id="selectTFA" class="selectpicker" title="<?=$lang['tfa']['select'];?>">
              <option value="yubi_otp"><?=$lang['tfa']['yubi_otp'];?></option>
              <option value="u2f"><?=$lang['tfa']['u2f'];?></option>
              <option value="totp"><?=$lang['tfa']['totp'];?></option>
              <option value="none"><?=$lang['tfa']['none'];?></option>
            </select>
          </div>
        </div>

        <? // FIDO2 ?>
        <legend style="margin-top:20px">
        <i class="bi bi-shield-fill-check"></i>
        <?=$lang['fido2']['fido2_auth'];?></legend>
        <div class="row">
          <div class="col-sm-3 col-xs-5 text-right"><?=$lang['fido2']['known_ids'];?>:</div>
          <div class="col-sm-9 col-xs-7">
              <div class="table-responsive">
              <table class="table table-striped table-hover table-condensed" id="fido2_keys">
                <tr>
                  <th>ID</th>
                  <th style="min-width:240px;text-align: right"><?=$lang['admin']['action'];?></th>
                </tr>
                <?php
                if (!empty($fido2_data)) {
                  foreach ($fido2_data as $key_info) {
                ?>
                <tr>
                  <td>
                    <?=($_SESSION['fido2_cid'] == $key_info['cid']) ? '<i class="bi bi-unlock-fill"></i> ' : NULL; ?><?=(!empty($key_info['fn']))?$key_info['fn']:$key_info['subject'];?>
                  </td>
                  <td style="min-width:240px;text-align: right">
                    <form style="display:inline;" method="post">
                    <input type="hidden" name="unset_fido2_key" value="<?=$key_info['cid'];?>">
                    <div class="btn-group">
                    <a href="#" class="btn btn-xs btn-default" data-cid="<?=$key_info['cid'];?>" data-subject="<?=base64_encode($key_info['subject']);?>" data-toggle="modal" data-target="#fido2ChangeFn"><i class="bi bi-pencil-fill"></i> <?=$lang['fido2']['rename'];?></a>
                    <a href="#" onClick='return confirm("<?=$lang['admin']['ays'];?>")?$(this).closest("form").submit():"";' class="btn btn-xs btn-danger"><i class="bi bi-trash"></i> <?=$lang['admin']['remove'];?></a>
                    </form>
                    </div>
                  </td>
                </tr>
                <?php
                  }
                }
                ?>
              </table>
              </div>
              <br>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-offset-3 col-sm-9">
            <button class="btn btn-sm btn-primary" id="register-fido2"><?=$lang['fido2']['set_fido2'];?></button>
          </div>
        </div>
        <br>
        <div class="row" id="status-fido2">
          <div class="col-sm-3 col-xs-5 text-right"><?=$lang['fido2']['register_status'];?>:</div>
          <div class="col-sm-9 col-xs-7">
            <div id="fido2-alerts">-</div>
          </div>
          <br>
        </div>

        <legend style="cursor:pointer;margin-top:40px" data-target="#license" unselectable="on" data-toggle="collapse">
          <i style="font-size:10pt;" class="bi bi-plus-square"></i> <?=$lang['admin']['guid_and_license'];?>
        </legend>
        <div id="license" class="collapse">
        <form class="form-horizontal" autocapitalize="none" autocorrect="off" role="form" method="post">
          <div class="form-group">
            <label class="control-label col-sm-3" for="guid"><?=$lang['admin']['guid'];?>:</label>
            <div class="col-sm-9">
              <div class="input-group">
                <span class="input-group-addon">
                  <i class="bi bi-suit-heart<?=(isset($_SESSION['gal']['valid']) && $_SESSION['gal']['valid'] === "true") ? '-fill text-danger' : '';?>"></i>
                </span>
                <input type="text" id="guid" class="form-control" value="<?=license('guid');?>" readonly>
              </div>
              <p class="help-block">
                <?=$lang['admin']['customer_id'];?>: <?=(isset($_SESSION['gal']['c'])) ? $_SESSION['gal']['c'] : '?';?> -
                <?=$lang['admin']['service_id'];?>: <?=(isset($_SESSION['gal']['s'])) ? $_SESSION['gal']['s'] : '?';?> -
                <?=$lang['admin']['sal_level'];?>: <?=(isset($_SESSION['gal']['m'])) ? $_SESSION['gal']['m'] : '?';?>
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

        <legend style="cursor:pointer;" data-target="#admin_api" unselectable="on" data-toggle="collapse">
          <i style="font-size:10pt;" class="bi bi-plus-square"></i> API
        </legend>
        <div id="admin_api" class="collapse">
        <div class="row">
        <?php
        $api_ro = admin_api('ro', 'get');
        $api_rw = admin_api('rw', 'get');
        ?>
          <div class="col-lg-12">
          <p class="help-block"><?=$lang['admin']['api_info'];?></p>
          </div>
          <div class="col-lg-6">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"><i class="bi bi-file-earmark-arrow-down"></i> Read-Only Access</h4>
              </div>
                <div class="panel-body">
                  <form class="form-horizontal" autocapitalize="none" autocorrect="off" role="form" method="post">
                    <div class="form-group">
                      <label class="control-label col-sm-3" for="allow_from_ro"><?=$lang['admin']['api_allow_from'];?>:</label>
                      <div class="col-sm-9">
                        <textarea class="form-control textarea-code" rows="7" name="allow_from" id="allow_from_ro" <?=($api_ro['skip_ip_check'] == 1) ? 'disabled' : null;?> required><?=htmlspecialchars($api_ro['allow_from']);?></textarea>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-3 col-sm-9">
                        <label>
                          <input type="checkbox" name="skip_ip_check" id="skip_ip_check_ro" <?=($api_ro['skip_ip_check'] == 1) ? 'checked' : null;?>> <?=$lang['admin']['api_skip_ip_check'];?>
                        </label>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-3"><?=$lang['admin']['api_key'];?>:</label>
                      <div class="col-sm-9">
                        <pre><?=(empty($api_ro['api_key'])) ? '-' : htmlspecialchars($api_ro['api_key']);?></pre>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-3 col-sm-9">
                        <label>
                          <input type="checkbox" name="active" <?=($api_ro['active'] == 1) ? 'checked' : null;?>> <?=$lang['admin']['activate_api'];?>
                        </label>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-3 col-sm-9">
                        <div class="btn-group">
                          <button class="btn btn-sm btn-success" name="admin_api[ro]" type="submit" href="#"><i class="bi bi-check-lg"></i> <?=$lang['admin']['save'];?></button>
                          <button class="btn btn-sm btn-default admin-ays-dialog" name="admin_api_regen_key[ro]" type="submit" href="#" <?=(!empty($api_ro['api_key'])) ?: 'disabled';?>><?=$lang['admin']['regen_api_key'];?></button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"><i class="bi bi-file-earmark-diff"></i> Read-Write Access</h4>
              </div>
                <div class="panel-body">
                  <form class="form-horizontal" autocapitalize="none" autocorrect="off" role="form" method="post">
                    <div class="form-group">
                      <label class="control-label col-sm-3" for="allow_from_rw"><?=$lang['admin']['api_allow_from'];?>:</label>
                      <div class="col-sm-9">
                        <textarea class="form-control textarea-code" rows="7" name="allow_from" id="allow_from_rw" <?=($api_rw['skip_ip_check'] == 1) ? 'disabled' : null;?> required><?=htmlspecialchars($api_rw['allow_from']);?></textarea>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-3 col-sm-9">
                        <label>
                          <input type="checkbox" name="skip_ip_check" id="skip_ip_check_rw" <?=($api_rw['skip_ip_check'] == 1) ? 'checked' : null;?>> <?=$lang['admin']['api_skip_ip_check'];?>
                        </label>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-3" for="admin_api_key"><?=$lang['admin']['api_key'];?>:</label>
                      <div class="col-sm-9">
                        <pre><?=(empty($api_rw['api_key'])) ? '-' : htmlspecialchars($api_rw['api_key']);?></pre>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-3 col-sm-9">
                        <label>
                          <input type="checkbox" name="active" <?=($api_rw['active'] == 1) ? 'checked' : null;?>> <?=$lang['admin']['activate_api'];?>
                        </label>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-3 col-sm-9">
                        <div class="btn-group">
                          <button class="btn btn-sm btn-success" name="admin_api[rw]" type="submit" href="#"><i class="bi bi-check-lg"></i> <?=$lang['admin']['save'];?></button>
                          <button class="btn btn-sm btn-default admin-ays-dialog" name="admin_api_regen_key[rw]" type="submit" <?=(!empty($api_rw['api_key'])) ?: 'disabled';?> href="#"><?=$lang['admin']['regen_api_key'];?></button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
            </div>
          </div>
        </div>
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
              <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="domain_admins" href="#"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></a>
              <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a data-action="edit_selected" data-id="domain_admins" data-api-url='edit/domain-admin' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                <li><a data-action="edit_selected" data-id="domain_admins" data-api-url='edit/domain-admin' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                <li role="separator" class="divider"></li>
                <li><a data-action="edit_selected" data-id="domain_admins" data-api-url='edit/domain-admin' data-api-attr='{"disable_tfa":"1"}' href="#"><?=$lang['tfa']['disable_tfa'];?></a></li>
                <li role="separator" class="divider"></li>
                <li><a data-action="delete_selected" data-id="domain_admins" data-api-url='delete/domain-admin' href="#"><?=$lang['mailbox']['remove'];?></a></li>
              </ul>
              <a class="btn btn-sm btn-success" data-id="add_domain_admin" data-toggle="modal" data-target="#addDomainAdminModal" href="#"><i class="bi bi-person-plus-fill"></i> <?=$lang['admin']['add_domain_admin'];?></a>
            </div>
          </div>
        </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-config-ldap-admins">
    <div class="panel panel-default">
    <div class="panel-heading"><?=$lang['admin']['admins_ldap'];?></div>
        <div class="panel-body">
        </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-config-oauth2">
    <div class="panel panel-default">
    <div class="panel-heading">OAuth2 Apps</div>
        <div class="panel-body">
          <p><?=$lang['admin']['oauth2_info'];?></p>
          <div class="table-responsive">
            <table class="table table-striped" id="oauth2clientstable"></table>
          </div>
          <div class="mass-actions-admin">
            <div class="btn-group">
              <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="oauth2_clients" href="#"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></a>
              <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a data-action="delete_selected" data-id="oauth2_clients" data-api-url='delete/oauth2-client' href="#"><?=$lang['mailbox']['remove'];?></a></li>
                <li role="separator" class="divider"></li>
                <li><a data-action="edit_selected" data-id="oauth2_clients" data-api-url='edit/oauth2-client' data-api-attr='{"revoke_tokens":"1"}' href="#"><?=$lang['admin']['oauth2_revoke_tokens'];?></a></li>
                <li role="separator" class="divider"></li>
                <li><a data-action="edit_selected" data-id="oauth2_clients" data-api-url='edit/oauth2-client' data-api-attr='{"renew_secret":"1"}' href="#"><?=$lang['admin']['oauth2_renew_secret'];?></a></li>
              </ul>
              <a class="btn btn-sm btn-success" data-id="add_oauth2_client" data-toggle="modal" data-target="#addOAuth2ClientModal" href="#"><i class="bi bi-plus-lg"></i> Add OAuth2 client</a>
            </div>
          </div>
        </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-config-rspamd">
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
                  <a href="/rspamd/" target="_blank"><i class="bi bi-window"></i> Rspamd UI</a>
                </label>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-3" for="rspamd_ui_pass"><?=$lang['admin']['password'];?>:</label>
              <div class="col-sm-9">
              <input type="password" class="form-control" id="rspamd_ui_pass" name="rspamd_ui_pass" autocomplete="new-password" required>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-3" for="rspamd_ui_pass2"><?=$lang['admin']['password_repeat'];?>:</label>
              <div class="col-sm-9">
              <input type="password" class="form-control" id="rspamd_ui_pass2" name="rspamd_ui_pass2" autocomplete="new-password" required>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-3 col-sm-9">
                <button type="submit" class="btn btn-sm btn-success" id="rspamd_ui" name="rspamd_ui" href="#"><i class="bi bi-check-lg"></i> <?=$lang['admin']['save'];?></button>
              </div>
            </div>
          </form>
          </div>
          <div class="col-sm-3">
            <img class="img-responsive" src="/img/rspamd_logo.png" alt="Rspamd UI">
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
          <div class="col-md-8">
            <form class="form" data-id="rlyhost" role="form" method="post">
              <div class="form-group">
                <label for="rlyhost_hostname"><?=$lang['admin']['host'];?></label>
                <input class="form-control" id="rlyhost_hostname" name="hostname" placeholder='[0.0.0.0], [0.0.0.0]:25, host:25, host, [host]:25' required>
              </div>
              <div class="form-group">
                <label for="rlyhost_username"><?=$lang['admin']['username'];?></label>
                <input class="form-control" id="rlyhost_username" name="username">
              </div>
              <div class="form-group">
                <label for="rlyhost_password"><?=$lang['admin']['password'];?></label>
                <input class="form-control" id="rlyhost_password" name="password">
              </div>
              <button class="btn btn-default" data-action="add_item" data-id="rlyhost" data-api-url='add/relayhost' data-api-attr='{}' href="#"><i class="bi bi-plus-lg"></i> <?=$lang['admin']['add'];?></button>
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
          <div class="col-md-8">
            <form class="form" data-id="transport" role="form" method="post">
              <div class="form-group">
                <label for="transport_destination"><?=$lang['admin']['destination'];?></label>
                <input class="form-control" id="transport_destination" name="destination" placeholder='<?=$lang['admin']['transport_dest_format'];?>' required>
              </div>
              <div class="form-group">
                <label for="transport_nexthop"><?=$lang['admin']['nexthop'];?></label>
                <input class="form-control" id="transport_nexthop" name="nexthop" placeholder='host:25, host, [host]:25, [0.0.0.0]:25' required>
              </div>
              <div class="form-group">
                <label for="transport_username"><?=$lang['admin']['username'];?></label>
                <input class="form-control" id="transport_username" name="username">
              </div>
              <div class="form-group">
                <label for="transport_password"><?=$lang['admin']['password'];?></label>
                <input class="form-control" id="transport_password" name="password">
              </div>
              <div class="form-group">
                <label>
                  <input type="checkbox" name="is_mx_based" value="1"> <?=$lang['admin']['lookup_mx'];?>
                </label>
              </div>
              <div class="form-group">
                <label>
                  <input type="checkbox" name="active" value="1"> <?=$lang['admin']['active'];?>
                </label>
              </div>
              <p class="help-block"><?=$lang['admin']['credentials_transport_warning'];?></p>
              <button class="btn btn-default" data-action="add_item" data-id="transport" data-api-url='add/transport' data-api-attr='{}' href="#"><i class="bi bi-plus-lg"></i> <?=$lang['admin']['add'];?></button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-config-dkim">
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['dkim_keys'];?></div>
      <div class="panel-body">
        <div class="btn-group" data-toggle="button" style="margin-bottom: 20px;">
          <a class="btn btn-sm btn-default active" href="#" data-toggle="collapse" data-target=".dkim_key_valid"><?=$lang['admin']['dkim_key_valid'];?></a>
          <a class="btn btn-sm btn-default active" href="#" data-toggle="collapse" data-target=".dkim_key_unused"><?=$lang['admin']['dkim_key_unused'];?></a>
          <a class="btn btn-sm btn-default active" href="#" data-toggle="collapse" data-target=".dkim_key_missing"><?=$lang['admin']['dkim_key_missing'];?></a>
        </div>
        <?php
        foreach(mailbox('get', 'domains') as $domain) {
            if (!empty($dkim = dkim('details', $domain))) {
              $dkim_domains[] = $domain;
              ($GLOBALS['SHOW_DKIM_PRIV_KEYS'] === true) ?: $dkim['privkey'] = base64_encode('Please set $SHOW_DKIM_PRIV_KEYS to true to show DKIM private keys.');
          ?>
            <div class="row collapse in dkim_key_valid">
              <div class="col-md-1"><input type="checkbox" data-id="dkim" name="multi_select" value="<?=$domain;?>"></div>
              <div class="col-md-3">
                <p><?=$lang['admin']['domain'];?>: <strong><?=htmlspecialchars($domain);?></strong>
                  <p class="dkim-label"><span class="label label-success"><?=$lang['admin']['dkim_key_valid'];?></span></p>
                  <p class="dkim-label"><span class="label label-primary"><?=$lang['admin']['dkim_domains_selector'];?> '<?=$dkim['dkim_selector'];?>'</span></p>
                  <p class="dkim-label"><span class="label label-info"><?=$dkim['length'];?> bit</span></p>
                </p>
              </div>
              <div class="col-md-8">
                  <pre><?=$dkim['dkim_txt'];?></pre>
                  <p data-toggle="modal" data-target="#showDKIMprivKey" id="dkim_priv" style="cursor:pointer;margin-top:-8pt" data-priv-key="<?=$dkim['privkey'];?>"><small><i class="bi bi-arrow-return-right"></i> <?=$lang['admin']['dkim_private_key'];?></small></p>
              </div>
              <hr class="visible-xs visible-sm">
            </div>
          <?php
          }
          else {
          ?>
          <div class="row collapse in dkim_key_missing">
            <div class="col-md-1"><input class="dkim_missing" type="checkbox" data-id="dkim" name="multi_select" value="<?=$domain;?>" disabled></div>
            <div class="col-md-3">
              <p><?=$lang['admin']['domain'];?>: <strong><?=htmlspecialchars($domain);?></strong><br><span class="label label-danger"><?=$lang['admin']['dkim_key_missing'];?></span></p>
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
              <div class="row collapse in dkim_key_valid">
              <div class="col-md-1"><input type="checkbox" data-id="dkim" name="multi_select" value="<?=$alias_domain;?>"></div>
                <div class="col-md-2 col-md-offset-1">
                  <p><small>↳ Alias-Domain: <strong><?=htmlspecialchars($alias_domain);?></strong></small>
                    <p class="dkim-label"><span class="label label-success"><?=$lang['admin']['dkim_key_valid'];?></span></p>
                    <p class="dkim-label"><span class="label label-primary">Selector '<?=$dkim['dkim_selector'];?>'</span></p>
                    <p class="dkim-label"><span class="label label-info"><?=$dkim['length'];?> bit</span></p>
                </p>
                </div>
                <div class="col-md-8">
                  <pre><?=$dkim['dkim_txt'];?></pre>
                  <p data-toggle="modal" data-target="#showDKIMprivKey" id="dkim_priv" style="cursor:pointer;margin-top:-8pt" data-priv-key="<?=$dkim['privkey'];?>"><small><i class="bi bi-arrow-return-right"></i> Private key</small></p>
                </div>
              <hr class="visible-xs visible-sm">
              </div>
            <?php
            }
            else {
            ?>
            <div class="row collapse in dkim_key_missing">
              <div class="col-md-1"><input class="dkim_missing" type="checkbox" data-id="dkim" name="multi_select" value="<?=$alias_domain;?>" disabled></div>
              <div class="col-md-2 col-md-offset-1">
                <p><small>↳ Alias-Domain: <strong><?=htmlspecialchars($alias_domain);?></strong><br></small><span class="label label-danger"><?=$lang['admin']['dkim_key_missing'];?></span></p>
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
            <div class="row collapse in dkim_key_unused">
              <div class="col-md-1"><input type="checkbox" data-id="dkim" name="multi_select" value="<?=$blind;?>"></div>
              <div class="col-md-3">
                <p><?=$lang['admin']['domain'];?>: <strong><?=htmlspecialchars($blind);?></strong>
                  <p class="dkim-label"><span class="label label-warning"><?=$lang['admin']['dkim_key_unused'];?></span></p>
                  <p class="dkim-label"><span class="label label-primary">Selector '<?=$dkim['dkim_selector'];?>'</span></p>
                  <p class="dkim-label"><span class="label label-info"><?=$dkim['length'];?> bit</span></p>
                </p>
                </div>
                <div class="col-md-8">
                  <pre><?=$dkim['dkim_txt'];?></pre>
                  <p data-toggle="modal" data-target="#showDKIMprivKey" id="dkim_priv" style="cursor:pointer;margin-top:-8pt" data-priv-key="<?=$dkim['privkey'];?>"><small><i class="bi bi-arrow-return-right"></i> Private key</small></p>
                </div>
                <hr class="visible-xs visible-sm">
            </div>
          <?php
          }
        }
        ?>
        <div class="mass-actions-admin">
          <div class="btn-group btn-group-sm">
            <button type="button" id="toggle_multi_select_all" data-id="dkim" class="btn btn-default"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></button>
            <button type="button" data-action="delete_selected" name="delete_selected" data-id="dkim" data-api-url="delete/dkim" class="btn btn-danger"><i class="bi bi-trash"></i> <?=$lang['admin']['remove'];?></button>
          </div>
        </div>

        <legend style="margin-top:40px"><?=$lang['admin']['dkim_add_key'];?></legend>
        <form class="form" data-id="dkim" role="form" method="post">
          <div class="form-group">
            <label for="dkim_add_domains"><?=$lang['admin']['domain_s'];?></label>
            <input class="form-control input-sm" id="dkim_add_domains" name="domains" placeholder="example.org, example.com" required>
            <small><i class="bi bi-arrow-return-right"></i> <a href="#" id="dkim_missing_keys"><?=$lang['admin']['dkim_domains_wo_keys'];?></a></small>
          </div>
          <div class="form-group">
            <label for="dkim_selector"><?=$lang['admin']['dkim_domains_selector'];?></label>
            <input class="form-control input-sm" id="dkim_selector" name="dkim_selector" value="dkim" required>
          </div>
          <div class="form-group">
            <select data-width="200px" data-style="btn btn-default btn-sm" class="form-control" id="key_size" name="key_size" title="<?=$lang['admin']['dkim_key_length'];?>" required>
              <option data-subtext="bits">1024</option>
              <option data-subtext="bits">2048</option>
            </select>
          </div>
          <button class="btn btn-sm btn-default" data-action="add_item" data-id="dkim" data-api-url='add/dkim' data-api-attr='{}' href="#"><i class="bi bi-plus-lg"></i> <?=$lang['admin']['add'];?></button>
        </form>

        <legend data-target="#import_dkim" style="margin-top:40px;cursor:pointer" unselectable="on" data-toggle="collapse">
          <i style="font-size:10pt;" class="bi bi-plus-square"></i> <?=$lang['admin']['import_private_key'];?>
        </legend>
        <div id="import_dkim" class="collapse">
        <form class="form" data-id="dkim_import" role="form" method="post">
          <div class="form-group">
            <label for="dkim_import_domain"><?=$lang['admin']['domain'];?>:</label>
            <input class="form-control input-sm" id="dkim_import_domain" name="domain" placeholder="example.org" required>
          </div>
          <div class="form-group">
            <label for="dkim_import_selector"><?=$lang['admin']['dkim_domains_selector'];?>:</label>
            <input class="form-control input-sm" id="dkim_import_selector" name="dkim_selector" value="dkim" required>
          </div>
          <div class="form-group">
            <label for="private_key_file"><?=$lang['admin']['private_key'];?>: (RSA PKCS#8)</label>
            <textarea class="form-control input-sm" rows="10" name="private_key_file" id="private_key_file" required placeholder="-----BEGIN RSA KEY-----"></textarea>
          </div>
          <div class="form-group">
            <label>
              <input type="checkbox" name="overwrite_existing" value="1"> <?=$lang['admin']['dkim_overwrite_key'];?>
            </label>
          </div>
          <button class="btn btn-sm btn-default" data-action="add_item" data-id="dkim_import" data-api-url='add/dkim_import' data-api-attr='{}' href="#"><i class="bi bi-plus-lg"></i> <?=$lang['admin']['import'];?></button>
        </form>
        </div>

        <legend data-target="#duplicate_dkim" style="margin-top:40px;cursor:pointer" unselectable="on" data-toggle="collapse">
          <i style="font-size:10pt;" class="bi bi-plus-square"></i> <?=$lang['admin']['duplicate_dkim'];?>
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
            <button class="btn btn-sm btn-default" data-action="add_item" data-id="dkim_duplicate" data-api-url='add/dkim_duplicate' data-api-attr='{}' href="#"><i class="bi bi-clipboard-plus"></i> <?=$lang['admin']['duplicate'];?></button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-config-fwdhosts">
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
            <label for="fwdhost_hostname"><?=$lang['admin']['host'];?></label>
            <input class="form-control" id="fwdhost_hostname" name="hostname" placeholder="example.org" required>
          </div>
          <div class="form-group">
            <select data-width="200px" class="form-control" id="filter_spam" name="filter_spam" title="<?=$lang['user']['spamfilter'];?>" required>
              <option value="1"><?=$lang['admin']['active'];?></option>
              <option value="0"><?=$lang['admin']['inactive'];?></option>
            </select>
          </div>
          <button class="btn btn-default" data-action="add_item" data-id="fwdhost" data-api-url='add/fwdhost' data-api-attr='{}' href="#"><i class="bi bi-plus-lg"></i> <?=$lang['admin']['add'];?></button>
        </form>
      </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-config-f2b">
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['f2b_parameters'];?></div>
      <div class="panel-body">
      <?php
      $f2b_data = fail2ban('get');
      ?>
        <form class="form" data-id="f2b" role="form" method="post">
          <div class="form-group">
            <label for="f2b_ban_time"><?=$lang['admin']['f2b_ban_time'];?>:</label>
            <input type="number" class="form-control" id="f2b_ban_time" name="ban_time" value="<?=$f2b_data['ban_time'];?>" required>
          </div>
          <div class="form-group">
            <label for="f2b_max_attempts"><?=$lang['admin']['f2b_max_attempts'];?>:</label>
            <input type="number" class="form-control" id="f2b_max_attempts" name="max_attempts" value="<?=$f2b_data['max_attempts'];?>" required>
          </div>
          <div class="form-group">
            <label for="f2b_retry_window"><?=$lang['admin']['f2b_retry_window'];?>:</label>
            <input type="number" class="form-control" id="f2b_retry_window" name="retry_window" value="<?=$f2b_data['retry_window'];?>" required>
          </div>
          <div class="form-group">
            <label for="f2b_netban_ipv4"><?=$lang['admin']['f2b_netban_ipv4'];?>:</label>
            <div class="input-group">
              <span class="input-group-addon">/</span>
              <input type="number" class="form-control" id="f2b_netban_ipv4" name="netban_ipv4" value="<?=$f2b_data['netban_ipv4'];?>" required>
            </div>
          </div>
          <div class="form-group">
            <label for="f2b_netban_ipv6"><?=$lang['admin']['f2b_netban_ipv6'];?>:</label>
            <div class="input-group">
              <span class="input-group-addon">/</span>
              <input type="number" class="form-control" id="f2b_netban_ipv6" name="netban_ipv6" value="<?=$f2b_data['netban_ipv6'];?>" required>
            </div>
          </div>
          <hr>
          <p class="help-block"><?=$lang['admin']['f2b_list_info'];?></p>
          <div class="form-group">
            <label for="f2b_whitelist"><?=$lang['admin']['f2b_whitelist'];?>:</label>
            <textarea class="form-control" id="f2b_whitelist" name="whitelist" rows="5"><?=$f2b_data['whitelist'];?></textarea>
          </div>
          <div class="form-group">
            <label for="f2b_blacklist"><?=$lang['admin']['f2b_blacklist'];?>:</label>
            <textarea class="form-control" id="f2b_blacklist" name="blacklist" rows="5"><?=$f2b_data['blacklist'];?></textarea>
          </div>
          <div class="btn-group">
            <button class="btn btn-sm btn-success" data-action="edit_selected" data-item="self" data-id="f2b" data-api-url='edit/fail2ban' data-api-attr='{}' href="#"><i class="bi bi-check-lg"></i> <?=$lang['admin']['save'];?></button>
            <a href="#" role="button" class="btn btn-sm btn-default" data-toggle="modal" data-container="netfilter-mailcow" data-target="#RestartContainer"><i class="bi bi-arrow-repeat"></i> <?= $lang['header']['restart_netfilter']; ?></a>
          </div>
        </form>
        <hr>
        <h4><?=$lang['admin']['f2b_filter'];?></h4>
        <p class="help-block"><?=$lang['admin']['f2b_regex_info'];?></p>
        <form class="form-inline" data-id="f2b_regex" role="form" method="post">
          <table class="table table-condensed" id="f2b_regex_table">
            <tr>
              <th width="50px">ID</th>
              <th>RegExp</th>
              <th width="100px">&nbsp;</th>
            </tr>
            <?php
            if (!empty($f2b_data['regex'])) {
              foreach ($f2b_data['regex'] as $regex_id => $regex_val) {
            ?>
            <tr>
              <td><input disabled class="input-sm form-control" style="text-align:center" data-id="f2b_regex" type="text" name="app" required value="<?=$regex_id;?>"></td>
              <td><input class="input-sm form-control regex-input" data-id="f2b_regex" type="text" name="regex" required value="<?=htmlspecialchars($regex_val);?>"></td>
              <td><a href="#" role="button" class="btn btn-xs btn-default" type="button"><?=$lang['admin']['remove_row'];?></a></td>
            </tr>
            <?php
              }
            }
            ?>
          </table>
          <p><div class="btn-group">
            <button class="btn btn-sm btn-success" data-action="edit_selected" data-item="admin" data-id="f2b_regex" data-reload="no" data-api-url='edit/fail2ban' data-api-attr='{"action":"edit-regex"}' href="#"><i class="bi bi-check-lg"></i> <?=$lang['admin']['save'];?></button>
            <button class="btn btn-sm btn-default admin-ays-dialog" data-action="edit_selected" data-item="self" data-id="f2b-quick" data-api-url='edit/fail2ban' data-api-attr='{"action":"reset-regex"}' href="#"><?=$lang['admin']['reset_default'];?></button>
            <button class="btn btn-sm btn-default" type="button" id="add_f2b_regex_row"><i class="bi bi-plus-lg"></i> <?=$lang['admin']['add_row'];?></button>
          </div></p>
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
          <p><span class="label label-info" style="padding:4px;font-size:85%;"><i class="bi bi-funnel-fill"></i> <?=$active_bans['network'];?> (<?=$active_bans['banned_until'];?>) -
            <?php
            if ($active_bans['queued_for_unban'] == 0):
            ?>
            <a data-action="edit_selected" data-item="<?=$active_bans['network'];?>" data-id="f2b-quick" data-api-url='edit/fail2ban' data-api-attr='{"action":"unban"}' href="#">[<?=$lang['admin']['queue_unban'];?>]</a>
            <a data-action="edit_selected" data-item="<?=$active_bans['network'];?>" data-id="f2b-quick" data-api-url='edit/fail2ban' data-api-attr='{"action":"whitelist"}' href="#">[whitelist]</a>
            <a data-action="edit_selected" data-item="<?=$active_bans['network'];?>" data-id="f2b-quick" data-api-url='edit/fail2ban' data-api-attr='{"action":"blacklist"}' href="#">[blacklist (<b>needs restart</b>)]</a>
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
          <span class="label label-danger" style="padding: 0.1em 0.4em 0.1em;"><i class="bi bi-funnel-fill"></i> <?=$perm_bans?></span>
          <?php
          endforeach;
        endif;
        ?>
      </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-config-quarantine">
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['quarantine'];?></div>
      <div class="panel-body">
        <?php $q_data = quarantine('settings');
        if (empty($q_data['retention_size']) || empty($q_data['max_size'])):
        ?>
        <div class="panel-body"><div class="alert alert-info"><?=$lang['quarantine']['disabled_by_config'];?></div></div>
        <?php
        endif;
        ?>
        <form class="form-horizontal" data-id="quarantine" role="form" method="post">
          <div class="form-group">
            <label class="col-sm-4 control-label" for="quarantine_retention_size"><?=$lang['admin']['quarantine_retention_size'];?></label>
            <div class="col-sm-8">
              <input type="number" class="form-control" id="quarantine_retention_size" name="retention_size" value="<?=$q_data['retention_size'];?>" placeholder="0" required>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4 control-label" for="quarantine_max_size"><?=$lang['admin']['quarantine_max_size'];?></label>
            <div class="col-sm-8">
              <input type="number" class="form-control" id="quarantine_max_size" name="max_size" value="<?=$q_data['max_size'];?>" placeholder="0" required>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4 control-label" for="quarantine_max_score"><?=$lang['admin']['quarantine_max_score'];?></label>
            <div class="col-sm-8">
              <input type="number" class="form-control" id="quarantine_max_score" name="max_score" value="<?=$q_data['max_score'];?>" placeholder="9999.0">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4 control-label" for="quarantine_max_age"><?=$lang['admin']['quarantine_max_age'];?></label>
            <div class="col-sm-8">
              <input type="number" class="form-control" id="quarantine_max_age" name="max_age" value="<?=$q_data['max_age'];?>" min="1" required>
            </div>
          </div>
          <hr>
          <div class="form-group">
            <label class="col-sm-4 control-label" for="quarantine_redirect"><i class="bi bi-box-arrow-right"></i> <?=$lang['admin']['quarantine_redirect'];?></label>
            <div class="col-sm-8">
              <input type="email" class="form-control" id="quarantine_redirect" name="redirect" value="<?=htmlspecialchars($q_data['redirect']);?>" placeholder="">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4 control-label" for="quarantine_bcc"><i class="bi bi-files"></i> <?=$lang['admin']['quarantine_bcc'];?></label>
            <div class="col-sm-8">
              <input type="email" class="form-control" id="quarantine_bcc" name="bcc" value="<?=htmlspecialchars($q_data['bcc']);?>" placeholder="">
            </div>
          </div>
          <hr>
          <div class="form-group">
            <label class="col-sm-4 control-label" for="quarantine_sender"><?=$lang['admin']['quarantine_notification_sender'];?>:</label>
            <div class="col-sm-8">
              <input type="email" class="form-control" id="quarantine_sender" name="sender" value="<?=htmlspecialchars($q_data['sender']);?>" placeholder="quarantine@localhost">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4 control-label" for="quarantine_subject"><?=$lang['admin']['quarantine_notification_subject'];?>:</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" id="quarantine_subject" name="subject" value="<?=htmlspecialchars($q_data['subject']);?>" placeholder="Spam Quarantine Notification">
            </div>
          </div>
          <hr>
          <div class="form-group">
            <label class="col-sm-4 control-label" for="quarantine_release_format"><?=$lang['admin']['quarantine_release_format'];?>:</label>
            <div class="col-sm-8">
              <select data-width="100%" id="quarantine_release_format" name="release_format" class="selectpicker" title="<?=$lang['tfa']['select'];?>">
                <option <?=($q_data['release_format'] == 'raw') ? 'selected' : null;?> value="raw"><?=$lang['admin']['quarantine_release_format_raw'];?></option>
                <option <?=($q_data['release_format'] == 'attachment') ? 'selected' : null;?> value="attachment"><?=$lang['admin']['quarantine_release_format_att'];?></option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4 control-label" for="exclude_domains"><?=$lang['admin']['quarantine_exclude_domains'];?>:</label>
            <div class="col-sm-8">
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
          <hr>
          <legend data-target="#quarantine_template" style="cursor:pointer" unselectable="on" data-toggle="collapse">
            <i style="font-size:10pt;" class="bi bi-plus-square"></i> <?=$lang['admin']['quarantine_notification_html'];?>
          </legend>
          <div id="quarantine_template" class="collapse" >
            <textarea autocorrect="off" spellcheck="false" autocapitalize="none" class="form-control textarea-code" rows="40" name="html_tmpl"><?=$q_data['html_tmpl'];?></textarea>
          </div>
          <button class="btn btn-sm btn-success" data-action="edit_selected" data-item="self" data-id="quarantine" data-api-url='edit/quarantine' data-api-attr='{"action":"settings"}' href="#"><i class="bi bi-check-lg"></i> <?=$lang['admin']['save'];?></button>
        </form>
      </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-config-quota">
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['quota_notifications'];?></div>
      <div class="panel-body">
      <p><?=$lang['admin']['quota_notifications_info'];?></p>
       <?php $qw_data = quota_notification('get');?>
      <form class="form" role="form" data-id="quota_notification" method="post">
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label for="quota_notification_sender"><?=$lang['admin']['quarantine_notification_sender'];?>:</label>
              <input type="email" class="form-control" id="quota_notification_sender" name="sender" value="<?=htmlspecialchars($qw_data['sender']);?>" placeholder="quota-warning@localhost">
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="quota_notification_subject"><?=$lang['admin']['quarantine_notification_subject'];?>:</label>
              <input type="text" class="form-control" id="quota_notification_subject" name="subject" value="<?=htmlspecialchars($qw_data['subject']);?>" placeholder="Quota warning">
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <legend data-target="#quota_template" style="cursor:pointer" unselectable="on" data-toggle="collapse">
              <i style="font-size:10pt;" class="bi bi-plus-square"></i> <?=$lang['admin']['quarantine_notification_html'];?>
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
                data-api-attr='{}'><i class="bi bi-check-lg"></i> <?=$lang['user']['save_changes'];?></a>
            </div>
          </div>
        </div>
      </form>
      </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-config-rsettings">
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['rspamd_settings_map'];?></div>
      <div class="panel-body">
      <legend data-target="#active_settings_map" style="cursor:pointer" unselectable="on" data-toggle="collapse">
        <i style="font-size:10pt;" class="bi bi-plus-square"></i> <?=$lang['admin']['active_rspamd_settings_map'];?>
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
                  <a href="#<?=$rsetting_details['id'];?>" class="list-group-item list-group-item-<?=($rsetting_details['active'] == '1') ? 'success' : ''; ?>" data-dont-remember="1" data-toggle="tab"><?=$rsetting_details['desc'];?> (ID #<?=$rsetting['id'];?>)</a>
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
                      <label for="rsettings_desc"><?=$lang['admin']['rsetting_desc'];?>:</label>
                      <input type="text" class="form-control" id="rsettings_desc" name="desc" value="<?=htmlspecialchars($rsetting_details['desc']);?>">
                    </div>
                    <div class="form-group">
                      <label for="rsettings_content"><?=$lang['admin']['rsetting_content'];?>:</label>
                      <textarea class="form-control" id="rsettings_content" name="content" rows="10"><?=htmlspecialchars($rsetting_details['content']);?></textarea>
                    </div>
                    <div class="form-group">
                      <label>
                        <input type="checkbox" name="active" value="1" <?=($rsetting_details['active'] == 1) ? 'checked' : null;?>> <?=$lang['admin']['active'];?>
                      </label>
                    </div>
                    <button class="btn btn-sm btn-success" data-action="edit_selected" data-item="<?=$rsetting_details['id'];?>" data-id="rsettings" data-api-url='edit/rsetting' data-api-attr='{}' href="#"><i class="bi bi-check-lg"></i> <?=$lang['admin']['save'];?></button>
                    <button class="btn btn-sm btn-danger" data-action="delete_selected" data-item="<?=$rsetting_details['id'];?>" data-id="rsettings" data-api-url="delete/rsetting" data-api-attr='{}' href="#"><?=$lang['admin']['remove'];?></button>
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
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-config-customize">
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['customize'];?></div>
      <div class="panel-body">
        <legend><i class="bi bi-file-image"></i> <?=$lang['admin']['change_logo'];?></legend>
        <p class="help-block"><?=$lang['admin']['logo_info'];?></p>
        <form class="form-inline" role="form" method="post" enctype="multipart/form-data">
          <p>
            <input type="file" name="main_logo" accept="image/gif, image/jpeg, image/pjpeg, image/x-png, image/png, image/svg+xml"><br>
            <button name="submit_main_logo" type="submit" class="btn btn-xs btn-default"><i class="bi bi-upload"></i> <?=$lang['admin']['upload'];?></button>
          </p>
        </form>
        <?php
        if ($main_logo = customize('get', 'main_logo')) {
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
        <?php } ?>
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
              foreach ($row as $key => $val) {
            ?>
            <tr>
              <td><input class="input-sm form-control" data-id="app_links" type="text" name="app" required value="<?=$key;?>"></td>
              <td><input class="input-sm form-control" data-id="app_links" type="text" name="href" required value="<?=$val;?>"></td>
              <td><a href="#" role="button" class="btn btn-xs btn-default" type="button"><?=$lang['admin']['remove_row'];?></a></td>
            </tr>
            <?php
              }
            }
            foreach ($MAILCOW_APPS as $app) {
            ?>
            <tr>
              <td><input class="input-sm form-control" value="<?=htmlspecialchars($app['name']);?>" disabled></td>
              <td><input class="input-sm form-control" value="<?=htmlspecialchars($app['link']);?>" disabled></td>
              <td>&nbsp;</td>
            </tr>
            <?php } ?>
          </table>
          <p><div class="btn-group">
            <button class="btn btn-sm btn-success" data-action="edit_selected" data-item="admin" data-id="app_links" data-reload="no" data-api-url='edit/app_links' data-api-attr='{}' href="#"><i class="bi bi-check-lg"></i> <?=$lang['admin']['save'];?></button>
            <button class="btn btn-sm btn-default" type="button" id="add_app_link_row"><?=$lang['admin']['add_row'];?></button>
          </div></p>
        </form>
        <legend data-target="#ui_texts" style="padding-top:20px" unselectable="on"><?=$lang['admin']['ui_texts'];?></legend>
        <div id="ui_texts">
        <?php $ui_texts = customize('get', 'ui_texts'); ?>
          <form class="form" data-id="uitexts" role="form" method="post">
            <div class="form-group">
              <label for="uitests_title_name"><?=$lang['admin']['title_name'];?>:</label>
              <input type="text" class="form-control" id="uitests_title_name" name="title_name" placeholder="mailcow UI" value="<?=$ui_texts['title_name'];?>">
            </div>
            <div class="form-group">
              <label for="uitests_main_name"><?=$lang['admin']['main_name'];?>:</label>
              <input type="text" class="form-control" id="uitests_main_name" name="main_name" placeholder="mailcow UI" value="<?=$ui_texts['main_name'];?>">
            </div>
            <div class="form-group">
              <label for="uitests_apps_name"><?=$lang['admin']['apps_name'];?>:</label>
              <input type="text" class="form-control" id="uitests_apps_name" name="apps_name" placeholder="mailcow Apps" value="<?=$ui_texts['apps_name'];?>">
            </div>
            <div class="form-group">
              <label for="help_text"><?=$lang['admin']['help_text'];?>:</label>
              <textarea class="form-control" id="help_text" name="help_text" rows="7"><?=$ui_texts['help_text'];?></textarea>
            </div>
            <hr>
            <div class="form-group">
              <p class="help-block"><?=$lang['admin']['ui_header_announcement_help'];?></p>
              <label for="ui_announcement_type"><?=$lang['admin']['ui_header_announcement'];?>:</label>
              <p><select multiple data-width="100%" id="ui_announcement_type" name="ui_announcement_type" class="selectpicker show-tick" data-max-options="1" title="<?=$lang['admin']['ui_header_announcement_select'];?>">
                <option <?=($ui_texts['ui_announcement_type'] == 'info') ? 'selected' : null;?> value="info"><?=$lang['admin']['ui_header_announcement_type_info'];?></option>
                <option <?=($ui_texts['ui_announcement_type'] == 'warning') ? 'selected' : null;?> value="warning"><?=$lang['admin']['ui_header_announcement_type_warning'];?></option>
                <option <?=($ui_texts['ui_announcement_type'] == 'danger') ? 'selected' : null;?> value="danger"><?=$lang['admin']['ui_header_announcement_type_danger'];?></option>
              </select></p>
              <p><textarea class="form-control" id="ui_announcement_text" name="ui_announcement_text" rows="7"><?=$ui_texts['ui_announcement_text'];?></textarea></p>
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="ui_announcement_active" class="form-check-input" <?=($ui_texts['ui_announcement_active'] == 1) ? 'checked' : null;?>> <?=$lang['admin']['ui_header_announcement_active'];?>
                </label>
              </div>
            </div>
            <hr>
            <div class="form-group">
              <label for="ui_footer"><?=$lang['admin']['ui_footer'];?>:</label>
              <textarea class="form-control" id="ui_footer" name="ui_footer" rows="7"><?=$ui_texts['ui_footer'];?></textarea>
            </div>
            <button class="btn btn-sm btn-success" data-action="edit_selected" data-item="ui" data-id="uitexts" data-api-url='edit/ui_texts' data-api-attr='{}' href="#"><i class="bi bi-check-lg"></i> <?=$lang['admin']['save'];?></button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-config-password-policy">
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['password_policy'];?></div>
      <div class="panel-body">
        <?php $password_complexity = password_complexity('get'); ?>
        <form class="form-horizontal" data-id="passwordpolicy" role="form" method="post">
          <?php
          foreach ($password_complexity as $name => $value) {
          if ($name == 'length') {
          ?>
          <div class="form-group">
            <label class="control-label col-sm-3" for="<?=$name;?>"><?=$lang['admin']['password_length'];?>:</label>
            <div class="col-sm-2">
              <input type="number" class="form-control" min="3" max="64" name="<?=$name;?>" id="<?=$name;?>" value="<?=$value;?>" required>
            </div>
          </div>
          <?php
          } else {
          ?>
          <input type="hidden" name="<?=$name;?>" value="0">
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
              <label>
                <input type="checkbox" name="<?=$name;?>" id="<?=$name;?>" value="1" <?=($value == 1) ? 'checked' : null;?>> <?=$lang['admin']["password_policy_$name"];?>
              </label>
            </div>
          </div>
          <?php
          }
          }
          ?>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
              <div class="btn-group">
                <button class="btn btn-sm btn-success" data-item="passwordpolicy" data-action="edit_selected" data-id="passwordpolicy" data-api-url='edit/passwordpolicy' data-api-attr='{}' href="#"><i class="bi bi-check-lg"></i> <?=$lang['admin']['save'];?></button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-sys-mails">
    <div class="panel panel-default">
      <div class="panel-heading"><?=$lang['admin']['sys_mails'];?></div>
      <div class="panel-body">
        <form class="form-horizontal" autocapitalize="none" data-id="admin" autocorrect="off" role="form" method="post">
          <div class="form-group">
            <label class="control-label col-sm-2" for="admin_mass_from"><?=$lang['admin']['from'];?>:</label>
            <div class="col-sm-10">
              <input type="email" class="form-control" id="admin_mass_from" name="mass_from" value="noreply@<?=getenv('MAILCOW_HOSTNAME');;?>" required>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="admin_mass_subject"><?=$lang['admin']['subject'];?>:</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="admin_mass_subject" name="mass_subject" required>
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
                foreach (array_filter($mailboxes) as $mailbox) {
                ?>
                <option><?=htmlspecialchars($mailbox);?></option>
                <?php
                }
              }
              ?>
              </select>
            </div>
            <div class="col-sm-5">
              <label class="control-label" for="mass_include"><?=$lang['admin']['includes'];?>:</label>
              <select id="mass_include" name="mass_include[]" data-live-search="true" data-width="100%"  size="30" multiple>
              <?php
              if (!empty($mailboxes)) {
                foreach (array_filter($mailboxes) as $mailbox) {
                ?>
                <option><?=htmlspecialchars($mailbox);?></option>
                <?php
                }
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
            <label class="control-label col-sm-2" for="mass_html"><?=$lang['admin']['html'];?> (<?=$lang['admin']['optional'];?>):</label>
            <div class="col-sm-10">
              <textarea class="form-control" rows="10" name="mass_html" id="mass_html"></textarea>
              <p class="small"><i class="bi bi-arrow-return-right"></i> <a target="_blank" href="https://templates.mailchimp.com/resources/html-to-text/"><?=$lang['admin']['convert_html_to_text'];?></a></p>
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
              <button class="btn btn-default" type="submit" id="mass_send" name="mass_send" disabled><i class="bi bi-envelope-fill"></i> <?=$lang['admin']['send'];?></button>
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
          <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="mailqitems" href="#"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></a>
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
            href="#"><i class="bi bi-check-all"></i> <?=$lang['admin']['flush_queue'];?></a>
          <a class="btn btn-sm btn-danger"
            id="super_delete"
            data-action="edit_selected"
            data-item="mailqitems-all"
            data-api-url='edit/mailq'
            data-api-attr='{"action":"super_delete"}'
            data-toggle="tooltip" title="postsuper -d ALL"
            href="#"><i class="bi bi-trash"></i> <?=$lang['admin']['delete_queue'];?></a>
        </div>
      </div>
      </div>
    </div>
  </div>

  <div role="tabpanel" class="tab-pane" id="tab-globalfilter-regex">
    <div class="panel panel-default">
      <div class="panel-heading">
        <?=$lang['admin']['rspamd_global_filters'];?>
      </div>
      <div class="panel-body">
        <p><?=$lang['admin']['rspamd_global_filters_info'];?></p>
        <div id="confirm_show_rspamd_global_filters" class="<?=($_SESSION['show_rspamd_global_filters'] === true) ? 'hidden' : '';?>">
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <label>
                <input type="checkbox" id="show_rspamd_global_filters"> <?=$lang['admin']['rspamd_global_filters_agree'];?>
              </label>
            </div>
          </div>
        </div>
        <div id="rspamd_global_filters" class="<?=($_SESSION['show_rspamd_global_filters'] !== true) ? 'hidden' : '';?>">
        <hr>
        <span class="anchor" id="regexmaps"></span>
        <h4><?=$lang['admin']['regex_maps'];?></h4>
        <p><?=$lang['admin']['rspamd_global_filters_regex'];?></p>
        <ul>
        <?php
        foreach ($RSPAMD_MAPS['regex'] as $rspamd_regex_desc => $rspamd_regex_map):
        ?>
        <li><a href="#<?=$rspamd_regex_map;?>"><?=$rspamd_regex_desc;?></a> (<small><?=$rspamd_regex_map;?></small>)</li>
        <?php
        endforeach;
        ?>
        </ul>
        <?php
        foreach ($RSPAMD_MAPS['regex'] as $rspamd_regex_desc => $rspamd_regex_map):
        ?>
        <hr>
        <span class="anchor" id="<?=$rspamd_regex_map;?>"></span>
        <form class="form-horizontal" data-cached-form="false" data-id="<?=$rspamd_regex_map;?>" role="form" method="post">
          <div class="form-group">
            <label class="control-label col-sm-3" for="<?=$rspamd_regex_map;?>"><?=$rspamd_regex_desc;?><br><small><?=$rspamd_regex_map;?></small></label>
            <div class="col-sm-9">
              <textarea id="<?=$rspamd_regex_map;?>" spellcheck="false" autocorrect="off" autocapitalize="none" class="form-control textarea-code" rows="10" name="rspamd_map_data" required><?=file_get_contents('/rspamd_custom_maps/' . $rspamd_regex_map);?></textarea>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
              <button class="btn btn-xs btn-default validate_rspamd_regex" data-regex-map="<?=$rspamd_regex_map;?>" href="#"><?=$lang['add']['validate'];?></button>
              <button class="btn btn-xs btn-success submit_rspamd_regex" data-action="edit_selected" data-id="<?=$rspamd_regex_map;?>" data-item="<?=htmlspecialchars($rspamd_regex_map);?>" data-api-url='edit/rspamd-map' data-api-attr='{}' href="#" disabled><?=$lang['edit']['save'];?></button>
            </div>
          </div>
        </form>
        <?php
        endforeach;
        ?>
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
$js_minifier->add('/web/js/presets/rspamd.js');
$js_minifier->add('/web/js/site/pwgen.js');
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/footer.inc.php';
} else {
	header('Location: /');
	exit();
}
?>
