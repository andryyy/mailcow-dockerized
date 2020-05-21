<?php
function init_db_schema() {
  try {
    global $pdo;

    $db_version = "12052020_1828";

    $stmt = $pdo->query("SHOW TABLES LIKE 'versions'");
    $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
    if ($num_results != 0) {
      $stmt = $pdo->query("SELECT `version` FROM `versions` WHERE `application` = 'db_schema'");
      if ($stmt->fetch(PDO::FETCH_ASSOC)['version'] == $db_version) {
        return true;
      }
      if (!preg_match('/y|yes/i', getenv('MASTER'))) {
        $_SESSION['return'][] = array(
          'type' => 'warning',
          'log' => array(__FUNCTION__),
          'msg' => 'Database not initialized: not running db_init on slave.'
        );
        return true;
      }
    }

    $views = array(
    "grouped_mail_aliases" => "CREATE VIEW grouped_mail_aliases (username, aliases) AS
      SELECT goto, IFNULL(GROUP_CONCAT(address ORDER BY address SEPARATOR ' '), '') AS address FROM alias
      WHERE address!=goto
      AND active = '1'
      AND sogo_visible = '1'
      AND address NOT LIKE '@%'
      GROUP BY goto;",
    // START
    // Unused at the moment - we cannot allow to show a foreign mailbox as sender address in SOGo, as SOGo does not like this
    // We need to create delegation in SOGo AND set a sender_acl in mailcow to allow to send as user X
    "grouped_sender_acl" => "CREATE VIEW grouped_sender_acl (username, send_as_acl) AS
      SELECT logged_in_as, IFNULL(GROUP_CONCAT(send_as SEPARATOR ' '), '') AS send_as_acl FROM sender_acl
      WHERE send_as NOT LIKE '@%'
      GROUP BY logged_in_as;",
    // END 
    "grouped_sender_acl_external" => "CREATE VIEW grouped_sender_acl_external (username, send_as_acl) AS
      SELECT logged_in_as, IFNULL(GROUP_CONCAT(send_as SEPARATOR ' '), '') AS send_as_acl FROM sender_acl
      WHERE send_as NOT LIKE '@%' AND external = '1'
      GROUP BY logged_in_as;",
    "grouped_domain_alias_address" => "CREATE VIEW grouped_domain_alias_address (username, ad_alias) AS
      SELECT username, IFNULL(GROUP_CONCAT(local_part, '@', alias_domain SEPARATOR ' '), '') AS ad_alias FROM mailbox
      LEFT OUTER JOIN alias_domain ON target_domain=domain
      GROUP BY username;",
    "sieve_before" => "CREATE VIEW sieve_before (id, username, script_name, script_data) AS
      SELECT md5(script_data), username, script_name, script_data FROM sieve_filters
      WHERE filter_type = 'prefilter';",
    "sieve_after" => "CREATE VIEW sieve_after (id, username, script_name, script_data) AS
      SELECT md5(script_data), username, script_name, script_data FROM sieve_filters
      WHERE filter_type = 'postfilter';"
    );

    $tables = array(
      "versions" => array(
        "cols" => array(
          "application" => "VARCHAR(255) NOT NULL",
          "version" => "VARCHAR(100) NOT NULL",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
        ),
        "keys" => array(
          "primary" => array(
            "" => array("application")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "admin" => array(
        "cols" => array(
          "username" => "VARCHAR(255) NOT NULL",
          "password" => "VARCHAR(255) NOT NULL",
          "superadmin" => "TINYINT(1) NOT NULL DEFAULT '0'",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "modified" => "DATETIME ON UPDATE NOW(0)",
          "active" => "TINYINT(1) NOT NULL DEFAULT '1'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("username")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "_sogo_static_view" => array(
        "cols" => array(
          "c_uid" => "VARCHAR(255) NOT NULL",
          "domain" => "VARCHAR(255) NOT NULL",
          "c_name" => "VARCHAR(255) NOT NULL",
          "c_password" => "VARCHAR(255) NOT NULL DEFAULT ''",
          "c_cn" => "VARCHAR(255)",
          "mail" => "VARCHAR(255) NOT NULL",
          // TODO -> use TEXT and check if SOGo login breaks on empty aliases
          "aliases" => "TEXT NOT NULL",
          "ad_aliases" => "VARCHAR(6144) NOT NULL DEFAULT ''",
          "ext_acl" => "VARCHAR(6144) NOT NULL DEFAULT ''",
          "kind" => "VARCHAR(100) NOT NULL DEFAULT ''",
          "multiple_bookings" => "INT NOT NULL DEFAULT -1"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("c_uid")
          ),
          "key" => array(
            "domain" => array("domain")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "relayhosts" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "hostname" => "VARCHAR(255) NOT NULL",
          "username" => "VARCHAR(255) NOT NULL",
          "password" => "VARCHAR(255) NOT NULL",
          "active" => "TINYINT(1) NOT NULL DEFAULT '1'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          ),
          "key" => array(
            "hostname" => array("hostname")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "transports" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "destination" => "VARCHAR(255) NOT NULL",
          "nexthop" => "VARCHAR(255) NOT NULL",
          "username" => "VARCHAR(255) NOT NULL",
          "password" => "VARCHAR(255) NOT NULL",
          "lookup_mx" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "active" => "TINYINT(1) NOT NULL DEFAULT '1'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          ),
          "key" => array(
            "destination" => array("destination"),
            "nexthop" => array("nexthop"),
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "alias" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "address" => "VARCHAR(255) NOT NULL",
          "goto" => "TEXT NOT NULL",
          "domain" => "VARCHAR(255) NOT NULL",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "modified" => "DATETIME ON UPDATE CURRENT_TIMESTAMP",
          "private_comment" => "TEXT",
          "public_comment" => "TEXT",
          "sogo_visible" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "active" => "TINYINT(1) NOT NULL DEFAULT '1'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          ),
          "unique" => array(
            "address" => array("address")
          ),
          "key" => array(
            "domain" => array("domain")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "api" => array(
        "cols" => array(
          "api_key" => "VARCHAR(255) NOT NULL",
          "allow_from" => "VARCHAR(512) NOT NULL",
          "skip_ip_check" => "TINYINT(1) NOT NULL DEFAULT '0'",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "modified" => "DATETIME ON UPDATE NOW(0)",
          "access" => "ENUM('ro', 'rw') NOT NULL DEFAULT 'rw'",
          "active" => "TINYINT(1) NOT NULL DEFAULT '1'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("api_key")
          ),
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "sender_acl" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "logged_in_as" => "VARCHAR(255) NOT NULL",
          "send_as" => "VARCHAR(255) NOT NULL",
          "external" => "TINYINT(1) NOT NULL DEFAULT '0'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "domain" => array(
        // Todo: Move some attributes to json
        "cols" => array(
          "domain" => "VARCHAR(255) NOT NULL",
          "description" => "VARCHAR(255)",
          "aliases" => "INT(10) NOT NULL DEFAULT '0'",
          "mailboxes" => "INT(10) NOT NULL DEFAULT '0'",
          "defquota" => "BIGINT(20) NOT NULL DEFAULT '3072'",
          "maxquota" => "BIGINT(20) NOT NULL DEFAULT '102400'",
          "quota" => "BIGINT(20) NOT NULL DEFAULT '102400'",
          "relayhost" => "VARCHAR(255) NOT NULL DEFAULT '0'",
          "backupmx" => "TINYINT(1) NOT NULL DEFAULT '0'",
          "gal" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "relay_all_recipients" => "TINYINT(1) NOT NULL DEFAULT '0'",
          "relay_unknown_only" => "TINYINT(1) NOT NULL DEFAULT '0'",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "modified" => "DATETIME ON UPDATE CURRENT_TIMESTAMP",
          "active" => "TINYINT(1) NOT NULL DEFAULT '1'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("domain")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "tls_policy_override" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "dest" => "VARCHAR(255) NOT NULL",
          "policy" => "ENUM('none', 'may', 'encrypt', 'dane', 'dane-only', 'fingerprint', 'verify', 'secure') NOT NULL",
          "parameters" => "VARCHAR(255) DEFAULT ''",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "modified" => "DATETIME ON UPDATE CURRENT_TIMESTAMP",
          "active" => "TINYINT(1) NOT NULL DEFAULT '1'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          ),
          "unique" => array(
            "dest" => array("dest")
          ),
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "quarantine" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "qid" => "VARCHAR(30) NOT NULL",
          "subject" => "VARCHAR(500)",
          "score" => "FLOAT(8,2)",
          "ip" => "VARCHAR(50)",
          "action" => "CHAR(20) NOT NULL DEFAULT 'unknown'",
          "symbols" => "JSON",
          "sender" => "VARCHAR(255) NOT NULL DEFAULT 'unknown'",
          "rcpt" => "VARCHAR(255)",
          "msg" => "LONGTEXT",
          "domain" => "VARCHAR(255)",
          "notified" => "TINYINT(1) NOT NULL DEFAULT '0'",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "user" => "VARCHAR(255) NOT NULL DEFAULT 'unknown'",
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "mailbox" => array(
        "cols" => array(
          "username" => "VARCHAR(255) NOT NULL",
          "password" => "VARCHAR(255) NOT NULL",
          "name" => "VARCHAR(255)",
          // mailbox_path_prefix is followed by domain/local_part/
          "mailbox_path_prefix" => "VARCHAR(150) DEFAULT '/var/vmail/'",
          "quota" => "BIGINT(20) NOT NULL DEFAULT '102400'",
          "local_part" => "VARCHAR(255) NOT NULL",
          "domain" => "VARCHAR(255) NOT NULL",
          "attributes" => "JSON",
          "kind" => "VARCHAR(100) NOT NULL DEFAULT ''",
          "multiple_bookings" => "INT NOT NULL DEFAULT -1",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "modified" => "DATETIME ON UPDATE CURRENT_TIMESTAMP",
          "active" => "TINYINT(1) NOT NULL DEFAULT '1'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("username")
          ),
          "key" => array(
            "domain" => array("domain")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "sieve_filters" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "username" => "VARCHAR(255) NOT NULL",
          "script_desc" => "VARCHAR(255) NOT NULL",
          "script_name" => "ENUM('active','inactive')",
          "script_data" => "TEXT NOT NULL",
          "filter_type" => "ENUM('postfilter','prefilter')",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "modified" => "DATETIME ON UPDATE CURRENT_TIMESTAMP"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          ),
          "key" => array(
            "username" => array("username"),
            "script_desc" => array("script_desc")
          ),
          "fkey" => array(
            "fk_username_sieve_global_before" => array(
              "col" => "username",
              "ref" => "mailbox.username",
              "delete" => "CASCADE",
              "update" => "NO ACTION"
            )
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "app_passwd" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "name" => "VARCHAR(255) NOT NULL",
          "mailbox" => "VARCHAR(255) NOT NULL",
          "domain" => "VARCHAR(255) NOT NULL",
          "password" => "VARCHAR(255) NOT NULL",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "modified" => "DATETIME ON UPDATE CURRENT_TIMESTAMP",
          "active" => "TINYINT(1) NOT NULL DEFAULT '1'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          ),
          "key" => array(
            "mailbox" => array("mailbox"),
            "password" => array("password"),
            "domain" => array("domain"),
          ),
          "fkey" => array(
            "fk_username_app_passwd" => array(
              "col" => "mailbox",
              "ref" => "mailbox.username",
              "delete" => "CASCADE",
              "update" => "NO ACTION"
            )
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "user_acl" => array(
        "cols" => array(
          "username" => "VARCHAR(255) NOT NULL",
          "spam_alias" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "tls_policy" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "spam_score" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "spam_policy" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "delimiter_action" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "syncjobs" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "eas_reset" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "sogo_profile_reset" => "TINYINT(1) NOT NULL DEFAULT '0'",
          "pushover" => "TINYINT(1) NOT NULL DEFAULT '1'",
          // quarantine is for quarantine actions, todo: rename
          "quarantine" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "quarantine_attachments" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "quarantine_notification" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "app_passwds" => "TINYINT(1) NOT NULL DEFAULT '1'",
          ),
        "keys" => array(
          "primary" => array(
            "" => array("username")
          ),
          "fkey" => array(
            "fk_username" => array(
              "col" => "username",
              "ref" => "mailbox.username",
              "delete" => "CASCADE",
              "update" => "NO ACTION"
            )
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "alias_domain" => array(
        "cols" => array(
          "alias_domain" => "VARCHAR(255) NOT NULL",
          "target_domain" => "VARCHAR(255) NOT NULL",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "modified" => "DATETIME ON UPDATE CURRENT_TIMESTAMP",
          "active" => "TINYINT(1) NOT NULL DEFAULT '1'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("alias_domain")
          ),
          "key" => array(
            "active" => array("active"),
            "target_domain" => array("target_domain")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "spamalias" => array(
        "cols" => array(
          "address" => "VARCHAR(255) NOT NULL",
          "goto" => "TEXT NOT NULL",
          "validity" => "INT(11) NOT NULL"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("address")
          ),
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "filterconf" => array(
        "cols" => array(
          "object" => "VARCHAR(255) NOT NULL DEFAULT ''",
          "option" => "VARCHAR(50) NOT NULL DEFAULT ''",
          "value" => "VARCHAR(100) NOT NULL DEFAULT ''",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "modified" => "DATETIME ON UPDATE CURRENT_TIMESTAMP",
          "prefid" => "INT(11) NOT NULL AUTO_INCREMENT"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("prefid")
          ),
          "key" => array(
            "object" => array("object")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "settingsmap" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "desc" => "VARCHAR(255) NOT NULL",
          "content" => "LONGTEXT NOT NULL",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "modified" => "DATETIME ON UPDATE CURRENT_TIMESTAMP",
          "active" => "TINYINT(1) NOT NULL DEFAULT '0'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "logs" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "task" => "CHAR(32) NOT NULL DEFAULT '000000'",
          "type" => "VARCHAR(32) DEFAULT ''",
          "msg" => "TEXT",
          "call" => "TEXT",
          "user" => "VARCHAR(64) NOT NULL",
          "role" => "VARCHAR(32) NOT NULL",
          "remote" => "VARCHAR(39) NOT NULL",
          "time" => "INT(11) NOT NULL"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "quota2" => array(
        "cols" => array(
          "username" => "VARCHAR(255) NOT NULL",
          "bytes" => "BIGINT(20) NOT NULL DEFAULT '0'",
          "messages" => "BIGINT(20) NOT NULL DEFAULT '0'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("username")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "quota2replica" => array(
        "cols" => array(
          "username" => "VARCHAR(255) NOT NULL",
          "bytes" => "BIGINT(20) NOT NULL DEFAULT '0'",
          "messages" => "BIGINT(20) NOT NULL DEFAULT '0'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("username")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "domain_admins" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "username" => "VARCHAR(255) NOT NULL",
          "domain" => "VARCHAR(255) NOT NULL",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "active" => "TINYINT(1) NOT NULL DEFAULT '1'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          ),
          "key" => array(
            "username" => array("username")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "da_acl" => array(
        "cols" => array(
          "username" => "VARCHAR(255) NOT NULL",
          "syncjobs" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "quarantine" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "login_as" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "sogo_access" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "app_passwds" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "bcc_maps" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "pushover" => "TINYINT(1) NOT NULL DEFAULT '0'",
          "filters" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "ratelimit" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "spam_policy" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "extend_sender_acl" => "TINYINT(1) NOT NULL DEFAULT '0'",
          "unlimited_quota" => "TINYINT(1) NOT NULL DEFAULT '0'",
          "alias_domains" => "TINYINT(1) NOT NULL DEFAULT '0'",
          ),
        "keys" => array(
          "primary" => array(
            "" => array("username")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "imapsync" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "user2" => "VARCHAR(255) NOT NULL",
          "host1" => "VARCHAR(255) NOT NULL",
          "authmech1" => "ENUM('PLAIN','LOGIN','CRAM-MD5') DEFAULT 'PLAIN'",
          "regextrans2" => "VARCHAR(255) DEFAULT ''",
          "authmd51" => "TINYINT(1) NOT NULL DEFAULT 0",
          "domain2" => "VARCHAR(255) NOT NULL DEFAULT ''",
          "subfolder2" => "VARCHAR(255) NOT NULL DEFAULT ''",
          "user1" => "VARCHAR(255) NOT NULL",
          "password1" => "VARCHAR(255) NOT NULL",
          "exclude" => "VARCHAR(500) NOT NULL DEFAULT ''",
          "maxage" => "SMALLINT NOT NULL DEFAULT '0'",
          "mins_interval" => "VARCHAR(50) NOT NULL DEFAULT '0'",
          "maxbytespersecond" => "VARCHAR(50) NOT NULL DEFAULT '0'",
          "port1" => "SMALLINT UNSIGNED NOT NULL",
          "enc1" => "ENUM('TLS','SSL','PLAIN') DEFAULT 'TLS'",
          "delete2duplicates" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "delete1" => "TINYINT(1) NOT NULL DEFAULT '0'",
          "delete2" => "TINYINT(1) NOT NULL DEFAULT '0'",
          "automap" => "TINYINT(1) NOT NULL DEFAULT '0'",
          "skipcrossduplicates" => "TINYINT(1) NOT NULL DEFAULT '0'",
          "custom_params" => "VARCHAR(512) NOT NULL DEFAULT ''",
          "timeout1" => "SMALLINT NOT NULL DEFAULT '600'",
          "timeout2" => "SMALLINT NOT NULL DEFAULT '600'",
          "subscribeall" => "TINYINT(1) NOT NULL DEFAULT '1'",
          "is_running" => "TINYINT(1) NOT NULL DEFAULT '0'",
          "returned_text" => "LONGTEXT",
          "last_run" => "TIMESTAMP NULL DEFAULT NULL",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "modified" => "DATETIME ON UPDATE CURRENT_TIMESTAMP",
          "active" => "TINYINT(1) NOT NULL DEFAULT '0'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "bcc_maps" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "local_dest" => "VARCHAR(255) NOT NULL",
          "bcc_dest" => "VARCHAR(255) NOT NULL",
          "domain" => "VARCHAR(255) NOT NULL",
          "type" => "ENUM('sender','rcpt')",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "modified" => "DATETIME ON UPDATE CURRENT_TIMESTAMP",
          "active" => "TINYINT(1) NOT NULL DEFAULT '0'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          ),
          "key" => array(
            "local_dest" => array("local_dest"),
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "recipient_maps" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "old_dest" => "VARCHAR(255) NOT NULL",
          "new_dest" => "VARCHAR(255) NOT NULL",
          "created" => "DATETIME(0) NOT NULL DEFAULT NOW(0)",
          "modified" => "DATETIME ON UPDATE CURRENT_TIMESTAMP",
          "active" => "TINYINT(1) NOT NULL DEFAULT '0'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          ),
          "key" => array(
            "local_dest" => array("old_dest"),
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "tfa" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "key_id" => "VARCHAR(255) NOT NULL",
          "username" => "VARCHAR(255) NOT NULL",
          "authmech" => "ENUM('yubi_otp', 'u2f', 'hotp', 'totp')",
          "secret" => "VARCHAR(255) DEFAULT NULL",
          "keyHandle" => "VARCHAR(255) DEFAULT NULL",
          "publicKey" => "VARCHAR(255) DEFAULT NULL",
          "counter" => "INT NOT NULL DEFAULT '0'",
          "certificate" => "TEXT",
          "active" => "TINYINT(1) NOT NULL DEFAULT '0'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "forwarding_hosts" => array(
        "cols" => array(
          "host" => "VARCHAR(255) NOT NULL",
          "source" => "VARCHAR(255) NOT NULL",
          "filter_spam" => "TINYINT(1) NOT NULL DEFAULT '0'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("host")
          ),
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "sogo_acl" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "c_folder_id" => "INT NOT NULL",
          "c_object" => "VARCHAR(255) NOT NULL",
          "c_uid" => "VARCHAR(255) NOT NULL",
          "c_role" => "VARCHAR(80) NOT NULL"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          ),
          "key" => array(
            "sogo_acl_c_folder_id_idx" => array("c_folder_id"),
            "sogo_acl_c_uid_idx" => array("c_uid")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "sogo_alarms_folder" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "c_path" => "VARCHAR(255) NOT NULL",
          "c_name" => "VARCHAR(255) NOT NULL",
          "c_uid" => "VARCHAR(255) NOT NULL",
          "c_recurrence_id" => "INT(11) DEFAULT NULL",
          "c_alarm_number" => "INT(11) NOT NULL",
          "c_alarm_date" => "INT(11) NOT NULL"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("id")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "sogo_cache_folder" => array(
        "cols" => array(
          "c_uid" => "VARCHAR(255) NOT NULL",
          "c_path" => "VARCHAR(255) NOT NULL",
          "c_parent_path" => "VARCHAR(255) DEFAULT NULL",
          "c_type" => "TINYINT(3) unsigned NOT NULL",
          "c_creationdate" => "INT(11) NOT NULL",
          "c_lastmodified" => "INT(11) NOT NULL",
          "c_version" => "INT(11) NOT NULL DEFAULT '0'",
          "c_deleted" => "TINYINT(4) NOT NULL DEFAULT '0'",
          "c_content" => "LONGTEXT"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("c_uid", "c_path")
          ),
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "sogo_folder_info" => array(
        "cols" => array(
          "c_folder_id" => "BIGINT(20) unsigned NOT NULL AUTO_INCREMENT",
          "c_path" => "VARCHAR(255) NOT NULL",
          "c_path1" => "VARCHAR(255) NOT NULL",
          "c_path2" => "VARCHAR(255) DEFAULT NULL",
          "c_path3" => "VARCHAR(255) DEFAULT NULL",
          "c_path4" => "VARCHAR(255) DEFAULT NULL",
          "c_foldername" => "VARCHAR(255) NOT NULL",
          "c_location" => "VARCHAR(2048) DEFAULT NULL",
          "c_quick_location" => "VARCHAR(2048) DEFAULT NULL",
          "c_acl_location" => "VARCHAR(2048) DEFAULT NULL",
          "c_folder_type" => "VARCHAR(255) NOT NULL"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("c_path")
          ),
          "unique" => array(
            "c_folder_id" => array("c_folder_id")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "sogo_quick_appointment" => array(
        "cols" => array(
          "c_folder_id" => "INT NOT NULL",
          "c_name" => "VARCHAR(255) NOT NULL",
          "c_uid" => "VARCHAR(1000) NOT NULL",
          "c_startdate" => "INT",
          "c_enddate" => "INT",
          "c_cycleenddate" => "INT",
          "c_title" => "VARCHAR(1000) NOT NULL",
          "c_participants" => "TEXT",
          "c_isallday" => "INT",
          "c_iscycle" => "INT",
          "c_cycleinfo" => "TEXT",
          "c_classification" => "INT NOT NULL",
          "c_isopaque" => "INT NOT NULL",
          "c_status" => "INT NOT NULL",
          "c_priority" => "INT",
          "c_location" => "VARCHAR(255)",
          "c_orgmail" => "VARCHAR(255)",
          "c_partmails" => "TEXT",
          "c_partstates" => "TEXT",
          "c_category" => "VARCHAR(255)",
          "c_sequence" => "INT",
          "c_component" => "VARCHAR(10) NOT NULL",
          "c_nextalarm" => "INT",
          "c_description" => "TEXT"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("c_folder_id", "c_name")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "sogo_quick_contact" => array(
        "cols" => array(
          "c_folder_id" => "INT NOT NULL",
          "c_name" => "VARCHAR(255) NOT NULL",
          "c_givenname" => "VARCHAR(255)",
          "c_cn" => "VARCHAR(255)",
          "c_sn" => "VARCHAR(255)",
          "c_screenname" => "VARCHAR(255)",
          "c_l" => "VARCHAR(255)",
          "c_mail" => "TEXT",
          "c_o" => "VARCHAR(255)",
          "c_ou" => "VARCHAR(255)",
          "c_telephonenumber" => "VARCHAR(255)",
          "c_categories" => "VARCHAR(255)",
          "c_component" => "VARCHAR(10) NOT NULL",
          "c_hascertificate" => "INT4 DEFAULT 0"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("c_folder_id", "c_name")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "sogo_sessions_folder" => array(
        "cols" => array(
          "c_id" => "VARCHAR(255) NOT NULL",
          "c_value" => "VARCHAR(255) NOT NULL",
          "c_creationdate" => "INT(11) NOT NULL",
          "c_lastseen" => "INT(11) NOT NULL"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("c_id")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "sogo_store" => array(
        "cols" => array(
          "c_folder_id" => "INT NOT NULL",
          "c_name" => "VARCHAR(255) NOT NULL",
          "c_content" => "MEDIUMTEXT NOT NULL",
          "c_creationdate" => "INT NOT NULL",
          "c_lastmodified" => "INT NOT NULL",
          "c_version" => "INT NOT NULL",
          "c_deleted" => "INT"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("c_folder_id", "c_name")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "pushover" => array(
        "cols" => array(
          "username" => "VARCHAR(255) NOT NULL",
          "key" => "VARCHAR(255) NOT NULL",
          "token" => "VARCHAR(255) NOT NULL",
          "attributes" => "JSON",
          "title" => "TEXT",
          "text" => "TEXT",
          "senders" => "TEXT",
          "senders_regex" => "TEXT",
          "active" => "TINYINT(1) NOT NULL DEFAULT '1'"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("username")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "sogo_user_profile" => array(
        "cols" => array(
          "c_uid" => "VARCHAR(255) NOT NULL",
          "c_defaults" => "LONGTEXT",
          "c_settings" => "LONGTEXT"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("c_uid")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "oauth_clients" => array(
        "cols" => array(
          "id" => "INT NOT NULL AUTO_INCREMENT",
          "client_id" => "VARCHAR(80) NOT NULL",
          "client_secret" => "VARCHAR(80)",
          "redirect_uri" => "VARCHAR(2000)",
          "grant_types" => "VARCHAR(80)",
          "scope" => "VARCHAR(4000)",
          "user_id" => "VARCHAR(80)"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("client_id")
          ),
          "unique" => array(
            "id" => array("id")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "oauth_access_tokens" => array(
        "cols" => array(
          "access_token" => "VARCHAR(40) NOT NULL",
          "client_id" => "VARCHAR(80) NOT NULL",
          "user_id" => "VARCHAR(80)",
          "expires" => "TIMESTAMP NOT NULL",
          "scope" => "VARCHAR(4000)"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("access_token")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "oauth_authorization_codes" => array(
        "cols" => array(
          "authorization_code" => "VARCHAR(40) NOT NULL",
          "client_id" => "VARCHAR(80) NOT NULL",
          "user_id" => "VARCHAR(80)",
          "redirect_uri" => "VARCHAR(2000)",
          "expires" => "TIMESTAMP NOT NULL",
          "scope" => "VARCHAR(4000)",
          "id_token" => "VARCHAR(1000)"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("authorization_code")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      ),
      "oauth_refresh_tokens" => array(
        "cols" => array(
          "refresh_token" => "VARCHAR(40) NOT NULL",
          "client_id" => "VARCHAR(80) NOT NULL",
          "user_id" => "VARCHAR(80)",
          "expires" => "TIMESTAMP NOT NULL",
          "scope" => "VARCHAR(4000)"
        ),
        "keys" => array(
          "primary" => array(
            "" => array("refresh_token")
          )
        ),
        "attr" => "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
      )
    );

    foreach ($tables as $table => $properties) {
      // Migrate to quarantine
      if ($table == 'quarantine') {
        $stmt = $pdo->query("SHOW TABLES LIKE 'quarantaine'");
        $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
        if ($num_results != 0) {
          $stmt = $pdo->query("SHOW TABLES LIKE 'quarantine'");
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results == 0) {
            $pdo->query("RENAME TABLE `quarantaine` TO `quarantine`");
          }
        }
      }
      // Migrate tls_enforce_* options
      if ($table == 'mailbox') {
        $stmt = $pdo->query("SHOW TABLES LIKE 'mailbox'");
        $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
        if ($num_results != 0) {
          $stmt = $pdo->query("SHOW COLUMNS FROM `mailbox` LIKE '%tls_enforce%'"); 
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results != 0) {
            $stmt = $pdo->query("SELECT `username`, `tls_enforce_in`, `tls_enforce_out` FROM `mailbox`");
            $tls_options_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            while ($row = array_shift($tls_options_rows)) {
              $tls_options[$row['username']] = array('tls_enforce_in' => $row['tls_enforce_in'], 'tls_enforce_out' => $row['tls_enforce_out']);
            }
          }
        }
      }
      $stmt = $pdo->query("SHOW TABLES LIKE '" . $table . "'"); 
      $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
      if ($num_results != 0) {
        $stmt = $pdo->prepare("SELECT CONCAT('ALTER TABLE ', `table_schema`, '.', `table_name`, ' DROP FOREIGN KEY ', `constraint_name`, ';') AS `FKEY_DROP` FROM `information_schema`.`table_constraints`
          WHERE `constraint_type` = 'FOREIGN KEY' AND `table_name` = :table;");
        $stmt->execute(array(':table' => $table));
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        while ($row = array_shift($rows)) {
          $pdo->query($row['FKEY_DROP']);
        }
        foreach($properties['cols'] as $column => $type) {
          $stmt = $pdo->query("SHOW COLUMNS FROM `" . $table . "` LIKE '" . $column . "'"); 
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results == 0) {
            if (strpos($type, 'AUTO_INCREMENT') !== false) {
              $type = $type . ' PRIMARY KEY ';
              // Adding an AUTO_INCREMENT key, need to drop primary keys first, if exists
              $stmt = $pdo->query("SHOW KEYS FROM `" . $table . "` WHERE Key_name = 'PRIMARY'");
              $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
              if ($num_results != 0) {
                $pdo->query("ALTER TABLE `" . $table . "` DROP PRIMARY KEY");
              }
            }
            $pdo->query("ALTER TABLE `" . $table . "` ADD `" . $column . "` " . $type);
          }
          else {
            $pdo->query("ALTER TABLE `" . $table . "` MODIFY COLUMN `" . $column . "` " . $type);
          }
        }
        foreach($properties['keys'] as $key_type => $key_content) {
          if (strtolower($key_type) == 'primary') {
            foreach ($key_content as $key_values) {
              $fields = "`" . implode("`, `", $key_values) . "`";
              $stmt = $pdo->query("SHOW KEYS FROM `" . $table . "` WHERE Key_name = 'PRIMARY'"); 
              $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
              $is_drop = ($num_results != 0) ? "DROP PRIMARY KEY, " : "";
              $pdo->query("ALTER TABLE `" . $table . "` " . $is_drop . "ADD PRIMARY KEY (" . $fields . ")");
            }
          }
          if (strtolower($key_type) == 'key') {
            foreach ($key_content as $key_name => $key_values) {
              $fields = "`" . implode("`, `", $key_values) . "`";
              $stmt = $pdo->query("SHOW KEYS FROM `" . $table . "` WHERE Key_name = '" . $key_name . "'"); 
              $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
              $is_drop = ($num_results != 0) ? "DROP INDEX `" . $key_name . "`, " : "";
              $pdo->query("ALTER TABLE `" . $table . "` " . $is_drop . "ADD KEY `" . $key_name . "` (" . $fields . ")");
            }
          }
          if (strtolower($key_type) == 'unique') {
            foreach ($key_content as $key_name => $key_values) {
              $fields = "`" . implode("`, `", $key_values) . "`";
              $stmt = $pdo->query("SHOW KEYS FROM `" . $table . "` WHERE Key_name = '" . $key_name . "'");
              $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
              $is_drop = ($num_results != 0) ? "DROP INDEX `" . $key_name . "`, " : "";
              $pdo->query("ALTER TABLE `" . $table . "` " . $is_drop . "ADD UNIQUE KEY `" . $key_name . "` (" . $fields . ")");
            }
          }
          if (strtolower($key_type) == 'fkey') {
            foreach ($key_content as $key_name => $key_values) {
              $fields = "`" . implode("`, `", $key_values) . "`";
              $stmt = $pdo->query("SHOW KEYS FROM `" . $table . "` WHERE Key_name = '" . $key_name . "'");
              $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
              if ($num_results != 0) {
                $pdo->query("ALTER TABLE `" . $table . "` DROP INDEX `" . $key_name . "`");
              }
              @list($table_ref, $field_ref) = explode('.', $key_values['ref']);
              $pdo->query("ALTER TABLE `" . $table . "` ADD FOREIGN KEY `" . $key_name . "` (" . $key_values['col'] . ") REFERENCES `" . $table_ref . "` (`" . $field_ref . "`)
                ON DELETE " . $key_values['delete'] . " ON UPDATE " . $key_values['update']);
            }
          }
        }
        // Drop all vanished columns
        $stmt = $pdo->query("SHOW COLUMNS FROM `" . $table . "`"); 
        $cols_in_table = $stmt->fetchAll(PDO::FETCH_ASSOC); 
        while ($row = array_shift($cols_in_table)) {
          if (!array_key_exists($row['Field'], $properties['cols'])) {
            $pdo->query("ALTER TABLE `" . $table . "` DROP COLUMN `" . $row['Field'] . "`;");
          }
        }

        // Step 1: Get all non-primary keys, that currently exist and those that should exist
        $stmt = $pdo->query("SHOW KEYS FROM `" . $table . "` WHERE `Key_name` != 'PRIMARY'"); 
        $keys_in_table = $stmt->fetchAll(PDO::FETCH_ASSOC); 
        $keys_to_exist = array();
        if (isset($properties['keys']['unique']) && is_array($properties['keys']['unique'])) {
          foreach ($properties['keys']['unique'] as $key_name => $key_values) {
             $keys_to_exist[] = $key_name;
          }
        }
        if (isset($properties['keys']['key']) && is_array($properties['keys']['key'])) {
          foreach ($properties['keys']['key'] as $key_name => $key_values) {
             $keys_to_exist[] = $key_name;
          }
        }
        // Index for foreign key must exist
        if (isset($properties['keys']['fkey']) && is_array($properties['keys']['fkey'])) {
          foreach ($properties['keys']['fkey'] as $key_name => $key_values) {
             $keys_to_exist[] = $key_name;
          }
        }
        // Step 2: Drop all vanished indexes
        while ($row = array_shift($keys_in_table)) {
          if (!in_array($row['Key_name'], $keys_to_exist)) {
            $pdo->query("ALTER TABLE `" . $table . "` DROP INDEX `" . $row['Key_name'] . "`");
          }
        }
        // Step 3: Drop all vanished primary keys
        if (!isset($properties['keys']['primary'])) {
          $stmt = $pdo->query("SHOW KEYS FROM `" . $table . "` WHERE Key_name = 'PRIMARY'"); 
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results != 0) {
            $pdo->query("ALTER TABLE `" . $table . "` DROP PRIMARY KEY");
          }
        }
      }
      else {
        // Create table if it is missing
        $sql = "CREATE TABLE IF NOT EXISTS `" . $table . "` (";
        foreach($properties['cols'] as $column => $type) {
          $sql .= "`" . $column . "` " . $type . ",";
        }
        foreach($properties['keys'] as $key_type => $key_content) {
          if (strtolower($key_type) == 'primary') {
            foreach ($key_content as $key_values) {
              $fields = "`" . implode("`, `", $key_values) . "`";
              $sql .= "PRIMARY KEY (" . $fields . ")" . ",";
            }
          }
          elseif (strtolower($key_type) == 'key') {
            foreach ($key_content as $key_name => $key_values) {
              $fields = "`" . implode("`, `", $key_values) . "`";
              $sql .= "KEY `" . $key_name . "` (" . $fields . ")" . ",";
            }
          }
          elseif (strtolower($key_type) == 'unique') {
            foreach ($key_content as $key_name => $key_values) {
              $fields = "`" . implode("`, `", $key_values) . "`";
              $sql .= "UNIQUE KEY `" . $key_name . "` (" . $fields . ")" . ",";
            }
          }
          elseif (strtolower($key_type) == 'fkey') {
            foreach ($key_content as $key_name => $key_values) {
              @list($table_ref, $field_ref) = explode('.', $key_values['ref']);
              $fields = "`" . implode("`, `", $key_values) . "`";
              $sql .= "FOREIGN KEY `" . $key_name . "` (" . $key_values['col'] . ") REFERENCES `" . $table_ref . "` (`" . $field_ref . "`)
                ON DELETE " . $key_values['delete'] . " ON UPDATE " . $key_values['update'] . ",";
            }
          }
        }
        $sql = rtrim($sql, ",");
        $sql .= ") " . $properties['attr'];
        $pdo->query($sql);
      }
      // Reset table attributes
      $pdo->query("ALTER TABLE `" . $table . "` " . $properties['attr'] . ";");

    }

    // Recreate SQL views
    foreach ($views as $view => $create) {
      $pdo->query("DROP VIEW IF EXISTS `" . $view . "`;");
      $pdo->query($create);
    }

    // Inject admin if not exists
    $stmt = $pdo->query("SELECT NULL FROM `admin`"); 
    $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
    if ($num_results == 0) {
    $stmt = $pdo->query("INSERT INTO `admin` (`username`, `password`, `superadmin`, `created`, `modified`, `active`)
      VALUES ('admin', '{SSHA256}K8eVJ6YsZbQCfuJvSUbaQRLr0HPLz5rC9IAp0PAFl0tmNDBkMDc0NDAyOTAxN2Rk', 1, NOW(), NOW(), 1)");
    $stmt = $pdo->query("INSERT INTO `domain_admins` (`username`, `domain`, `created`, `active`)
        SELECT `username`, 'ALL', NOW(), 1 FROM `admin`
          WHERE superadmin='1' AND `username` NOT IN (SELECT `username` FROM `domain_admins`);");
    $stmt = $pdo->query("DELETE FROM `admin` WHERE `username` NOT IN  (SELECT `username` FROM `domain_admins`);");
    }
    // Insert new DB schema version
    $stmt = $pdo->query("REPLACE INTO `versions` (`application`, `version`) VALUES ('db_schema', '" . $db_version . "');"); 

    // Fix dangling domain admins
    $stmt = $pdo->query("DELETE FROM `admin` WHERE `superadmin` = 0 AND `username` NOT IN (SELECT `username`FROM `domain_admins`);");
    $stmt = $pdo->query("DELETE FROM `da_acl` WHERE `username` NOT IN (SELECT `username`FROM `domain_admins`);");

    // Migrate attributes
    // pushover
    $stmt = $pdo->query("UPDATE `pushover` SET `attributes` = '{}' WHERE `attributes` = '' OR `attributes` IS NULL;");
    $stmt = $pdo->query("UPDATE `pushover` SET `attributes` =  JSON_SET(`attributes`, '$.evaluate_x_prio', \"0\") WHERE JSON_EXTRACT(`attributes`, '$.evaluate_x_prio') IS NULL;");
    $stmt = $pdo->query("UPDATE `pushover` SET `attributes` =  JSON_SET(`attributes`, '$.only_x_prio', \"0\") WHERE JSON_EXTRACT(`attributes`, '$.only_x_prio') IS NULL;");
    // mailbox
    $stmt = $pdo->query("UPDATE `mailbox` SET `attributes` = '{}' WHERE `attributes` = '' OR `attributes` IS NULL;");
    $stmt = $pdo->query("UPDATE `mailbox` SET `attributes` =  JSON_SET(`attributes`, '$.force_pw_update', \"0\") WHERE JSON_EXTRACT(`attributes`, '$.force_pw_update') IS NULL;");
    $stmt = $pdo->query("UPDATE `mailbox` SET `attributes` =  JSON_SET(`attributes`, '$.sogo_access', \"1\") WHERE JSON_EXTRACT(`attributes`, '$.sogo_access') IS NULL;");
    $stmt = $pdo->query("UPDATE `mailbox` SET `attributes` =  JSON_SET(`attributes`, '$.mailbox_format', \"maildir:\") WHERE JSON_EXTRACT(`attributes`, '$.mailbox_format') IS NULL;");
    $stmt = $pdo->query("UPDATE `mailbox` SET `attributes` =  JSON_SET(`attributes`, '$.quarantine_notification', \"never\") WHERE JSON_EXTRACT(`attributes`, '$.quarantine_notification') IS NULL;");
    foreach($tls_options as $tls_user => $tls_options) {
      $stmt = $pdo->prepare("UPDATE `mailbox` SET `attributes` = JSON_SET(`attributes`, '$.tls_enforce_in', :tls_enforce_in),
        `attributes` = JSON_SET(`attributes`, '$.tls_enforce_out', :tls_enforce_out)
          WHERE `username` = :username");
      $stmt->execute(array(':tls_enforce_in' => $tls_options['tls_enforce_in'], ':tls_enforce_out' => $tls_options['tls_enforce_out'], ':username' => $tls_user));
    }
    // Set tls_enforce_* if still missing (due to deleted attrs for example)
    $stmt = $pdo->query("UPDATE `mailbox` SET `attributes` =  JSON_SET(`attributes`, '$.tls_enforce_out', \"1\") WHERE JSON_EXTRACT(`attributes`, '$.tls_enforce_out') IS NULL;");
    $stmt = $pdo->query("UPDATE `mailbox` SET `attributes` =  JSON_SET(`attributes`, '$.tls_enforce_in', \"1\") WHERE JSON_EXTRACT(`attributes`, '$.tls_enforce_in') IS NULL;");
    // Fix ACL
    $stmt = $pdo->query("INSERT INTO `user_acl` (`username`) SELECT `username` FROM `mailbox` WHERE `kind` = '' AND NOT EXISTS (SELECT `username` FROM `user_acl`);");
    $stmt = $pdo->query("INSERT INTO `da_acl` (`username`) SELECT DISTINCT `username` FROM `domain_admins` WHERE `username` != 'admin' AND NOT EXISTS (SELECT `username` FROM `da_acl`);");
    // Fix domain_admins
    $stmt = $pdo->query("DELETE FROM `domain_admins` WHERE `domain` = 'ALL';");

    if (php_sapi_name() == "cli") {
      echo "DB initialization completed" . PHP_EOL;
    } else {
      $_SESSION['return'][] = array(
        'type' => 'success',
        'log' => array(__FUNCTION__),
        'msg' => 'db_init_complete'
      );
    }
  }
  catch (PDOException $e) {
    if (php_sapi_name() == "cli") {
      echo "DB initialization failed: " . print_r($e, true) . PHP_EOL;
    } else {
      $_SESSION['return'][] = array(
        'type' => 'danger',
        'log' => array(__FUNCTION__),
        'msg' => array('mysql_error', $e)
      );
    }
  }
}
if (php_sapi_name() == "cli") {
  include '/web/inc/vars.inc.php';
  // $now = new DateTime();
  // $mins = $now->getOffset() / 60;
  // $sgn = ($mins < 0 ? -1 : 1);
  // $mins = abs($mins);
  // $hrs = floor($mins / 60);
  // $mins -= $hrs * 60;
  // $offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
  $dsn = $database_type . ":unix_socket=" . $database_sock . ";dbname=" . $database_name;
  $opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    //PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '" . $offset . "', group_concat_max_len = 3423543543;",
  ];
  $pdo = new PDO($dsn, $database_user, $database_pass, $opt);
  $stmt = $pdo->query("SELECT COUNT('OK') AS OK_C FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'sogo_view' OR TABLE_NAME = '_sogo_static_view';");
  $res = $stmt->fetch(PDO::FETCH_ASSOC);
  if (intval($res['OK_C']) === 2) {
    // Be more precise when replacing into _sogo_static_view, col orders may change
    try {
      $stmt = $pdo->query("REPLACE INTO _sogo_static_view (`c_uid`, `domain`, `c_name`, `c_password`, `c_cn`, `mail`, `aliases`, `ad_aliases`, `kind`, `multiple_bookings`)
        SELECT `c_uid`, `domain`, `c_name`, `c_password`, `c_cn`, `mail`, `aliases`, `ad_aliases`, `kind`, `multiple_bookings` from sogo_view");
      $stmt = $pdo->query("DELETE FROM _sogo_static_view WHERE `c_uid` NOT IN (SELECT `username` FROM `mailbox` WHERE `active` = '1');");
      echo "Fixed _sogo_static_view" . PHP_EOL;
    }
    catch ( Exception $e ) {
      // Dunno
    }
  }
  try {
    $m = new Memcached();
    $m->addServer('memcached', 11211);
    $m->flush();
    echo "Cleaned up memcached". PHP_EOL;
  }
  catch ( Exception $e ) {
    // Dunno
  }
  init_db_schema();
}
