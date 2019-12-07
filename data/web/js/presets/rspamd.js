var rspamdPresets = [
  {
    description: lang.rsettings_preset_1,
    codeValue: 'priority = 10;\nauthenticated = yes;\napply "default" {\n  symbols_enabled = ["DKIM_SIGNED", "RATELIMITED", "RATELIMIT_UPDATE", "RATELIMIT_CHECK", "DYN_RL_CHECK", "HISTORY_SAVE", "MILTER_HEADERS", "ARC_SIGNED"];\n}'
  },
  {
    description: lang.rsettings_preset_2,
    codeValue: 'priority = 10;\nrcpt = "/postmaster@.*/";\nwant_spam = yes;'
  }
];

var rspamd_presetsElem = document.getElementById("rspamd_presets");
if (rspamd_presetsElem && rspamdPresets) {
  rspamd_presetsElem.innerHTML = '';
  rspamdPresets.forEach(function (item, index) {
    var elemID = 'rspamd_preset_' + index;
    rspamd_presetsElem.innerHTML += '<li><a href="#" class="small" id="' + elemID + '">' + lang.rsettings_insert_preset.replace('%s', item.description) + '</a></li>';

    /*
    we need to define 0-timeout here, to prevent dom not be ready.
     */
    setTimeout(function () {
      document.getElementById(elemID).addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector('form[data-id=rsetting] #adminRspamdSettingsDesc').value = item.description;
        document.querySelector('form[data-id=rsetting] #adminRspamdSettingsContent').value = item.codeValue;
        return true;
      });
    }, 0)

  });
}
