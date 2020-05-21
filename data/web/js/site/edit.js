$(document).ready(function() {
  $(".arrow-toggle").on('click', function(e) { e.preventDefault(); $(this).find('.arrow').toggleClass("animation"); });
  $("#pushover_delete").click(function() { return confirm(lang.delete_ays); });
  $(".goto_checkbox").click(function( event ) {
   $("form[data-id='editalias'] .goto_checkbox").not(this).prop('checked', false);
    if ($("form[data-id='editalias'] .goto_checkbox:checked").length > 0) {
      $('#textarea_alias_goto').prop('disabled', true);
    }
    else {
      $("#textarea_alias_goto").removeAttr('disabled');
    }
  });
  $("#disable_sender_check").click(function( event ) {
    if ($("form[data-id='editmailbox'] #disable_sender_check:checked").length > 0) {
      $('#editSelectSenderACL').prop('disabled', true);
      $('#editSelectSenderACL').selectpicker('refresh');
    }
    else {
      $('#editSelectSenderACL').prop('disabled', false);
      $('#editSelectSenderACL').selectpicker('refresh');
    }
  });
  if ($("form[data-id='editalias'] .goto_checkbox:checked").length > 0) {
    $('#textarea_alias_goto').prop('disabled', true);
  }

  $("#mailbox-password-warning-close").click(function( event ) {
    $('#mailbox-passwd-hidden-info').addClass('hidden');
    $('#mailbox-passwd-form-groups').removeClass('hidden');
  });
});
if ($("#editSelectMultipleBookings").val() == "custom") {
  $("#multiple_bookings_custom_div").show();
  $('input[name=multiple_bookings]').val($("#multiple_bookings_custom").val());
}
$("#editSelectMultipleBookings").change(function() {
  $('input[name=multiple_bookings]').val($("#editSelectMultipleBookings").val());
  if ($('input[name=multiple_bookings]').val() == "custom") {
    $("#multiple_bookings_custom_div").show();
  }
  else {
    $("#multiple_bookings_custom_div").hide();
  }
});
if ($("#editSelectSenderACL option[value='\*']:selected").length > 0){
  $("#sender_acl_disabled").show();
}
$('#editSelectSenderACL').change(function() {
  if ($("#editSelectSenderACL option[value='\*']:selected").length > 0){
    $("#sender_acl_disabled").show();
  }
  else {
    $("#sender_acl_disabled").hide();
  }
});
$("#multiple_bookings_custom").bind("change keypress keyup blur", function() {
  $('input[name=multiple_bookings]').val($("#multiple_bookings_custom").val());
});
jQuery(function($){
  // http://stackoverflow.com/questions/46155/validate-email-address-in-javascript
  function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
  }
  function draw_wl_policy_domain_table() {
    ft_wl_policy_mailbox_table = FooTable.init('#wl_policy_domain_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"40px","width":"40px"},"filterable": false,"sortable": false,"type":"html"},
        {"name":"prefid","style":{"maxWidth":"40px","width":"40px"},"title":"ID","filterable": false,"sortable": false},
        {"sorted": true,"name":"value","title":lang_user.spamfilter_table_rule},
        {"name":"object","title":"Scope"}
      ],
      "empty": lang_user.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/policy_wl_domain/' + table_for_domain,
        jsonp: false,
        error: function () {
          console.log('Cannot draw mailbox policy wl table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            if (!validateEmail(item.object)) {
              item.chkbox = '<input type="checkbox" data-id="policy_wl_domain" name="multi_select" value="' + item.prefid + '" />';
            }
            else {
              item.chkbox = '<input type="checkbox" disabled title="' + lang_user.spamfilter_table_domain_policy + '" />';
            }
          });
        }
      }),
      "paging": {
        "enabled": true,
        "limit": 5,
        "size": pagination_size
      },
      "sorting": {
        "enabled": true
      }
    });
  }
  function draw_bl_policy_domain_table() {
    ft_bl_policy_mailbox_table = FooTable.init('#bl_policy_domain_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"40px","width":"40px"},"filterable": false,"sortable": false,"type":"html"},
        {"name":"prefid","style":{"maxWidth":"40px","width":"40px"},"title":"ID","filterable": false,"sortable": false},
        {"sorted": true,"name":"value","title":lang_user.spamfilter_table_rule},
        {"name":"object","title":"Scope"}
      ],
      "empty": lang_user.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/policy_bl_domain/' + table_for_domain,
        jsonp: false,
        error: function () {
          console.log('Cannot draw mailbox policy bl table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            if (!validateEmail(item.object)) {
              item.chkbox = '<input type="checkbox" data-id="policy_bl_domain" name="multi_select" value="' + item.prefid + '" />';
            }
            else {
              item.chkbox = '<input type="checkbox" disabled tooltip="' + lang_user.spamfilter_table_domain_policy + '" />';
            }
          });
        }
      }),
      "paging": {
        "enabled": true,
        "limit": 5,
        "size": pagination_size
      },
      "sorting": {
        "enabled": true
      }
    });
  }
  draw_wl_policy_domain_table();
  draw_bl_policy_domain_table();
});