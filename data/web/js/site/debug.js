jQuery(function($){
  if (localStorage.getItem("current_page") === null) {
    var current_page = {};
  } else {
    var current_page = JSON.parse(localStorage.getItem('current_page'));
  }
  // http://stackoverflow.com/questions/24816/escaping-html-strings-with-jquery
  var entityMap={"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;","/":"&#x2F;","`":"&#x60;","=":"&#x3D;"};
  function escapeHtml(n){return String(n).replace(/[&<>"'`=\/]/g,function(n){return entityMap[n]})}
  function humanFileSize(i){if(Math.abs(i)<1024)return i+" B";var B=["KiB","MiB","GiB","TiB","PiB","EiB","ZiB","YiB"],e=-1;do{i/=1024,++e}while(Math.abs(i)>=1024&&e<B.length-1);return i.toFixed(1)+" "+B[e]}
  function hashCode(t){for(var n=0,r=0;r<t.length;r++)n=t.charCodeAt(r)+((n<<5)-n);return n}
  function intToRGB(t){var n=(16777215&t).toString(16).toUpperCase();return"00000".substring(0,6-n.length)+n}
  $(".refresh_table").on('click', function(e) {
    e.preventDefault();
    var table_name = $(this).data('table');
    $('#' + table_name).find("tr.footable-empty").remove();
    draw_table = $(this).data('draw');
    eval(draw_table + '()');
  });
  function table_log_ready(ft, name) {
    heading = ft.$el.parents('.panel').find('.panel-heading')
    var ft_paging = ft.use(FooTable.Paging)
    $('.refresh_table').prop("disabled", false);
    $(heading).children('.table-lines').text(function(){
      return ft_paging.totalRows;
    })
    if (current_page[name]) {
      ft_paging.goto(parseInt(current_page[name]))
    }
  }
  function table_log_paging(ft, name) {
    var ft_paging = ft.use(FooTable.Paging)
    current_page[name] = ft_paging.current;
    localStorage.setItem('current_page', JSON.stringify(current_page));
  }
  function draw_autodiscover_logs() {
    ft_autodiscover_logs = FooTable.init('#autodiscover_log', {
      "columns": [
        {"name":"time","formatter":function unix_time_format(tm) { var date = new Date(tm ? tm * 1000 : 0); return date.toLocaleString();},"title":lang.time,"style":{"width":"170px"}},
        {"name":"ua","title":"User-Agent","style":{"min-width":"200px"}},
        {"name":"user","title":"Username","style":{"min-width":"200px"}},
        {"name":"service","title":"Service"},
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/logs/autodiscover/100',
        jsonp: false,
        error: function () {
          console.log('Cannot draw autodiscover log table');
        },
        success: function (data) {
          return process_table_data(data, 'autodiscover_log');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "filtering": {"enabled": true,"delay": 1200,"position": "left","placeholder": lang.filter_table},
      "sorting": {"enabled": true},
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_log_ready(ft, 'autodiscover_logs');
        },
        "after.ft.paging": function(e, ft){
          table_log_paging(ft, 'autodiscover_logs');
        }
      }
    });
  }
  function draw_postfix_logs() {
    ft_postfix_logs = FooTable.init('#postfix_log', {
      "columns": [
        {"name":"time","formatter":function unix_time_format(tm) { var date = new Date(tm ? tm * 1000 : 0); return date.toLocaleString();},"title":lang.time,"style":{"width":"170px"}},
        {"name":"priority","title":lang.priority,"style":{"width":"80px"}},
        {"name":"message","title":lang.message},
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/logs/postfix',
        jsonp: false,
        error: function () {
          console.log('Cannot draw postfix log table');
        },
        success: function (data) {
          return process_table_data(data, 'general_syslog');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "filtering": {"enabled": true,"delay": 1200,"position": "left","placeholder": lang.filter_table},
      "sorting": {"enabled": true},
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_log_ready(ft, 'postfix_logs');
        },
        "after.ft.paging": function(e, ft){
          table_log_paging(ft, 'postfix_logs');
        }
      }
    });
  }
  function draw_watchdog_logs() {
    ft_watchdog_logs = FooTable.init('#watchdog_log', {
      "columns": [
        {"name":"time","formatter":function unix_time_format(tm) { var date = new Date(tm ? tm * 1000 : 0); return date.toLocaleString();},"title":lang.time,"style":{"width":"170px"}},
        {"name":"service","title":"Service"},
        {"name":"trend","title":"Trend"},
        {"name":"message","title":lang.message},
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/logs/watchdog',
        jsonp: false,
        error: function () {
          console.log('Cannot draw watchdog log table');
        },
        success: function (data) {
          return process_table_data(data, 'watchdog');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "filtering": {"enabled": true,"delay": 1200,"position": "left","connectors": false,"placeholder": lang.filter_table},
      "sorting": {"enabled": true},
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_log_ready(ft, 'postfix_logs');
        },
        "after.ft.paging": function(e, ft){
          table_log_paging(ft, 'postfix_logs');
        }
      }
    });
  }
  function draw_api_logs() {
    ft_api_logs = FooTable.init('#api_log', {
      "columns": [
        {"name":"time","formatter":function unix_time_format(tm) { var date = new Date(tm ? tm * 1000 : 0); return date.toLocaleString();},"title":lang.time,"style":{"width":"170px"}},
        {"name":"uri","title":"URI","style":{"width":"310px"}},
        {"name":"method","title":"Method","style":{"width":"80px"}},
        {"name":"remote","title":"IP","style":{"width":"80px"}},
        {"name":"data","title":"Data","breakpoints": "all","style":{"word-break":"break-all"}},
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/logs/api',
        jsonp: false,
        error: function () {
          console.log('Cannot draw api log table');
        },
        success: function (data) {
          return process_table_data(data, 'apilog');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "filtering": {"enabled": true,"delay": 1200,"position": "left","connectors": false,"placeholder": lang.filter_table},
      "sorting": {"enabled": true},
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_log_ready(ft, 'api_logs');
        },
        "after.ft.paging": function(e, ft){
          table_log_paging(ft, 'api_logs');
        }
      }
    });
  }
  function draw_rl_logs() {
    ft_rl_logs = FooTable.init('#rl_log', {
      "columns": [
        {"name":"indicator","title":" ","style":{"width":"50px"}},
        {"name":"time","formatter":function unix_time_format(tm) { var date = new Date(tm ? tm * 1000 : 0); return date.toLocaleString();},"title":lang.last_applied,"style":{"width":"170px"}},
        {"name":"rl_name","title":lang.rate_name},
        {"name":"from","title":lang.sender},
        {"name":"rcpt","title":lang.recipients},
        {"name":"user","title":lang.authed_user},
        {"name":"message_id","title":"Msg ID","breakpoints": "all","style":{"word-break":"break-all"}},
        {"name":"header_from","title":"Header From","breakpoints": "all","style":{"word-break":"break-all"}},
        {"name":"header_subject","title":"Subject","breakpoints": "all","style":{"word-break":"break-all"}},
        {"name":"rl_hash","title":"Hash","breakpoints": "all","style":{"word-break":"break-all"}},
        {"name":"qid","title":"Rspamd QID","breakpoints": "all","style":{"word-break":"break-all"}},
        {"name":"ip","title":"IP","breakpoints": "all","style":{"word-break":"break-all"}},
        {"name":"action","title":lang.action,"breakpoints": "all","style":{"word-break":"break-all"}},
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/logs/ratelimited',
        jsonp: false,
        error: function () {
          console.log('Cannot draw rl log table');
        },
        success: function (data) {
          return process_table_data(data, 'rllog');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "filtering": {"enabled": true,"delay": 1200,"position": "left","connectors": false,"placeholder": lang.filter_table},
      "sorting": {"enabled": true},
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_log_ready(ft, 'rl_logs');
        },
        "after.ft.paging": function(e, ft){
          table_log_paging(ft, 'rl_logs');
        }
      }
    });
  }
  function draw_ui_logs() {
    ft_api_logs = FooTable.init('#ui_logs', {
      "columns": [
        {"name":"time","formatter":function unix_time_format(tm) { var date = new Date(tm ? tm * 1000 : 0); return date.toLocaleString();},"title":lang.time,"style":{"width":"170px"}},
        {"name":"type","title":"Type"},
        {"name":"task","title":"Task"},
        {"name":"user","title":"User"},
        {"name":"role","title":"Role"},
        {"name":"remote","title":"IP"},
        {"name":"msg","title":lang.message,"style":{"word-break":"break-all"}},
        {"name":"call","title":"Call","breakpoints": "all"},
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/logs/ui',
        jsonp: false,
        error: function () {
          console.log('Cannot draw ui log table');
        },
        success: function (data) {
          return process_table_data(data, 'mailcow_ui');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "filtering": {"enabled": true,"delay": 1200,"position": "left","connectors": false,"placeholder": lang.filter_table},
      "sorting": {"enabled": true},
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_log_ready(ft, 'ui_logs');
        },
        "after.ft.paging": function(e, ft){
          table_log_paging(ft, 'ui_logs');
        }
      }
    });
  }
  function draw_acme_logs() {
    ft_acme_logs = FooTable.init('#acme_log', {
      "columns": [
        {"name":"time","formatter":function unix_time_format(tm) { var date = new Date(tm ? tm * 1000 : 0); return date.toLocaleString();},"title":lang.time,"style":{"width":"170px"}},
        {"name":"message","title":lang.message,"style":{"word-break":"break-all"}},
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/logs/acme',
        jsonp: false,
        error: function () {
          console.log('Cannot draw acme log table');
        },
        success: function (data) {
          return process_table_data(data, 'general_syslog');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "filtering": {"enabled": true,"delay": 1200,"position": "left","connectors": false,"placeholder": lang.filter_table},
      "sorting": {"enabled": true},
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_log_ready(ft, 'acme_logs');
        },
        "after.ft.paging": function(e, ft){
          table_log_paging(ft, 'acme_logs');
        }
      }
    });
  }
  function draw_netfilter_logs() {
    ft_netfilter_logs = FooTable.init('#netfilter_log', {
      "columns": [
        {"name":"time","formatter":function unix_time_format(tm) { var date = new Date(tm ? tm * 1000 : 0); return date.toLocaleString();},"title":lang.time,"style":{"width":"170px"}},
        {"name":"priority","title":lang.priority,"style":{"width":"80px"}},
        {"name":"message","title":lang.message},
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/logs/netfilter',
        jsonp: false,
        error: function () {
          console.log('Cannot draw netfilter log table');
        },
        success: function (data) {
          return process_table_data(data, 'general_syslog');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "filtering": {"enabled": true,"delay": 1200,"position": "left","connectors": false,"placeholder": lang.filter_table},
      "sorting": {"enabled": true},
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_log_ready(ft, 'netfilter_logs');
        },
        "after.ft.paging": function(e, ft){
          table_log_paging(ft, 'netfilter_logs');
        }
      }
    });
  }
  function draw_sogo_logs() {
    ft_sogo_logs = FooTable.init('#sogo_log', {
      "columns": [
        {"name":"time","formatter":function unix_time_format(tm) { var date = new Date(tm ? tm * 1000 : 0); return date.toLocaleString();},"title":lang.time,"style":{"width":"170px"}},
        {"name":"priority","title":lang.priority,"style":{"width":"80px"}},
        {"name":"message","title":lang.message},
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/logs/sogo',
        jsonp: false,
        error: function () {
          console.log('Cannot draw sogo log table');
        },
        success: function (data) {
          return process_table_data(data, 'general_syslog');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "filtering": {"enabled": true,"delay": 1200,"position": "left","connectors": false,"placeholder": lang.filter_table},
      "sorting": {"enabled": true},
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_log_ready(ft, 'sogo_logs');
        },
        "after.ft.paging": function(e, ft){
          table_log_paging(ft, 'sogo_logs');
        }
      }
    });
  }
  function draw_dovecot_logs() {
    ft_dovecot_logs = FooTable.init('#dovecot_log', {
      "columns": [
        {"name":"time","formatter":function unix_time_format(tm) { var date = new Date(tm ? tm * 1000 : 0); return date.toLocaleString();},"title":lang.time,"style":{"width":"170px"}},
        {"name":"priority","title":lang.priority,"style":{"width":"80px"}},
        {"name":"message","title":lang.message},
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/logs/dovecot',
        jsonp: false,
        error: function () {
          console.log('Cannot draw dovecot log table');
        },
        success: function (data) {
          return process_table_data(data, 'general_syslog');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "filtering": {"enabled": true,"delay": 1200,"position": "left","connectors": false,"placeholder": lang.filter_table},
      "sorting": {"enabled": true},
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_log_ready(ft, 'dovecot_logs');
        },
        "after.ft.paging": function(e, ft){
          table_log_paging(ft, 'dovecot_logs');
        }
      }
    });
  }
  function rspamd_pie_graph() {
    $.ajax({
      url: '/api/v1/get/rspamd/actions',
      async: true,
      success: function(data){

        var total = 0;
        $(data).map(function(){total += this[1];});
        var labels = $.makeArray($(data).map(function(){return this[0] + ' ' + Math.round(this[1]/total * 100) + '%';}));
        var values = $.makeArray($(data).map(function(){return this[1];}));

        var graphdata = {
          labels: labels,
          datasets: [{
            data: values,
            backgroundColor: ['#DC3023', '#59ABE3', '#FFA400', '#FFA400', '#26A65B']
          }]
        };

        var options = {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            datalabels: {
              color: '#FFF',
              font: {
                weight: 'bold'
              },
              display: function(context) {
                return context.dataset.data[context.dataIndex] !== 0;
              },
              formatter: function(value, context) {
                return Math.round(value/total*100) + '%';
              }
            }
          }
        };
        var chartcanvas = document.getElementById('rspamd_donut');
        Chart.plugins.register('ChartDataLabels');
        if(typeof chart == 'undefined') {
          chart = new Chart(chartcanvas.getContext("2d"), {
            plugins: [ChartDataLabels],
            type: 'doughnut',
            data: graphdata,
            options: options
          });
        }
        else {
          chart.destroy();
          chart = new Chart(chartcanvas.getContext("2d"), {
            plugins: [ChartDataLabels],
            type: 'doughnut',
            data: graphdata,
            options: options
          });
        }
      }
    });
  }
  function draw_rspamd_history() {
    ft_rspamd_history = FooTable.init('#rspamd_history', {
      "columns": [
        {"name":"unix_time","formatter":function unix_time_format(tm) { var date = new Date(tm ? tm * 1000 : 0); return date.toLocaleString();},"title":lang.time,"style":{"width":"170px"}},
        {"name": "ip","title": "IP address","breakpoints": "all","style": {"minWidth": 88}},
        {"name": "sender_mime","title": "From","breakpoints": "xs sm md","style": {"minWidth": 100}},
        {"name": "rcpt","title": "To","breakpoints": "xs sm md","style": {"minWidth": 100}},
        {"name": "subject","title": "Subject","breakpoints": "all","style": {"word-break": "break-all","minWidth": 150}},
        {"name": "action","title": "Action","style": {"minwidth": 82}},
        {"name": "score","title": "Score","style": {"maxWidth": 110},},
        {"name": "symbols","title": "Symbols","breakpoints": "all",},
        {"name": "size","title": "Msg size","breakpoints": "all","style": {"minwidth": 50},"formatter": function(value){return humanFileSize(value);}},
        {"name": "scan_time","title": "Scan time","breakpoints": "all","style": {"maxWidth": 72},},
        {"name": "message-id","title": "ID","breakpoints": "all","style": {"minWidth": 130,"overflow": "hidden","textOverflow": "ellipsis","wordBreak": "break-all","whiteSpace": "normal"}},
        {"name": "user","title": "Authenticated user","breakpoints": "xs sm md","style": {"minWidth": 100}}
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/logs/rspamd-history',
        jsonp: false,
        error: function () {
          console.log('Cannot draw rspamd history table');
        },
        success: function (data) {
          return process_table_data(data, 'rspamd_history');
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": log_pagination_size},
      "filtering": {"enabled": true,"delay": 1200,"position": "left","connectors": false,"placeholder": lang.filter_table},
      "sorting": {"enabled": true},
      "on": {
        "destroy.ft.table": function(e, ft){
          $('.refresh_table').attr('disabled', 'true');
        },
        "ready.ft.table": function(e, ft){
          table_log_ready(ft, 'rspamd_history');
          heading = ft.$el.parents('.panel').find('.panel-heading')
          $(heading).children('.table-lines').text(function(){
            var ft_paging = ft.use(FooTable.Paging)
            return ft_paging.totalRows;
          })
          rspamd_pie_graph();
        },
        "after.ft.paging": function(e, ft){
          table_log_paging(ft, 'rspamd_history');
        }
      }
    });
  }
  function process_table_data(data, table) {
    if (table == 'rspamd_history') {
    $.each(data, function (i, item) {
      if (item.rcpt_mime != "") {
        item.rcpt = item.rcpt_mime.join(", ");
      }
      else {
        item.rcpt = item.rcpt_smtp.join(", ");
      }
      item.symbols = Object.keys(item.symbols).sort(function (a, b) {
        if (item.symbols[a].score === 0) return 1
        if (item.symbols[b].score === 0) return -1
        if (item.symbols[b].score < 0 && item.symbols[a].score < 0) {
          return item.symbols[a].score - item.symbols[b].score
        }
        if (item.symbols[b].score > 0 && item.symbols[a].score > 0) {
          return item.symbols[b].score - item.symbols[a].score
        }
        return item.symbols[b].score - item.symbols[a].score
      }).map(function(key) {
        var sym = item.symbols[key];
        if (sym.score < 0) {
          sym.score_formatted = '(<span class="text-success"><b>' + sym.score + '</b></span>)'
        }
        else if (sym.score === 0) {
          sym.score_formatted = '(<span><b>' + sym.score + '</b></span>)'
        }
        else {
          sym.score_formatted = '(<span class="text-danger"><b>' + sym.score + '</b></span>)'
        }
        var str = '<strong>' + key + '</strong> ' + sym.score_formatted;
        if (sym.options) {
          str += ' [' + escapeHtml(sym.options.join(", ")) + "]";
        }
        return str
      }).join('<br>\n');
      item.subject = escapeHtml(item.subject);
      var scan_time = item.time_real.toFixed(3);
      if (item.time_virtual) {
        scan_time += ' / ' + item.time_virtual.toFixed(3);
      }
      item.scan_time = {
        "options": {
          "sortValue": item.time_real
        },
        "value": scan_time
      };
      if (item.action === 'clean' || item.action === 'no action') {
        item.action = "<div class='label label-success'>" + item.action + "</div>";
      } else if (item.action === 'rewrite subject' || item.action === 'add header' || item.action === 'probable spam') {
        item.action = "<div class='label label-warning'>" + item.action + "</div>";
      } else if (item.action === 'spam' || item.action === 'reject') {
        item.action = "<div class='label label-danger'>" + item.action + "</div>";
      } else {
        item.action = "<div class='label label-info'>" + item.action + "</div>";
      }
      var score_content;
      if (item.score < item.required_score) {
        score_content = "[ <span class='text-success'>" + item.score.toFixed(2) + " / " + item.required_score + "</span> ]";
      } else {
        score_content = "[ <span class='text-danger'>" + item.score.toFixed(2) + " / " + item.required_score + "</span> ]";
      }
      item.score = {
        "options": {
          "sortValue": item.score
        },
        "value": score_content
      };
      if (item.user == null) {
        item.user = "none";
      }
    });
    } else if (table == 'autodiscover_log') {
      $.each(data, function (i, item) {
        if (item.ua == null) {
          item.ua = 'unknown';
        } else {
          item.ua = escapeHtml(item.ua);
        }
        item.ua = '<span style="font-size:small">' + item.ua + '</span>';
        if (item.service == "activesync") {
          item.service = '<span class="label label-info">ActiveSync</span>';
        }
        else if (item.service == "imap") {
          item.service = '<span class="label label-success">IMAP, SMTP, Cal-/CardDAV</span>';
        }
        else {
          item.service = '<span class="label label-danger">' + escapeHtml(item.service) + '</span>';
        }
      });
    } else if (table == 'watchdog') {
      $.each(data, function (i, item) {
        if (item.message == null) {
          item.message = 'Health level: ' + item.lvl + '% (' + item.hpnow + '/' + item.hptotal + ')';
          if (item.hpdiff < 0) {
            item.trend = '<span class="label label-danger"><span class="glyphicon glyphicon-arrow-down"></span> ' + item.hpdiff + '</span>';
          }
          else if (item.hpdiff == 0) {
            item.trend = '<span class="label label-info"><span class="glyphicon glyphicon-arrow-right"></span> ' + item.hpdiff + '</span>';
          }
          else {
            item.trend = '<span class="label label-success"><span class="glyphicon glyphicon-arrow-up"></span> ' + item.hpdiff + '</span>';
          }
        }
        else {
          item.trend = '';
          item.service = '';
        }
      });
    } else if (table == 'mailcow_ui') {
      $.each(data, function (i, item) {
        if (item === null) { return true; }
        item.user = escapeHtml(item.user);
        item.call = escapeHtml(item.call);
        item.task = '<code>' + item.task + '</code>';
        item.type = '<span class="label label-' + item.type + '">' + item.type + '</span>';
      });
    } else if (table == 'general_syslog') {
      $.each(data, function (i, item) {
        if (item === null) { return true; }
        if (item.message.match("^base64,")) {
          item.message = atob(item.message.slice(7)).replace(/\\n/g, "<br />");
        } else {
          item.message = escapeHtml(item.message);
        }
        item.call = escapeHtml(item.call);
        var danger_class = ["emerg", "alert", "crit", "err"];
        var warning_class = ["warning", "warn"];
        var info_class = ["notice", "info", "debug"];
        if (jQuery.inArray(item.priority, danger_class) !== -1) {
          item.priority = '<span class="label label-danger">' + item.priority + '</span>';
        } else if (jQuery.inArray(item.priority, warning_class) !== -1) {
          item.priority = '<span class="label label-warning">' + item.priority + '</span>';
        } else if (jQuery.inArray(item.priority, info_class) !== -1) {
          item.priority = '<span class="label label-info">' + item.priority + '</span>';
        }
      });
    } else if (table == 'apilog') {
      $.each(data, function (i, item) {
        if (item === null) { return true; }
        if (item.method == 'GET') {
          item.method = '<span class="label label-success">' + item.method + '</span>';
        } else if (item.method == 'POST') {
          item.method = '<span class="label label-warning">' + item.method + '</span>';
        }
        item.data = escapeHtml(item.data);
      });
    } else if (table == 'rllog') {
      $.each(data, function (i, item) {
        if (item.user == null) {
          item.user = "none";
        }
        if (item.rl_hash == null) {
          item.rl_hash = "err";
        }
        item.indicator = '<span style="border-right:6px solid #' + intToRGB(hashCode(item.rl_hash)) + ';padding-left:5px;">&nbsp;</span>';
        if (item.rl_hash != 'err') {
          item.action = '<a href="#" data-action="delete_selected" data-id="single-hash" data-api-url="delete/rlhash" data-item="' + encodeURI(item.rl_hash) + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.reset_limit + '</a>';
        }
      });
    }
    return data
  };
  $('.add_log_lines').on('click', function (e) {
    e.preventDefault();
    var log_table= $(this).data("table")
    var new_nrows = $(this).data("nrows")
    var post_process = $(this).data("post-process")
    var log_url = $(this).data("log-url")
    if (log_table === undefined || new_nrows === undefined || post_process === undefined || log_url === undefined) {
      console.log("no data-table or data-nrows or log_url or data-post-process attr found");
      return;
    }
    if (ft = FooTable.get($('#' + log_table))) {
      var heading = ft.$el.parents('.panel').find('.panel-heading')
      var ft_paging = ft.use(FooTable.Paging)
      var load_rows = (ft_paging.totalRows + 1) + '-' + (ft_paging.totalRows + new_nrows)
      $.get('/api/v1/get/logs/' + log_url + '/' + load_rows).then(function(data){
        if (data.length === undefined) { mailcow_alert_box(lang.no_new_rows, "info"); return; }
        var rows = process_table_data(data, post_process);
        var rows_now = (ft_paging.totalRows + data.length);
        $(heading).children('.table-lines').text(rows_now)
        mailcow_alert_box(data.length + lang.additional_rows, "success");
        ft.rows.load(rows, true);
      });
    }
  })
  // Initial table drawings
  draw_postfix_logs();
  draw_autodiscover_logs();
  draw_dovecot_logs();
  draw_sogo_logs();
  draw_watchdog_logs();
  draw_acme_logs();
  draw_api_logs();
  draw_rl_logs();
  draw_ui_logs();
  draw_netfilter_logs();
  draw_rspamd_history();
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    var target = $(e.target).attr("href");
    if (target == '#tab-rspamd-history') {
      rspamd_pie_graph();
    }
  });
});
