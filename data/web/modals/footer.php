<?php
if (isset($_SESSION['mailcow_cc_role']) && ($_SESSION['mailcow_cc_role'] == "admin" || $_SESSION['mailcow_cc_role'] == "domainadmin")):
?>
<div class="modal fade" id="YubiOTPModal" tabindex="-1" role="dialog" aria-labelledby="YubiOTPModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><b><?=$lang['tfa']['yubi_otp'];?></b></div>
      <div class="modal-body">
      <form role="form" method="post">
        <div class="form-group">
          <input type="text" class="form-control" name="key_id" placeholder="<?=$lang['tfa']['key_id'];?>" autocomplete="off" required>
        </div>
        <hr>
        <p class="help-block"><?=sprintf($lang['tfa']['api_register'], $UI_TEXTS['main_name']);?></p>
        <div class="form-group">
          <input type="text" class="form-control" name="yubico_id" placeholder="Yubico API ID" autocomplete="off" required>
        </div>
        <div class="form-group">
          <input type="text" class="form-control" name="yubico_key" placeholder="Yubico API Key" autocomplete="off" required>
        </div>
        <hr>
        <div class="form-group">
          <input type="password" class="form-control" name="confirm_password" placeholder="<?=$lang['user']['password_now'];?>" autocomplete="off" required>
        </div>
        <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon" id="yubi-addon"><img alt="Yubicon Icon" src="/img/yubi.ico"></span>
            <input type="text" name="otp_token" class="form-control" placeholder="Touch Yubikey" aria-describedby="yubi-addon">
            <input type="hidden" name="tfa_method" value="yubi_otp">
          </div>
        </div>
        <button class="btn btn-sm btn-default" type="submit" name="set_tfa"><?=$lang['user']['save_changes'];?></button>
      </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="U2FModal" tabindex="-1" role="dialog" aria-labelledby="U2FModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><b><?=$lang['tfa']['u2f'];?></b></div>
      <div class="modal-body">
        <form role="form" method="post" id="u2f_reg_form">
          <div class="form-group">
            <input type="text" class="form-control" name="key_id" placeholder="<?=$lang['tfa']['key_id'];?>" autocomplete="off" required>
          </div>
          <div class="form-group">
            <input type="password" class="form-control" name="confirm_password" placeholder="<?=$lang['user']['password_now'];?>" autocomplete="off" required>
          </div>
          <hr>
          <center>
          <div style="cursor:pointer" id="start_u2f_register">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24">
            <path d="M17.81 4.47c-.08 0-.16-.02-.23-.06C15.66 3.42 14 3 12.01 3c-1.98 0-3.86.47-5.57 1.41-.24.13-.54.04-.68-.2-.13-.24-.04-.55.2-.68C7.82 2.52 9.86 2 12.01 2c2.13 0 3.99.47 6.03 1.52.25.13.34.43.21.67-.09.18-.26.28-.44.28zM3.5 9.72c-.1 0-.2-.03-.29-.09-.23-.16-.28-.47-.12-.7.99-1.4 2.25-2.5 3.75-3.27C9.98 4.04 14 4.03 17.15 5.65c1.5.77 2.76 1.86 3.75 3.25.16.22.11.54-.12.7-.23.16-.54.11-.7-.12-.9-1.26-2.04-2.25-3.39-2.94-2.87-1.47-6.54-1.47-9.4.01-1.36.7-2.5 1.7-3.4 2.96-.08.14-.23.21-.39.21zm6.25 12.07c-.13 0-.26-.05-.35-.15-.87-.87-1.34-1.43-2.01-2.64-.69-1.23-1.05-2.73-1.05-4.34 0-2.97 2.54-5.39 5.66-5.39s5.66 2.42 5.66 5.39c0 .28-.22.5-.5.5s-.5-.22-.5-.5c0-2.42-2.09-4.39-4.66-4.39-2.57 0-4.66 1.97-4.66 4.39 0 1.44.32 2.77.93 3.85.64 1.15 1.08 1.64 1.85 2.42.19.2.19.51 0 .71-.11.1-.24.15-.37.15zm7.17-1.85c-1.19 0-2.24-.3-3.1-.89-1.49-1.01-2.38-2.65-2.38-4.39 0-.28.22-.5.5-.5s.5.22.5.5c0 1.41.72 2.74 1.94 3.56.71.48 1.54.71 2.54.71.24 0 .64-.03 1.04-.1.27-.05.53.13.58.41.05.27-.13.53-.41.58-.57.11-1.07.12-1.21.12zM14.91 22c-.04 0-.09-.01-.13-.02-1.59-.44-2.63-1.03-3.72-2.1-1.4-1.39-2.17-3.24-2.17-5.22 0-1.62 1.38-2.94 3.08-2.94 1.7 0 3.08 1.32 3.08 2.94 0 1.07.93 1.94 2.08 1.94s2.08-.87 2.08-1.94c0-3.77-3.25-6.83-7.25-6.83-2.84 0-5.44 1.58-6.61 4.03-.39.81-.59 1.76-.59 2.8 0 .78.07 2.01.67 3.61.1.26-.03.55-.29.64-.26.1-.55-.04-.64-.29-.49-1.31-.73-2.61-.73-3.96 0-1.2.23-2.29.68-3.24 1.33-2.79 4.28-4.6 7.51-4.6 4.55 0 8.25 3.51 8.25 7.83 0 1.62-1.38 2.94-3.08 2.94s-3.08-1.32-3.08-2.94c0-1.07-.93-1.94-2.08-1.94s-2.08.87-2.08 1.94c0 1.71.66 3.31 1.87 4.51.95.94 1.86 1.46 3.27 1.85.27.07.42.35.35.61-.05.23-.26.38-.47.38z"></path>
            </svg>
            <p><?=$lang['tfa']['start_u2f_validation'];?></p>
            <hr>
          </div>
          </center>
          <p id="u2f_status_reg"></p>
          <div class="alert alert-danger" style="display:none" id="u2f_return_code"></div>
          <input type="hidden" name="token" id="u2f_register_data"/>
          <input type="hidden" name="tfa_method" value="u2f">
          <input type="hidden" name="set_tfa"/><br/>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="TOTPModal" tabindex="-1" role="dialog" aria-labelledby="TOTPModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><b><?=$lang['tfa']['totp'];?></b></div>
      <div class="modal-body">
        <form role="form" method="post">
          <div class="form-group">
            <input type="text" class="form-control" name="key_id" placeholder="<?=$lang['tfa']['key_id_totp'];?>" autocomplete="off" required>
          </div>
          <div class="form-group">
            <input type="password" class="form-control" name="confirm_password" placeholder="<?=$lang['user']['password_now'];?>" autocomplete="off" required>
          </div>
          <hr>
          <?php
          $totp_secret = $tfa->createSecret();
          ?>
          <input type="hidden" value="<?=$totp_secret;?>" name="totp_secret">
          <input type="hidden" name="tfa_method" value="totp">
          <ol>
            <li>
              <p><?=$lang['tfa']['scan_qr_code'];?></p>
              <img id="tfa-qr-img" data-totp-secret="<?=$totp_secret;?>" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=">
              <p class="help-block"><?=$lang['tfa']['enter_qr_code'];?>:<br />
              <code><?=$totp_secret;?></code>
              </p>
            </li>
            <li>
              <p><?=$lang['tfa']['confirm_totp_token'];?>:</p>
              <p><input type="number" style="width:33%" class="form-control" name="totp_confirm_token" autocomplete="off" required></p>
              <p><button class="btn btn-default" type="submit" name="set_tfa"><?=$lang['tfa']['confirm'];?></button></p>
            </li>
          </ol>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="DisableTFAModal" tabindex="-1" role="dialog" aria-labelledby="DisableTFAModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><b><?=$lang['tfa']['delete_tfa'];?></b></div>
      <div class="modal-body">
        <form role="form" method="post">
          <div class="input-group">
            <input type="password" class="form-control" name="confirm_password" placeholder="<?=$lang['user']['password_now'];?>" autocomplete="off" required>
            <span class="input-group-btn">
              <input type="hidden" name="tfa_method" value="none">
              <button class="btn btn-danger" type="submit" name="set_tfa"><?=$lang['tfa']['delete_tfa'];?></button>
            </span>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
endif;
if (isset($_SESSION['pending_tfa_method'])):
  $tfa_method = $_SESSION['pending_tfa_method'];
?>
<div class="modal fade" id="ConfirmTFAModal" tabindex="-1" role="dialog" aria-labelledby="ConfirmTFAModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><b><?=$lang['tfa'][$tfa_method];?></b></div>
      <div class="modal-body">
      <?php
      switch ($tfa_method) {
        case "yubi_otp":
      ?>
        <form role="form" method="post">
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon" id="yubi-addon"><img alt="Yubicon Icon" src="/img/yubi.ico"></span>
              <input type="text" name="token" class="form-control" autocomplete="off" placeholder="Touch Yubikey" aria-describedby="yubi-addon">
              <input type="hidden" name="tfa_method" value="yubi_otp">
            </div>
          </div>
          <button class="btn btn-sm btn-default" type="submit" name="verify_tfa_login"><?=$lang['login']['login'];?></button>
        </form>
      <?php
        break;
        case "u2f":
      ?>
        <form role="form" method="post" id="u2f_auth_form">
          <center>
          <div id="start_u2f_confirmation">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24">
            <path d="M17.81 4.47c-.08 0-.16-.02-.23-.06C15.66 3.42 14 3 12.01 3c-1.98 0-3.86.47-5.57 1.41-.24.13-.54.04-.68-.2-.13-.24-.04-.55.2-.68C7.82 2.52 9.86 2 12.01 2c2.13 0 3.99.47 6.03 1.52.25.13.34.43.21.67-.09.18-.26.28-.44.28zM3.5 9.72c-.1 0-.2-.03-.29-.09-.23-.16-.28-.47-.12-.7.99-1.4 2.25-2.5 3.75-3.27C9.98 4.04 14 4.03 17.15 5.65c1.5.77 2.76 1.86 3.75 3.25.16.22.11.54-.12.7-.23.16-.54.11-.7-.12-.9-1.26-2.04-2.25-3.39-2.94-2.87-1.47-6.54-1.47-9.4.01-1.36.7-2.5 1.7-3.4 2.96-.08.14-.23.21-.39.21zm6.25 12.07c-.13 0-.26-.05-.35-.15-.87-.87-1.34-1.43-2.01-2.64-.69-1.23-1.05-2.73-1.05-4.34 0-2.97 2.54-5.39 5.66-5.39s5.66 2.42 5.66 5.39c0 .28-.22.5-.5.5s-.5-.22-.5-.5c0-2.42-2.09-4.39-4.66-4.39-2.57 0-4.66 1.97-4.66 4.39 0 1.44.32 2.77.93 3.85.64 1.15 1.08 1.64 1.85 2.42.19.2.19.51 0 .71-.11.1-.24.15-.37.15zm7.17-1.85c-1.19 0-2.24-.3-3.1-.89-1.49-1.01-2.38-2.65-2.38-4.39 0-.28.22-.5.5-.5s.5.22.5.5c0 1.41.72 2.74 1.94 3.56.71.48 1.54.71 2.54.71.24 0 .64-.03 1.04-.1.27-.05.53.13.58.41.05.27-.13.53-.41.58-.57.11-1.07.12-1.21.12zM14.91 22c-.04 0-.09-.01-.13-.02-1.59-.44-2.63-1.03-3.72-2.1-1.4-1.39-2.17-3.24-2.17-5.22 0-1.62 1.38-2.94 3.08-2.94 1.7 0 3.08 1.32 3.08 2.94 0 1.07.93 1.94 2.08 1.94s2.08-.87 2.08-1.94c0-3.77-3.25-6.83-7.25-6.83-2.84 0-5.44 1.58-6.61 4.03-.39.81-.59 1.76-.59 2.8 0 .78.07 2.01.67 3.61.1.26-.03.55-.29.64-.26.1-.55-.04-.64-.29-.49-1.31-.73-2.61-.73-3.96 0-1.2.23-2.29.68-3.24 1.33-2.79 4.28-4.6 7.51-4.6 4.55 0 8.25 3.51 8.25 7.83 0 1.62-1.38 2.94-3.08 2.94s-3.08-1.32-3.08-2.94c0-1.07-.93-1.94-2.08-1.94s-2.08.87-2.08 1.94c0 1.71.66 3.31 1.87 4.51.95.94 1.86 1.46 3.27 1.85.27.07.42.35.35.61-.05.23-.26.38-.47.38z"></path>
            </svg>
            <p><?=$lang['tfa']['start_u2f_validation'];?></p>
            <hr>
          </div>
          </center>
          <p id="u2f_status_auth"></p>
          <div class="alert alert-danger" style="display:none" id="u2f_return_code"></div>
          <input type="hidden" name="token" id="u2f_auth_data"/>
          <input type="hidden" name="tfa_method" value="u2f">
          <input type="hidden" name="verify_tfa_login"/><br/>
        </form>
      <?php
        break;
        case "totp":
      ?>
        <form role="form" method="post">
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon" id="tfa-addon"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span></span>
              <input type="number" min="000000" max="999999" name="token" class="form-control" placeholder="123456" aria-describedby="tfa-addon">
              <input type="hidden" name="tfa_method" value="totp">
            </div>
          </div>
          <button class="btn btn-sm btn-default" type="submit" name="verify_tfa_login"><?=$lang['login']['login'];?></button>
        </form>
        <?php
        break;
        case "hotp":
      ?>
       <div class="empty"></div>
      <?php
        break;
      }
      ?>
      </div>
    </div>
  </div>
</div>
<?php
endif;
if (isset($_SESSION['mailcow_cc_role']) && $_SESSION['mailcow_cc_role'] == 'admin'):
?>
<div id="RestartContainer" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h4 class="modal-title"><?= $lang['footer']['restart_container']; ?> (<code id="containerName"></code>)</h4>
    </div>
    <div class="modal-body">
      <p><?= $lang['footer']['restart_container_info']; ?></p>
      <hr>
      <button class="btn btn-md btn-primary" id="triggerRestartContainer"><?= $lang['footer']['restart_now']; ?></button>
      <br><br>
      <div id="statusTriggerRestartContainer"></div>
    </div>
    </div>
  </div>
</div>
<?php
endif;
?>
<div id="ConfirmDeleteModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h4 class="modal-title"><?= $lang['footer']['confirm_delete']; ?></h4>
    </div>
    <div class="modal-body">
      <p id="DeleteText"><?= $lang['footer']['delete_these_items']; ?></p>
      <ul id="ItemsToDelete"></ul>
      <hr>
      <button class="btn btn-sm btn-danger" id="IsConfirmed"><?= $lang['footer']['delete_now']; ?></button>
      <button class="btn btn-sm btn-default" id="isCanceled"><?= $lang['footer']['cancel']; ?></button>
    </div>
    </div>
  </div>
</div>
