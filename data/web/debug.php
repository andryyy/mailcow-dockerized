<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';

if (isset($_SESSION['mailcow_cc_role']) && $_SESSION['mailcow_cc_role'] == "admin") {
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/header.inc.php';
$_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
$solr_status = (preg_match("/^([yY][eE][sS]|[yY])+$/", $_ENV["SKIP_SOLR"])) ? false : solr_status();
$clamd_status = (preg_match("/^([yY][eE][sS]|[yY])+$/", $_ENV["SKIP_CLAMD"])) ? false : true;
?>
<div class="container">

  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#tab-containers" aria-controls="tab-containers" role="tab" data-toggle="tab"><?=$lang['debug']['system_containers'];?></a></li>
    <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#"><?=$lang['debug']['logs'];?>
      <span class="caret"></span></a>
      <ul class="dropdown-menu">
        <li role="presentation"><span class="dropdown-desc"><?=$lang['debug']['in_memory_logs'];?></span></li>
        <li role="presentation"><a href="#tab-postfix-logs" aria-controls="tab-postfix-logs" role="tab" data-toggle="tab">Postfix</a></li>
        <li role="presentation"><a href="#tab-dovecot-logs" aria-controls="tab-dovecot-logs" role="tab" data-toggle="tab">Dovecot</a></li>
        <li role="presentation"><a href="#tab-sogo-logs" aria-controls="tab-sogo-logs" role="tab" data-toggle="tab">SOGo</a></li>
        <li role="presentation"><a href="#tab-netfilter-logs" aria-controls="tab-netfilter-logs" role="tab" data-toggle="tab">Netfilter</a></li>
        <li role="presentation"><a href="#tab-autodiscover-logs" aria-controls="tab-autodiscover-logs" role="tab" data-toggle="tab">Autodiscover</a></li>
        <li role="presentation"><a href="#tab-watchdog-logs" aria-controls="tab-watchdog-logs" role="tab" data-toggle="tab">Watchdog</a></li>
        <li role="presentation"><a href="#tab-acme-logs" aria-controls="tab-acme-logs" role="tab" data-toggle="tab">ACME</a></li>
        <li role="presentation"><a href="#tab-api-logs" aria-controls="tab-api-logs" role="tab" data-toggle="tab">API</a></li>
        <li role="presentation"><a href="#tab-api-rl" aria-controls="tab-api-rl" role="tab" data-toggle="tab">Ratelimits</a></li>
        <li role="presentation"><span class="dropdown-desc"><?=$lang['debug']['external_logs'];?></span></li>
        <li role="presentation"><a href="#tab-rspamd-history" aria-controls="tab-rspamd-history" role="tab" data-toggle="tab">Rspamd</a></li>
        <li role="presentation"><span class="dropdown-desc"><?=$lang['debug']['static_logs'];?></span></li>
        <li role="presentation"><a href="#tab-ui" aria-controls="tab-ui" role="tab" data-toggle="tab">mailcow UI</a></li>
        <li role="presentation"><a href="#tab-sasl" aria-controls="tab-sasl" role="tab" data-toggle="tab">SASL</a></li>
      </ul>
    </li>
  </ul>

	<div class="row">
		<div class="col-md-12">
      <div class="tab-content" style="padding-top:20px">
        <div class="debug-log-info"><?=sprintf($lang['debug']['log_info'], getenv('LOG_LINES') + 1);?></div>
        <?php
          $exec_fields = array('cmd' => 'system', 'task' => 'df', 'dir' => '/var/vmail');
          $vmail_df = explode(',', json_decode(docker('post', 'dovecot-mailcow', 'exec', $exec_fields), true));
        ?>
        <div role="tabpanel" class="tab-pane active" id="tab-containers">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title"><?=$lang['debug']['disk_usage'];?></h3>
            </div>
            <div class="panel-body">
              <div class="row">
                <div class="col-sm-3">
                  <p><i class="bi bi-hdd-fill"></i> <?=$vmail_df[0];?></p>
                  <p><?=$vmail_df[2];?> / <?=$vmail_df[1];?> (<?=$vmail_df[4];?>)</p>
                </div>
                <div class="col-sm-9">
                  <div class="progress">
                    <div class="progress-bar progress-bar-info" role="progressbar" style="width:<?=$vmail_df[4];?>"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title"><?=$lang['debug']['solr_status'];?></h3>
            </div>
            <div class="panel-body">
              <div class="row">
                <div class="col-sm-3">
                  <p><img class="img-responsive" alt="Solr Logo" width="128px" src="data:image/png;base64,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" /></p>
                </div>
                <div class="col-sm-9">
                  <?php
                  if ($solr_status !== false):
                  ?>
                  <div class="progress">
                    <div class="progress-bar progress-bar-info" role="progressbar" style="width:<?=round($solr_status['jvm']['memory']['raw']['used%']);?>%"></div>
                  </div>
                  <p><?=$lang['debug']['jvm_memory_solr'];?>: <?=$solr_status['jvm']['memory']['total'] - $solr_status['jvm']['memory']['free'];?> / <?=$solr_status['jvm']['memory']['total'];?>
                    (<?=round($solr_status['jvm']['memory']['raw']['used%']);?>%)</p>
                  <hr>
                  <p><?=$lang['debug']['uptime'];?>: <?=round($solr_status['status']['dovecot-fts']['uptime'] / 1000 / 60 / 60);?>h</p>
                  <p><?=$lang['debug']['started_at'];?>: <span class="parse_date"><?=$solr_status['status']['dovecot-fts']['startTime'];?></span></p>
                  <p><?=$lang['debug']['last_modified'];?>: <span class="parse_date"><?=$solr_status['status']['dovecot-fts']['index']['lastModified'];?></span></p>
                  <p><?=$lang['debug']['size'];?>: <?=$solr_status['status']['dovecot-fts']['index']['size'];?></p>
                  <p><i class="bi bi-file-text"></i> <?=$lang['debug']['docs'];?>: <?=$solr_status['status']['dovecot-fts']['index']['numDocs'];?></p>
                  <?php
                  else:
                  ?>
                  <p><?=$lang['debug']['solr_dead'];?></p>
                  <?php
                  endif;
                  ?>
                </div>
              </div>
            </div>
          </div>
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title"><?=$lang['debug']['containers_info'];?></h3>
            </div>
            <div class="panel-body">
            <ul class="list-group">
            <?php
            $containers = (docker('info'));
            ksort($containers);
            foreach ($containers as $container => $container_info) {
              if ($container == 'clamd-mailcow' && $clamd_status === false) { continue; }
              if ($container == 'solr-mailcow' && $solr_status === false) { continue; }
              ?>
              <li class="list-group-item">
              <?=$container . ' (' . $container_info['Config']['Image'] . ')';?>
              <?php
              date_default_timezone_set('UTC');
              $StartedAt = date_parse($container_info['State']['StartedAt']);
              if ($StartedAt['hour'] !== false) {
                $date = new \DateTime();
                $date->setTimestamp(mktime(
                  $StartedAt['hour'],
                  $StartedAt['minute'],
                  $StartedAt['second'],
                  $StartedAt['month'],
                  $StartedAt['day'],
                  $StartedAt['year']));
                $user_tz = new DateTimeZone(getenv('TZ'));
                $date->setTimezone($user_tz);
                $started = $date->format('r');
              }
              else {
                $started = '?';
              }
              ?>
              <br class="visible-xs" />
              <small>(<?=$lang['debug']['started_on'];?> <span class="parse_date"><?=$started;?></span>)</small>
              <a href data-toggle="modal" data-container="<?=$container;?>" data-target="#RestartContainer" class="pull-right btn btn-xs btn-default"><?=$lang['debug']['restart_container'];?>
              <i class="pull-right bi <?=($container_info['State'] !== false && !empty($container_info['State'])) ? (($container_info['State']['Running'] == 1) ? 'bi-record-fill text-success' : 'bi-record-fill text-danger') : 'default'; ?>"></i></a>
              </li>
              <?php
              }
            ?>
            </ul>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-postfix-logs">
          <div class="panel panel-xs-lg panel-default">
            <div class="panel-heading">Postfix <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="general_syslog" data-table="postfix_log" data-log-url="postfix" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="general_syslog" data-table="postfix_log" data-log-url="postfix" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-xs-lg btn-default refresh_table" data-draw="draw_postfix_logs" data-table="postfix_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="postfix_log"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-ui">
          <div class="panel panel-xs-lg panel-default">
            <div class="panel-heading">mailcow UI <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="mailcow_ui" data-table="ui_logs" data-log-url="ui" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="mailcow_ui" data-table="ui_logs" data-log-url="ui" data-nrows="10000">+ 10000</button>
                <button class="btn btn-xs btn-xs-lg btn-default refresh_table" data-draw="draw_ui_logs" data-table="ui_logs"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="ui_logs"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-sasl">
          <div class="panel panel-xs-lg panel-default">
            <div class="panel-heading">SASL <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="sasl_log_table" data-table="sasl_logs" data-log-url="ui" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="sasl_log_table" data-table="sasl_logs" data-log-url="ui" data-nrows="10000">+ 10000</button>
                <button class="btn btn-xs btn-xs-lg btn-default refresh_table" data-draw="draw_sasl_logs" data-table="sasl_logs"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="sasl_logs"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-dovecot-logs">
          <div class="panel panel-xs-lg panel-default">
            <div class="panel-heading">Dovecot <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="general_syslog" data-table="dovecot_log" data-log-url="dovecot" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="general_syslog" data-table="dovecot_log" data-log-url="dovecot" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-xs-lg btn-default refresh_table" data-draw="draw_dovecot_logs" data-table="dovecot_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="dovecot_log"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-sogo-logs">
          <div class="panel panel-xs-lg panel-default">
            <div class="panel-heading">SOGo <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="general_syslog" data-table="sogo_log" data-log-url="sogo" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="general_syslog" data-table="sogo_log" data-log-url="sogo" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-xs-lg btn-default refresh_table" data-draw="draw_sogo_logs" data-table="sogo_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="sogo_log"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-netfilter-logs">
          <div class="panel panel-xs-lg panel-default">
            <div class="panel-heading">Netfilter <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="general_syslog" data-table="netfilter_log" data-log-url="netfilter" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="general_syslog" data-table="netfilter_log" data-log-url="netfilter" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-xs-lg btn-default refresh_table" data-draw="draw_netfilter_logs" data-table="netfilter_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="netfilter_log"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-rspamd-history">
          <div class="panel panel-xs-lg panel-default">
            <div class="panel-heading">Rspamd history <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="rspamd_history" data-table="rspamd_history" data-log-url="rspamd-history" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="rspamd_history" data-table="rspamd_history" data-log-url="rspamd-history" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-xs-lg btn-default refresh_table" data-draw="draw_rspamd_history" data-table="rspamd_history"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <legend><?=$lang['debug']['chart_this_server'];?></legend>
              <div id="chart-container">
                <canvas id="rspamd_donut" style="width:100%;height:400px"></canvas>
              </div>
              <legend><?=$lang['debug']['history_all_servers'];?></legend>
              <div class="table-responsive">
                <table class="table table-striped table-condensed log-table" id="rspamd_history"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-autodiscover-logs">
          <div class="panel panel-xs-lg panel-default">
            <div class="panel-heading">Autodiscover <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="autodiscover_log" data-table="autodiscover_log" data-log-url="autodiscover" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="autodiscover_log" data-table="autodiscover_log" data-log-url="autodiscover" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-xs-lg btn-default refresh_table" data-draw="draw_autodiscover_logs" data-table="autodiscover_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="autodiscover_log"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-watchdog-logs">
          <div class="panel panel-xs-lg panel-default">
            <div class="panel-heading">Watchdog <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="watchdog" data-table="watchdog_log" data-log-url="watchdog" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="watchdog" data-table="watchdog_log" data-log-url="watchdog" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-xs-lg btn-default refresh_table" data-draw="draw_watchdog_logs" data-table="watchdog_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="watchdog_log"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-acme-logs">
          <div class="panel panel-xs-lg panel-default">
            <div class="panel-heading">ACME <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="general_syslog" data-table="acme_log" data-log-url="acme" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="general_syslog" data-table="acme_log" data-log-url="acme" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-xs-lg btn-default refresh_table" data-draw="draw_acme_logs" data-table="acme_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="acme_log"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-api-logs">
          <div class="panel panel-xs-lg panel-default">
            <div class="panel-heading">API <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="apilog" data-table="api_log" data-log-url="api" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="apilog" data-table="api_log" data-log-url="api" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-xs-lg btn-default refresh_table" data-draw="draw_api_logs" data-table="api_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="api_log"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-api-rl">
          <div class="panel panel-xs-lg panel-default">
            <div class="panel-heading">Ratelimits <span class="badge badge-info table-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="rllog" data-table="rl_log" data-log-url="ratelimited" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-xs-lg btn-default add_log_lines" data-post-process="rllog" data-table="rl_log" data-log-url="ratelimited" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-xs-lg btn-default refresh_table" data-draw="draw_rl_logs" data-table="rl_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <p class="help-block"><?=$lang['admin']['hash_remove_info'];?></p>
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="rl_log"></table>
              </div>
            </div>
          </div>
        </div>

      </div> <!-- /tab-content -->
    </div> <!-- /col-md-12 -->
  </div> <!-- /row -->
</div> <!-- /container -->
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/modals/debug.php';
?>
<script type='text/javascript'>
<?php
$lang_admin = json_encode($lang['admin']);
echo "var lang = ". $lang_admin . ";\n";
echo "var csrf_token = '". $_SESSION['CSRF']['TOKEN'] . "';\n";
echo "var log_pagination_size = '". $LOG_PAGINATION_SIZE . "';\n";

?>
</script>
<?php
$js_minifier->add('/web/js/site/debug.js');
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/footer.inc.php';
}
else {
	header('Location: /');
	exit();
}
?>
