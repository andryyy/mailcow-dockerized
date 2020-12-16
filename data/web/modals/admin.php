<?php
if (!isset($_SESSION['mailcow_cc_role'])) {
	header('Location: /');
	exit();
}
?>
<!-- add settings rule modal -->
<div class="modal fade" id="addRsettingModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
        <h3 class="modal-title"><?=$lang['admin']['add_settings_rule'];?></h3>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" data-cached-form="true" data-id="rsetting" role="form" method="post">
          <div class="form-group">
            <label class="control-label col-sm-2" for="desc"><?=$lang['admin']['rsetting_desc'];?>:</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="adminRspamdSettingsDesc" name="desc" required>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="content"><?=$lang['admin']['rsetting_content'];?>:</label>
            <div class="col-sm-10">
              <textarea class="form-control" id="adminRspamdSettingsContent" name="content" rows="10"><?=$rsetting_details['content'];?></textarea>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <div class="checkbox">
              <label><input type="checkbox" value="1" name="active" checked> <?=$lang['admin']['active'];?></label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <button class="btn btn-default" data-action="add_item" data-id="rsetting" data-api-url='add/rsetting' data-api-attr='{}' href="#"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?=$lang['admin']['add'];?></button>
            </div>
          </div>
        </form>
        <hr>
        <p><?=$lang['admin']['rspamd-com_settings'];?></p>
        <ul id="rspamd_presets"></ul>
      </div>
    </div>
  </div>
</div><!-- add settings rule modal -->
<!-- add domain admin modal -->
<div class="modal fade" id="addDomainAdminModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
        <h3 class="modal-title"><?=$lang['admin']['add_domain_admin'];?></h3>
      </div>
      <div class="modal-body">
          <form class="form-horizontal" data-cached-form="true" data-id="add_domain_admin" role="form" method="post" autocomplete="off">
            <div class="form-group">
              <label class="control-label col-sm-2" for="username"><?=$lang['admin']['username'];?>:</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="username" required>
                &rdsh; <kbd>a-z A-Z - _ .</kbd>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="name"><?=$lang['admin']['admin_domains'];?>:</label>
              <div class="col-sm-10">
                <select title="<?=$lang['admin']['search_domain_da'];?>" class="full-width-select" name="domains" size="5" multiple>
                <?php
                foreach (mailbox('get', 'domains') as $domain) {
                  echo "<option>".htmlspecialchars($domain)."</option>";
                }
                ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="password"><?=$lang['admin']['password'];?> (<a href="#" class="generate_password"><?=$lang['admin']['generate'];?></a>)</label>
              <div class="col-sm-10">
              <input type="password" class="form-control" data-pwgen-field="true" data-hibp="true" name="password" placeholder="" autocomplete="new-password" required>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="password2"><?=$lang['admin']['password_repeat'];?>:</label>
              <div class="col-sm-10">
              <input type="password" class="form-control" data-pwgen-field="true" name="password2" placeholder="" autocomplete="new-password" required>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="active" checked> <?=$lang['admin']['active'];?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-default" data-action="add_item" data-id="add_domain_admin" data-api-url='add/domain-admin' data-api-attr='{}' href="#"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?=$lang['admin']['add'];?></button>
              </div>
            </div>
          </form>
      </div>
    </div>
  </div>
</div><!-- add domain admin modal -->
<!-- change fido2 fn -->
<div class="modal fade" id="fido2ChangeFn" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
        <h3 class="modal-title"><?=$lang['fido2']['set_fn'];?></h3>
        <p class="help-block" style="word-break:break-all" id="fido2_subject_desc" data-fido2-subject=""></p>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" data-cached-form="false" data-id="fido2ChangeFn" role="form" method="post" autocomplete="off">
          <input type="hidden" class="form-control" name="fido2_cid" id="fido2_cid">
          <div class="form-group">
            <label class="control-label col-sm-4" for="fido2_fn"><?=$lang['fido2']['fn'];?>:</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="fido2_fn">
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-4 col-sm-8">
              <button class="btn btn-default" data-action="edit_selected" data-id="fido2ChangeFn" data-item="null" data-api-url='edit/fido2-fn' data-api-attr='{}' href="#"><?=$lang['admin']['save'];?></button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div><!-- add domain admin modal -->
<!-- add oauth2 client modal -->
<div class="modal fade" id="addOAuth2ClientModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
        <h3 class="modal-title">OAuth2</h3>
      </div>
      <div class="modal-body">
          <form class="form-horizontal" data-cached-form="true" data-id="add_oauth2_client" role="form" method="post">
            <div class="form-group">
              <label class="control-label col-sm-2" for="redirect_uri"><?=$lang['admin']['oauth2_redirect_uri'];?>:</label>
              <div class="col-sm-10">
              <input type="text" class="form-control" name="redirect_uri" required>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-default" data-action="add_item" data-id="add_oauth2_client" data-api-url='add/oauth2-client' data-api-attr='{}' href="#"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?=$lang['admin']['add'];?></button>
              </div>
            </div>
          </form>
      </div>
    </div>
  </div>
</div><!-- add domain admin modal -->
<!-- add admin modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
        <h3 class="modal-title"><?=$lang['admin']['add_admin'];?></h3>
      </div>
      <div class="modal-body">
          <form class="form-horizontal" data-cached-form="true" data-id="add_admin" role="form" method="post" autocomplete="off">
            <div class="form-group">
              <label class="control-label col-sm-2" for="username"><?=$lang['admin']['username'];?>:</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="username" required>
                &rdsh; <kbd>a-z A-Z - _ .</kbd>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="password"><?=$lang['admin']['password'];?> (<a href="#" class="generate_password"><?=$lang['admin']['generate'];?></a>):</label>
              <div class="col-sm-10">
              <input type="password" class="form-control" data-pwgen-field="true" data-hibp="true" name="password" placeholder="" autocomplete="new-password" required>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-sm-2" for="password2"><?=$lang['admin']['password_repeat'];?>:</label>
              <div class="col-sm-10">
              <input type="password" class="form-control" data-pwgen-field="true" name="password2" placeholder="" autocomplete="new-password" required>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                <label><input type="checkbox" value="1" name="active" checked> <?=$lang['admin']['active'];?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-default" data-action="add_item" data-id="add_admin" data-api-url='add/admin' data-api-attr='{}' href="#"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?=$lang['admin']['add'];?></button>
              </div>
            </div>
          </form>
      </div>
    </div>
  </div>
</div><!-- add admin modal -->
<!-- test transport modal -->
<div class="modal fade" id="testTransportModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
        <h3 class="modal-title"><span class="glyphicon glyphicon-stats"></span> Transport</h3>
      </div>
      <div class="modal-body">
          <form class="form-horizontal" data-cached-form="true" id="test_transport_form" role="form" method="post">
            <input type="hidden" class="form-control" name="transport_id" id="transport_id">
            <input type="hidden" class="form-control" name="transport_type" id="transport_type">
            <div class="form-group">
              <label class="control-label col-sm-2" for="mail_from"><?=$lang['admin']['relay_from'];?></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="mail_from" placeholder="relay@example.org">
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-default" id="test_transport" href="#"><?=$lang['admin']['relay_run'];?></button>
              </div>
            </div>
          </form>
          <hr>
          <div id="test_transport_result" style="font-size:10pt">-</div>
      </div>
    </div>
  </div>
</div><!-- test transport modal -->
<!-- show queue item modal -->
<div class="modal fade" id="showQueuedMsg" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
        <h3 class="modal-title"><span class="glyphicon glyphicon-hourglass" style="font-size:18px"></span> ID <span id="queue_id"></span></h3>
      </div>
      <div class="modal-body">
        <textarea class="form-control" id="queue_msg_content" name="content" rows="40"></textarea>
      </div>
    </div>
  </div>
</div><!-- show queue item modal -->
<!-- priv key modal -->
<div class="modal fade" id="showDKIMprivKey" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
        <h3 class="modal-title"><span class="glyphicon glyphicon-lock"></span> Private key</h3>
      </div>
      <div class="modal-body">
      <pre id="priv_key_pre"></pre>
      </div>
    </div>
  </div>
</div><!-- priv key modal -->
