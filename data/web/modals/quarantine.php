<?php
if (!isset($_SESSION['mailcow_cc_role'])) {
	header('Location: /');
	exit();
}
?>
<div class="modal fade" id="qidDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
        <h3 class="modal-title"><i class="bi bi-info-circle-fill"></i> <?=$lang['quarantine']['qitem'];?></h3>
      </div>
      <div class="modal-body">
        <div id="qid_error" style="display:none" class="alert alert-danger"></div>
        <div class="form-group">
          <label for="qid_detail_symbols"><h4><?=$lang['quarantine']['rspamd_result'];?>:</h4></label>
          <p><?=$lang['quarantine']['spam_score'];?>: <span id="qid_detail_score"></span></p>
          <hr>
          <p id="qid_detail_symbols"></p>
        </div>
        <div class="form-group">
          <label for="qid_detail_subj"><h4><?=$lang['quarantine']['subj'];?>:</h4></label>
          <p id="qid_detail_subj"></p>
        </div>
        <div class="form-group">
          <label for="qid_detail_recipients"><h4><?=$lang['quarantine']['recipients'];?>:</h4></label>
          <p id="qid_detail_recipients"></p>
        </div>
        <div class="form-group">
          <label for="qid_detail_hfrom"><h4><?=$lang['quarantine']['sender_header'];?>:</h4></label>
          <p><span class="mail-address-item" id="qid_detail_hfrom"></span></p>
        </div>
        <div class="form-group">
          <label for="qid_detail_efrom"><h4><?=$lang['quarantine']['sender'];?>:</h4></label>
          <p><span class="mail-address-item" id="qid_detail_efrom"></span></p>
        </div>
        <div class="form-group">
          <label for="qid_detail_fuzzy"><h4>Fuzzy Hashes:</h4></label>
          <p id="qid_detail_fuzzy"></p>
        </div>
        <div class="form-group" id="qTextPlain">
          <label for="qid_detail_text"><h4><?=$lang['quarantine']['text_plain_content'];?>:</h4></label>
          <pre id="qid_detail_text"></pre>
        </div>
        <div class="form-group" id="qTextHtml">
          <label for="qid_detail_text_from_html"><h4><?=$lang['quarantine']['text_from_html_content'];?>:</h4></label>
          <pre id="qid_detail_text_from_html"></pre>
        </div>
        <?php
        if ($_SESSION['acl']['quarantine_attachments'] == 1) {
        ?>
        <div class="form-group">
          <label for="qid_detail_atts"><h4><?=$lang['quarantine']['atts'];?>:</h4></label>
          <div id="qid_detail_atts">-</div>
        </div>
        <?php
        }
        ?>
        <div class="btn-group dropup" data-acl="<?=$_SESSION['acl']['quarantine'];?>">
          <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['quarantine']['quick_actions'];?> <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a data-action="edit_selected" data-id="qitems_single" data-item="" data-api-url='edit/qitem' data-api-attr='{"action":"release"}' href="#"><?=$lang['quarantine']['deliver_inbox'];?></a></li>
            <li role="separator" class="divider"></li>
            <li><a data-action="edit_selected" data-id="qitems_single" data-item="" data-api-url='edit/qitem' data-api-attr='{"action":"learnspam"}' href="#"><?=$lang['quarantine']['learn_spam_delete'];?></a></li>
            <li role="separator" class="divider"></li>
            <li><a data-id="qitems_single" data-item="" id="quick_download_link" href="#"><?=$lang['quarantine']['download_eml'];?></a></li>
            <li role="separator" class="divider"></li>
            <li><a data-id="qitems_single" data-item="" id="quick_release_link" href="#"><?=$lang['quarantine']['quick_release_link'];?></a></li>
            <li><a data-id="qitems_single" data-item="" id="quick_delete_link" href="#"><?=$lang['quarantine']['quick_delete_link'];?></a></li>
            <li role="separator" class="divider"></li>
            <li><a data-action="delete_selected" data-id="qitems_single" data-item="" data-api-url='delete/qitem' href="#"><?=$lang['quarantine']['remove'];?></a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

