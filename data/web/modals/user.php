<?php
if (!isset($_SESSION['mailcow_cc_role'])) {
	header('Location: /');
	exit();
}
?>
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
<!-- add sync job modal -->
<div class="modal fade" id="addSyncJobModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
        <h3 class="modal-title"><?=$lang['add']['syncjob'];?></h3>
      </div>
      <div class="modal-body">
        <p><?=$lang['add']['syncjob_hint'];?></p>
				<form class="form-horizontal" data-cached-form="true" role="form" data-id="add_syncjob">
					<div class="form-group">
						<label class="control-label col-sm-2" for="host1"><?=$lang['add']['hostname'];?></label>
						<div class="col-sm-10">
						<input type="text" class="form-control" name="host1" required>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2" for="port1"><?=$lang['add']['port'];?></label>
						<div class="col-sm-10">
						<input type="number" class="form-control" name="port1" min="1" max="65535" value="143" required>
            <small class="help-block">1-65535</small>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2" for="user1"><?=$lang['add']['username'];?></label>
						<div class="col-sm-10">
						<input type="text" class="form-control" name="user1" required>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2" for="password1"><?=$lang['add']['password'];?></label>
						<div class="col-sm-10">
						<input type="password" class="form-control" name="password1" data-hibp="true" required>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2" for="enc1"><?=$lang['add']['enc_method'];?></label>
						<div class="col-sm-10">
							<select name="enc1" title="<?=$lang['add']['select'];?>" required>
                <option value="SSL" selected>SSL</option>
                <option value="TLS">STARTTLS</option>
                <option value="PLAIN">PLAIN</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2" for="mins_interval"><?=$lang['add']['mins_interval'];?></label>
						<div class="col-sm-10">
              <input type="number" class="form-control" name="mins_interval" min="1" max="43800" value="20" required>
              <small class="help-block">1-43800</small>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2" for="subfolder2"><?=$lang['edit']['subfolder2'];?></label>
						<div class="col-sm-10">
						<input type="text" class="form-control" name="subfolder2" value="">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2" for="maxage"><?=$lang['edit']['maxage'];?></label>
						<div class="col-sm-10">
						<input type="number" class="form-control" name="maxage" min="0" max="32000" value="0">
            <small class="help-block">0-32000</small>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2" for="maxbytespersecond"><?=$lang['edit']['maxbytespersecond'];?></label>
						<div class="col-sm-10">
						<input type="number" class="form-control" name="maxbytespersecond" min="0" max="125000000" value="0">
            <small class="help-block">0-125000000</small>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2" for="timeout1"><?=$lang['edit']['timeout1'];?></label>
						<div class="col-sm-10">
						<input type="number" class="form-control" name="timeout1" min="1" max="32000" value="600">
            <small class="help-block">1-32000</small>
						</div>
					</div>
          <div class="form-group">
						<label class="control-label col-sm-2" for="timeout2"><?=$lang['edit']['timeout2'];?></label>
						<div class="col-sm-10">
						<input type="number" class="form-control" name="timeout2" min="1" max="32000" value="600">
            <small class="help-block">1-32000</small>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2" for="exclude"><?=$lang['add']['exclude'];?></label>
						<div class="col-sm-10">
						<input type="text" class="form-control" name="exclude" value="(?i)spam|(?i)junk">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2" for="custom_params"><?=$lang['add']['custom_params'];?></label>
						<div class="col-sm-10">
						<input type="text" class="form-control" name="custom_params" placeholder="--delete2folders --otheroption">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<div class="checkbox">
							<label><input type="checkbox" value="1" name="delete2duplicates" checked> <?=$lang['add']['delete2duplicates'];?> (--delete2duplicates)</label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<div class="checkbox">
							<label><input type="checkbox" value="1" name="delete1"> <?=$lang['add']['delete1'];?> (--delete1)</label>
							</div>
						</div>
					</div>
          <div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<div class="checkbox">
							<label><input type="checkbox" value="1" name="delete2"> <?=$lang['add']['delete2'];?> (--delete2)</label>
							</div>
						</div>
					</div>
          <div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<div class="checkbox">
							<label><input type="checkbox" value="1" name="automap" checked> <?=$lang['add']['automap'];?> (--automap)</label>
							</div>
						</div>
					</div>
          <div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<div class="checkbox">
							<label><input type="checkbox" value="1" name="skipcrossduplicates"> <?=$lang['add']['skipcrossduplicates'];?> (--skipcrossduplicates)</label>
							</div>
						</div>
					</div>
          <div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<div class="checkbox">
							<label><input type="checkbox" value="1" name="subscribeall" checked> <?=$lang['add']['subscribeall'];?> (--subscribeall)</label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<div class="checkbox">
							<label><input type="checkbox" value="1" name="active" checked> <?=$lang['add']['active'];?></label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
              <button class="btn btn-default" data-action="add_item" data-id="add_syncjob" data-api-url='add/syncjob' data-api-attr='{}' href="#"><?=$lang['admin']['add'];?></button>
						</div>
					</div>
				</form>
      </div>
    </div>
  </div>
</div><!-- add sync job modal -->
<!-- app passwd modal -->
<div class="modal fade" id="addAppPasswdModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
        <h3 class="modal-title"><?=$lang['add']['app_password'];?></h3>
      </div>
      <div class="modal-body">
				<form class="form-horizontal" data-cached-form="true" role="form" data-pwgen-length="32" data-id="add_apppasswd">
					<div class="form-group">
						<label class="control-label col-sm-2" for="app_name"><?=$lang['add']['app_name'];?></label>
						<div class="col-sm-10">
						<input type="text" class="form-control" name="app_name" required>
						</div>
					</div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="app_passwd"><?=$lang['user']['password'];?> (<a href="#" class="generate_password"><?=$lang['user']['generate'];?></a>)</label>
            <div class="col-sm-10">
            <input type="password" data-pwgen-field="true" data-hibp="true" class="form-control" name="app_passwd" autocomplete="new-password" required>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="app_passwd2"><?=$lang['user']['password_repeat'];?></label>
            <div class="col-sm-10">
            <input type="password" data-pwgen-field="true" class="form-control" name="app_passwd2" autocomplete="new-password" required>
            <p class="help-block"><?=$lang['user']['new_password_description'];?></p>
            </div>
          </div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<div class="checkbox">
							<label><input type="checkbox" value="1" name="active" checked> <?=$lang['add']['active'];?></label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
              <button class="btn btn-default" data-action="add_item" data-id="add_apppasswd" data-api-url='add/app-passwd' data-api-attr='{}' href="#"><?=$lang['admin']['add'];?></button>
						</div>
					</div>
				</form>
      </div>
    </div>
  </div>
</div><!-- add app passwd modal -->
<!-- log modal -->
<div class="modal fade" id="syncjobLogModal" tabindex="-1" role="dialog" aria-labelledby="syncjobLogModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header"><h4 class="modal-title">Log</h4></div>
      <div class="modal-body">
        <textarea class="form-control textarea-code" rows="20" id="logText" spellcheck="false"></textarea>
      </div>
    </div>
  </div>
</div><!-- log modal -->
<!-- pw change modal -->
<div class="modal fade" id="pwChangeModal" tabindex="-1" role="dialog" aria-labelledby="pwChangeModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <form class="form-horizontal" data-cached-form="false" data-id="pwchange" role="form" method="post" autocomplete="off">
          <div class="form-group">
            <label class="control-label col-sm-3" for="user_new_pass"><?=$lang['user']['new_password'];?> (<a href="#" class="generate_password"><?=$lang['user']['generate'];?></a>)</label>
            <div class="col-sm-5">
            <input type="password" data-pwgen-field="true" data-hibp="true" class="form-control" name="user_new_pass" autocomplete="new-password" required>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-3" for="user_new_pass2"><?=$lang['user']['new_password_repeat'];?></label>
            <div class="col-sm-5">
            <input type="password" data-pwgen-field="true" class="form-control" name="user_new_pass2" autocomplete="new-password" required>
            <p class="help-block"><?=$lang['user']['new_password_description'];?></p>
            </div>
          </div>
          <hr>
          <div class="form-group">
            <label class="control-label col-sm-3" for="user_old_pass"><?=$lang['user']['password_now'];?></label>
            <div class="col-sm-5">
            <input type="password" class="form-control" name="user_old_pass" autocomplete="off" required>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
              <button class="btn btn-default" data-action="edit_selected" data-id="pwchange" data-item="null" data-api-url='edit/self' data-api-attr='{}' href="#"><?=$lang['user']['change_password'];?></button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div><!-- pw change modal -->
<!-- sieve filter modal -->
<div class="modal fade" id="userFilterModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
        <h3 class="modal-title"><?=$lang['user']['active_sieve'];?></h3>
      </div>
      <div class="modal-body">
      <pre id="user_sieve_filter"></pre>
      </div>
    </div>
  </div>
</div><!-- sieve filter modal -->
