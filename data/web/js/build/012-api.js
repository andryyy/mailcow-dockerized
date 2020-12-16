$(document).ready(function() {
  mass_action = false;
  function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
  }
  function validateRegex(e){var t=e.split("/"),n=e,r="";t.length>1&&(n=t[1],r=t[2]);try{return new RegExp(n,r),!0}catch(e){return!1}}
  function is_active(elem) {
    if ($(elem).data('submitted') == '1') {
      return true;
    } else {
      var parent_btn_grp = $(elem).parentsUntil(".btn-group").parent();
      if (parent_btn_grp.hasClass('btn-group')) {
        parent_btn_grp.replaceWith('<button class="btn btn-default btn-sm" disabled>' + lang_footer.loading + '</a>');
      }
      $(elem).text(lang_footer.loading);
      $(elem).attr('data-submitted', '1');
      function disableF5(e) { if ((e.which || e.keyCode) == 116 || (e.which || e.keyCode) == 82) e.preventDefault(); };
      $(document).on("keydown", disableF5);
      return false;
    }
  }
  $.fn.serializeObject = function() {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
      if (o[this.name]) {
        if (!o[this.name].push) {
          o[this.name] = [o[this.name]];
        }
        o[this.name].push(this.value || '');
      } else {
        o[this.name] = this.value || '';
      }
    });
    return o;
  };
  // Collect values of input fields with name "multi_select" and same data-id to js array multi_data[data-id]
  var multi_data = [];
  $(document).on('change', 'input[name=multi_select]:checkbox', function(e) {
    if(mass_action === true) {
      multi_data = [];
      mass_action = false;
    }
    if ($(this).is(':checked') && $(this).data('id')) {
      var id = $(this).data('id');
      if (typeof multi_data[id] == "undefined") {
        multi_data[id] = [];
      }
      multi_data[id].push($(this).val());
    }
    else {
      var id = $(this).data('id');
      if (typeof multi_data[id] !== "undefined") {
        multi_data[id].splice($.inArray($(this).val(), multi_data[id]),1);
      }
    }
  });

  // Select checkbox by click on parent tr
  $(document).on('click', 'tbody>tr', function(e) {
    if(e.target.tagName.toLowerCase() === 'button') {
      e.stopPropagation();
    }
    else if(e.target.tagName.toLowerCase() === 'a') {
      e.stopPropagation();
    }
    else if (e.target.type == "checkbox") {
      e.stopPropagation();
    }
    else {
      var checkbox = $(this).find(':checkbox');
      checkbox.trigger('click');
    }
  });

  // Select or deselect all checkboxes with same data-id
  $(document).on('click', '#toggle_multi_select_all', function(e) {
    mass_action = true
    e.preventDefault();
    id = $(this).data("id");
    var all_checkboxes = $("input[data-id=" + id + "]:enabled");
    all_checkboxes.prop("checked", !all_checkboxes.prop("checked")).change();
  });

  // General API edit actions
  $(document).on('click', "[data-action='edit_selected']", function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var api_url = $(this).data('api-url');
    var api_attr = $(this).data('api-attr');
    if (typeof $(this).data('api-reload-window') !== 'undefined') {
      api_reload_window = $(this).data('api-reload-window');
    } else {
      api_reload_window = true;
    }
    if (typeof $(this).data('api-reload-location') !== 'undefined') {
      api_reload_location = $(this).data('api-reload-location');
    } else {
      api_reload_location = '#';
    }
    // If clicked element #edit_selected is in a form with the same data-id as the button,
    // we merge all input fields by {"name":"value"} into api-attr
    if ($(this).closest("form").data('id') == id) {
      var invalid = false;
      $(this).closest("form").find('select, textarea, input').each(function() {
        if ($(this).prop('required')) {
          if (!$(this).val() && $(this).prop('disabled') === false) {
            invalid = true;
            if ($(this).is("select")) {
              $(this).selectpicker('setStyle', 'btn-input-missing', 'add');
            }
            $(this).addClass('inputMissingAttr');
          } else {
            if ($(this).is("select")) {
              $(this).selectpicker('setStyle', 'btn-input-missing', 'remove');
            }
            $(this).removeClass('inputMissingAttr');
          }
        }
        if ($(this).val() && $(this).attr("type") == 'email') {
          if (!validateEmail($(this).val())) {
            invalid = true;
            $(this).addClass('inputMissingAttr');
          } else {
            $(this).removeClass('inputMissingAttr');
          }
        }
        if ($(this).attr("max")) {
          if (Number($(this).val()) > Number($(this).attr("max"))) {
            invalid = true;
            $(this).addClass('inputMissingAttr');
          } else {
            if ($(this).attr("min")) {
              if (Number($(this).val()) < Number($(this).attr("min"))) {
                invalid = true;
                $(this).addClass('inputMissingAttr');
              } else {
                $(this).removeClass('inputMissingAttr');
              }
            }
          }
        }
        if ($(this).val() && $(this).attr("regex")) {
          var regex_content = $(this).val();
          $(this).removeClass('inputMissingAttr');
          if(!validateRegex(regex_content)) {
            invalid = true;
            $(this).addClass('inputMissingAttr');
          }
          if(!regex_content.startsWith('/') || !/\/[ims]?$/.test(regex_content)){
            invalid = true;
            $(this).addClass('inputMissingAttr');
          }
        }
      });
      if (!invalid) {
        var attr_to_merge = $(this).closest("form").serializeObject();
        var api_attr = $.extend(api_attr, attr_to_merge)
      } else {
        return false;
      }
    }
    // alert(JSON.stringify(api_attr));
    // If clicked element #edit_selected has data-item attribute, it is added to "items"
    if (typeof $(this).data('item') !== 'undefined') {
      var id = $(this).data('id');
      if (typeof multi_data[id] == "undefined") {
        multi_data[id] = [];
      }
      multi_data[id].splice($.inArray($(this).data('item'), multi_data[id]), 1);
      multi_data[id].push($(this).data('item'));
    }
    if (typeof multi_data[id] == "undefined") return;
    api_items = multi_data[id];
    for (var i in api_items) {
      api_items[i] = decodeURIComponent(api_items[i]);
    }
    // alert(JSON.stringify(api_attr));
    if (Object.keys(api_items).length !== 0) {
      if (is_active($(this))) { return false; }
      $.ajax({
        type: "POST",
        dataType: "json",
        data: {
          "items": JSON.stringify(api_items),
          "attr": JSON.stringify(api_attr),
          "csrf_token": csrf_token
        },
        url: '/api/v1/' + api_url,
        jsonp: false,
        complete: function(data) {
          var response = (data.responseText);
          if (typeof response !== 'undefined' && response.length !== 0) {
            response_obj = JSON.parse(response);
          }
          if (api_reload_window === true) {
            if (api_reload_location != '#') {
              window.location.replace(api_reload_location)
            } else {
              window.location = window.location.href.split("#")[0];
            }
          }
        }
      });
    }
  });

  // General API add actions
  $(document).on('click', "[data-action='add_item']", function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var api_url = $(this).data('api-url');
    var api_attr = $(this).data('api-attr');
    if (typeof $(this).data('api-reload-window') !== 'undefined') {
      api_reload_window = $(this).data('api-reload-window');
    } else {
      api_reload_window = true;
    }
    // If clicked button is in a form with the same data-id as the button,
    // we merge all input fields by {"name":"value"} into api-attr
    if ($(this).closest("form").data('id') == id) {
      var invalid = false;
      $(this).closest("form").find('select, textarea, input').each(function() {
        if ($(this).prop('required')) {
          if (!$(this).val() && $(this).prop('disabled') === false) {
            invalid = true;
            if ($(this).is("select")) {
              $(this).selectpicker('setStyle', 'btn-input-missing', 'add');
            }
            $(this).addClass('inputMissingAttr');
          } else {
            if ($(this).is("select")) {
              $(this).selectpicker('setStyle', 'btn-input-missing', 'remove');
            }
            $(this).removeClass('inputMissingAttr');
          }
        }
        if ($(this).attr("type") == 'email') {
          var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
          if (!emailReg.test($(this).val())) {
            invalid = true;
            $(this).addClass('inputMissingAttr');
          } else {
            $(this).removeClass('inputMissingAttr');
          }
        }
        if ($(this).attr("max")) {
          if (Number($(this).val()) > Number($(this).attr("max"))) {
            invalid = true;
            $(this).addClass('inputMissingAttr');
          } else {
            if ($(this).attr("min")) {
              if (Number($(this).val()) < Number($(this).attr("min"))) {
                invalid = true;
                $(this).addClass('inputMissingAttr');
              } else {
                $(this).removeClass('inputMissingAttr');
              }
            }
          }
        }
      });
      if (!invalid) {
        var attr_to_merge = $(this).closest("form").serializeObject();
        var api_attr = $.extend(api_attr, attr_to_merge)
      } else {
        return false;
      }
    }
    if (is_active($(this))) { return false; }
    // alert(JSON.stringify(api_attr));
    $.ajax({
      type: "POST",
      dataType: "json",
      data: {
        "attr": JSON.stringify(api_attr),
        "csrf_token": csrf_token
      },
      url: '/api/v1/' + api_url,
      jsonp: false,
      complete: function(data) {
        var response = (data.responseText);
        if (typeof response !== 'undefined' && response.length !== 0) {
          response_obj = JSON.parse(response);
          unset = true;
          $.each(response_obj, function(i, v) {
            if (v.type == "danger") {
              unset = false;
            }
          });
          if (unset === true) {
            unset = null;
            // Keep form data for sync jobs
            if (id != "add_syncjob") {
              $('form').formcache('clear');
              $('form').formcache('destroy');
              var i = localStorage.length;
              while(i--) {
                var key = localStorage.key(i);
                if(/formcache/.test(key)) {
                  localStorage.removeItem(key);
                }  
              }
            }
          }
          else {
            var add_modal = $('.modal.in').attr('id');
            localStorage.setItem("add_modal", add_modal);
          }
        }
        if (api_reload_window === true) {
          window.location = window.location.href.split("#")[0];
        }
      }
    });
  });

  // General API delete actions
  $(document).on('click', "[data-action='delete_selected']", function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    // If clicked element #delete_selected has data-item attribute, it is added to "items"
    if (typeof $(this).data('item') !== 'undefined') {
      var id = $(this).data('id');
      if (typeof multi_data[id] == "undefined") {
        multi_data[id] = [];
      }
      multi_data[id].splice($.inArray($(this).data('item'), multi_data[id]), 1);
      multi_data[id].push($(this).data('item'));
    }
    if (typeof $(this).data('text') !== 'undefined') {
      $("#DeleteText").empty();
      $("#DeleteText").text($(this).data('text'));
    }
    if (typeof multi_data[id] == "undefined" || multi_data[id] == "") return;
    data_array = multi_data[id];
    api_url = $(this).data('api-url');
    $(document).on('show.bs.modal', '#ConfirmDeleteModal', function() {
      $("#ItemsToDelete").empty();
      for (var i in data_array) {
        data_array[i] = decodeURIComponent(data_array[i]);
        $("#ItemsToDelete").append("<li>" + data_array[i] + "</li>");
      }
    })
    $('#ConfirmDeleteModal').modal({
        backdrop: 'static',
        keyboard: false
      })
      .one('click', '#IsConfirmed', function(e) {
        if (is_active($('#IsConfirmed'))) { return false; }
        $.ajax({
          type: "POST",
          dataType: "json",
          cache: false,
          data: {
            "items": JSON.stringify(data_array),
            "csrf_token": csrf_token
          },
          url: '/api/v1/' + api_url,
          jsonp: false,
          complete: function(data) {
            window.location = window.location.href.split("#")[0];
          }
        });
      })
      .one('click', '#isCanceled', function(e) {
        // Remove event handler to allow to close modal and restart dialog without multiple submits
        $('#ConfirmDeleteModal').off();
        $('#ConfirmDeleteModal').modal('hide');
      });
  });
});
