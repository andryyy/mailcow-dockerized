$(document).ready(function() {
  acl_data = JSON.parse(acl);
  FooTable.domainFilter = FooTable.Filtering.extend({
    construct: function(instance){
      this._super(instance);
      this.def = 'All Domains';
      this.$domain = null;
    },
    $create: function(){
      this._super();
      var self = this;
      var domains = [];
      
      $.each(self.ft.rows.all, function(i, row){
        if((row.val().domain != null) && ($.inArray(row.val().domain, domains) === -1)) domains.push(row.val().domain);
      });
      
      $form_grp = $('<div/>', {'class': 'form-group'})
        .append($('<label/>', {'class': 'sr-only', text: 'Domain'}))
        .prependTo(self.$form);
      self.$domain = $('<select/>', { 'class': 'aform-control' })
        .on('change', {self: self}, self._onDomainDropdownChanged)
        .append($('<option/>', {text: self.def}))
        .appendTo($form_grp);

      $.each(domains, function(i, domain){
        self.$domain.append($('<option/>').text(domain));
      });
    },
    _onDomainDropdownChanged: function(e){
      var self = e.data.self,
        selected = $(this).val();
      if (selected !== self.def){
        self.addFilter('domain', selected, ['domain']);
      } else {
        self.removeFilter('domain');
      }
      self.filter();
    },
    draw: function(){
      this._super();
      var domain = this.find('domain');
      if (domain instanceof FooTable.Filter){
        this.$domain.val(domain.query.val());
      } else {
        this.$domain.val(this.def);
      }
      $(this.$domain).closest("select").selectpicker();
    }
  });
  // Auto-fill domain quota when adding new domain
  auto_fill_quota = function(domain) {
		$.get("/api/v1/get/domain/" + domain, function(data){
      var result = $.parseJSON(JSON.stringify(data));
      def_new_mailbox_quota = ( result.def_new_mailbox_quota / 1048576);
      max_new_mailbox_quota = ( result.max_new_mailbox_quota / 1048576);
			if (max_new_mailbox_quota != '0') {
				$("#quotaBadge").html('max. ' +  max_new_mailbox_quota + ' MiB');
				$('#addInputQuota').attr({"disabled": false, "value": "", "type": "number", "max": max_new_mailbox_quota});
				$('#addInputQuota').val(def_new_mailbox_quota);
			}
			else {
				$("#quotaBadge").html('max. ' + max_new_mailbox_quota + ' MiB');
				$('#addInputQuota').attr({"disabled": true, "value": "", "type": "text", "value": "n/a"});
				$('#addInputQuota').val(max_new_mailbox_quota);
			}
		});
  }
	$('#addSelectDomain').on('change', function() {
    auto_fill_quota($('#addSelectDomain').val());
	});
  auto_fill_quota($('#addSelectDomain').val());
  $(".generate_password").click(function( event ) {
    event.preventDefault();
    $('[data-hibp]').trigger('input');
    var random_passwd = GPW.pronounceable(8)
    $(this).closest("form").find("input[name='password']").prop('type', 'text');
    $(this).closest("form").find("input[name='password2']").prop('type', 'text');
    $(this).closest("form").find("input[name='password']").val(random_passwd);
    $(this).closest("form").find("input[name='password2']").val(random_passwd);
  });
  $(".goto_checkbox").click(function( event ) {
   $("form[data-id='add_alias'] .goto_checkbox").not(this).prop('checked', false);
    if ($("form[data-id='add_alias'] .goto_checkbox:checked").length > 0) {
      $('#textarea_alias_goto').prop('disabled', true);
    }
    else {
      $("#textarea_alias_goto").removeAttr('disabled');
    }
  });
  $('#addAliasModal').on('show.bs.modal', function(e) {
    if ($("form[data-id='add_alias'] .goto_checkbox:checked").length > 0) {
      $('#textarea_alias_goto').prop('disabled', true);
    }
    else {
      $("#textarea_alias_goto").removeAttr('disabled');
    }
  });
  // Log modal
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
  // Log modal
  $('#dnsInfoModal').on('show.bs.modal', function(e) {
    var domain = $(e.relatedTarget).data('domain');
    $('.dns-modal-body').html('<center><span style="font-size:18pt;margin:50px" class="glyphicon glyphicon-refresh glyphicon-spin"></span></center>');
    $.ajax({
      url: '/inc/ajax/dns_diagnostics.php',
      data: { domain: domain },
      dataType: 'text',
      success: function(data){
        $('.dns-modal-body').html(data);
      },
      error: function(xhr, status, error) {
        $('.dns-modal-body').html(xhr.responseText);
      }
    });
  });
  // Sieve data modal
  $('#sieveDataModal').on('show.bs.modal', function(e) {
    var sieveScript = $(e.relatedTarget).data('sieve-script');
    $(e.currentTarget).find('#sieveDataText').html('<pre style="font-size:14px;line-height:1.1">' + sieveScript + '</pre>');
  });
  // Disable submit button on script change
	$('#script_data').on('keyup', function() {
    $('#add_filter_btns > #add_sieve_script').attr({"disabled": true});
    $('#validation_msg').html('-');
	});
  // Validate script data
  $("#validate_sieve").click(function( event ) {
    event.preventDefault();
    var script = $('#script_data').val();
    $.ajax({
      dataType: 'jsonp',
      url: "/inc/ajax/sieve_validation.php",
      type: "get",
      data: { script: script },
      complete: function(data) {
        var response = (data.responseText);
        response_obj = JSON.parse(response);
        if (response_obj.type == "success") {
          $('#add_filter_btns > #add_sieve_script').attr({"disabled": false});
        }
        mailcow_alert_box(response_obj.msg, response_obj.type);
      },
    });
  });
  // $(document).on('DOMNodeInserted', '#prefilter_table', function () {
    // $("#active-script").closest('td').css('background-color','#b0f0a0');
    // $("#inactive-script").closest('td').css('background-color','#b0f0a0');
  // });
  $('#addResourceModal').on('shown.bs.modal', function() {
    $("#multiple_bookings").val($("#multiple_bookings_select").val());
    if ($("#multiple_bookings").val() == "custom") {
      $("#multiple_bookings_custom_div").show();
      $("#multiple_bookings").val($("#multiple_bookings_custom").val());
    }
  })
  $("#multiple_bookings_select").change(function() {
    $("#multiple_bookings").val($("#multiple_bookings_select").val());
    if ($("#multiple_bookings").val() == "custom") {
      $("#multiple_bookings_custom_div").show();
    }
    else {
      $("#multiple_bookings_custom_div").hide();
    }
  });
  $("#multiple_bookings_custom").bind ("change keypress keyup blur", function () {
    $("#multiple_bookings").val($("#multiple_bookings_custom").val());
  });


});
jQuery(function($){
  // http://stackoverflow.com/questions/24816/escaping-html-strings-with-jquery
  var entityMap={"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;","/":"&#x2F;","`":"&#x60;","=":"&#x3D;"};
  function escapeHtml(n){return String(n).replace(/[&<>"'`=\/]/g,function(n){return entityMap[n]})}
  // http://stackoverflow.com/questions/46155/validate-email-address-in-javascript
  function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
  }
  function humanFileSize(i){if(Math.abs(i)<1024)return i+" B";var B=["KiB","MiB","GiB","TiB","PiB","EiB","ZiB","YiB"],e=-1;do{i/=1024,++e}while(Math.abs(i)>=1024&&e<B.length-1);return i.toFixed(1)+" "+B[e]}
  $(".refresh_table").on('click', function(e) {
    e.preventDefault();
    var table_name = $(this).data('table');
    $('#' + table_name).find("tr.footable-empty").remove();
    draw_table = $(this).data('draw');
    eval(draw_table + '()');
  });
  function table_mailbox_ready(ft, name) {
    if(is_dual) {
      $('.login_as').data("toggle", "tooltip")
        .attr("disabled", true)
        .removeAttr("href")
        .attr("title", "Dual login cannot be used twice")
        .tooltip();
    }
    $('.refresh_table').prop("disabled", false);
    heading = ft.$el.parents('.panel').find('.panel-heading')
    var ft_paging = ft.use(FooTable.Paging)
    $(heading).children('.table-lines').text(function(){
      return ft_paging.totalRows;
    })
  }
  function draw_domain_table() {
    ft_domain_table = FooTable.init('#domain_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px"},"filterable": false,"sortable": false,"type":"html"},
        {"sorted": true,"name":"domain_name","title":lang.domain,"style":{"width":"250px"}},
        {"name":"aliases","title":lang.aliases,"breakpoints":"xs sm"},
        {"name":"mailboxes","title":lang.mailboxes},
        {"name":"quota","style":{"whiteSpace":"nowrap"},"title":lang.domain_quota,"formatter": function(value){
          res = value.split("/");
          return humanFileSize(res[0]) + " / " + humanFileSize(res[1]);
        },
        "sortValue": function(value){
          res = value.split("/");
          return Number(res[0]);
        },
        },
        {"name":"def_quota_for_mbox","title":lang.mailbox_defquota,"breakpoints":"xs sm md","style":{"width":"125px"}},
        {"name":"max_quota_for_mbox","title":lang.mailbox_quota,"breakpoints":"xs sm","style":{"width":"125px"}},
        {"name":"rl","title":"RL","breakpoints":"xs sm md lg","style":{"maxWidth":"100px","width":"100px"}},
        {"name":"backupmx","filterable": false,"style":{"maxWidth":"120px","width":"120px"},"title":lang.backup_mx,"breakpoints":"xs sm md lg"},
        {"name":"active","filterable": false,"style":{"maxWidth":"80px","width":"80px"},"title":lang.active},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"240px","width":"240px"},"type":"html","title":lang.action,"breakpoints":"xs sm md"}
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/domain/all',
        jsonp: false,
        error: function (data) {
          console.log('Cannot draw domain table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            item.aliases = item.aliases_in_domain + " / " + item.max_num_aliases_for_domain;
            item.mailboxes = item.mboxes_in_domain + " / " + item.max_num_mboxes_for_domain;
            item.quota = item.quota_used_in_domain + "/" + item.max_quota_for_domain;
            if (!item.rl) {
              item.rl = '∞';
            } else {
              item.rl = $.map(item.rl, function(e){
                return e;
              }).join('/1');
            }
            item.def_quota_for_mbox = humanFileSize(item.def_quota_for_mbox);
            item.max_quota_for_mbox = humanFileSize(item.max_quota_for_mbox);
            item.chkbox = '<input type="checkbox" data-id="domain" name="multi_select" value="' + encodeURIComponent(item.domain_name) + '" />';
            item.action = '<div class="btn-group">';
            if (role == "admin") {
              item.action += '<a href="/edit/domain/' + encodeURIComponent(item.domain_name) + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
                '<a href="#" data-action="delete_selected" data-id="single-domain" data-api-url="delete/domain" data-item="' + encodeURIComponent(item.domain_name) + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>';
            }
            else {
              item.action += '<a href="/edit/domain/' + encodeURIComponent(item.domain_name) + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>';
            }
            item.action += '<a href="#dnsInfoModal" class="btn btn-xs btn-info" data-toggle="modal" data-domain="' + encodeURIComponent(item.domain_name) + '"><span class="glyphicon glyphicon-question-sign"></span> DNS</a></div>';
          });
        }
      }),
      "empty": lang.empty,
      "paging": {
        "enabled": true,
        "limit": 5,
        "size": pagination_size
      },
      "state": {
        "enabled": true
      },
      "filtering": {
        "enabled": true,
        "delay": 1200,
        "position": "left",
        "connectors": false,
        "placeholder": lang.filter_table
      },
      "sorting": {
        "enabled": true
      },
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_mailbox_ready(ft, 'domain_table');
        },
        "after.ft.filtering": function(e, ft){
          table_mailbox_ready(ft, 'domain_table');
        }
      },
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }
  function draw_mailbox_table() {
    ft_mailbox_table = FooTable.init('#mailbox_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px"},"filterable": false,"sortable": false,"type":"html"},
        {"sorted": true,"name":"username","style":{"word-break":"break-all","min-width":"120px"},"title":lang.username},
        {"name":"name","title":lang.fname,"style":{"word-break":"break-all","min-width":"120px"},"breakpoints":"xs sm md lg"},
        {"name":"domain","title":lang.domain,"breakpoints":"xs sm md lg"},
        {"name":"quota","style":{"whiteSpace":"nowrap"},"title":lang.domain_quota,"formatter": function(value){
          res = value.split("/");
          var of_q = (res[1] == 0 ? "∞" : humanFileSize(res[1]));
          return humanFileSize(res[0]) + " / " + of_q;
        },
        "sortValue": function(value){
          res = value.split("/");
          return Number(res[0]);
        },
        },
        {"name":"spam_aliases","filterable": false,"title":lang.spam_aliases,"breakpoints":"all"},
        {"name":"tls_enforce_in","filterable": false,"title":lang.tls_enforce_in,"breakpoints":"all"},
        {"name":"tls_enforce_out","filterable": false,"title":lang.tls_enforce_out,"breakpoints":"all"},
        {"name":"quarantine_notification","filterable": false,"title":lang.quarantine_notification,"breakpoints":"all"},
        {"name":"in_use","filterable": false,"type":"html","title":lang.in_use,"sortValue": function(value){
          return Number($(value).find(".progress-bar").attr('aria-valuenow'));
        },
        },
        {"name":"messages","filterable": false,"title":lang.msg_num,"breakpoints":"xs sm md"},
        {"name":"rl","title":"RL","breakpoints":"all","style":{"width":"125px"}},
        {"name":"active","filterable": false,"title":lang.active},
        {"name":"action","filterable": false,"sortable": false,"style":{"min-width":"290px","text-align":"right"},"type":"html","title":lang.action,"breakpoints":"xs sm md"}
      ],
      "empty": lang.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/mailbox/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw mailbox table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            item.quota = item.quota_used + "/" + item.quota;
            item.max_quota_for_mbox = humanFileSize(item.max_quota_for_mbox);
            if (!item.rl) {
              item.rl = '∞';
            } else {
              item.rl = $.map(item.rl, function(e){
                return e;
              }).join('/1');
              if (item.rl_scope === 'domain') {
                item.rl = '↪ ' + item.rl + ' (via ' + item.domain + ')';
              }
            }
            item.chkbox = '<input type="checkbox" data-id="mailbox" name="multi_select" value="' + encodeURIComponent(item.username) + '" />';
            item.tls_enforce_in = '<span class="text-' + (item.attributes.tls_enforce_in == 1 ? 'success' : 'danger') + ' glyphicon glyphicon-lock"></span>';
            item.tls_enforce_out = '<span class="text-' + (item.attributes.tls_enforce_out == 1 ? 'success' : 'danger') + ' glyphicon glyphicon-lock"></span>';
            if (item.attributes.quarantine_notification === 'never') {
              item.quarantine_notification = lang.never;
            } else if (item.attributes.quarantine_notification === 'hourly') {
              item.quarantine_notification = lang.hourly;
            } else if (item.attributes.quarantine_notification === 'daily') {
              item.quarantine_notification = lang.daily;
            } else if (item.attributes.quarantine_notification === 'weekly') {
              item.quarantine_notification = lang.weekly;
            }
            if (acl_data.login_as === 1) {
            item.action = '<div class="btn-group">' +
              '<a href="/edit/mailbox/' + encodeURIComponent(item.username) + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
              '<a href="#" data-action="delete_selected" data-id="single-mailbox" data-api-url="delete/mailbox" data-item="' + encodeURIComponent(item.username) + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
              '<a href="/index.php?duallogin=' + encodeURIComponent(item.username) + '" class="login_as btn btn-xs btn-success"><span class="glyphicon glyphicon-user"></span> Login</a>';
              if (ALLOW_ADMIN_EMAIL_LOGIN) {
                item.action += '<a href="/sogo-auth.php?login=' + encodeURIComponent(item.username) + '" class="login_as btn btn-xs btn-primary" target="_blank"><span class="glyphicon glyphicon-envelope"></span> SOGo</a>';
              }
              item.action += '</div>';
            }
            else {
            item.action = '<div class="btn-group">' +
              '<a href="/edit/mailbox/' + encodeURIComponent(item.username) + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
              '<a href="#" data-action="delete_selected" data-id="single-mailbox" data-api-url="delete/mailbox" data-item="' + encodeURIComponent(item.username) + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
              '</div>';
            }
            item.in_use = '<div class="progress">' +
              '<div class="progress-bar progress-bar-' + item.percent_class + ' role="progressbar" aria-valuenow="' + item.percent_in_use + '" aria-valuemin="0" aria-valuemax="100" ' +
              'style="min-width:2em;width:' + item.percent_in_use + '%">' + item.percent_in_use + '%' + '</div></div>';
            item.username = escapeHtml(item.username);
          });
        }
      }),
      "paging": {
        "enabled": true,
        "limit": 5,
        "size": pagination_size
      },
      "state": {
        "enabled": true
      },
      "filtering": {
        "enabled": true,
        "delay": 1200,
        "position": "left",
        "connectors": false,
        //"container": "#tab-mailboxes.panel",
        "placeholder": lang.filter_table
      },
      "components": {
        "filtering": FooTable.domainFilter
      },
      "sorting": {
        "enabled": true
      },
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_mailbox_ready(ft, 'mailbox_table');
        },
        "after.ft.filtering": function(e, ft){
          table_mailbox_ready(ft, 'mailbox_table');
        }
      },
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }
  function draw_resource_table() {
    ft_resource_table = FooTable.init('#resource_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px"},"filterable": false,"sortable": false,"type":"html"},
        {"sorted": true,"name":"description","title":lang.description,"style":{"width":"250px"}},
        {"name":"name","title":lang.alias},
        {"name":"kind","title":lang.kind},
        {"name":"domain","title":lang.domain,"breakpoints":"xs sm"},
        {"name":"multiple_bookings","filterable": false,"style":{"maxWidth":"150px","width":"140px"},"title":lang.multiple_bookings,"breakpoints":"xs sm"},
        {"name":"active","filterable": false,"style":{"maxWidth":"80px","width":"80px"},"title":lang.active},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"180px","width":"180px"},"type":"html","title":lang.action,"breakpoints":"xs sm"}
      ],
      "empty": lang.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/resource/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw resource table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            if (item.multiple_bookings == '0') {
              item.multiple_bookings = '<span id="active-script" class="label label-success">' + lang.booking_0_short + '</span>';
            } else if (item.multiple_bookings == '-1') {
              item.multiple_bookings = '<span id="active-script" class="label label-warning">' + lang.booking_lt0_short + '</span>';
            } else {
              item.multiple_bookings = '<span id="active-script" class="label label-danger">' + lang.booking_custom_short + ' (' + item.multiple_bookings + ')</span>';
            }
            item.action = '<div class="btn-group">' +
              '<a href="/edit/resource/' + encodeURIComponent(item.name) + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
              '<a href="#" data-action="delete_selected" data-id="single-resource" data-api-url="delete/resource" data-item="' + item.name + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
              '</div>';
            item.chkbox = '<input type="checkbox" data-id="resource" name="multi_select" value="' + encodeURIComponent(item.name) + '" />';
            item.name = escapeHtml(item.name);
          });
        }
      }),
      "paging": {
        "enabled": true,
        "limit": 5,
        "size": pagination_size
      },
      "state": {
        "enabled": true
      },
      "filtering": {
        "enabled": true,
        "delay": 1200,
        "position": "left",
        "connectors": false,
        "placeholder": lang.filter_table
      },
      "components": {
        "filtering": FooTable.domainFilter
      },
      "sorting": {
        "enabled": true
      },
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_mailbox_ready(ft, 'resource_table');
        },
        "after.ft.filtering": function(e, ft){
          table_mailbox_ready(ft, 'resource_table');
        }
      },
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }
  function draw_bcc_table() {
    ft_bcc_table = FooTable.init('#bcc_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px"},"filterable": false,"sortable": false,"type":"html"},
        {"sorted": true,"name":"id","title":"ID","style":{"maxWidth":"60px","width":"60px","text-align":"center"}},
        {"name":"type","title":lang.bcc_type},
        {"name":"local_dest","title":lang.bcc_local_dest},
        {"name":"bcc_dest","title":lang.bcc_destinations},
        {"name":"domain","title":lang.domain,"breakpoints":"xs sm"},
        {"name":"active","filterable": false,"style":{"maxWidth":"80px","width":"80px"},"title":lang.active},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"180px","width":"180px"},"type":"html","title":lang.action,"breakpoints":"xs sm"}
      ],
      "empty": lang.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/bcc/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw bcc table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            item.action = '<div class="btn-group">' +
              '<a href="/edit/bcc/' + item.id + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
              '<a href="#" data-action="delete_selected" data-id="single-bcc" data-api-url="delete/bcc" data-item="' + item.id + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
              '</div>';
            item.chkbox = '<input type="checkbox" data-id="bcc" name="multi_select" value="' + item.id + '" />';
            item.local_dest = escapeHtml(item.local_dest);
            item.bcc_dest = escapeHtml(item.bcc_dest);
            if (item.type == 'sender') {
              item.type = '<span id="active-script" class="label label-success">Sender</span>';
            } else {
              item.type = '<span id="inactive-script" class="label label-warning">Recipient</span>';
            }
          });
        }
      }),
      "paging": {
        "enabled": true,
        "limit": 5,
        "size": pagination_size
      },
      "state": {
        "enabled": true
      },
      "filtering": {
        "enabled": true,
        "delay": 1200,
        "position": "left",
        "connectors": false,
        "placeholder": lang.filter_table
      },
      "sorting": {
        "enabled": true
      },
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_mailbox_ready(ft, 'bcc_table');
        },
        "after.ft.filtering": function(e, ft){
          table_mailbox_ready(ft, 'bcc_table');
        }
      },
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }
  function draw_recipient_map_table() {
    ft_recipient_map_table = FooTable.init('#recipient_map_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px"},"filterable": false,"sortable": false,"type":"html"},
        {"sorted": true,"name":"id","title":"ID","style":{"maxWidth":"60px","width":"60px","text-align":"center"}},
        {"name":"recipient_map_old","title":lang.recipient_map_old},
        {"name":"recipient_map_new","title":lang.recipient_map_new},
        {"name":"active","filterable": false,"style":{"maxWidth":"80px","width":"80px"},"title":lang.active},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"180px","width":"180px"},"type":"html","title":(role == "admin" ? lang.action : ""),"breakpoints":"xs sm"}
      ],
      "empty": lang.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/recipient_map/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw recipient map table');
        },
        success: function (data) {
          if (role == "admin") {
            $.each(data, function (i, item) {
              item.recipient_map_old = escapeHtml(item.recipient_map_old);
              item.recipient_map_new = escapeHtml(item.recipient_map_new);
              item.action = '<div class="btn-group">' +
                '<a href="/edit/recipient_map/' + item.id + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
                '<a href="#" data-action="delete_selected" data-id="single-recipient_map" data-api-url="delete/recipient_map" data-item="' + item.id + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
                '</div>';
              item.chkbox = '<input type="checkbox" data-id="recipient_map" name="multi_select" value="' + item.id + '" />';
            });
          }
        }
      }),
      "paging": {
        "enabled": true,
        "limit": 5,
        "size": pagination_size
      },
      "state": {
        "enabled": true
      },
      "filtering": {
        "enabled": true,
        "delay": 1200,
        "position": "left",
        "connectors": false,
        "placeholder": lang.filter_table
      },
      "sorting": {
        "enabled": true
      },
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_mailbox_ready(ft, 'recipient_map_table');
        },
        "after.ft.filtering": function(e, ft){
          table_mailbox_ready(ft, 'recipient_map_table');
        }
      },
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }
  function draw_tls_policy_table() {
    ft_tls_policy_table = FooTable.init('#tls_policy_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px"},"filterable": false,"sortable": false,"type":"html"},
        {"sorted": true,"name":"id","title":"ID","style":{"maxWidth":"60px","width":"60px","text-align":"center"}},
        {"name":"dest","title":lang.tls_map_dest},
        {"name":"policy","title":lang.tls_map_policy},
        {"name":"parameters","title":lang.tls_map_parameters},
        {"name":"active","filterable": false,"style":{"maxWidth":"80px","width":"80px"},"title":lang.active},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"180px","width":"180px"},"type":"html","title":(role == "admin" ? lang.action : ""),"breakpoints":"xs sm"}
      ],
      "empty": lang.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/tls-policy-map/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw tls policy map table');
        },
        success: function (data) {
          if (role == "admin") {
            $.each(data, function (i, item) {
              item.dest = escapeHtml(item.dest);
              item.policy = '<b>' + escapeHtml(item.policy) + '</b>';
              if (item.parameters == '') {
                item.parameters = '<code>-</code>';
              } else {
                item.parameters = '<code>' + escapeHtml(item.parameters) + '</code>';
              }
              item.action = '<div class="btn-group">' +
                '<a href="/edit/tls_policy_map/' + item.id + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
                '<a href="#" data-action="delete_selected" data-id="single-tls-policy-map" data-api-url="delete/tls-policy-map" data-item="' + item.id + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
                '</div>';
              item.chkbox = '<input type="checkbox" data-id="tls-policy-map" name="multi_select" value="' + item.id + '" />';
            });
          }
        }
      }),
      "paging": {
        "enabled": true,
        "limit": 5,
        "size": pagination_size
      },
      "state": {
        "enabled": true
      },
      "filtering": {
        "enabled": true,
        "delay": 1200,
        "position": "left",
        "connectors": false,
        "placeholder": lang.filter_table
      },
      "sorting": {
        "enabled": true
      },
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_mailbox_ready(ft, 'tls_policy_table');
        },
        "after.ft.filtering": function(e, ft){
          table_mailbox_ready(ft, 'tls_policy_table');
        }
      },
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }
  function draw_alias_table() {
    ft_alias_table = FooTable.init('#alias_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px"},"filterable": false,"sortable": false,"type":"html"},
        {"name":"id","title":"ID","style":{"maxWidth":"60px","width":"60px","text-align":"center"}},
        {"sorted": true,"name":"address","title":lang.alias,"style":{"width":"250px"}},
        {"name":"goto","title":lang.target_address},
        {"name":"domain","title":lang.domain,"breakpoints":"xs sm"},
        {"name":"public_comment","title":lang.public_comment,"breakpoints":"all"},
        {"name":"private_comment","title":lang.private_comment,"breakpoints":"all"},
        {"name":"sogo_visible","title":lang.sogo_visible,"breakpoints":"all"},
        {"name":"active","filterable": false,"style":{"maxWidth":"50px","width":"70px"},"title":lang.active},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"180px","width":"180px"},"type":"html","title":lang.action,"breakpoints":"xs sm"}
      ],
      "empty": lang.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/alias/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw alias table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            item.action = '<div class="btn-group">' +
              '<a href="/edit/alias/' + encodeURIComponent(item.id) + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
              '<a href="#" data-action="delete_selected" data-id="single-alias" data-api-url="delete/alias" data-item="' + encodeURIComponent(item.id) + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
              '</div>';
            item.chkbox = '<input type="checkbox" data-id="alias" name="multi_select" value="' + encodeURIComponent(item.id) + '" />';
            item.goto = escapeHtml(item.goto.replace(/,/g, " "));
            if (item.public_comment !== null) {
              item.public_comment = escapeHtml(item.public_comment);
            }
            else {
              item.public_comment = '-';
            }
            if (item.private_comment !== null) {
              item.private_comment = escapeHtml(item.private_comment);
            }
            else {
              item.private_comment = '-';
            }
            if (item.is_catch_all == 1) {
              item.address = '<div class="label label-default">Catch-All</div> ' + escapeHtml(item.address);
            }
            else {
              item.address = escapeHtml(item.address);
            }
            if (item.goto == "null@localhost") {
              item.goto = '⤷ <span style="font-size:12px" class="glyphicon glyphicon-trash" aria-hidden="true"></span>';
            }
            else if (item.goto == "spam@localhost") {
              item.goto = '<span class="label label-danger">Learn as spam</span>';
            }
            else if (item.goto == "ham@localhost") {
              item.goto = '<span class="label label-success">Learn as ham</span>';
            }
            if (item.in_primary_domain !== "") {
              item.domain = "↳ " + item.domain + " (" + item.in_primary_domain + ")";
            }
          });
        }
      }),
      "paging": {
        "enabled": true,
        "limit": 5,
        "size": pagination_size
      },
      "state": {
        "enabled": true
      },
      "filtering": {
        "enabled": true,
        "delay": 1200,
        "position": "left",
        "connectors": false,
        "placeholder": lang.filter_table
      },
      "components": {
        "filtering": FooTable.domainFilter
      },
      "sorting": {
        "enabled": true
      },
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_mailbox_ready(ft, 'alias_table');
        },
        "after.ft.filtering": function(e, ft){
          table_mailbox_ready(ft, 'alias_table');
        }
      },
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }

  function draw_aliasdomain_table() {
    ft_aliasdomain_table = FooTable.init('#aliasdomain_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px"},"filterable": false,"sortable": false,"type":"html"},
        {"sorted": true,"name":"alias_domain","title":lang.alias,"style":{"width":"250px"}},
        {"name":"target_domain","title":lang.target_domain,"type":"html"},
        {"name":"active","filterable": false,"style":{"maxWidth":"50px","width":"70px"},"title":lang.active},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"250px","width":"250px"},"type":"html","title":lang.action,"breakpoints":"xs sm"}
      ],
      "empty": lang.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/alias-domain/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw alias domain table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            item.action = '<div class="btn-group">' +
              '<a href="/edit/aliasdomain/' + encodeURIComponent(item.alias_domain) + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
              '<a href="#" data-action="delete_selected" data-id="single-alias-domain" data-api-url="delete/alias-domain" data-item="' + encodeURIComponent(item.alias_domain) + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
              '<a href="#dnsInfoModal" class="btn btn-xs btn-info" data-toggle="modal" data-domain="' + encodeURIComponent(item.alias_domain) + '"><span class="glyphicon glyphicon-question-sign"></span> DNS</a></div>' +
              '</div>';
            item.chkbox = '<input type="checkbox" data-id="alias-domain" name="multi_select" value="' + encodeURIComponent(item.alias_domain) + '" />';
            if(item.parent_is_backupmx == '1') {
              item.target_domain = '<span><a href="/edit/domain/' + item.target_domain + '">' + item.target_domain + '</a> <div class="label label-warning">' + lang.alias_domain_backupmx + '</div></span>';
            } else {
              item.target_domain = '<span><a href="/edit/domain/' + item.target_domain + '">' + item.target_domain + '</a></span>';
            }
          });
        }
      }),
      "paging": {
        "enabled": true,
        "limit": 5,
        "size": pagination_size
      },
      "state": {
        "enabled": true
      },
      "filtering": {
        "enabled": true,
        "delay": 1200,
        "position": "left",
        "connectors": false,
        "placeholder": lang.filter_table
      },
      "sorting": {
        "enabled": true
      },
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_mailbox_ready(ft, 'aliasdomain_table');
        },
        "after.ft.filtering": function(e, ft){
          table_mailbox_ready(ft, 'aliasdomain_table');
        }
      },
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }

  function draw_sync_job_table() {
    ft_syncjob_table = FooTable.init('#sync_job_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px","text-align":"center"},"filterable": false,"sortable": false,"type":"html"},
        {"sorted": true,"name":"id","title":"ID","style":{"maxWidth":"60px","width":"60px","text-align":"center"}},
        {"name":"user2","title":lang.owner},
        {"name":"server_w_port","title":"Server","breakpoints":"xs sm md","style":{"word-break":"break-all"}},
        {"name":"exclude","title":lang.excludes,"breakpoints":"all"},
        {"name":"mins_interval","title":lang.mins_interval,"breakpoints":"all"},
        {"name":"last_run","title":lang.last_run,"breakpoints":"xs sm md"},
        {"name":"log","title":"Log"},
        {"name":"active","filterable": false,"style":{"maxWidth":"70px","width":"70px"},"title":lang.active},
        {"name":"is_running","filterable": false,"style":{"maxWidth":"120px","width":"100px"},"title":lang.status},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"180px","width":"180px"},"type":"html","title":lang.action,"breakpoints":"xs sm"}
      ],
      "empty": lang.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/syncjobs/all/no_log',
        jsonp: false,
        error: function () {
          console.log('Cannot draw sync job table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            item.log = '<a href="#syncjobLogModal" data-toggle="modal" data-syncjob-id="' + encodeURIComponent(item.id) + '">Open logs</a>'
            item.user2 = escapeHtml(item.user2);
            if (!item.exclude > 0) {
              item.exclude = '-';
            } else {
              item.exclude  = '<code>' + item.exclude + '</code>';
            }
            item.server_w_port = escapeHtml(item.user1) + '@' + item.host1 + ':' + item.port1;
            item.action = '<div class="btn-group">' +
              '<a href="/edit/syncjob/' + item.id + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
              '<a href="#" data-action="delete_selected" data-id="single-syncjob" data-api-url="delete/syncjob" data-item="' + item.id + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
              '</div>';
            item.chkbox = '<input type="checkbox" data-id="syncjob" name="multi_select" value="' + item.id + '" />';
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
      "state": {
        "enabled": true
      },
      "filtering": {
        "enabled": true,
        "delay": 1200,
        "position": "left",
        "connectors": false,
        "placeholder": lang.filter_table
      },
      "sorting": {
        "enabled": true
      },
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_mailbox_ready(ft, 'sync_job_table');
        },
        "after.ft.filtering": function(e, ft){
          table_mailbox_ready(ft, 'sync_job_table');
        }
      },
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }

  function draw_filter_table() {
    ft_filter_table = FooTable.init('#filter_table', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px","text-align":"center"},"filterable": false,"sortable": false,"type":"html"},
        {"name":"id","title":"ID","style":{"maxWidth":"60px","width":"60px","text-align":"center"}},
        {"name":"active","style":{"maxWidth":"80px","width":"80px"},"title":lang.active},
        {"name":"filter_type","style":{"maxWidth":"80px","width":"80px"},"title":"Type"},
        {"sorted": true,"name":"username","title":lang.owner,"style":{"maxWidth":"550px","width":"350px"}},
        {"name":"script_desc","title":lang.description,"breakpoints":"xs"},
        {"name":"script_data","title":"Script","breakpoints":"all"},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right","maxWidth":"180px","width":"180px"},"type":"html","title":lang.action,"breakpoints":"xs sm"}
      ],
      "empty": lang.empty,
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/filters/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw filter table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            if (item.active_int == 1) {
              item.active = '<span id="active-script" class="label label-success">' + lang.active + '</span>';
            } else {
              item.active = '<span id="inactive-script" class="label label-warning">' + lang.inactive + '</span>';
            }
            item.script_data = '<pre style="margin:0px">' + escapeHtml(item.script_data) + '</pre>'
            item.filter_type = '<div class="label label-default">' + item.filter_type.charAt(0).toUpperCase() + item.filter_type.slice(1).toLowerCase() + '</div>'
            item.action = '<div class="btn-group">' +
              '<a href="/edit/filter/' + item.id + '" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span> ' + lang.edit + '</a>' +
              '<a href="#" data-action="delete_selected" data-id="single-filter" data-api-url="delete/filter" data-item="' + encodeURIComponent(item.id) + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
              '</div>';
            item.chkbox = '<input type="checkbox" data-id="filter_item" name="multi_select" value="' + item.id + '" />'
          });
        }
      }),
      "paging": {
        "enabled": true,
        "limit": 5,
        "size": pagination_size
      },
      "state": {
        "enabled": true
      },
      "filtering": {
        "enabled": true,
        "delay": 1200,
        "position": "left",
        "connectors": false,
        "placeholder": lang.filter_table
      },
      "sorting": {
        "enabled": true
      },
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_mailbox_ready(ft, 'filter_table');
        },
        "after.ft.filtering": function(e, ft){
          table_mailbox_ready(ft, 'filter_table');
        }
      },
      "toggleSelector": "table tbody span.footable-toggle"
    });
  };

  $('body').on('click', 'span.footable-toggle', function () {
    event.stopPropagation();
  })

  draw_domain_table();
  draw_mailbox_table();
  draw_resource_table();
  draw_alias_table();
  draw_aliasdomain_table();
  draw_sync_job_table();
  draw_filter_table();
  draw_bcc_table();
  draw_recipient_map_table();
  draw_tls_policy_table();

});
