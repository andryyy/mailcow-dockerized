$(document).ready(function() {
  // Spam score slider
  var spam_slider = $('#spam_score')[0];
  noUiSlider.create(spam_slider, {
    start: user_spam_score,
    connect: [true, true, true],
    range: {
      'min': [0], //stepsize is 50.000
      '50%': [10],
      '70%': [20, 5],
      '80%': [50, 10],
      '90%': [100, 100],
      '95%': [1000, 1000],
      'max': [5000]
    },
  });
  var connect = spam_slider.querySelectorAll('.noUi-connect');
  var classes = ['c-1-color', 'c-2-color', 'c-3-color'];
  for (var i = 0; i < connect.length; i++) {
    connect[i].classList.add(classes[i]);
  }
  spam_slider.noUiSlider.on('update', function (values, handle) {
    $('.spam-ham-score').text('< ' + Math.round(values[0] * 10) / 10);
    $('.spam-spam-score').text(Math.round(values[0] * 10) / 10 + ' - ' + Math.round(values[1] * 10) / 10);
    $('.spam-reject-score').text('> ' + Math.round(values[1] * 10) / 10);
    $('#spam_score_value').val((Math.round(values[0] * 10) / 10) + ',' + (Math.round(values[1] * 10) / 10));
  });
  // syncjobLogModal
  $('#syncjobLogModal').on('show.bs.modal', function(e) {
    var syncjob_id = $(e.relatedTarget).data('syncjob-id');
    $.ajax({
      url: '/inc/ajax/syncjob_logs.php',
      data: { id: syncjob_id },
      dataType: 'text',
      success: function(data){
        $(e.currentTarget).find('#logText').text(data);
      },
      error: function(xhr, status, error) {
        $(e.currentTarget).find('#logText').text(xhr.responseText);
      }
    });
  });
  $(".arrow-toggle").on('click', function(e) { e.preventDefault(); $(this).find('.arrow').toggleClass("animation"); });
  $("#pushover_delete").click(function() { return confirm(lang.delete_ays); });

});
jQuery(function($){
  // http://stackoverflow.com/questions/24816/escaping-html-strings-with-jquery
  var entityMap = {
  '&': '&amp;',
  '<': '&lt;',
  '>': '&gt;',
  '"': '&quot;',
  "'": '&#39;',
  '/': '&#x2F;',
  '`': '&#x60;',
  '=': '&#x3D;'
  };
  function escapeHtml(string) {
    return String(string).replace(/[&<>"'`=\/]/g, function (s) {
      return entityMap[s];
    });
  }
  // http://stackoverflow.com/questions/46155/validate-email-address-in-javascript
  function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
  }
  function unix_time_format(tm) {
    var date = new Date(tm ? tm * 1000 : 0);
    return date.toLocaleDateString(undefined, {year: "numeric", month: "2-digit", day: "2-digit", hour: "2-digit", minute: "2-digit", second: "2-digit"});
  }
  acl_data = JSON.parse(acl);

  $('.clear-last-logins').on('click', function () {if (confirm(lang.delete_ays)) {last_logins('reset');}})
  $(".login-history").on('click', function(e) {e.preventDefault(); last_logins('get', $(this).data('days'));$(this).addClass('active').siblings().removeClass('active');});

  function last_logins(action, days = 7) {
    if (action == 'get') {
      $('.last-login').html('<i class="bi bi-hourglass"></i>' +  lang.waiting);
      $.ajax({
        dataType: 'json',
        url: '/api/v1/get/last-login/' + encodeURIComponent(mailcow_cc_username) + '/' + days,
        jsonp: false,
        error: function () {
          console.log('error reading last logins');
        },
        success: function (data) {
          $('.last-login').html();
          if (data.ui.time) {
            $('.last-login').html('<i class="bi bi-person-fill"></i> ' + lang.last_ui_login + ': ' + unix_time_format(data.ui.time));
          } else {
            $('.last-login').text(lang.no_last_login);
          }
          if (data.sasl) {
            $('.last-login').append('<ul class="list-group">');
            $.each(data.sasl, function (i, item) {
              var datetime = new Date(item.datetime.replace(/-/g, "/"));
              var local_datetime = datetime.toLocaleDateString(undefined, {year: "numeric", month: "2-digit", day: "2-digit", hour: "2-digit", minute: "2-digit", second: "2-digit"});
              item.app_password ? app_password = ' <a href="/edit/app-passwd/' + item.app_password + '">(App)</a>' : app_password = "", item.location ? ip_location = ' <span class="flag-icon flag-icon-' + item.location.toLowerCase() + '"></span>' : ip_location = "";
              "smtp" == item.service ? service = '<div class="label label-default">' + item.service.toUpperCase() + '<i class="bi bi-chevron-compact-right"></i></div>' : "imap" == item.service ? service = '<div class="label label-default"><i class="bi bi-chevron-compact-left"></i> ' + item.service.toUpperCase() + "</div>" : service = '<div class="label label-default">' + item.service.toUpperCase() + "</div>";
              item.real_rip.startsWith("Web") ? real_rip = item.real_rip : real_rip = '<a href="https://www.virustotal.com/gui/ip-address/' + item.real_rip + '/detection" target="_blank">' + item.real_rip + "</a>";
              ip_data = real_rip + ip_location + app_password;
              $(".last-login").append('<li class="list-group-item">' + local_datetime + " " + service + " " + lang.from + " " + ip_data + "</li>");
            })
            $('.last-login').append('</ul>');
          }
        }
      })
    } else if (action == 'reset') {
      $.ajax({
        dataType: 'json',
        url: '/api/v1/get/reset-last-login/' + encodeURIComponent(mailcow_cc_username),
        jsonp: false,
        error: function () {
          console.log('cannot reset last logins');
        },
        success: function (data) {
          last_logins('get');
        }
      })
    }
  }

  function draw_tla_table() {
    ft_tla_table = FooTable.init('#tla_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"40px","width":"40px","text-align":"center"},"filterable": false,"sortable": false,"type":"html"},
        {"name":"address","title":lang.alias},
        {"name":"validity","formatter":function unix_time_format(tm) { var date = new Date(tm ? tm * 1000 : 0); return date.toLocaleDateString(undefined, {year: "numeric", month: "2-digit", day: "2-digit", hour: "2-digit", minute: "2-digit", second: "2-digit"});},"title":lang.alias_valid_until,"style":{"width":"170px"}},
        {"sorted": true,"sortValue": function(value){res = new Date(value);return res.getTime();},"direction":"DESC","name":"created","formatter":function date_format(datetime) { var date = new Date(datetime.replace(/-/g, "/")); return date.toLocaleDateString(undefined, {year: "numeric", month: "2-digit", day: "2-digit", hour: "2-digit", minute: "2-digit", second: "2-digit"});},"title":lang.created_on,"style":{"width":"170px"}},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"180px","width":"180px"},"type":"html","title":lang.action,"breakpoints":"xs sm"}
      ],
      "empty": lang.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/time_limited_aliases',
        jsonp: false,
        error: function () {
          console.log('Cannot draw tla table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            if (acl_data.spam_alias === 1) {
              item.action = '<div class="btn-group">' +
                '<a href="#" data-action="delete_selected" data-id="single-tla" data-api-url="delete/time_limited_alias" data-item="' + encodeURIComponent(item.address) + '" class="btn btn-xs btn-danger"><i class="bi bi-trash"></i> ' + lang.remove + '</a>' +
                '</div>';
              item.chkbox = '<input type="checkbox" data-id="tla" name="multi_select" value="' + encodeURIComponent(item.address) + '" />';
              item.address = escapeHtml(item.address);
            }
            else {
              item.chkbox = '<input type="checkbox" disabled />';
              item.action = '<span>-</span>';
            }
          });
        }
      }),
      "paging": {
        "enabled": true,
        "limit": 5,
        "size": pagination_size
      },
      "state": {"enabled": true},
      "sorting": {
        "enabled": true
      },
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }
  function draw_sync_job_table() {
    ft_syncjob_table = FooTable.init('#sync_job_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px","text-align":"center"},"filterable": false,"sortable": false,"type":"html"},
        {"sorted": true,"name":"id","title":"ID","style":{"maxWidth":"60px","width":"60px","text-align":"center"}},
        {"name":"server_w_port","title":"Server"},
        {"name":"enc1","title":lang.encryption,"breakpoints":"all"},
        {"name":"user1","title":lang.username},
        {"name":"exclude","title":lang.excludes,"breakpoints":"all"},
        {"name":"mins_interval","title":lang.interval + " (min)","breakpoints":"all"},
        {"name":"last_run","title":lang.last_run,"breakpoints":"all"},
        {"name":"log","title":"Log"},
        {"name":"active","filterable": false,"style":{"maxWidth":"70px","width":"70px"},"title":lang.active,"formatter": function(value){return 1==value?'<i class="bi bi-check-lg"></i>':0==value&&'<i class="bi bi-x-lg"></i>';}},
        {"name":"is_running","filterable": false,"style":{"maxWidth":"120px","width":"100px"},"title":lang.status},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","min-width":"260px","width":"260px"},"type":"html","title":lang.action,"breakpoints":"xs sm"}
      ],
      "empty": lang.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/syncjobs/' + encodeURIComponent(mailcow_cc_username) + '/no_log',
        jsonp: false,
        error: function () {
          console.log('Cannot draw sync job table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            item.user1 = escapeHtml(item.user1);
            item.log = '<a href="#syncjobLogModal" data-toggle="modal" data-syncjob-id="' + item.id + '">Open logs</a>'
            if (!item.exclude > 0) {
              item.exclude = '-';
            } else {
              item.exclude  = '<code>' + escapeHtml(item.exclude) + '</code>';
            }
            item.server_w_port = escapeHtml(item.user1 + '@' + item.host1 + ':' + item.port1);
            if (acl_data.syncjobs === 1) {
              item.action = '<div class="btn-group">' +
                '<a href="/edit/syncjob/' + item.id + '" class="btn btn-xs btn-default"><i class="bi bi-pencil-fill"></i> ' + lang.edit + '</a>' +
                '<a href="#" data-action="delete_selected" data-id="single-syncjob" data-api-url="delete/syncjob" data-item="' + item.id + '" class="btn btn-xs btn-danger"><i class="bi bi-trash"></i> ' + lang.remove + '</a>' +
                '</div>';
              item.chkbox = '<input type="checkbox" data-id="syncjob" name="multi_select" value="' + item.id + '" />';
            }
            else {
              item.action = '<span>-</span>';
              item.chkbox = '<input type="checkbox" disabled />';
            }
            if (item.is_running == 1) {
              item.is_running = '<span id="active-script" class="label label-success">' + lang.running + '</span>';
            } else {
              item.is_running = '<span id="inactive-script" class="label label-warning">' + lang.waiting + '</span>';
            }
            if (!item.last_run > 0) {
              item.last_run = lang.waiting;
            }
          });
        }
      }),
      "paging": {
        "enabled": true,
        "limit": 5,
        "size": pagination_size
      },
      "state": {"enabled": true},
      "sorting": {
        "enabled": true
      },
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }
  function draw_app_passwd_table() {
    ft_apppasswd_table = FooTable.init('#app_passwd_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px","text-align":"center"},"filterable": false,"sortable": false,"type":"html"},
        {"sorted": true,"name":"id","title":"ID","style":{"maxWidth":"60px","width":"60px","text-align":"center"}},
        {"name":"name","title":lang.app_name},
        {"name":"active","filterable": false,"style":{"maxWidth":"70px","width":"70px"},"title":lang.active,"formatter": function(value){return 1==value?'<i class="bi bi-check-lg"></i>':0==value&&'<i class="bi bi-x-lg"></i>';}},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"180px","width":"180px"},"type":"html","title":lang.action,"breakpoints":"xs sm"}
      ],
      "empty": lang.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/app-passwd/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw app passwd table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            item.name = escapeHtml(item.name);
            if (acl_data.app_passwds === 1) {
              item.action = '<div class="btn-group">' +
                '<a href="/edit/app-passwd/' + item.id + '" class="btn btn-xs btn-default"><i class="bi bi-pencil-fill"></i> ' + lang.edit + '</a>' +
                '<a href="#" data-action="delete_selected" data-id="single-apppasswd" data-api-url="delete/app-passwd" data-item="' + item.id + '" class="btn btn-xs btn-danger"><i class="bi bi-trash"></i> ' + lang.remove + '</a>' +
                '</div>';
              item.chkbox = '<input type="checkbox" data-id="apppasswd" name="multi_select" value="' + item.id + '" />';
            }
            else {
              item.action = '<span>-</span>';
              item.chkbox = '<input type="checkbox" disabled />';
            }
          });
        }
      }),
      "paging": {
        "enabled": true,
        "limit": 5,
        "size": pagination_size
      },
      "state": {"enabled": true},
      "sorting": {
        "enabled": true
      },
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }
  function draw_wl_policy_mailbox_table() {
    ft_wl_policy_mailbox_table = FooTable.init('#wl_policy_mailbox_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"40px","width":"40px","text-align":"center"},"filterable": false,"sortable": false,"type":"html"},
        {"name":"prefid","style":{"maxWidth":"40px","width":"40px"},"title":"ID","filterable": false,"sortable": false},
        {"sorted": true,"name":"value","title":lang.spamfilter_table_rule},
        {"name":"object","title":"Scope"}
      ],
      "empty": lang.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/policy_wl_mailbox',
        jsonp: false,
        error: function () {
          console.log('Cannot draw mailbox policy wl table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            if (validateEmail(item.object)) {
              item.chkbox = '<input type="checkbox" data-id="policy_wl_mailbox" name="multi_select" value="' + item.prefid + '" />';
            }
            else {
              item.chkbox = '<input type="checkbox" disabled title="' + lang.spamfilter_table_domain_policy + '" />';
            }
            if (acl_data.spam_policy === 0) {
              item.chkbox = '<input type="checkbox" disabled />';
            }
          });
        }
      }),
      "state": {"enabled": true},
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
  function draw_bl_policy_mailbox_table() {
    ft_bl_policy_mailbox_table = FooTable.init('#bl_policy_mailbox_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"40px","width":"40px","text-align":"center"},"filterable": false,"sortable": false,"type":"html"},
        {"name":"prefid","style":{"maxWidth":"40px","width":"40px"},"title":"ID","filterable": false,"sortable": false},
        {"sorted": true,"name":"value","title":lang.spamfilter_table_rule},
        {"name":"object","title":"Scope"}
      ],
      "empty": lang.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/policy_bl_mailbox',
        jsonp: false,
        error: function () {
          console.log('Cannot draw mailbox policy bl table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            if (validateEmail(item.object)) {
              item.chkbox = '<input type="checkbox" data-id="policy_bl_mailbox" name="multi_select" value="' + item.prefid + '" />';
            }
            else {
              item.chkbox = '<input type="checkbox" disabled tooltip="' + lang.spamfilter_table_domain_policy + '" />';
            }
            if (acl_data.spam_policy === 0) {
              item.chkbox = '<input type="checkbox" disabled />';
            }
          });
        }
      }),
      "paging": {
        "enabled": true,
        "limit": 5,
        "size": pagination_size
      },
      "state": {"enabled": true},
      "sorting": {
        "enabled": true
      }
    });
  }

  $('body').on('click', 'span.footable-toggle', function () {
    event.stopPropagation();
  })

  draw_sync_job_table();
  draw_app_passwd_table();
  draw_tla_table();
  draw_wl_policy_mailbox_table();
  draw_bl_policy_mailbox_table();
  last_logins('get');

  // FIDO2 friendly name modal
  $('#fido2ChangeFn').on('show.bs.modal', function (e) {
    rename_link = $(e.relatedTarget)
    if (rename_link != null) {
      $('#fido2_cid').val(rename_link.data('cid'));
      $('#fido2_subject_desc').text(Base64.decode(rename_link.data('subject')));
    }
  })

  // Sieve data modal
  $('#userFilterModal').on('show.bs.modal', function(e) {
    $('#user_sieve_filter').text(lang.loading);
    $.ajax({
      dataType: 'json',
      url: '/api/v1/get/active-user-sieve/' + encodeURIComponent(mailcow_cc_username),
      jsonp: false,
      error: function () {
        console.log('Cannot get active sieve script');
      },
      complete: function (data) {
        if (data.responseText == '{}') {
          $('#user_sieve_filter').text(lang.no_active_filter);
        } else {
          $('#user_sieve_filter').text(JSON.parse(data.responseText));
        }
      }
    })
  });
  $('#userFilterModal').on('hidden.bs.modal', function () {
    $('#user_sieve_filter').text(lang.loading);
  });
});