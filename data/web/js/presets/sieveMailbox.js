var sieveMailboxPresets = [
  {
    description: lang.sieve_preset_1,
    codeValue: 'if header :contains "x-attached"\n  [".exe",".bat",".js",".com",".cmd",".ini",".dll",".bas",".cpl",".drv",".inf",".sys",".pif",".doc",".docx"] {\n  discard;\n  stop;\n}'
  },
  {
    description: lang.sieve_preset_2,
    codeValue: 'require ["envelope", "imap4flags"];\nif envelope "from" "mark@me-read.com"\n{\n   setflag "\\\\seen";\n}'
  }
];

var sieve_presetsElem = document.getElementById("sieve_presets");
if (sieve_presetsElem && sieveMailboxPresets) {
  sieve_presetsElem.innerHTML = '';
  sieveMailboxPresets.forEach(function (item, index) {
    var elemID = 'sieve_preset_' + index;
    sieve_presetsElem.innerHTML += '<li><a href="#" class="small" id="' + elemID + '">' + lang.insert_preset.replace('%s', item.description) + '</a></li>';

    /*
    we need to define 0-timeout here, to prevent dom not be ready.
     */
    setTimeout(function () {
      document.getElementById(elemID).addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector('form[data-id=add_filter] #script_desc').value = item.description;
        document.querySelector('form[data-id=add_filter] #script_data').value = item.codeValue;
        return true;
      });
    }, 0)
  });
}
