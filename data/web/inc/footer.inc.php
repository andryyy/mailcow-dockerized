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
echo "var lang_footer = ". $lang_footer . ";\n";
echo "var lang_acl = ". $lang_acl . ";\n";
echo "var lang_tfa = ". $lang_tfa . ";\n";
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
  ?>
  mailcow_alert_box(<?=$log['msg'];?>, <?=$log['type'];?>);
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
  $('#u2f_status_auth').html('<p><span class="glyphicon glyphicon-refresh glyphicon-spin"></span> ' + lang_tfa.init_u2f + '</p>');
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

  // Set TFA modals
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
        $('#u2f_status_reg').html('<p><span class="glyphicon glyphicon-refresh glyphicon-spin"></span> ' + lang_tfa.init_u2f + '</p>');
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
    <?php
    if (!empty($UI_TEXTS['ui_footer'])):
    ?>
     <hr><?=$UI_TEXTS['ui_footer'];?>
    <?php
    endif;
    ?>
  </div>
</body>
</html>
<?php
$stmt = null;
$pdo = null;
