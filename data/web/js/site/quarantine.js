// Base64 functions
var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(r){var t,e,o,a,h,n,c,d="",C=0;for(r=Base64._utf8_encode(r);C<r.length;)a=(t=r.charCodeAt(C++))>>2,h=(3&t)<<4|(e=r.charCodeAt(C++))>>4,n=(15&e)<<2|(o=r.charCodeAt(C++))>>6,c=63&o,isNaN(e)?n=c=64:isNaN(o)&&(c=64),d=d+this._keyStr.charAt(a)+this._keyStr.charAt(h)+this._keyStr.charAt(n)+this._keyStr.charAt(c);return d},decode:function(r){var t,e,o,a,h,n,c="",d=0;for(r=r.replace(/[^A-Za-z0-9\+\/\=]/g,"");d<r.length;)t=this._keyStr.indexOf(r.charAt(d++))<<2|(a=this._keyStr.indexOf(r.charAt(d++)))>>4,e=(15&a)<<4|(h=this._keyStr.indexOf(r.charAt(d++)))>>2,o=(3&h)<<6|(n=this._keyStr.indexOf(r.charAt(d++))),c+=String.fromCharCode(t),64!=h&&(c+=String.fromCharCode(e)),64!=n&&(c+=String.fromCharCode(o));return c=Base64._utf8_decode(c)},_utf8_encode:function(r){r=r.replace(/\r\n/g,"\n");for(var t="",e=0;e<r.length;e++){var o=r.charCodeAt(e);o<128?t+=String.fromCharCode(o):o>127&&o<2048?(t+=String.fromCharCode(o>>6|192),t+=String.fromCharCode(63&o|128)):(t+=String.fromCharCode(o>>12|224),t+=String.fromCharCode(o>>6&63|128),t+=String.fromCharCode(63&o|128))}return t},_utf8_decode:function(r){for(var t="",e=0,o=c1=c2=0;e<r.length;)(o=r.charCodeAt(e))<128?(t+=String.fromCharCode(o),e++):o>191&&o<224?(c2=r.charCodeAt(e+1),t+=String.fromCharCode((31&o)<<6|63&c2),e+=2):(c2=r.charCodeAt(e+1),c3=r.charCodeAt(e+2),t+=String.fromCharCode((15&o)<<12|(63&c2)<<6|63&c3),e+=3);return t}};

jQuery(function($){
  acl_data = JSON.parse(acl);
  // http://stackoverflow.com/questions/24816/escaping-html-strings-with-jquery
  var entityMap={"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;","/":"&#x2F;","`":"&#x60;","=":"&#x3D;"};
  function escapeHtml(n){return String(n).replace(/[&<>"'`=\/]/g,function(n){return entityMap[n]})}
  function humanFileSize(i){if(Math.abs(i)<1024)return i+" B";var B=["KiB","MiB","GiB","TiB","PiB","EiB","ZiB","YiB"],e=-1;do{i/=1024,++e}while(Math.abs(i)>=1024&&e<B.length-1);return i.toFixed(1)+" "+B[e]}

  function draw_quarantine_table() {
    ft_quarantinetable = FooTable.init('#quarantinetable', {
      "columns": [
        {"name":"chkbox","title":"","style":{"maxWidth":"60px","width":"60px"},"filterable": false,"sortable": false,"type":"html"},
        {"name":"id","type":"ID","filterable": false,"sorted": true,"direction":"DESC","title":"ID","style":{"width":"50px"}},
        {"name":"qid","breakpoints":"all","type":"text","title":lang.qid,"style":{"width":"125px"}},
        {"name":"sender","title":lang.sender},
        {"name":"subject","title":lang.subj, "type": "text"},
        {"name":"rcpt","title":lang.rcpt, "breakpoints":"xs sm md", "type": "text"},
        {"name":"virus","title":lang.danger, "type": "text"},
        {"name":"score","title": lang.spam_score, "type": "text"},
        {"name":"notified","title":lang.notified, "type": "text"},
        {"name":"created","formatter":function unix_time_format(tm) { var date = new Date(tm ? tm * 1000 : 0); return date.toLocaleString();},"title":lang.received,"style":{"width":"170px"}},
        {"name":"action","filterable": false,"sortable": false,"style":{"text-align":"right"},"style":{"width":"220px"},"type":"html","title":lang.action,"breakpoints":"xs sm md"}
      ],
      "rows": $.ajax({
        dataType: 'json',
        url: '/api/v1/get/quarantine/all',
        jsonp: false,
        error: function () {
          console.log('Cannot draw quarantine table');
        },
        success: function (data) {
          $.each(data, function (i, item) {
            if (item.subject === null) {
              item.subject = '';
            } else {
              item.subject = escapeHtml(item.subject);
            }
            if (item.score === null) {
              item.score = '-';
            }
            if (item.virus_flag > 0) {
              item.virus = '<span class="dot-danger"></span>';
            } else {
              item.virus = '<span class="dot-neutral"></span>';
            }
            if(item.notified > 0) {
              item.notified = '&#10004;';
            } else {
              item.notified = '&#10006;';
            }
            if (acl_data.login_as === 1) {
            item.action = '<div class="btn-group">' +
              '<a href="#" data-item="' + encodeURI(item.id) + '" class="btn btn-xs btn-info show_qid_info"><span class="glyphicon glyphicon-modal-window"></span> ' + lang.show_item + '</a>' +
              '<a href="#" data-action="delete_selected" data-id="del-single-qitem" data-api-url="delete/qitem" data-item="' + encodeURI(item.id) + '" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> ' + lang.remove + '</a>' +
              '</div>';
            }
            else {
            item.action = '<div class="btn-group">' +
              '<a href="#" data-item="' + encodeURI(item.id) + '" class="btn btn-xs btn-info show_qid_info"><span class="glyphicon glyphicon-modal-window"></span> ' + lang.show_item + '</a>' +
              '</div>';
            }
            item.chkbox = '<input type="checkbox" data-id="qitems" name="multi_select" value="' + item.id + '" />';
          });
        }
      }),
      "empty": lang.empty,
      "paging": {"enabled": true,"limit": 5,"size": pagination_size},
      "sorting": {"enabled": true},
      "filtering": {"enabled": true,"position": "left","connectors": false,"placeholder": lang.filter_table},
      "toggleSelector": "table tbody span.footable-toggle"
    });
  }

  $('body').on('click', '.show_qid_info', function (e) {
    e.preventDefault();
    var qitem = $(this).data('item');
    var qError = $("#qid_error");

    $('#qidDetailModal').modal('show');
    qError.hide();

    $.ajax({
      url: '/inc/ajax/qitem_details.php',
      data: { id: qitem },
      dataType: 'json',
      success: function(data){
        if (typeof data.error !== 'undefined') {
          qError.text(data.error);
          qError.show();
        }
        $('[data-id="qitems_single"]').each(function(index) {
          $(this).attr("data-item", qitem);
        });

        $('#qid_detail_subj').text(data.subject);
        $('#qid_detail_text').text(data.text_plain);
        $('#qid_detail_text_from_html').text(data.text_html);

        $('#qid_detail_score').text(data.score);
        $('#qid_detail_symbols').html('');
        if (typeof data.symbols !== 'undefined') {
          data.symbols.sort(function (a, b) {
            if (a.score === 0) return 1
            if (b.score === 0) return -1
            if (b.score < 0 && a.score < 0) {
              return a.score - b.score
            }
            if (b.score > 0 && a.score > 0) {
              return b.score - a.score
            }
            return b.score - a.score
          })
          $.each(data.symbols, function (index, value) {
            var highlightClass = ''
            if (value.score > 0) highlightClass = 'negative'
            else if (value.score < 0) highlightClass = 'positive'
            else highlightClass = 'neutral'
            $('#qid_detail_symbols').append('<span class="rspamd-symbol ' + highlightClass + '" title="' + (value.options ? value.options.join(', ') : '') + '">' + value.name + ' (<span class="score">' + value.score + '</span>)</span>');
          });
        }

        $('#qid_detail_recipients').html('');
        if (typeof data.recipients !== 'undefined') {
          $.each(data.recipients, function(index, value) {
            var elem = $('<span class="mail-address-item"></span>');
            elem.text(value.address + (value.type != 'to' ? (' (' + value.type.toUpperCase() + ')') : ''));
            $('#qid_detail_recipients').append(elem);
          });
        }

        var qAtts = $("#qid_detail_atts");
        if (typeof data.attachments !== 'undefined') {
          qAtts.text('');
          $.each(data.attachments, function(index, value) {
            qAtts.append(
              '<p><a href="/inc/ajax/qitem_details.php?id=' + qitem + '&att=' + index + '" target="_blank">' + value[0] + '</a> (' + value[1] + ')' +
              ' - <small><a href="' + value[3] + '" target="_blank">' + lang.check_hash + '</a></small></p>'
            );
          });
        }
        else {
          qAtts.text('-');
        }
      }
    });
  });

  $('body').on('click', 'span.footable-toggle', function () {
    event.stopPropagation();
  })

  // Initial table drawings
  draw_quarantine_table();
});
