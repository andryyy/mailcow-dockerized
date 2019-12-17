$(document).ready(function() {
  // mailcow alert box generator
  window.mailcow_alert_box = function(message, type) {
    msg = $('<span/>').text(message).text();
    if (type == 'danger' || type == 'info') {
      auto_hide = 0;
      $('#' + localStorage.getItem("add_modal")).modal('show');
      localStorage.removeItem("add_modal");
    } else {
      auto_hide = 5000;
    }
    $.notify({message: msg},{z_index: 20000, delay: auto_hide, type: type,placement: {from: "bottom",align: "right"},animate: {enter: 'animated fadeInUp',exit: 'animated fadeOutDown'}});
  }

  $(".generate_password").click(function( event ) {
    event.preventDefault();
    $('[data-hibp]').trigger('input');
    if (typeof($(this).closest("form").data('pwgen-length')) == "number") {
      var random_passwd = GPW.pronounceable($(this).closest("form").data('pwgen-length'))
    }
    else {
      var random_passwd = GPW.pronounceable(8)
    }
    $(this).closest("form").find('[data-pwgen-field]').attr('type', 'text');
    $(this).closest("form").find('[data-pwgen-field]').val(random_passwd);
  });

  // https://stackoverflow.com/questions/4399005/implementing-jquerys-shake-effect-with-animate
  function shake(div,interval,distance,times) {
      if(typeof interval === 'undefined') {
        interval = 100;
      }
      if(typeof distance === 'undefined') {
        distance = 10;
      }
      if(typeof times === 'undefined') {
        times = 4;
      }
    $(div).css('position','relative');
    for(var iter=0;iter<(times+1);iter++){
      $(div).animate({ left: ((iter%2==0 ? distance : distance*-1))}, interval);
    }
    $(div).animate({ left: 0},interval);
  }

  // form cache
  $('[data-cached-form="true"]').formcache({key: $(this).data('id')});

  //  tooltips
  $(function () {
    $('[data-toggle="tooltip"]').tooltip()
  });

  // remember last navigation pill
  (function () {
    'use strict';
    if ($('a[data-toggle="tab"]').length) {
      $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(this).data('dont-remember') == 1) {
          return true;
        }
        var id = $(this).parents('[role="tablist"]').attr('id');
        var key = 'lastTag';
        if (id) {
          key += ':' + id;
        }
        localStorage.setItem(key, $(e.target).attr('href'));
      });
      $('[role="tablist"]').each(function (idx, elem) {
        var id = $(elem).attr('id');
        var key = 'lastTag';
        if (id) {
          key += ':' + id;
        }
        var lastTab = localStorage.getItem(key);
        if (lastTab) {
          $('[href="' + lastTab + '"]').tab('show');
        }
      });
    }
  })();

  // IE fix to hide scrollbars when table body is empty
  $('tbody').filter(function (index) {
    return $(this).children().length < 1;
  }).remove();

  // selectpicker
  $('select').selectpicker();

  // haveibeenpwned?
  $('[data-hibp]').after('<p class="small haveibeenpwned">↪ Check against haveibeenpwned.com</p><span class="hibp-out"></span>');
  $('[data-hibp]').on('input', function() {
    out_field = $(this).next('.haveibeenpwned').next('.hibp-out').text('').attr('class', 'hibp-out');
  });
  $('.haveibeenpwned:not(.task-running)').on('click', function() {
    var hibp_field = $(this)
    $(hibp_field).addClass('task-running');
    var hibp_result = $(hibp_field).next('.hibp-out')
    var password_field = $(this).prev('[data-hibp]')
    if ($(password_field).val() == '') {
      shake(password_field);
    }
    else {
      $(hibp_result).attr('class', 'hibp-out label label-info');
      $(hibp_result).text(lang_footer.loading);
      var password_digest = $.sha1($(password_field).val())
      var digest_five = password_digest.substring(0, 5).toUpperCase();
      var queryURL = "https://api.pwnedpasswords.com/range/" + digest_five;
      var compl_digest = password_digest.substring(5, 41).toUpperCase();
      $.ajax({
        url: queryURL,
        type: 'GET',
        success: function(res) {
          if (res.search(compl_digest) > -1){
            $(hibp_result).removeClass('label label-info').addClass('label label-danger');
            $(hibp_result).text(lang_footer.hibp_nok)
          } else {
            $(hibp_result).removeClass('label label-info').addClass('label label-success');
            $(hibp_result).text(lang_footer.hibp_ok)
          }
          $(hibp_field).removeClass('task-running');
        },
        error: function(xhr, status, error) {
          $(hibp_result).removeClass('label label-info').addClass('label label-warning');
          $(hibp_result).text('API error: ' + xhr.responseText)
          $(hibp_field).removeClass('task-running');
        }
      });
    }
  });

  // Disable disallowed inputs
  $('[data-acl="0"]').each(function(event){
    if ($(this).is("a")) {
      $(this).removeAttr("data-toggle");
      $(this).removeAttr("data-target");
      $(this).removeAttr("data-action");
      $(this).click(function(event) {
        event.preventDefault();
      });
    }
    if ($(this).hasClass('btn-group')) {
      $(this).find('a').each(function(){
        $(this).removeClass('dropdown-toggle')
          .removeAttr('data-toggle')
          .removeAttr('data-target')
          .removeAttr('data-action')
          .removeAttr('id')
          .attr("disabled", true);
        $(this).click(function(event) {
          event.preventDefault();
          return;
        });
      });
      $(this).find('button').each(function() {
        $(this).attr("disabled", true);
      });
    } else if ($(this).hasClass('input-group')) {
      $(this).find('input').each(function() {
        $(this).removeClass('dropdown-toggle')
          .removeAttr('data-toggle')
          .attr("disabled", true);
        $(this).click(function(event) {
          event.preventDefault();
        });
      });
      $(this).find('button').each(function() {
        $(this).attr("disabled", true);
      });
    } else if ($(this).hasClass('form-group')) {
      $(this).find('input').each(function() {
        $(this).attr("disabled", true);
      });
    } else if ($(this).hasClass('btn')) {
      $(this).attr("disabled", true);
    } else if ($(this).attr('data-provide') == 'slider') {
      $(this).slider("disable");
    }
    $(this).data("toggle", "tooltip");
    $(this).attr("title", lang_acl.prohibited);
    $(this).tooltip();
  });

  // disable submit after submitting form (not API driven buttons)
  $('form').submit(function() {
    if ($('form button[type="submit"]').data('submitted') == '1') {
      return false;
    } else {
      $(this).find('button[type="submit"]').first().text(lang_footer.loading);
      $('form button[type="submit"]').attr('data-submitted', '1');
      function disableF5(e) { if ((e.which || e.keyCode) == 116 || (e.which || e.keyCode) == 82) e.preventDefault(); };
      $(document).on("keydown", disableF5);
    }
  });
  // Textarea line numbers
  $(".textarea-code").numberedtextarea({allowTabChar: true});
  // trigger container restart
  $('#RestartContainer').on('show.bs.modal', function(e) {
    var container = $(e.relatedTarget).data('container');
    $('#containerName').text(container);
    $('#triggerRestartContainer').click(function(){
      $(this).prop("disabled",true);
      $(this).html('<span class="glyphicon glyphicon-refresh glyphicon-spin"></span> ');
      $('#statusTriggerRestartContainer').html(lang_footer.restarting_container);
      $.ajax({
        method: 'get',
        url: '/inc/ajax/container_ctrl.php',
        timeout: docker_timeout,
        data: {
        'service': container,
        'action': 'restart'
        }
      })
      .always( function (data, status) {
        $('#statusTriggerRestartContainer').append(data);
        var htmlResponse = $.parseHTML(data)
        if ($(htmlResponse).find('span').hasClass('text-success')) {
          $('#triggerRestartContainer').html('<span class="glyphicon glyphicon-ok"></span> ');
          setTimeout(function(){
            $('#RestartContainer').modal('toggle'); 
            window.location = window.location.href.split("#")[0];
          }, 1200);
        } else {
          $('#triggerRestartContainer').html('<span class="glyphicon glyphicon-remove"></span> ');
        }
      })
    });
  })
});
