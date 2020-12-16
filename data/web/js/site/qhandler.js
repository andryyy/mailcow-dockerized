jQuery(function($){
  var qitem = $('legend').data('hash');
  var qError = $("#qid_error");
  $.ajax({
    url: '/inc/ajax/qitem_details.php',
    data: { hash: qitem },
    dataType: 'json',
    success: function(data){
      $('[data-id="qitems_single"]').each(function(index) {
        $(this).attr("data-item", qitem);
      });
      $('#qid_detail_subj').text(data.subject);
      $('#qid_detail_hfrom').text(data.header_from);
      $('#qid_detail_efrom').text(data.env_from);
      $('#qid_detail_score').html('');
      $('#qid_detail_symbols').html('');
      $('#qid_detail_recipients').html('');
      $('#qid_detail_fuzzy').html('');
      if (typeof data.fuzzy_hashes === 'object' && data.fuzzy_hashes !== null && data.fuzzy_hashes.length !== 0) {
        $.each(data.fuzzy_hashes, function (index, value) {
          $('#qid_detail_fuzzy').append('<p style="font-family:monospace">' + value + '</p>');
        });
      } else {
        $('#qid_detail_fuzzy').append('-');
      }
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
          $('#qid_detail_symbols').append('<span data-toggle="tooltip" class="rspamd-symbol ' + highlightClass + '" title="' + (value.options ? value.options.join(', ') : '') + '">' + value.name + ' (<span class="score">' + value.score + '</span>)</span>');
        });
        $('[data-toggle="tooltip"]').tooltip()
      }
      if (typeof data.score !== 'undefined' && typeof data.action !== 'undefined') {
        if (data.action === "add header") {
          $('#qid_detail_score').append('<span class="label-rspamd-action label label-warning"><b>' + data.score + '</b> - ' + lang.junk_folder + '</span>');
        } else if (data.action === "reject") {
          $('#qid_detail_score').append('<span class="label-rspamd-action label label-danger"><b>' + data.score + '</b> - ' + lang.rejected + '</span>');
        } else if (data.action === "rewrite subject") {
          $('#qid_detail_score').append('<span class="label-rspamd-action label label-warning"><b>' + data.score + '</b> - ' + lang.rewrite_subject + '</span>');
        }
      }
      if (typeof data.recipients !== 'undefined') {
        $.each(data.recipients, function(index, value) {
          var elem = $('<span class="mail-address-item"></span>');
          elem.text(value.address + ' (' + value.type.toUpperCase() + ')');
          $('#qid_detail_recipients').append(elem);
        });
      }
    },
    error: function(data){
      if (typeof data.error !== 'undefined') {
        qError.text("Error loading quarantine item");
        qError.show();
      }
    }
  });
});
