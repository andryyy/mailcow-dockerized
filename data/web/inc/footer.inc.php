<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/modals/footer.php';
logger();

$hash = $js_minifier->getDataHash();
$JSPath = '/tmp/' . $hash . '.js';
if(!file_exists($JSPath)) {
  $js_minifier->minify($JSPath);
  cleanupJS($hash);
}
?>
<script src="/cache/<?=basename($JSPath)?>"></script>
<script>
<?php
$lang_footer = json_encode($lang['footer']);
$lang_acl = json_encode($lang['acl']);
$lang_tfa = json_encode($lang['tfa']);
$lang_fido2 = json_encode($lang['fido2']);
echo "var lang_footer = ". $lang_footer . ";\n";
echo "var lang_acl = ". $lang_acl . ";\n";
echo "var lang_tfa = ". $lang_tfa . ";\n";
echo "var lang_fido2 = ". $lang_fido2 . ";\n";
echo "var docker_timeout = ". $DOCKER_TIMEOUT * 1000 . ";\n";
?>
$(window).scroll(function() {
  sessionStorage.scrollTop = $(this).scrollTop();
});
// Select language and reopen active URL without POST
function setLang(sel) {
  $.post( "<?= $_SERVER['REQUEST_URI']; ?>", {lang: sel} );
  window.location.href = window.location.pathname + window.location.search;
}
// FIDO2 functions
function arrayBufferToBase64(buffer) {
  let binary = '';
  let bytes = new Uint8Array(buffer);
  let len = bytes.byteLength;
  for (let i = 0; i < len; i++) {
    binary += String.fromCharCode( bytes[ i ] );
  }
  return window.btoa(binary);
}
function recursiveBase64StrToArrayBuffer(obj) {
  let prefix = '=?BINARY?B?';
  let suffix = '?=';
  if (typeof obj === 'object') {
    for (let key in obj) {
      if (typeof obj[key] === 'string') {
        let str = obj[key];
        if (str.substring(0, prefix.length) === prefix && str.substring(str.length - suffix.length) === suffix) {
          str = str.substring(prefix.length, str.length - suffix.length);
          let binary_string = window.atob(str);
          let len = binary_string.length;
          let bytes = new Uint8Array(len);
          for (let i = 0; i < len; i++) {
            bytes[i] = binary_string.charCodeAt(i);
          }
          obj[key] = bytes.buffer;
        }
      } else {
        recursiveBase64StrToArrayBuffer(obj[key]);
      }
    }
  }
}
$(window).load(function() {
  $(".overlay").hide();
});
$(document).ready(function() {
  $(document).on('shown.bs.modal', function(e) {
    modal_id = $(e.relatedTarget).data('target');
    $(modal_id).attr("aria-hidden","false");
  });
  // TFA, CSRF, Alerts in footer.inc.php
  // Other general functions in mailcow.js
  <?php
  $alertbox_log_parser = alertbox_log_parser($_SESSION);
  if (is_array($alertbox_log_parser)) {
    foreach($alertbox_log_parser as $log) {
      $alerts[$log['type']][] = $log['msg'];
    }
    $alerts = array_filter(array_unique($alerts));
    foreach($alerts as $alert_type => $alert_msg) {
  ?>
  mailcow_alert_box(<?=json_encode(implode('<hr class="alert-hr">', $alert_msg));?>, <?=$alert_type;?>);
  <?php
    }
  unset($_SESSION['return']);
  }
  ?>
  // Confirm TFA modal
  <?php if (isset($_SESSION['pending_tfa_method'])):?>
  $('#ConfirmTFAModal').modal({
    backdrop: 'static',
    keyboard: false
  });
  $('#u2f_status_auth').html('<p><i class="bi bi-arrow-repeat icon-spin"></i> ' + lang_tfa.init_u2f + '</p>');
  $('#ConfirmTFAModal').on('shown.bs.modal', function(){
      $(this).find('input[name=token]').focus();
      // If U2F
      if(document.getElementById("u2f_auth_data") !== null) {
        $.ajax({
          type: "GET",
          cache: false,
          dataType: 'script',
          url: "/api/v1/get/u2f-authentication/<?= (isset($_SESSION['pending_mailcow_cc_username'])) ? rawurlencode($_SESSION['pending_mailcow_cc_username']) : null; ?>",
          complete: function(data){
            $('#u2f_status_auth').html(lang_tfa.waiting_usb_auth);
            data;
            setTimeout(function() {
              console.log("Ready to authenticate");
              u2f.sign(appId, challenge, registeredKeys, function(data) {
                var form = document.getElementById('u2f_auth_form');
                var auth = document.getElementById('u2f_auth_data');
                console.log("Authenticate callback", data);
                auth.value = JSON.stringify(data);
                form.submit();
              });
            }, 1000);
          }
        });
      }
  });
  $('#ConfirmTFAModal').on('hidden.bs.modal', function(){
      $.ajax({
        type: "GET",
        cache: false,
        dataType: 'script',
        url: '/inc/ajax/destroy_tfa_auth.php',
        complete: function(data){
          window.location = window.location.href.split("#")[0];
        }
      });
  });
  <?php endif; ?>
  // Validate FIDO2
  $("#fido2-login").click(function(){
    $('#fido2-alerts').html();
    if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
      window.alert('Browser not supported.');
      return;
    }
    window.fetch("/api/v1/get/fido2-get-args", {method:'GET',cache:'no-cache'}).then(function(response) {
      return response.json();
    }).then(function(json) {
    if (json.success === false) {
      throw new Error();
    }
    recursiveBase64StrToArrayBuffer(json);
    return json;
    }).then(function(getCredentialArgs) {
      return navigator.credentials.get(getCredentialArgs);
    }).then(function(cred) {
      return {
        id: cred.rawId ? arrayBufferToBase64(cred.rawId) : null,
        clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
        authenticatorData: cred.response.authenticatorData ? arrayBufferToBase64(cred.response.authenticatorData) : null,
        signature : cred.response.signature ? arrayBufferToBase64(cred.response.signature) : null
      };
    }).then(JSON.stringify).then(function(AuthenticatorAttestationResponse) {
      return window.fetch("/api/v1/process/fido2-args", {method:'POST', body: AuthenticatorAttestationResponse, cache:'no-cache'});
    }).then(function(response) {
      return response.json();
    }).then(function(json) {
      if (json.success) {
        window.location = window.location.href.split("#")[0];
      } else {
        throw new Error();
      }
    }).catch(function(err) {
      if (typeof err.message === 'undefined') {
        mailcow_alert_box(lang_fido2.fido2_validation_failed, "danger");
      } else {
        mailcow_alert_box(lang_fido2.fido2_validation_failed + ":<br><i>" + err.message + "</i>", "danger");
      }
    });
  });
  // Set TFA/FIDO2
  $("#register-fido2").click(function(){
    $("option:selected").prop("selected", false);
    if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
        window.alert('Browser not supported.');
        return;
    }
    window.fetch("/api/v1/get/fido2-registration/<?= (isset($_SESSION['mailcow_cc_username'])) ? rawurlencode($_SESSION['mailcow_cc_username']) : null; ?>", {method:'GET',cache:'no-cache'}).then(function(response) {
      return response.json();
    }).then(function(json) {
      if (json.success === false) {
        throw new Error(json.msg);
      }
      recursiveBase64StrToArrayBuffer(json);
      return json;
    }).then(function(createCredentialArgs) {
      console.log(createCredentialArgs);
      return navigator.credentials.create(createCredentialArgs);
    }).then(function(cred) {
      return {
        clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
        attestationObject: cred.response.attestationObject ? arrayBufferToBase64(cred.response.attestationObject) : null
      };
    }).then(JSON.stringify).then(function(AuthenticatorAttestationResponse) {
      return window.fetch("/api/v1/add/fido2-registration", {method:'POST', body: AuthenticatorAttestationResponse, cache:'no-cache'});
    }).then(function(response) {
      return response.json();
    }).then(function(json) {
      if (json.success) {
        window.location = window.location.href.split("#")[0];
      } else {
        throw new Error(json.msg);
      }
    }).catch(function(err) {
      $('#fido2-alerts').html('<span class="text-danger"><b>' + err.message + '</b></span>');
    });
  });
  $('#selectTFA').change(function () {
    if ($(this).val() == "yubi_otp") {
      $('#YubiOTPModal').modal('show');
      $("option:selected").prop("selected", false);
    }
    if ($(this).val() == "totp") {
      $('#TOTPModal').modal('show');
      request_token = $('#tfa-qr-img').data('totp-secret');
      $.ajax({
        url: '/inc/ajax/qr_gen.php',
        data: {
          token: request_token,
        },
      }).done(function (result) {
        $("#tfa-qr-img").attr("src", result);
      });
      $("option:selected").prop("selected", false);
    }
    if ($(this).val() == "u2f") {
      $('#U2FModal').modal('show');
      $("option:selected").prop("selected", false);
      $("#start_u2f_register").click(function(){
        $('#u2f_return_code').html('');
        $('#u2f_return_code').hide();
        $('#u2f_status_reg').html('<p><i class="bi bi-arrow-repeat icon-spin"></i> ' + lang_tfa.init_u2f + '</p>');
        $.ajax({
          type: "GET",
          cache: false,
          dataType: 'script',
          url: "/api/v1/get/u2f-registration/<?= (isset($_SESSION['mailcow_cc_username'])) ? rawurlencode($_SESSION['mailcow_cc_username']) : null; ?>",
          complete: function(data){
            data;
            setTimeout(function() {
              console.log("Ready to register");
              $('#u2f_status_reg').html(lang_tfa.waiting_usb_register);
              u2f.register(appId, registerRequests, registeredKeys, function(deviceResponse) {
                var form  = document.getElementById('u2f_reg_form');
                var reg   = document.getElementById('u2f_register_data');
                console.log("Register callback: ", data);
                if (deviceResponse.errorCode && deviceResponse.errorCode != 0) {
                  var u2f_return_code = document.getElementById('u2f_return_code');
                  u2f_return_code.style.display = u2f_return_code.style.display === 'none' ? '' : null;
                  if (deviceResponse.errorCode == "4") {
                    deviceResponse.errorCode = "4 - The presented device is not eligible for this request. For a registration request this may mean that the token is already registered, and for a sign request it may mean that the token does not know the presented key handle";
                  }
                  else if (deviceResponse.errorCode == "5") {
                    deviceResponse.errorCode = "5 - Timeout reached before request could be satisfied.";
                  }
                  u2f_return_code.innerHTML = lang_tfa.error_code + ': ' + deviceResponse.errorCode + ' ' + lang_tfa.reload_retry;
                  return;
                }
                reg.value = JSON.stringify(deviceResponse);
                form.submit();
              });
            }, 1000);
          }
        });
      });
    }
    if ($(this).val() == "none") {
      $('#DisableTFAModal').modal('show');
      $("option:selected").prop("selected", false);
    }
  });

  // Reload after session timeout
  var session_lifetime = <?=((int)$SESSION_LIFETIME * 1000) + 15000;?>;
  <?php
  if (isset($_SESSION['mailcow_cc_username'])):
  ?>
  setTimeout(function() {
    location.reload();
  }, session_lifetime);
  <?php
  endif;
  ?>

  // CSRF
  $('<input type="hidden" value="<?= $_SESSION['CSRF']['TOKEN']; ?>">').attr('name', 'csrf_token').appendTo('form');
  if (sessionStorage.scrollTop != "undefined") {
    $(window).scrollTop(sessionStorage.scrollTop);
  }
});
</script>

  <div class="container footer">
  <?php if (!empty($UI_TEXTS['ui_footer'])) { ?>
   <hr><span class="rot-enc"><?=str_rot13($UI_TEXTS['ui_footer']);?></span>
  <?php } ?>
  </div>
</body>
</html>
<?php
if (isset($_SESSION['mailcow_cc_api'])) {
  session_regenerate_id(true);
  session_unset();
  session_destroy();
  session_write_close();
  header("Location: /");
}
$stmt = null;
$pdo = null;
