$(document).ready(function() {
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
  var last_login = $('.last_login_date').data('time');
  $('.last_login_date').text(unix_time_format(last_login));

  function draw_tla_table() {
    ft_tla_table = FooTable.init('#tla_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"40px","width":"40px","text-align":"center"},"filterable": false,"sortable": false,"type":"html"},
        {"sorted": true,"name":"address","title":lang.alias},
        {"name":"validity","formatter":function unix_time_format(tm) { var date = new Date(tm ? tm * 1000 : 0); return date.toLocaleDateString(undefined, {year: "numeric", month: "2-digit", day: "2-digit", hour: "2-digit", minute: "2-digit", second: "2-digit"});},"title":lang.alias_valid_until,"style":{"width":"170px"}},
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
                '<a href="#" data-action="delete_selected" data-id="single-tla" data-api-url="delete/time_limited_alias" data-item="' + encodeURIComponent(item.address) + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
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
        {"name":"enc1","title":lang.encryption,"breakpoints":"xs sm"},
        {"name":"user1","title":lang.username},
        {"name":"exclude","title":lang.excludes,"breakpoints":"all"},
        {"name":"mins_interval","title":lang.interval + " (min)","breakpoints":"all"},
        {"name":"last_run","title":lang.last_run,"breakpoints":"all"},
        {"name":"log","title":"Log"},
        {"name":"active","filterable": false,"style":{"maxWidth":"70px","width":"70px"},"title":lang.active},
        {"name":"is_running","filterable": false,"style":{"maxWidth":"120px","width":"100px"},"title":lang.status},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"180px","width":"180px"},"type":"html","title":lang.action,"breakpoints":"xs sm"}
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
                '<a href="/edit/syncjob/' + item.id + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
                '<a href="#" data-action="delete_selected" data-id="single-syncjob" data-api-url="delete/syncjob" data-item="' + item.id + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
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
        {"name":"active","filterable": false,"style":{"maxWidth":"70px","width":"70px"},"title":lang.active},
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
            if (acl_data.app_passwds === 1) {
              item.action = '<div class="btn-group">' +
                '<a href="/edit/app-passwd/' + item.id + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
                '<a href="#" data-action="delete_selected" data-id="single-apppasswd" data-api-url="delete/app-passwd" data-item="' + item.id + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
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