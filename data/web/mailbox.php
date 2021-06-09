<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';

if (isset($_SESSION['mailcow_cc_role']) && ($_SESSION['mailcow_cc_role'] == "admin" || $_SESSION['mailcow_cc_role'] == "domainadmin")) {
require_once $_SERVER['DOCUMENT_ROOT'] .  '/inc/header.inc.php';
$_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
?>
<div class="container">

  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#tab-domains" aria-controls="tab-domains" role="tab" data-toggle="tab"><?=$lang['mailbox']['domains'];?></a></li>
    <li role="presentation"><a href="#tab-mailboxes" aria-controls="tab-mailboxes" role="tab" data-toggle="tab"><?=$lang['mailbox']['mailboxes'];?></a></li>
    <?php /* <li class="dropdown">
      <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['mailboxes'];?>
      <span class="caret"></span></a>
      <ul class="dropdown-menu">
        <li role="presentation"><a href="#tab-mailboxes" aria-controls="tab-mailboxes" role="tab" data-toggle="tab"><?=$lang['mailbox']['mailboxes'];?></a></li>
        <li role="presentation"><a href="#tab-mailbox-defaults" aria-controls="tab-mailbox-defaults" role="tab" data-toggle="tab"><?=$lang['mailbox']['mailbox_defaults'];?></a></li>
      </ul>
    </li> */ ?>
    <li role="presentation"><a href="#tab-resources" aria-controls="tab-resources" role="tab" data-toggle="tab"><?=$lang['mailbox']['resources'];?></a></li>
    <li class="dropdown">
      <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['aliases'];?>
      <span class="caret"></span></a>
      <ul class="dropdown-menu">
        <li role="presentation"><a href="#tab-mbox-aliases" aria-controls="tab-mbox-aliases" role="tab" data-toggle="tab"><?=$lang['mailbox']['aliases'];?></a></li>
        <li role="presentation"><a href="#tab-domain-aliases" aria-controls="tab-domain-aliases" role="tab" data-toggle="tab"><?=$lang['mailbox']['domain_aliases'];?></a></li>
      </ul>
    </li>
    <li role="presentation"><a href="#tab-syncjobs" aria-controls="tab-syncjobs" role="tab" data-toggle="tab"><?=$lang['mailbox']['sync_jobs'];?></a></li>
    <li role="presentation"><a href="#tab-filters" aria-controls="tab-filters" role="tab" data-toggle="tab"><?=$lang['mailbox']['filters'];?></a></li>
    <li role="presentation"><a href="#tab-bcc" aria-controls="tab-filters" role="tab" data-toggle="tab"><?=$lang['mailbox']['address_rewriting'];?></a></li>
    <li role="presentation"<?=($_SESSION['mailcow_cc_role'] == "admin") ?: ' class="hidden"';?>><a href="#tab-tls-policy" aria-controls="tab-tls-policy" role="tab" data-toggle="tab"><?=$lang['mailbox']['tls_policy_maps'];?></a></li>
  </ul>

	<div class="row">
		<div class="col-md-12">
      <div class="tab-content" style="padding-top:20px">
        <div role="tabpanel" class="tab-pane active" id="tab-domains">
          <div class="panel panel-default">
            <div class="panel-heading">
              <?=$lang['mailbox']['domains'];?> <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right hidden-xs">
                <? if($_SESSION['mailcow_cc_role'] == "admin"): ?><button class="btn btn-xs btn-success" href="#" data-toggle="modal" data-target="#addDomainModal"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_domain'];?></button><? endif; ?>
                <button class="btn btn-xs btn-default refresh_table" data-draw="draw_domain_table" data-table="domain_table"><?=$lang['admin']['refresh'];?></button>
                <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown"><?=$lang['mailbox']['table_size'];?> 
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" data-table-id="domain_table" role="menu">
                  <li><a href="#" data-page-size="3"><?=sprintf($lang['mailbox']['table_size_show_n'], 3);?></a></li>
                  <li><a href="#" data-page-size="10"><?=sprintf($lang['mailbox']['table_size_show_n'], 10);?></a></li>
                  <li><a href="#" data-page-size="20"><?=sprintf($lang['mailbox']['table_size_show_n'], 20);?></a></li>
                  <li><a href="#" data-page-size="50"><?=sprintf($lang['mailbox']['table_size_show_n'], 50);?></a></li>
                  <li><a href="#" data-page-size="100"><?=sprintf($lang['mailbox']['table_size_show_n'], 100);?></a></li>
                  <li><a href="#" data-page-size="200"><?=sprintf($lang['mailbox']['table_size_show_n'], 200);?></a></li>
                </ul>
              </div>
            </div>
            <!-- <div class="mass-actions-mailbox" data-actions-header="true"></div> -->
            <div class="table-responsive">
              <table id="domain_table" class="table table-striped"></table>
            </div>
            <div class="mass-actions-mailbox">
              <div class="btn-group">
                <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="domain" href="#"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></a>
                <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <? if($_SESSION['mailcow_cc_role'] == "admin"): ?>
                    <li><a data-action="edit_selected" data-id="domain" data-api-url='edit/domain' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                    <li><a data-action="edit_selected" data-id="domain" data-api-url='edit/domain' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                    <li role="separator" class="divider"></li>
                    <li><a data-action="delete_selected" data-id="domain" data-api-url='delete/domain' href="#"><?=$lang['mailbox']['remove'];?></a></li>
                  <? endif; ?>
                </ul>
                <? if($_SESSION['mailcow_cc_role'] == "admin"): ?>
                  <a class="btn btn-sm btn-success" href="#" data-toggle="modal" data-target="#addDomainModal"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_domain'];?></a>
                <? endif; ?>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-mailbox-defaults">
          <div class="panel panel-default">
            <div class="panel-heading">
              <?=$lang['mailbox']['mailbox_defaults'];?>
            </div>
            <div class="panel-body help-block">
            <?=$lang['mailbox']['mailbox_defaults_info'];?>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-mailboxes">
          <div class="panel panel-default">
            <div class="panel-heading">
              <?=$lang['mailbox']['mailboxes'];?> <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right hidden-xs">
                <button class="btn btn-xs btn-success" href="#" data-toggle="modal" data-target="#addMailboxModal"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_mailbox'];?></button>
                <button class="btn btn-xs btn-default refresh_table" data-draw="draw_mailbox_table" data-table="mailbox_table"><?=$lang['admin']['refresh'];?></button>
                <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown"><?=$lang['mailbox']['table_size'];?> 
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" data-table-id="mailbox_table" role="menu">
                  <li><a href="#" data-page-size="3"><?=sprintf($lang['mailbox']['table_size_show_n'], 3);?></a></li>
                  <li><a href="#" data-page-size="10"><?=sprintf($lang['mailbox']['table_size_show_n'], 10);?></a></li>
                  <li><a href="#" data-page-size="20"><?=sprintf($lang['mailbox']['table_size_show_n'], 20);?></a></li>
                  <li><a href="#" data-page-size="50"><?=sprintf($lang['mailbox']['table_size_show_n'], 50);?></a></li>
                  <li><a href="#" data-page-size="100"><?=sprintf($lang['mailbox']['table_size_show_n'], 100);?></a></li>
                  <li><a href="#" data-page-size="200"><?=sprintf($lang['mailbox']['table_size_show_n'], 200);?></a></li>
                </ul>
              </div>
            </div>
            <div class="mass-actions-mailbox" data-actions-header="true"></div>
            <div class="table-responsive">
              <table id="mailbox_table" class="table table-striped"></table>
            </div>
            <div class="mass-actions-mailbox">
              <div class="btn-group hidden-md hidden-lg hidden-xl">
                <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="mailbox" href="#"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></a>
                <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li class="dropdown-header"><?=$lang['mailbox']['mailbox'];?></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                  <li><a data-action="delete_selected" data-id="mailbox" data-api-url='delete/mailbox' href="#"><?=$lang['mailbox']['remove'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li class="dropdown-header"><?=$lang['mailbox']['tls_enforce_in'];?></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/tls_policy' data-api-attr='{"tls_enforce_in":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/tls_policy' data-api-attr='{"tls_enforce_in":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li class="dropdown-header"><?=$lang['mailbox']['tls_enforce_out'];?></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/tls_policy' data-api-attr='{"tls_enforce_out":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/tls_policy' data-api-attr='{"tls_enforce_out":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li class="dropdown-header"><?=$lang['mailbox']['quarantine_notification'];?></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/quarantine_notification' data-api-attr='{"quarantine_notification":"hourly"}' href="#"><?=$lang['user']['hourly'];?></a></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/quarantine_notification' data-api-attr='{"quarantine_notification":"daily"}' href="#"><?=$lang['user']['daily'];?></a></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/quarantine_notification' data-api-attr='{"quarantine_notification":"weekly"}' href="#"><?=$lang['user']['weekly'];?></a></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/quarantine_notification' data-api-attr='{"quarantine_notification":"never"}' href="#"><?=$lang['user']['never'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/quarantine_category' data-api-attr='{"quarantine_category":"reject"}' href="#"><?=$lang['user']['q_reject'];?></a></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/quarantine_category' data-api-attr='{"quarantine_category":"add_header"}' href="#"><?=$lang['user']['q_add_header'];?></a></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/quarantine_category' data-api-attr='{"quarantine_category":"all"}' href="#"><?=$lang['user']['q_all'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li class="dropdown-header"><?=$lang['mailbox']['allowed_protocols'];?></li>
                  <li class="dropdown-header">IMAP</li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"imap_access":1}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"imap_access":0}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li class="dropdown-header">POP3</li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"pop3_access":1}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"pop3_access":0}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li class="dropdown-header">SMTP</li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"smtp_access":1}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                  <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"smtp_access":0}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                </ul>
                <a class="btn btn-sm btn-success" href="#" data-toggle="modal" data-target="#addMailboxModal"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_mailbox'];?></a>
              </div>
              <div class="btn-group hidden-xs hidden-sm">
                <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="mailbox" href="#"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></a>
                <div class="btn-group">
                  <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['mailbox'];?> <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"active":"2"}' href="#"><?=$lang['mailbox']['disable_login'];?></a></li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                    <li role="separator" class="divider"></li>
                    <li><a data-action="delete_selected" data-id="mailbox" data-api-url='delete/mailbox' href="#"><?=$lang['mailbox']['remove'];?></a></li>
                  </ul>
                </div>
                <div class="btn-group">
                  <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#">TLS <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <li class="dropdown-header"><?=$lang['mailbox']['tls_enforce_in'];?></li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/tls_policy' data-api-attr='{"tls_enforce_in":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/tls_policy' data-api-attr='{"tls_enforce_in":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                    <li role="separator" class="divider"></li>
                    <li class="dropdown-header"><?=$lang['mailbox']['tls_enforce_out'];?></li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/tls_policy' data-api-attr='{"tls_enforce_out":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/tls_policy' data-api-attr='{"tls_enforce_out":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                  </ul>
                </div>
                <div class="btn-group">
                  <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['allowed_protocols'];?> <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <li class="dropdown-header">IMAP</li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"imap_access":1}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"imap_access":0}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                    <li role="separator" class="divider"></li>
                    <li class="dropdown-header">POP3</li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"pop3_access":1}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"pop3_access":0}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                    <li role="separator" class="divider"></li>
                    <li class="dropdown-header">SMTP</li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"smtp_access":1}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/mailbox' data-api-attr='{"smtp_access":0}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                  </ul>
                </div>
                <div class="btn-group">
                  <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quarantine_notification'];?> <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/quarantine_notification' data-api-attr='{"quarantine_notification":"hourly"}' href="#"><?=$lang['user']['hourly'];?></a></li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/quarantine_notification' data-api-attr='{"quarantine_notification":"daily"}' href="#"><?=$lang['user']['daily'];?></a></li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/quarantine_notification' data-api-attr='{"quarantine_notification":"weekly"}' href="#"><?=$lang['user']['weekly'];?></a></li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/quarantine_notification' data-api-attr='{"quarantine_notification":"never"}' href="#"><?=$lang['user']['never'];?></a></li>
                    <li role="separator" class="divider"></li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/quarantine_category' data-api-attr='{"quarantine_category":"reject"}' href="#"><?=$lang['user']['q_reject'];?></a></li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/quarantine_category' data-api-attr='{"quarantine_category":"add_header"}' href="#"><?=$lang['user']['q_add_header'];?></a></li>
                    <li><a data-action="edit_selected" data-id="mailbox" data-api-url='edit/quarantine_category' data-api-attr='{"quarantine_category":"all"}' href="#"><?=$lang['user']['q_all'];?></a></li>
                  </ul>
                </div>
                <a class="btn btn-sm btn-success" href="#" data-toggle="modal" data-target="#addMailboxModal"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_mailbox'];?></a>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-resources">
          <div class="panel panel-default">
            <div class="panel-heading">
              <?=$lang['mailbox']['resources'];?> <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right hidden-xs">
                <button class="btn btn-xs btn-success" href="#" data-toggle="modal" data-target="#addResourceModal"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_resource'];?></button>
                <button class="btn btn-xs btn-default refresh_table" data-draw="draw_resource_table" data-table="resource_table"><?=$lang['admin']['refresh'];?></button>
                <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown"><?=$lang['mailbox']['table_size'];?> 
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" data-table-id="resource_table" role="menu">
                  <li><a href="#" data-page-size="3"><?=sprintf($lang['mailbox']['table_size_show_n'], 3);?></a></li>
                  <li><a href="#" data-page-size="10"><?=sprintf($lang['mailbox']['table_size_show_n'], 10);?></a></li>
                  <li><a href="#" data-page-size="20"><?=sprintf($lang['mailbox']['table_size_show_n'], 20);?></a></li>
                  <li><a href="#" data-page-size="50"><?=sprintf($lang['mailbox']['table_size_show_n'], 50);?></a></li>
                  <li><a href="#" data-page-size="100"><?=sprintf($lang['mailbox']['table_size_show_n'], 100);?></a></li>
                  <li><a href="#" data-page-size="200"><?=sprintf($lang['mailbox']['table_size_show_n'], 200);?></a></li>
                </ul>
              </div>
            </div>
            <div class="panel-body help-block">
            <p><span class="label label-success"><?=$lang['mailbox']['booking_0_short'];?></span> - <?=$lang['mailbox']['booking_0'];?></p>
            <p><span class="label label-warning"><?=$lang['mailbox']['booking_lt0_short'];?></span> - <?=$lang['mailbox']['booking_lt0'];?></p>
            <p><span class="label label-danger"><?=$lang['mailbox']['booking_custom_short'];?></span> - <?=$lang['mailbox']['booking_custom'];?></p>
            </div>
            <!-- <div class="mass-actions-mailbox" data-actions-header="true"></div> -->
            <div class="table-responsive">
              <table id="resource_table" class="table table-striped"></table>
            </div>
            <div class="mass-actions-mailbox">
              <div class="btn-group">
                <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="resource" href="#"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></a>
                <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a data-action="edit_selected" data-id="resource" data-api-url='edit/resource' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                  <li><a data-action="edit_selected" data-id="resource" data-api-url='edit/resource' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li><a data-action="delete_selected" data-id="resource" data-api-url='delete/resource' href="#"><?=$lang['mailbox']['remove'];?></a></li>
                </ul>
                <a class="btn btn-sm btn-success" href="#" data-toggle="modal" data-target="#addResourceModal"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_resource'];?></a>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-domain-aliases">
          <div class="panel panel-default">
            <div class="panel-heading">
              <?=$lang['mailbox']['domain_aliases'];?> <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right hidden-xs">
                <button class="btn btn-xs btn-success" href="#" data-acl="<?=$_SESSION['acl']['alias_domains'];?>" data-toggle="modal" data-target="#addAliasDomainModal"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_domain_alias'];?></button>
                <button class="btn btn-xs btn-default refresh_table" data-draw="draw_aliasdomain_table" data-table="aliasdomain_table"><?=$lang['admin']['refresh'];?></button>
                <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown"><?=$lang['mailbox']['table_size'];?> 
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" data-table-id="aliasdomain_table" role="menu">
                  <li><a href="#" data-page-size="3"><?=sprintf($lang['mailbox']['table_size_show_n'], 3);?></a></li>
                  <li><a href="#" data-page-size="10"><?=sprintf($lang['mailbox']['table_size_show_n'], 10);?></a></li>
                  <li><a href="#" data-page-size="20"><?=sprintf($lang['mailbox']['table_size_show_n'], 20);?></a></li>
                  <li><a href="#" data-page-size="50"><?=sprintf($lang['mailbox']['table_size_show_n'], 50);?></a></li>
                  <li><a href="#" data-page-size="100"><?=sprintf($lang['mailbox']['table_size_show_n'], 100);?></a></li>
                  <li><a href="#" data-page-size="200"><?=sprintf($lang['mailbox']['table_size_show_n'], 200);?></a></li>
                </ul>
              </div>
            </div>
            <!-- <div class="mass-actions-mailbox" data-actions-header="true"></div> -->
            <div class="table-responsive">
              <table id="aliasdomain_table" class="table table-striped"></table>
            </div>
            <div class="mass-actions-mailbox">
              <div class="btn-group">
                <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="alias-domain" href="#"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></a>
                <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a data-action="edit_selected" data-id="alias-domain" data-api-url='edit/alias-domain' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                  <li><a data-action="edit_selected" data-id="alias-domain" data-api-url='edit/alias-domain' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li><a data-action="delete_selected" data-id="alias-domain" data-api-url='delete/alias-domain' href="#"><?=$lang['mailbox']['remove'];?></a></li>
                </ul>
                <a class="btn btn-sm btn-success" href="#" data-acl="<?=$_SESSION['acl']['alias_domains'];?>" data-toggle="modal" data-target="#addAliasDomainModal"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_domain_alias'];?></a>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-mbox-aliases">
          <div class="panel panel-default">
            <div class="panel-heading">
              <?=$lang['mailbox']['aliases'];?> <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right hidden-xs">
                <button class="btn btn-xs btn-success" href="#" data-toggle="modal" data-target="#addAliasModal"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_alias'];?></button>
                <button class="btn btn-xs btn-default refresh_table" data-draw="draw_alias_table" data-table="alias_table"><?=$lang['admin']['refresh'];?></button>
                <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown"><?=$lang['mailbox']['table_size'];?> 
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" data-table-id="alias_table" role="menu">
                  <li><a href="#" data-page-size="3"><?=sprintf($lang['mailbox']['table_size_show_n'], 3);?></a></li>
                  <li><a href="#" data-page-size="10"><?=sprintf($lang['mailbox']['table_size_show_n'], 10);?></a></li>
                  <li><a href="#" data-page-size="20"><?=sprintf($lang['mailbox']['table_size_show_n'], 20);?></a></li>
                  <li><a href="#" data-page-size="50"><?=sprintf($lang['mailbox']['table_size_show_n'], 50);?></a></li>
                  <li><a href="#" data-page-size="100"><?=sprintf($lang['mailbox']['table_size_show_n'], 100);?></a></li>
                  <li><a href="#" data-page-size="200"><?=sprintf($lang['mailbox']['table_size_show_n'], 200);?></a></li>
                </ul>
              </div>
            </div>
            <div class="panel-body help-block">
              <?=$lang['mailbox']['alias_domain_alias_hint'];?>
            </div>
            <!-- <div class="mass-actions-mailbox" data-actions-header="true"></div> -->
            <div class="table-responsive">
              <table id="alias_table" class="table table-striped"></table>
            </div>
            <div class="mass-actions-mailbox">
              <div class="btn-group">
                <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="alias" href="#"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></a>
                <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a data-action="edit_selected" data-id="alias" data-api-url='edit/alias' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                  <li><a data-action="edit_selected" data-id="alias" data-api-url='edit/alias' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li><a data-action="delete_selected" data-id="alias" data-api-url='delete/alias' href="#"><?=$lang['mailbox']['remove'];?></a></li>
                  <?php if (getenv('SKIP_SOGO') != "y") { ?>
                  <li role="separator" class="divider"></li>
                  <li><a data-action="edit_selected" data-id="alias" data-api-url='edit/alias' data-api-attr='{"sogo_visible":"1"}' href="#"><?=$lang['mailbox']['sogo_visible_y'];?></a></li>
                  <li><a data-action="edit_selected" data-id="alias" data-api-url='edit/alias' data-api-attr='{"sogo_visible":"0"}' href="#"><?=$lang['mailbox']['sogo_visible_n'];?></a></li>
                  <?php } ?>
                </ul>
                <a class="btn btn-sm btn-default" data-action="edit_selected" data-id="alias" data-api-url='edit/alias' data-api-attr='{"expand_alias":true}' ><i class="bi bi-arrows-angle-expand"></i> <?=$lang['mailbox']['add_alias_expand'];?></a>
                <a class="btn btn-sm btn-success" href="#" data-toggle="modal" data-target="#addAliasModal"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_alias'];?></a>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-syncjobs">
          <div class="panel panel-default">
            <div class="panel-heading">
              <?=$lang['mailbox']['sync_jobs'];?> <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right hidden-xs">
                <button data-acl="<?=$_SESSION['acl']['syncjobs'];?>" class="btn btn-xs btn-success" href="#" data-toggle="modal" data-target="#addSyncJobModalAdmin"><i class="bi bi-plus-lg"></i> <?=$lang['user']['create_syncjob'];?></button>
                <button class="btn btn-xs btn-default refresh_table" data-draw="draw_sync_job_table" data-table="sync_job_table"><?=$lang['admin']['refresh'];?></button>
                <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown"><?=$lang['mailbox']['table_size'];?> 
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" data-table-id="sync_job_table" role="menu">
                  <li><a href="#" data-page-size="3"><?=sprintf($lang['mailbox']['table_size_show_n'], 3);?></a></li>
                  <li><a href="#" data-page-size="10"><?=sprintf($lang['mailbox']['table_size_show_n'], 10);?></a></li>
                  <li><a href="#" data-page-size="20"><?=sprintf($lang['mailbox']['table_size_show_n'], 20);?></a></li>
                  <li><a href="#" data-page-size="50"><?=sprintf($lang['mailbox']['table_size_show_n'], 50);?></a></li>
                  <li><a href="#" data-page-size="100"><?=sprintf($lang['mailbox']['table_size_show_n'], 100);?></a></li>
                  <li><a href="#" data-page-size="200"><?=sprintf($lang['mailbox']['table_size_show_n'], 200);?></a></li>
                </ul>
              </div>
            </div>
            <!-- <div class="mass-actions-mailbox" data-actions-header="true"></div> -->
            <div class="table-responsive">
              <table class="table table-striped" id="sync_job_table"></table>
            </div>
            <div class="mass-actions-mailbox">
              <div class="btn-group" data-acl="<?=$_SESSION['acl']['syncjobs'];?>">
                <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="syncjob" href="#"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></a>
                <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a data-action="edit_selected" data-id="syncjob" data-api-url='edit/syncjob' data-api-attr='{"last_run":""}' href="#"><?=$lang['mailbox']['last_run_reset'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li><a data-action="edit_selected" data-id="syncjob" data-api-url='edit/syncjob' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                  <li><a data-action="edit_selected" data-id="syncjob" data-api-url='edit/syncjob' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li><a data-action="delete_selected" data-id="syncjob" data-api-url='delete/syncjob' href="#"><?=$lang['mailbox']['remove'];?></a></li>
                </ul>
                <a class="btn btn-sm btn-success" href="#" data-toggle="modal" data-target="#addSyncJobModalAdmin"><i class="bi bi-plus-lg"></i> <?=$lang['user']['create_syncjob'];?></a>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-filters">
          <div class="panel panel-default">
            <div class="panel-heading">
              <?=$lang['mailbox']['filters'];?> <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right hidden-xs">
                <button class="btn btn-xs btn-success" href="#" data-acl="<?=$_SESSION['acl']['filters'];?>" data-toggle="modal" data-target="#addFilterModalAdmin"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_filter'];?></button>
                <button class="btn btn-xs btn-default refresh_table" data-draw="draw_filter_table" data-table="filter_table"><?=$lang['admin']['refresh'];?></button>
                <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown"><?=$lang['mailbox']['table_size'];?> 
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" data-table-id="filter_table" role="menu">
                  <li><a href="#" data-page-size="3"><?=sprintf($lang['mailbox']['table_size_show_n'], 3);?></a></li>
                  <li><a href="#" data-page-size="10"><?=sprintf($lang['mailbox']['table_size_show_n'], 10);?></a></li>
                  <li><a href="#" data-page-size="20"><?=sprintf($lang['mailbox']['table_size_show_n'], 20);?></a></li>
                  <li><a href="#" data-page-size="50"><?=sprintf($lang['mailbox']['table_size_show_n'], 50);?></a></li>
                  <li><a href="#" data-page-size="100"><?=sprintf($lang['mailbox']['table_size_show_n'], 100);?></a></li>
                  <li><a href="#" data-page-size="200"><?=sprintf($lang['mailbox']['table_size_show_n'], 200);?></a></li>
                </ul>
              </div>
            </div>
            <div class="panel-body">
              <p class="help-block"><?=$lang['mailbox']['sieve_info'];?></p><br>
            </div>
            <!-- <div class="mass-actions-mailbox" data-actions-header="true"></div> -->
            <div class="table-responsive">
              <table class="table table-striped" id="filter_table"></table>
            </div>
            <div class="mass-actions-mailbox">
              <div class="btn-group" data-acl="<?=$_SESSION['acl']['filters'];?>">
                <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="filter_item" href="#"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></a>
                <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a data-action="edit_selected" data-id="filter_item" data-api-url='edit/filter' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                  <li><a data-action="edit_selected" data-id="filter_item" data-api-url='edit/filter' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li><a data-action="edit_selected" data-id="filter_item" data-api-url='edit/filter' data-api-attr='{"filter_type":"prefilter"}' href="#"><?=$lang['mailbox']['set_prefilter'];?></a></li>
                  <li><a data-action="edit_selected" data-id="filter_item" data-api-url='edit/filter' data-api-attr='{"filter_type":"postfilter"}' href="#"><?=$lang['mailbox']['set_postfilter'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li><a data-action="delete_selected" data-text="<?=$lang['user']['eas_reset'];?>?" data-id="filter_item" data-api-url='delete/filter' href="#"><?=$lang['mailbox']['remove'];?></a></li>
                </ul>
                <a class="btn btn-sm btn-success" href="#" data-toggle="modal" data-target="#addFilterModalAdmin"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_filter'];?></a>
              </div>
            </div>
            <div class="panel-body <?=($_SESSION['mailcow_cc_role'] == "admin") ?: 'hidden';?>">
              <?php
              $global_filters = mailbox('get', 'global_filter_details');
              ?>
              <div class="row">
                <div class="col-lg-6">
                <h5>Global Prefilter</h5>
                <form class="form-horizontal" data-cached-form="false" role="form" data-id="add_prefilter">
                  <div class="form-group">
                    <div class="col-sm-12">
                      <textarea autocorrect="off" spellcheck="false" autocapitalize="none" class="form-control textarea-code script_data" rows="10" name="script_data" required><?=$global_filters['prefilter'];?></textarea>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-sm-10 add_filter_btns">
                      <div class="btn-group">
                        <button class="btn btn-sm btn-default validate_sieve" href="#"><?=$lang['add']['validate'];?></button>
                        <button class="btn btn-sm btn-success add_sieve_script" data-action="add_item" data-id="add_prefilter" data-api-url='add/global-filter' data-api-attr='{"filter_type":"prefilter"}' href="#" disabled><i class="bi bi-check-lg"></i> <?=$lang['admin']['save'];?></button>
                      </div>
                    </div>
                  </div>
                </form>
                </div>
                <div class="col-lg-6">
                <h5>Global Postfilter</h5>
                <form class="form-horizontal" data-cached-form="false" role="form" data-id="add_postfilter">
                  <div class="form-group">
                    <div class="col-sm-12">
                      <textarea autocorrect="off" spellcheck="false" autocapitalize="none" class="form-control textarea-code script_data" rows="10" name="script_data" required><?=$global_filters['postfilter'];?></textarea>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-sm-10 add_filter_btns">
                      <div class="btn-group">
                        <button class="btn btn-sm btn-default validate_sieve" href="#"><?=$lang['add']['validate'];?></button>
                        <button class="btn btn-sm btn-success add_sieve_script" data-action="add_item" data-id="add_postfilter" data-api-url='add/global-filter' data-api-attr='{"filter_type":"postfilter"}' href="#" disabled><i class="bi bi-check-lg"></i> <?=$lang['admin']['save'];?></button>
                      </div>
                    </div>
                  </div>
                </form>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-bcc">
          <div class="panel panel-default">
            <div class="panel-heading">
              <?=$lang['mailbox']['bcc_maps'];?> <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right hidden-xs">
                <button class="btn btn-xs btn-success" href="#" data-acl="<?=$_SESSION['acl']['bcc_maps'];?>" data-toggle="modal" data-target="#addBCCModalAdmin"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_bcc_entry'];?></button>
                <button class="btn btn-xs btn-default refresh_table" data-draw="draw_bcc_table" data-table="bcc_table"><?=$lang['admin']['refresh'];?></button>
                <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown"><?=$lang['mailbox']['table_size'];?> 
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" data-table-id="bcc_table" role="menu">
                  <li><a href="#" data-page-size="3"><?=sprintf($lang['mailbox']['table_size_show_n'], 3);?></a></li>
                  <li><a href="#" data-page-size="10"><?=sprintf($lang['mailbox']['table_size_show_n'], 10);?></a></li>
                  <li><a href="#" data-page-size="20"><?=sprintf($lang['mailbox']['table_size_show_n'], 20);?></a></li>
                  <li><a href="#" data-page-size="50"><?=sprintf($lang['mailbox']['table_size_show_n'], 50);?></a></li>
                  <li><a href="#" data-page-size="100"><?=sprintf($lang['mailbox']['table_size_show_n'], 100);?></a></li>
                  <li><a href="#" data-page-size="200"><?=sprintf($lang['mailbox']['table_size_show_n'], 200);?></a></li>
                </ul>
              </div>
            </div>
            <p style="margin:10px" class="help-block"><?=$lang['mailbox']['bcc_info'];?></p>
            <!-- <div class="mass-actions-mailbox" data-actions-header="true"></div> -->
            <div class="table-responsive">
              <table class="table table-striped" id="bcc_table"></table>
            </div>
            <div class="mass-actions-mailbox">
              <div class="btn-group" data-acl="<?=$_SESSION['acl']['bcc_maps'];?>">
                <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="bcc" href="#"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></a>
                <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a data-action="edit_selected" data-id="bcc" data-api-url='edit/bcc' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                  <li><a data-action="edit_selected" data-id="bcc" data-api-url='edit/bcc' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li><a data-action="edit_selected" data-id="bcc" data-api-url='edit/bcc' data-api-attr='{"type":"sender"}' href="#"><?=$lang['mailbox']['bcc_to_sender'];?></a></li>
                  <li><a data-action="edit_selected" data-id="bcc" data-api-url='edit/bcc' data-api-attr='{"type":"rcpt"}' href="#"><?=$lang['mailbox']['bcc_to_rcpt'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li><a data-action="delete_selected" data-id="bcc" data-api-url='delete/bcc' href="#"><?=$lang['mailbox']['remove'];?></a></li>
                </ul>
                <a class="btn btn-sm btn-success" href="#" data-toggle="modal" data-target="#addBCCModalAdmin"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_bcc_entry'];?></a>
              </div>
            </div>
          </div>
          <div class="panel panel-default <?=($_SESSION['mailcow_cc_role'] == "admin") ?: 'hidden';?>">
            <div class="panel-heading">
              <?=$lang['mailbox']['recipient_maps'];?> <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right hidden-xs">
                <button class="btn btn-xs btn-success" href="#" data-toggle="modal" data-target="#addRecipientMapModalAdmin"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_recipient_map_entry'];?></button>
                <button class="btn btn-xs btn-default refresh_table" data-draw="draw_recipient_map_table" data-table="recipient_map_table"><?=$lang['admin']['refresh'];?></button>
                <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown"><?=$lang['mailbox']['table_size'];?> 
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" data-table-id="recipient_map_table" role="menu">
                  <li><a href="#" data-page-size="3"><?=sprintf($lang['mailbox']['table_size_show_n'], 3);?></a></li>
                  <li><a href="#" data-page-size="10"><?=sprintf($lang['mailbox']['table_size_show_n'], 10);?></a></li>
                  <li><a href="#" data-page-size="20"><?=sprintf($lang['mailbox']['table_size_show_n'], 20);?></a></li>
                  <li><a href="#" data-page-size="50"><?=sprintf($lang['mailbox']['table_size_show_n'], 50);?></a></li>
                  <li><a href="#" data-page-size="100"><?=sprintf($lang['mailbox']['table_size_show_n'], 100);?></a></li>
                  <li><a href="#" data-page-size="200"><?=sprintf($lang['mailbox']['table_size_show_n'], 200);?></a></li>
                </ul>
              </div>
            </div>
            <p style="margin:10px" class="help-block"><?=$lang['mailbox']['recipient_map_info'];?></p>
            <!-- <div class="mass-actions-mailbox" data-actions-header="true"></div> -->
            <div class="table-responsive">
              <table class="table table-striped" id="recipient_map_table"></table>
            </div>
            <div class="mass-actions-mailbox">
              <div class="btn-group">
                <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="recipient_map" href="#"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></a>
                <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a data-action="edit_selected" data-id="recipient_map" data-api-url='edit/recipient_map' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                  <li><a data-action="edit_selected" data-id="recipient_map" data-api-url='edit/recipient_map' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li><a data-action="delete_selected" data-id="recipient_map" data-api-url='delete/recipient_map' href="#"><?=$lang['mailbox']['remove'];?></a></li>
                </ul>
                <a class="btn btn-sm btn-success" href="#" data-toggle="modal" data-target="#addRecipientMapModalAdmin"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_recipient_map_entry'];?></a>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane <?=($_SESSION['mailcow_cc_role'] == "admin") ?: 'hidden';?>" id="tab-tls-policy">
          <div class="panel panel-default">
            <div class="panel-heading">
              <?=$lang['mailbox']['tls_policy_maps_long'];?> <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right hidden-xs">
                <button class="btn btn-xs btn-success" href="#" data-toggle="modal" data-target="#addTLSPolicyMapAdmin"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_tls_policy_map'];?></button>
                <button class="btn btn-xs btn-default refresh_table" data-draw="draw_tls_policy_table" data-table="tls_policy_table"><?=$lang['admin']['refresh'];?></button>
                <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown"><?=$lang['mailbox']['table_size'];?> 
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" data-table-id="tls_policy_table" role="menu">
                  <li><a href="#" data-page-size="3"><?=sprintf($lang['mailbox']['table_size_show_n'], 3);?></a></li>
                  <li><a href="#" data-page-size="10"><?=sprintf($lang['mailbox']['table_size_show_n'], 10);?></a></li>
                  <li><a href="#" data-page-size="20"><?=sprintf($lang['mailbox']['table_size_show_n'], 20);?></a></li>
                  <li><a href="#" data-page-size="50"><?=sprintf($lang['mailbox']['table_size_show_n'], 50);?></a></li>
                  <li><a href="#" data-page-size="100"><?=sprintf($lang['mailbox']['table_size_show_n'], 100);?></a></li>
                  <li><a href="#" data-page-size="200"><?=sprintf($lang['mailbox']['table_size_show_n'], 200);?></a></li>
                </ul>
              </div>
            </div>
            <p style="margin:10px" class="help-block"><?=$lang['mailbox']['tls_policy_maps_info'];?></p>
            <!-- <div class="mass-actions-mailbox" data-actions-header="true"></div> -->
            <div class="table-responsive">
              <table class="table table-striped" id="tls_policy_table"></table>
            </div>
            <div class="mass-actions-mailbox">
              <div class="btn-group">
                <a class="btn btn-sm btn-default" id="toggle_multi_select_all" data-id="tls-policy-map" href="#"><i class="bi bi-check-all"></i> <?=$lang['mailbox']['toggle_all'];?></a>
                <a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['mailbox']['quick_actions'];?> <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a data-action="edit_selected" data-id="tls-policy-map" data-api-url='edit/tls-policy-map' data-api-attr='{"active":"1"}' href="#"><?=$lang['mailbox']['activate'];?></a></li>
                  <li><a data-action="edit_selected" data-id="tls-policy-map" data-api-url='edit/tls-policy-map' data-api-attr='{"active":"0"}' href="#"><?=$lang['mailbox']['deactivate'];?></a></li>
                  <li role="separator" class="divider"></li>
                  <li><a data-action="delete_selected" data-id="tls-policy-map" data-api-url='delete/tls-policy-map' href="#"><?=$lang['mailbox']['remove'];?></a></li>
                </ul>
                <a class="btn btn-sm btn-success" href="#" data-toggle="modal" data-target="#addTLSPolicyMapAdmin"><i class="bi bi-plus-lg"></i> <?=$lang['mailbox']['add_tls_policy_map'];?></a>
              </div>
            </div>
          </div>
        </div>
      </div> <!-- /tab-content -->
    </div> <!-- /col-md-12 -->
  </div> <!-- /row -->
</div> <!-- /container -->
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/modals/mailbox.php';
?>
<script type='text/javascript'>
<?php
$lang_mailbox = json_encode($lang['mailbox']);
echo "var lang = ". $lang_mailbox . ";\n";
echo "var acl = '". json_encode($_SESSION['acl']) . "';\n";
echo "var csrf_token = '". $_SESSION['CSRF']['TOKEN'] . "';\n";
$role = ($_SESSION['mailcow_cc_role'] == "admin") ? 'admin' : 'domainadmin';
$is_dual = (!empty($_SESSION["dual-login"]["username"])) ? 'true' : 'false';
echo "var role = '". $role . "';\n";
echo "var is_dual = " . $is_dual . ";\n";
echo "var pagination_size = '". $PAGINATION_SIZE . "';\n";
$ALLOW_ADMIN_EMAIL_LOGIN = (preg_match(
	"/^([yY][eE][sS]|[yY])+$/",
    $_ENV["ALLOW_ADMIN_EMAIL_LOGIN"]
)) ? "true" : "false";
echo "var ALLOW_ADMIN_EMAIL_LOGIN = " . $ALLOW_ADMIN_EMAIL_LOGIN . ";\n";
?>
</script>
<?php
$js_minifier->add('/web/js/site/mailbox.js');
$js_minifier->add('/web/js/presets/sieveMailbox.js');
$js_minifier->add('/web/js/site/pwgen.js');
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/footer.inc.php';
}
else {
	header('Location: /');
	exit();
}
?>
