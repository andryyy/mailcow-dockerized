// Base64 functions
var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(r){var t,e,o,a,h,n,c,d="",C=0;for(r=Base64._utf8_encode(r);C<r.length;)a=(t=r.charCodeAt(C++))>>2,h=(3&t)<<4|(e=r.charCodeAt(C++))>>4,n=(15&e)<<2|(o=r.charCodeAt(C++))>>6,c=63&o,isNaN(e)?n=c=64:isNaN(o)&&(c=64),d=d+this._keyStr.charAt(a)+this._keyStr.charAt(h)+this._keyStr.charAt(n)+this._keyStr.charAt(c);return d},decode:function(r){var t,e,o,a,h,n,c="",d=0;for(r=r.replace(/[^A-Za-z0-9\+\/\=]/g,"");d<r.length;)t=this._keyStr.indexOf(r.charAt(d++))<<2|(a=this._keyStr.indexOf(r.charAt(d++)))>>4,e=(15&a)<<4|(h=this._keyStr.indexOf(r.charAt(d++)))>>2,o=(3&h)<<6|(n=this._keyStr.indexOf(r.charAt(d++))),c+=String.fromCharCode(t),64!=h&&(c+=String.fromCharCode(e)),64!=n&&(c+=String.fromCharCode(o));return c=Base64._utf8_decode(c)},_utf8_encode:function(r){r=r.replace(/\r\n/g,"\n");for(var t="",e=0;e<r.length;e++){var o=r.charCodeAt(e);o<128?t+=String.fromCharCode(o):o>127&&o<2048?(t+=String.fromCharCode(o>>6|192),t+=String.fromCharCode(63&o|128)):(t+=String.fromCharCode(o>>12|224),t+=String.fromCharCode(o>>6&63|128),t+=String.fromCharCode(63&o|128))}return t},_utf8_decode:function(r){for(var t="",e=0,o=c1=c2=0;e<r.length;)(o=r.charCodeAt(e))<128?(t+=String.fromCharCode(o),e++):o>191&&o<224?(c2=r.charCodeAt(e+1),t+=String.fromCharCode((31&o)<<6|63&c2),e+=2):(c2=r.charCodeAt(e+1),c3=r.charCodeAt(e+2),t+=String.fromCharCode((15&o)<<12|(63&c2)<<6|63&c3),e+=3);return t}};
jQuery(function($){
  // http://stackoverflow.com/questions/24816/escaping-html-strings-with-jquery
  var entityMap={"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;","/":"&#x2F;","`":"&#x60;","=":"&#x3D;"};
  function jq(myid) {return "#" + myid.replace( /(:|\.|\[|\]|,|=|@)/g, "\\$1" );}
  function escapeHtml(n){return String(n).replace(/[&<>"'`=\/]/g,function(n){return entityMap[n]})}
  function validateRegex(e){var t=e.split("/"),n=e,r="";t.length>1&&(n=t[1],r=t[2]);try{return new RegExp(n,r),!0}catch(e){return!1}}
  function humanFileSize(i){if(Math.abs(i)<1024)return i+" B";var B=["KiB","MiB","GiB","TiB","PiB","EiB","ZiB","YiB"],e=-1;do{i/=1024,++e}while(Math.abs(i)>=1024&&e<B.length-1);return i.toFixed(1)+" "+B[e]}
  function hashCode(t){for(var n=0,r=0;r<t.length;r++)n=t.charCodeAt(r)+((n<<5)-n);return n}
  function intToRGB(t){var n=(16777215&t).toString(16).toUpperCase();return"00000".substring(0,6-n.length)+n}
  $("#dkim_missing_keys").on('click', function(e) {
    e.preventDefault();
     var domains = [];
     $('.dkim_missing').each(function() {
       domains.push($(this).val());
     });
     $('#dkim_add_domains').val(domains);
  });
  $(".arrow-toggle").on('click', function(e) { e.preventDefault(); $(this).find('.arrow').toggleClass("animation"); });
  $("#mass_exclude").change(function(){ $("#mass_include").selectpicker('deselectAll'); });
  $("#mass_include").change(function(){ $("#mass_exclude").selectpicker('deselectAll'); });
  $("#mass_disarm").click(function() { $("#mass_send").attr("disabled", !this.checked); });
  $(".validate_rspamd_regex").click(function( event ) {
    event.preventDefault();
    var regex_map_id = $(this).data('regex-map');
    var regex_data = $(jq(regex_map_id)).val().split(/\r?\n/);
    var regex_valid = true;
    for(var i = 0;i < regex_data.length;i++){
      if(regex_data[i].startsWith('#') || !regex_data[i]){
        continue;
      }
      if(!validateRegex(regex_data[i])) {
        mailcow_alert_box('Cannot build regex from line ' + (i+1), 'danger');
        var regex_valid = false;
        break;
      }
      if(!regex_data[i].startsWith('/') || !/\/[ims]?$/.test(regex_data[i])){
        mailcow_alert_box('Line ' + (i+1) + ' is invalid', 'danger');
        var regex_valid = false;
        break;
      }
    }
    if (regex_valid) {
      mailcow_alert_box('Regex OK', 'success');
      $('button[data-id="' + regex_map_id + '"]').attr({"disabled": false});
    }
  });
	$('.textarea-code').on('keyup', function() {
    $('.submit_rspamd_regex').attr({"disabled": true});
	});
  $("#show_rspamd_global_filters").click(function() {
    $.get("inc/ajax/show_rspamd_global_filters.php");
    $("#confirm_show_rspamd_global_filters").hide();
    $("#rspamd_global_filters").removeClass("hidden");
  });
  $("#super_delete").click(function() { return confirm(lang.queue_ays); });
  $(".refresh_table").on('click', function(e) {
    e.preventDefault();
    var table_name = $(this).data('table');
    $('#' + table_name).find("tr.footable-empty").remove();
    draw_table = $(this).data('draw');
    eval(draw_table + '()');
  });
  function table_admin_ready(ft, name) {
    heading = ft.$el.parents('.panel').find('.panel-heading')
    var ft_paging = ft.use(FooTable.Paging)
    $(heading).children('.table-lines').text(function(){
      return ft_paging.totalRows;
    })
  }
  function draw_domain_admins() {
    ft_domainadmins = FooTable.init('#domainadminstable', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px"},"filterable": false,"sortable": false,"type":"html"},
        {"sorted": true,"name":"username","title":lang.username,"style":{"width":"250px"}},
        {"name":"selected_domains","title":lang.admin_domains,"breakpoints":"xs sm"},
        {"name":"tfa_active","title":"TFA", "filterable": false,"style":{"maxWidth":"80px","width":"80px"}},
        {"name":"active","filterable": false,"style":{"maxWidth":"80px","width":"80px"},"title":lang.active},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"250px","width":"250px"},"type":"html","title":lang.action,"breakpoints":"xs sm"}
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/domain-admin/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw domain admin table');
        },
        success: function (data) {
          return process_table_data(data, 'domainadminstable');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "state": {"enabled": true},
      "filtering": {"enabled": true,"delay": 1200,"position": "left","connectors": false,"placeholder": lang.filter_table},
      "sorting": {"enabled": true},
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }
  function draw_oauth2_clients() {
    ft_oauth2clientstable = FooTable.init('#oauth2clientstable', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"40px","width":"40px"},"filterable": false,"sortable": false,"type":"html"},
        {"name":"id","type":"text","title":"ID","style":{"width":"50px"}},
        {"name":"client_id","type":"text","title":lang.oauth2_client_id,"style":{"width":"200px"}},
        {"name":"client_secret","title":lang.oauth2_client_secret,"breakpoints":"xs sm md","style":{"width":"200px"}},
        {"name":"redirect_uri","title":lang.oauth2_redirect_uri, "type": "text"},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"180px","width":"180px"},"type":"html","title":lang.action,"breakpoints":"xs sm"}
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/oauth2-client/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw oauth2 clients table');
        },
        success: function (data) {
          return process_table_data(data, 'oauth2clientstable');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "sorting": {"enabled": true},
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }
  function draw_admins() {
    ft_admins = FooTable.init('#adminstable', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px"},"filterable": false,"sortable": false,"type":"html"},
        {"sorted": true,"name":"usr","title":lang.username,"style":{"width":"250px"}},
        {"name":"tfa_active","title":"TFA", "filterable": false,"style":{"maxWidth":"80px","width":"80px"}},
        {"name":"active","filterable": false,"style":{"maxWidth":"80px","width":"80px"},"title":lang.active},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"250px","width":"250px"},"type":"html","title":lang.action,"breakpoints":"xs sm"}
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/admin/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw admin table');
        },
        success: function (data) {
          return process_table_data(data, 'adminstable');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "filtering": {"enabled": false},
      "state": {"enabled": true},
      "sorting": {"enabled": true},
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }
  function draw_fwd_hosts() {
    ft_forwardinghoststable = FooTable.init('#forwardinghoststable', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px"},"filterable": false,"sortable": false,"type":"html"},
        {"name":"host","type":"text","title":lang.host,"style":{"width":"250px"}},
        {"name":"source","title":lang.source,"breakpoints":"xs sm"},
        {"name":"keep_spam","title":lang.spamfilter, "type": "text","style":{"maxWidth":"80px","width":"80px"}},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"180px","width":"180px"},"type":"html","title":lang.action,"breakpoints":"xs sm"}
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/fwdhost/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw forwarding hosts table');
        },
        success: function (data) {
          return process_table_data(data, 'forwardinghoststable');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "sorting": {"enabled": true},
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }
  function draw_relayhosts() {
    ft_relayhoststable = FooTable.init('#relayhoststable', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px"},"filterable": false,"sortable": false,"type":"html"},
        {"name":"id","type":"text","title":"ID","style":{"width":"50px"}},
        {"name":"hostname","type":"text","title":lang.host,"style":{"width":"250px"}},
        {"name":"username","title":lang.username,"breakpoints":"xs sm"},
        {"name":"used_by_domains","title":lang.in_use_by,"style":{"width":"110px"}, "type": "text","breakpoints":"xs sm"},
        {"name":"active","filterable": false,"style":{"maxWidth":"80px","width":"80px"},"title":lang.active},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"220px","width":"220px"},"type":"html","title":lang.action,"breakpoints":"xs sm md"}
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/relayhost/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw forwarding hosts table');
        },
        success: function (data) {
          return process_table_data(data, 'relayhoststable');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "sorting": {"enabled": true},
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }
  function draw_transport_maps() {
    ft_relayhoststable = FooTable.init('#transportstable', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px"},"filterable": false,"sortable": false,"type":"html"},
        {"name":"id","type":"text","title":"ID","style":{"width":"50px"}},
        {"name":"destination","type":"text","title":lang.destination,"style":{"width":"250px"}},
        {"name":"nexthop","type":"text","title":lang.nexthop,"style":{"width":"250px"}},
        {"name":"username","title":lang.username,"breakpoints":"xs sm"},
        {"name":"active","filterable": false,"style":{"maxWidth":"80px","width":"80px"},"title":lang.active},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"220px","width":"220px"},"type":"html","title":lang.action,"breakpoints":"xs sm md"}
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/transport/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw transports table');
        },
        success: function (data) {
          return process_table_data(data, 'transportstable');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "sorting": {"enabled": true},
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }
  function draw_queue() {
    ft_queuetable = FooTable.init('#queuetable', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px"},"filterable": false,"sortable": false,"type":"html"},
        {"name":"queue_id","type":"text","title":"QID","style":{"width":"50px"}},
        {"name":"queue_name","type":"text","title":"Queue","style":{"width":"120px"}},
        {"name":"arrival_time","sorted": true,"direction": "DESC","formatter":function unix_time_format(tm) { var date = new Date(tm ? tm * 1000 : 0); return date.toLocaleDateString(undefined, {year: "numeric", month: "2-digit", day: "2-digit", hour: "2-digit", minute: "2-digit", second: "2-digit"});},"title":lang.arrival_time,"style":{"width":"170px"}},
        {"name":"message_size","style":{"whiteSpace":"nowrap"},"title":lang.message_size,"formatter": function(value){
          return humanFileSize(value);
        }},
        {"name":"sender","title":lang.sender, "type": "text","breakpoints":"xs sm"},
        {"name":"recipients","title":lang.recipients, "type": "text","style":{"word-break":"break-all","min-width":"300px"},"breakpoints":"xs sm md"},
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/mailq/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw forwarding hosts table');
        },
        success: function (data) {
          return process_table_data(data, 'queuetable');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "sorting": {"enabled": true},
      "toggleSelector": "table tbody span.footable-toggle",
      "on": {
        "ready.ft.table": function(e, ft){
          table_admin_ready(ft, 'queuetable');
        }
      }
    });
  }

  function process_table_data(data, table) {
    if (table == 'relayhoststable') {
      $.each(data, function (i, item) {
        item.action = '<div class="btn-group">' +
          '<a href="#" data-toggle="modal" data-target="#testTransportModal" data-transport-id="' + encodeURI(item.id) + '" data-transport-type="sender-dependent" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-triangle-right"></span> Test</a>' +
          '<a href="/edit/relayhost/' + encodeURI(item.id) + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
          '<a href="#" data-action="delete_selected" data-id="single-rlyhost" data-api-url="delete/relayhost" data-item="' + encodeURI(item.id) + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
          '</div>';
        item.chkbox = '<input type="checkbox" data-id="rlyhosts" name="multi_select" value="' + item.id + '" />';
      });
    } else if (table == 'transportstable') {
      $.each(data, function (i, item) {
        if (item.username) {
          item.username = '<span style="border-left:3px solid #' + intToRGB(hashCode(item.nexthop)) + ';padding-left:5px;">' + item.username + '</span>';
        }
        item.action = '<div class="btn-group">' +
          '<a href="#" data-toggle="modal" data-target="#testTransportModal" data-transport-id="' + encodeURI(item.id) + '" data-transport-type="transport-map" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-triangle-right"></span> Test</a>' +
          '<a href="/edit/transport/' + encodeURI(item.id) + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
          '<a href="#" data-action="delete_selected" data-id="single-transport" data-api-url="delete/transport" data-item="' + encodeURI(item.id) + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
          '</div>';
        item.chkbox = '<input type="checkbox" data-id="transports" name="multi_select" value="' + item.id + '" />';
      });
    } else if (table == 'queuetable') {
      $.each(data, function (i, item) {
        item.chkbox = '<input type="checkbox" data-id="mailqitems" name="multi_select" value="' + item.queue_id + '" />';
        rcpts = $.map(item.recipients, function(i) {
          return escapeHtml(i);
        });
        item.recipients = rcpts.join('<hr style="margin:1px!important">');
      });
    } else if (table == 'forwardinghoststable') {
      $.each(data, function (i, item) {
        item.action = '<div class="btn-group">' +
          '<a href="#" data-action="delete_selected" data-id="single-fwdhost" data-api-url="delete/fwdhost" data-item="' + encodeURI(item.host) + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
          '</div>';
        if (item.keep_spam == "yes") {
          item.keep_spam = lang.no;
        }
        else {
          item.keep_spam = lang.yes;
        }
        item.chkbox = '<input type="checkbox" data-id="fwdhosts" name="multi_select" value="' + item.host + '" />';
      });
    } else if (table == 'oauth2clientstable') {
      $.each(data, function (i, item) {
        item.action = '<div class="btn-group">' +
          '<a href="/edit.php?oauth2client=' + encodeURI(item.id) + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
          '<a href="#" data-action="delete_selected" data-id="single-oauth2-client" data-api-url="delete/oauth2-client" data-item="' + encodeURI(item.id) + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
          '</div>';
        item.scope = "profile";
        item.grant_types = 'refresh_token password authorization_code';
        item.chkbox = '<input type="checkbox" data-id="oauth2_clients" name="multi_select" value="' + item.id + '" />';
      });
    } else if (table == 'domainadminstable') {
      $.each(data, function (i, item) {
        item.selected_domains = escapeHtml(item.selected_domains);
        item.selected_domains = item.selected_domains.toString().replace(/,/g, "<br>");
        item.chkbox = '<input type="checkbox" data-id="domain_admins" name="multi_select" value="' + item.username + '" />';
        item.action = '<div class="btn-group">' +
          '<a href="/edit/domainadmin/' + encodeURI(item.username) + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
          '<a href="#" data-action="delete_selected" data-id="single-domain-admin" data-api-url="delete/domain-admin" data-item="' + encodeURI(item.username) + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
          '<a href="/index.php?duallogin=' + encodeURIComponent(item.username) + '" class="btn btn-xs btn-success"><span class="glyphicon glyphicon-user"></span> Login</a>' +
          '</div>';
      });
    } else if (table == 'adminstable') {
      $.each(data, function (i, item) {
        if (admin_username.toLowerCase() == item.username.toLowerCase()) {
          item.usr = '→ ' + item.username;
        } else {
          item.usr = item.username;
        }
        item.chkbox = '<input type="checkbox" data-id="admins" name="multi_select" value="' + item.username + '" />';
        item.action = '<div class="btn-group">' +
          '<a href="/edit/admin/' + encodeURI(item.username) + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
          '<a href="#" data-action="delete_selected" data-id="single-admin" data-api-url="delete/admin" data-item="' + encodeURI(item.username) + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
          '</div>';
      });
    }
    return data
  };
  // Initial table drawings
  draw_domain_admins();
  draw_admins();
  draw_fwd_hosts();
  draw_relayhosts();
  draw_oauth2_clients();
  draw_transport_maps();
  draw_queue();
  // API IP check toggle
  $("#skip_ip_check_ro").click(function( event ) {
   $("#skip_ip_check_ro").not(this).prop('checked', false);
    if ($("#skip_ip_check_ro:checked").length > 0) {
      $('#allow_from_ro').prop('disabled', true);
    }
    else {
      $("#allow_from_ro").removeAttr('disabled');
    }
  });
  $("#skip_ip_check_rw").click(function( event ) {
   $("#skip_ip_check_rw").not(this).prop('checked', false);
    if ($("#skip_ip_check_rw:checked").length > 0) {
      $('#allow_from_rw').prop('disabled', true);
    }
    else {
      $("#allow_from_rw").removeAttr('disabled');
    }
  });
  // Relayhost
  $('#testRelayhostModal').on('show.bs.modal', function (e) {
    $('#test_relayhost_result').text("-");
    button = $(e.relatedTarget)
    if (button != null) {
      $('#relayhost_id').val(button.data('relayhost-id'));
    }
  })
  $('#test_relayhost').on('click', function (e) {
    e.preventDefault();
    prev = $('#test_relayhost').text();
    $(this).prop("disabled",true);
    $(this).html('<span class="glyphicon glyphicon-refresh glyphicon-spin"></span> ');
    $.ajax({
        type: 'GET',
        url: 'inc/ajax/relay_check.php',
        dataType: 'text',
        data: $('#test_relayhost_form').serialize(),
        complete: function (data) {
          $('#test_relayhost_result').html(data.responseText);
          $('#test_relayhost').prop("disabled",false);
          $('#test_relayhost').text(prev);
        }
    });
  })
  // Transport
  $('#testTransportModal').on('show.bs.modal', function (e) {
    $('#test_transport_result').text("-");
    button = $(e.relatedTarget)
    if (button != null) {
      $('#transport_id').val(button.data('transport-id'));
      $('#transport_type').val(button.data('transport-type'));
    }
  })
  $('#test_transport').on('click', function (e) {
    e.preventDefault();
    prev = $('#test_transport').text();
    $(this).prop("disabled",true);
    $(this).html('<span class="glyphicon glyphicon-refresh glyphicon-spin"></span> ');
    $.ajax({
        type: 'GET',
        url: 'inc/ajax/transport_check.php',
        dataType: 'text',
        data: $('#test_transport_form').serialize(),
        complete: function (data) {
          $('#test_transport_result').html(data.responseText);
          $('#test_transport').prop("disabled",false);
          $('#test_transport').text(prev);
        }
    });
  })
  // DKIM private key modal
  $('#showDKIMprivKey').on('show.bs.modal', function (e) {
    $('#priv_key_pre').text("-");
    p_related = $(e.relatedTarget)
    if (p_related != null) {
      var decoded_key = Base64.decode((p_related.data('priv-key')));
      $('#priv_key_pre').text(decoded_key);
    }
  })
  // App links
  function add_table_row(table_id) {
    var row = $('<tr />');
    cols = '<td><input class="input-sm form-control" data-id="app_links" type="text" name="app" required></td>';
    cols += '<td><input class="input-sm form-control" data-id="app_links" type="text" name="href" required></td>';
    cols += '<td><a href="#" role="button" class="btn btn-xs btn-default" type="button">Remove row</a></td>';
    row.append(cols);
    table_id.append(row);
  }
  $('#app_link_table').on('click', 'tr a', function (e) {
    e.preventDefault();
    $(this).parents('tr').remove();
  });
  $('#add_app_link_row').click(function() {
      add_table_row($('#app_link_table'));
  });
});
$(window).load(function(){
  $('.sidebar').affix({
        offset: {
            top: 0
        }
    }).on('affix.bs.affix',function(){
        setAffixContainerSize();
    });

    /*Setting the width of the sidebar (I took 10px of its value which is the margin between cols in my Bootstrap CSS*/
    function setAffixContainerSize(){
        $('.sidebar').width($('.sidebar').parent().innerWidth()-10);
    }

    $(window).resize(function(){
        setAffixContainerSize();
    });
  initial_width_config = $("#sidebar-admin-config").width();
  initial_width_maps = $("#sidebar-admin-maps").width();
  $("#scrollbox-config").css("width", initial_width_config);
  $("#scrollbox-maps").css("width", initial_width_maps);
  if (sessionStorage.scrollTop > 70) {
    $('#scrollbox-config').addClass('scrollboxFixed');
    $('#scrollbox-maps').addClass('scrollboxFixed');
  }
  $(window).bind('scroll', function() {
    if ($(window).scrollTop() > 70) {
      $('#scrollbox-config').addClass('scrollboxFixed');
      $('#scrollbox-maps').addClass('scrollboxFixed');
    } else {
      $('#scrollbox-config').removeClass('scrollboxFixed');
      $('#scrollbox-maps').removeClass('scrollboxFixed');
    }
  });
});
function resizeScrollbox() {
  on_resize_width_config = $("#sidebar-admin-config").width();
  on_resize_width_maps = $("#sidebar-admin-maps").width();
  $("#scrollbox-config").removeAttr("style");
  $("#scrollbox-config").css("width", on_resize_width_config);
  $("#scrollbox-maps").removeAttr("style");
  $("#scrollbox-maps").css("width", on_resize_width_maps);
}
$(window).on('resize', resizeScrollbox);
$('a[data-toggle="tab"]').on('shown.bs.tab', resizeScrollbox);
