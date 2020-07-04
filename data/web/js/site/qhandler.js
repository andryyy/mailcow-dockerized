jQuery(function($){
  var qitem = $('legend').data('hash');
  var qError = $("#qid_error");
  $.ajax({
    url: '/inc/ajax/qitem_details.php',
    data: { hash: qitem },
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
      $('#qid_detail_hfrom').text(data.header_from);
      $('#qid_detail_efrom').text(data.env_from);
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
          $('#qid_detail_symbols').append('<span data-toggle="tooltip" class="rspamd-symbol ' + highlightClass + '" title="' + (value.options ? value.options.join(', ') : '') + '">' + value.name + ' (<span class="score">' + value.score + '</span>)</span>');
        });
        $('[data-toggle="tooltip"]').tooltip()
      }
      $('#qid_detail_recipients').html('');
      if (typeof data.recipients !== 'undefined') {
        $.each(data.recipients, function(index, value) {
          var elem = $('<span class="mail-address-item"></span>');
          elem.text(value.address + ' (' + value.type.toUpperCase() + ')');
          $('#qid_detail_recipients').append(elem);
        });
      }
    }
  });
});