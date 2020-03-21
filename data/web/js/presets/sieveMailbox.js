var sieve_presetsElem = document.getElementById("sieve_presets");

if (sieve_presetsElem) {
  $.ajax({
    dataType: 'json',
    url: '/api/v1/get/presets/sieve',
    jsonp: false,
    complete: function (data) {
      if (data.responseText !== '{}') {
        var sieveMailboxPresets = JSON.parse(data.responseText);

        if (sieveMailboxPresets) {
          sieve_presetsElem.innerHTML = '';
          sieveMailboxPresets.forEach(function (item, index) {
            var elemID = 'sieve_preset_' + index;
            sieve_presetsElem.innerHTML += '<li><a href="#" class="small" id="' + elemID + '">' + lang.insert_preset.replace('%s', item.headline) + '</a></li>';

            /*
            we need to define 0-timeout here, to prevent dom not be ready.
             */
            setTimeout(function () {
              document.getElementById(elemID).addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector('form[data-id=add_filter] #script_desc').value = item.headline;
                document.querySelector('form[data-id=add_filter] .script_data').value = item.content;
                return true;
              });
            }, 0);
          });
        }
      }
    }
  });
}
