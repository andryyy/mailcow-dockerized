var rspamd_presetsElem = document.getElementById("rspamd_presets");

if (rspamd_presetsElem) {
  $.ajax({
    dataType: 'json',
    url: '/api/v1/get/presets/rspamd',
    jsonp: false,
    complete: function (data) {
      if (data.responseText !== '{}') {
        var rspamdPresets = JSON.parse(data.responseText);

        if (rspamdPresets) {
          rspamd_presetsElem.innerHTML = '';
          rspamdPresets.forEach(function (item, index) {
            var elemID = 'rspamd_preset_' + index;
            rspamd_presetsElem.innerHTML += '<li><a href="#" class="small" id="' + elemID + '">' + lang.rsettings_insert_preset.replace('%s', item.headline) + '</a></li>';

            /*
            we need to define 0-timeout here, to prevent dom not be ready.
             */
            setTimeout(function () {
              document.getElementById(elemID).addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector('form[data-id=rsetting] #adminRspamdSettingsDesc').value = item.headline;
                document.querySelector('form[data-id=rsetting] #adminRspamdSettingsContent').value = item.content;
                return true;
              });
            }, 0);
          });
        }
      }
    }
  });
}
