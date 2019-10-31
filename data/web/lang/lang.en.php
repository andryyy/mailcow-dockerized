<?php
/*
 * English language file
 */

$lang['header']['apps'] = 'Apps';
$lang['footer']['loading'] = "Please wait...";
$lang['header']['restart_sogo'] = 'Restart SOGo';
$lang['header']['restart_netfilter'] = 'Restart netfilter';
$lang['footer']['restart_container'] = 'Restart container';
$lang['footer']['restart_now'] = 'Restart now';
$lang['footer']['restarting_container'] = 'Restarting container, this may take a while...';
$lang['footer']['restart_container_info'] = '<b>Important:</b> A graceful restart may take a while to complete, please wait for it to finish.';

$lang['footer']['confirm_delete'] = 'Confirm deletion';
$lang['footer']['delete_these_items'] = 'Please confirm your changes to the following object id';
$lang['footer']['delete_now'] = 'Delete now';
$lang['footer']['cancel'] = 'Cancel';

$lang['footer']['hibp_nok'] = 'Matched! This is a potentially dangerous password!';
$lang['footer']['hibp_ok'] = 'No match found.';

$lang['danger']['transport_dest_exists'] = 'Transport destination "%s" exists';
$lang['danger']['unlimited_quota_acl'] = "Unlimited quota prohibited by ACL";
$lang['danger']['mysql_error'] = "MySQL error: %s";
$lang['danger']['redis_error'] = "Redis error: %s";
$lang['danger']['unknown_tfa_method'] = "Unknown TFA method";
$lang['danger']['totp_verification_failed'] = "TOTP verification failed";
$lang['success']['verified_totp_login'] = "Verified TOTP login";
$lang['danger']['u2f_verification_failed'] = "U2F verification failed: %s";
$lang['success']['verified_u2f_login'] = "Verified U2F login";
$lang['success']['verified_yotp_login'] = "Verified Yubico OTP login";
$lang['danger']['yotp_verification_failed'] = "Yubico OTP verification failed: %s";
$lang['danger']['ip_list_empty'] = "List of allowed IPs cannot be empty";
$lang['danger']['invalid_destination'] = 'Destination format "%s" is invalid';
$lang['danger']['invalid_nexthop'] = "Next hop format is invalid";
$lang['danger']['invalid_nexthop_authenticated'] = "Next hop exists with different credentials, please update the existing credentials for this next hop first.";
$lang['danger']['next_hop_interferes'] = "%s interferes with nexthop %s";
$lang['danger']['next_hop_interferes_any'] = "An existing next hop interferes with %s";
$lang['danger']['rspamd_ui_pw_length'] = "Rspamd UI password should be at least 6 chars long";
$lang['success']['rspamd_ui_pw_set'] = "Rspamd UI password successfully set";
$lang['success']['queue_command_success'] = "Queue command completed successfully";
$lang['danger']['unknown'] = "An unknown error occurred";
$lang['danger']['malformed_username'] = "Malformed username";
$lang['info']['awaiting_tfa_confirmation'] = "Awaiting TFA confirmation";
$lang['info']['session_expires'] = "Your session will expire in about 15 seconds";
$lang['success']['logged_in_as'] = "Logged in as %s";
$lang['danger']['login_failed'] = "Login failed";
$lang['danger']['set_acl_failed'] = "Failed to set ACL";
$lang['danger']['no_user_defined'] = "No user defined";
$lang['danger']['script_empty'] = "Script cannot be empty";
$lang['danger']['sieve_error'] = "Sieve parser error: %s";
$lang['danger']['value_missing'] = "Please provide all values";
$lang['danger']['filter_type'] = "Wrong filter type";
$lang['danger']['domain_cannot_match_hostname'] = "Domain cannot match hostname";
$lang['warning']['domain_added_sogo_failed'] = "Added domain but failed to restart SOGo, please check your server logs.";
$lang['danger']['rl_timeframe'] = "Rate limit time frame is incorrect";
$lang['success']['rl_saved'] = "Rate limit for object %s saved";
$lang['success']['acl_saved'] = "ACL for object %s saved";
$lang['success']['deleted_syncjobs'] = "Deleted syncjobs: %s";
$lang['success']['deleted_syncjob'] = "Deleted syncjob ID %s";
$lang['success']['delete_filters'] = "Deleted filters: %s";
$lang['success']['delete_filter'] = "Deleted filters ID %s";
$lang['danger']['invalid_bcc_map_type'] = "Invalid BCC map type";
$lang['danger']['bcc_empty'] = "BCC destination cannot be empty";
$lang['danger']['bcc_must_be_email'] = "BCC destination %s is not a valid email address";
$lang['danger']['bcc_exists'] = "A BCC map %s exists for type %s";
$lang['success']['bcc_saved'] = "BCC map entry saved";
$lang['success']['bcc_edited'] = "BCC map entry %s edited";
$lang['success']['bcc_deleted'] = "BCC map entries deleted: %s";
$lang['danger']['private_key_error'] = "Private key error: %s";
$lang['danger']['map_content_empty'] = "Map content cannot be empty";
$lang['success']['settings_map_added'] = "Added settings map entry";
$lang['danger']['settings_map_invalid'] = "Settings map ID %s invalid";
$lang['success']['settings_map_removed'] = "Removed settings map ID %s";
$lang['danger']['invalid_host'] = "Invalid host specified: %s";
$lang['danger']['relayhost_invalid'] = "Map entry %s is invalid";
$lang['success']['saved_settings'] = "Saved settings";
$lang['success']['db_init_complete'] = "Database initialization completed";

$lang['warning']['session_ua'] = "Form token invalid: User-Agent validation error";
$lang['warning']['session_token'] = "Form token invalid: Token mismatch";

$lang['danger']['dkim_domain_or_sel_invalid'] = "DKIM domain or selector invalid: %s";
$lang['success']['dkim_removed'] = "DKIM key %s has been removed";
$lang['success']['dkim_added'] = "DKIM key %s has been saved";
$lang['success']['dkim_duplicated'] = "DKIM key for domain %s has been copied to %s";
$lang['danger']['access_denied'] = "Access denied or invalid form data";
$lang['danger']['domain_invalid'] = "Domain name is empty or invalid";
$lang['danger']['mailbox_quota_exceeds_domain_quota'] = "Max. quota exceeds domain quota limit";
$lang['danger']['object_is_not_numeric'] = "Value %s is not numeric";
$lang['success']['domain_added'] = "Added domain %s";
$lang['success']['items_deleted'] = "Item %s successfully deleted";
$lang['success']['item_deleted'] = "Item %s successfully deleted";
$lang['danger']['alias_empty'] = "Alias address must not be empty";
$lang['danger']['last_key'] = 'Last key cannot be deleted';
$lang['danger']['goto_empty'] = "Goto address must not be empty";
$lang['danger']['policy_list_from_exists'] = "A record with given name exists";
$lang['danger']['policy_list_from_invalid'] = "Record has invalid format";
$lang['danger']['alias_invalid'] = "Alias address %s is invalid";
$lang['danger']['goto_invalid'] = "Goto address %s is invalid";
$lang['danger']['alias_domain_invalid'] = "Alias domain %s is invalid";
$lang['danger']['target_domain_invalid'] = "Target domain %s is invalid";
$lang['danger']['object_exists'] = "Object %s already exists";
$lang['danger']['domain_exists'] = "Domain %s already exists";
$lang['danger']['alias_goto_identical'] = "Alias and goto address must not be identical";
$lang['danger']['aliasd_targetd_identical'] = "Alias domain must not be equal to target domain: %s";
$lang['danger']['maxquota_empty'] = 'Max. quota per mailbox must not be 0.';
$lang['success']['alias_added'] = "Alias address %s has been added";
$lang['success']['alias_modified'] = "Changes to alias address %s have been saved";
$lang['success']['mailbox_modified'] = "Changes to mailbox %s have been saved";
$lang['success']['resource_modified'] = "Changes to mailbox %s have been saved";
$lang['success']['object_modified'] = "Changes to object %s have been saved";
$lang['success']['f2b_modified'] = "Changes to Fail2ban parameters have been saved";
$lang['danger']['targetd_not_found'] = "Target domain %s not found";
$lang['danger']['targetd_relay_domain'] = "Target domain %s is a relay domain";
$lang['success']['aliasd_added'] = "Added alias domain %s";
$lang['success']['aliasd_modified'] = "Changes to alias domain %s have been saved";
$lang['success']['domain_modified'] = "Changes to domain %s have been saved";
$lang['success']['domain_admin_modified'] = "Changes to domain administrator %s have been saved";
$lang['success']['domain_admin_added'] = "Domain administrator %s has been added";
$lang['success']['admin_added'] = "Administrator %s has been added";
$lang['success']['admin_modified'] = "Changes to administrator have been saved";
$lang['success']['admin_api_modified'] = "Changes to API have been saved";
$lang['success']['license_modified'] = "Changes to license have been saved";
$lang['danger']['username_invalid'] = "Username %s cannot be used";
$lang['danger']['password_mismatch'] = "Confirmation password does not match";
$lang['danger']['password_complexity'] = "Password does not meet the policy";
$lang['danger']['password_empty'] = "Password must not be empty";
$lang['danger']['login_failed'] = "Login failed";
$lang['danger']['mailbox_invalid'] = "Mailbox name is invalid";
$lang['danger']['description_invalid'] = 'Resource description for %s is invalid';
$lang['danger']['resource_invalid'] = "Resource name %s is invalid";
$lang['danger']['is_alias'] = "%s is already known as an alias address";
$lang['danger']['is_alias_or_mailbox'] = "%s is already known as an alias, a mailbox or an alias address expanded from an alias domain.";
$lang['danger']['is_spam_alias'] = "%s is already known as a spam alias address";
$lang['danger']['quota_not_0_not_numeric'] = "Quota must be numeric and >= 0";
$lang['danger']['domain_not_found'] = 'Domain %s not found';
$lang['danger']['max_mailbox_exceeded'] = "Max. mailboxes exceeded (%d of %d)";
$lang['danger']['max_alias_exceeded'] = 'Max. aliases exceeded';
$lang['danger']['mailbox_quota_exceeded'] = "Quota exceeds the domain limit (max. %d MiB)";
$lang['danger']['mailbox_quota_left_exceeded'] = "Not enough space left (space left: %d MiB)";
$lang['success']['mailbox_added'] = "Mailbox %s has been added";
$lang['success']['resource_added'] = "Resource %s has been added";
$lang['success']['domain_removed'] = "Domain %s has been removed";
$lang['success']['alias_removed'] = "Alias %s has been removed";
$lang['success']['alias_domain_removed'] = "Alias domain %s has been removed";
$lang['success']['domain_admin_removed'] = "Domain administrator %s has been removed";
$lang['success']['admin_removed'] = "Administrator %s has been removed";
$lang['success']['mailbox_removed'] = "Mailbox %s has been removed";
$lang['success']['eas_reset'] = "ActiveSync devices for user %s were reset";
$lang['success']['sogo_profile_reset'] = "SOGo profile for user %s was reset";
$lang['success']['resource_removed'] = "Resource %s has been removed";
$lang['warning']['cannot_delete_self'] = "Cannot delete logged in user";
$lang['warning']['no_active_admin'] = "Cannot deactivate last active admin";
$lang['danger']['max_quota_in_use'] = "Mailbox quota must be greater or equal to %d MiB";
$lang['danger']['domain_quota_m_in_use'] = "Domain quota must be greater or equal to %s MiB";
$lang['danger']['mailboxes_in_use'] = "Max. mailboxes must be greater or equal to %d";
$lang['danger']['aliases_in_use'] = "Max. aliases must be greater or equal to %d";
$lang['danger']['sender_acl_invalid'] = "Sender ACL value %s is invalid";
$lang['danger']['domain_not_empty'] = "Cannot remove non-empty domain %s";
$lang['danger']['validity_missing'] = 'Please assign a period of validity';
$lang['user']['loading'] = "Loading...";
$lang['user']['force_pw_update'] = 'You <b>must</b> set a new password to be able to access groupware related services.';
$lang['user']['active_sieve'] = "Active filter";
$lang['user']['show_sieve_filters'] = "Show active user sieve filter";
$lang['user']['no_active_filter'] = "No active filter available";
$lang['user']['messages'] = "messages"; // "123 messages"
$lang['user']['in_use'] = "Used";
$lang['user']['user_change_fn'] = "";
$lang['user']['user_settings'] = 'User settings';
$lang['user']['mailbox_details'] = 'Mailbox details';
$lang['user']['change_password'] = 'Change password';
$lang['user']['client_configuration'] = 'Show configuration guides for email clients and smartphones';
$lang['user']['new_password'] = 'New password';
$lang['user']['save_changes'] = 'Save changes';
$lang['user']['password_now'] = 'Current password (confirm changes)';
$lang['user']['new_password_repeat'] = 'Confirmation password (repeat)';
$lang['user']['new_password_description'] = 'Requirement: 6 characters long, letters and numbers.';
$lang['user']['spam_aliases'] = 'Temporary email aliases';
$lang['user']['alias'] = 'Alias';
$lang['user']['shared_aliases'] = 'Shared alias addresses';
$lang['user']['shared_aliases_desc'] = 'Shared aliases are not affected by user specific settings such as the spam filter or encryption policy. Corresponding spam filters can only be made by an administrator as a domain-wide policy.';
$lang['user']['direct_aliases'] = 'Direct alias addresses';
$lang['user']['direct_aliases_desc'] = 'Direct alias addresses are affected by spam filter and TLS policy settings.';
$lang['user']['is_catch_all'] = 'Catch-all for domain/s';
$lang['user']['aliases_also_send_as'] = 'Also allowed to send as user';
$lang['user']['aliases_send_as_all'] = 'Do not check sender access for the following domain(s) and its alias domains';
$lang['user']['alias_create_random'] = 'Generate random alias';
$lang['user']['alias_extend_all'] = 'Extend aliases by 1 hour';
$lang['user']['alias_valid_until'] = 'Valid until';
$lang['user']['alias_remove_all'] = 'Remove all aliases';
$lang['user']['alias_time_left'] = 'Time left';
$lang['user']['alias_full_date'] = 'd.m.Y, H:i:s T';
$lang['user']['alias_select_validity'] = 'Period of validity';
$lang['user']['sync_jobs'] = 'Sync jobs';
$lang['user']['expire_in'] = 'Expire in';
$lang['user']['hour'] = 'hour';
$lang['user']['hours'] = 'hours';
$lang['user']['day'] = 'day';
$lang['user']['week'] = 'week';
$lang['user']['weeks'] = 'weeks';
$lang['user']['spamfilter'] = 'Spam filter';
$lang['admin']['spamfilter'] = 'Spam filter';
$lang['user']['spamfilter_wl'] = 'Whitelist';
$lang['user']['spamfilter_wl_desc'] = 'Whitelisted email addresses to <b>never</b> classify as spam. Wildcards may be used. A filter is only applied to direct aliases (aliases with a single target mailbox) excluding catch-all aliases and a mailbox itself.';
$lang['user']['spamfilter_bl'] = 'Blacklist';
$lang['user']['spamfilter_bl_desc'] = 'Blacklisted email addresses to <b>always</b> classify as spam and reject. Wildcards may be used. A filter is only applied to direct aliases (aliases with a single target mailbox) excluding catch-all aliases and a mailbox itself.';
$lang['user']['spamfilter_behavior'] = 'Rating';
$lang['user']['spamfilter_table_rule'] = 'Rule';
$lang['user']['spamfilter_table_action'] = 'Action';
$lang['user']['spamfilter_table_empty'] = 'No data to display';
$lang['user']['spamfilter_table_remove'] = 'remove';
$lang['user']['spamfilter_table_add'] = 'Add item';
$lang['user']['spamfilter_green'] = 'Green: this message is not spam';
$lang['user']['spamfilter_yellow'] = 'Yellow: this message may be spam, will be tagged as spam and moved to your junk folder';
$lang['user']['spamfilter_red'] = 'Red: This message is spam and will be rejected by the server';
$lang['user']['spamfilter_default_score'] = 'Default values';
$lang['user']['spamfilter_hint'] = 'The first value describes the "low spam score", the second represents the "high spam score".';
$lang['user']['spamfilter_table_domain_policy'] = "n/a (domain policy)";
$lang['user']['waiting'] = "Waiting";
$lang['user']['status'] = "Status";
$lang['user']['running'] = "Running";

$lang['user']['tls_policy_warning'] = '<strong>Warning:</strong> If you decide to enforce encrypted mail transfer, you may lose emails.<br>Messages to not satisfy the policy will be bounced with a hard fail by the mail system.<br>This option applies to your primary email address (login name), all addresses derived from alias domains as well as alias addresses <b>with only this single mailbox</b> as target.';
$lang['user']['tls_policy'] = 'Encryption policy';
$lang['user']['tls_enforce_in'] = 'Enforce TLS incoming';
$lang['user']['tls_enforce_out'] = 'Enforce TLS outgoing';
$lang['user']['no_record'] = 'No record';


$lang['user']['tag_handling'] = 'Set handling for tagged mail';
$lang['user']['tag_in_subfolder'] = 'In subfolder';
$lang['user']['tag_in_subject'] = 'In subject';
$lang['user']['tag_in_none'] = 'Do nothing';
$lang['user']['tag_help_explain'] = 'In subfolder: a new subfolder named after the tag will be created below INBOX ("INBOX/Facebook").<br>
In subject: the tags name will be prepended to the mails subject, example: "[Facebook] My News".';
$lang['user']['tag_help_example'] = 'Example for a tagged email address: me<b>+Facebook</b>@example.org';

$lang['user']['eas_reset'] = 'Reset ActiveSync device cache';
$lang['user']['eas_reset_now'] = 'Reset now';
$lang['user']['eas_reset_help'] = 'In many cases a device cache reset will help to recover a broken ActiveSync profile.<br><b>Attention:</b> All elements will be redownloaded!';

$lang['user']['sogo_profile_reset'] = 'Reset SOGo profile';
$lang['user']['sogo_profile_reset_now'] = 'Reset profile now';
$lang['user']['sogo_profile_reset_help'] = 'This will destroy a users SOGo profile and <b>delete all contact and calendar data irretrievable</b>.';

$lang['user']['encryption'] = 'Encryption';
$lang['user']['username'] = 'Username';
$lang['user']['last_run'] = 'Last run';
$lang['user']['excludes'] = 'Excludes';
$lang['user']['interval'] = 'Interval';
$lang['user']['active'] = 'Active';
$lang['user']['action'] = 'Action';
$lang['user']['edit'] = 'Edit';
$lang['user']['remove'] = 'Remove';
$lang['user']['create_syncjob'] = 'Create new sync job';

$lang['start']['mailcow_apps_detail'] = 'Use a mailcow app to access your mails, calendar, contacts and more.';
$lang['start']['mailcow_panel_detail'] = '<b>Domain administrators</b> create, modify or delete mailboxes and aliases, change domains and read further information about their assigned domains.<br>
<b>Mailbox users</b> are able to create time-limited aliases (spam aliases), change their password and spam filter settings.';
$lang['start']['imap_smtp_server_auth_info'] = 'Please use your full email address and the PLAIN authentication mechanism.<br>
Your login data will be encrypted by the server-side mandatory encryption.';
$lang['start']['help'] = 'Show/Hide help panel';
$lang['header']['mailcow_settings'] = 'Configuration';
$lang['header']['administration'] = 'Configuration & Details';
$lang['header']['mailboxes'] = 'Mail Setup';
$lang['header']['user_settings'] = 'User Settings';
$lang['header']['quarantine'] = "Quarantine";
$lang['header']['debug'] = "System Information";
$lang['quarantine']['disabled_by_config'] = "The current system configuration disables the quarantine functionality.";
$lang['mailbox']['tls_policy_maps'] = 'TLS policy maps';
$lang['mailbox']['tls_policy_maps_long'] = 'Outgoing TLS policy map overrides';
$lang['mailbox']['tls_policy_maps_info'] = 'This policy map overrides outgoing TLS transport rules independently of a users TLS policy settings.<br>
  Please check <a href="http://www.postfix.org/postconf.5.html#smtp_tls_policy_maps" target="_blank">the "smtp_tls_policy_maps" docs</a> for further information.';
$lang['mailbox']['tls_enforce_in'] = 'Enforce TLS incoming';
$lang['mailbox']['tls_enforce_out'] = 'Enforce TLS outgoing';
$lang['mailbox']['tls_map_dest'] = 'Destination';
$lang['mailbox']['tls_map_dest_info'] = 'Examples: example.org, .example.org, [mail.example.org]:25';
$lang['mailbox']['tls_map_policy'] = 'Policy';
$lang['mailbox']['tls_map_parameters'] = 'Parameters';
$lang['mailbox']['tls_map_parameters_info'] = 'Empty or parameters, for example: protocols=!SSLv2 ciphers=medium exclude=3DES';
$lang['mailbox']['booking_0'] = 'Always show as free';
$lang['mailbox']['booking_lt0'] = 'Unlimited, but show as busy when booked';
$lang['mailbox']['booking_custom'] = 'Hard-limit to a custom amount of bookings';
$lang['mailbox']['booking_0_short'] = 'Always free';
$lang['mailbox']['booking_lt0_short'] = 'Soft limit';
$lang['mailbox']['booking_custom_short'] = 'Hard limit';
$lang['mailbox']['domain'] = 'Domain';
$lang['mailbox']['spam_aliases'] = 'Temp. alias';
$lang['mailbox']['multiple_bookings'] = 'Multiple bookings';
$lang['mailbox']['kind'] = 'Kind';
$lang['mailbox']['description'] = 'Description';
$lang['mailbox']['alias'] = 'Alias';
$lang['mailbox']['aliases'] = 'Aliases';
$lang['mailbox']['domains'] = 'Domains';
$lang['admin']['domain'] = 'Domain';
$lang['admin']['domain_s'] = 'Domain/s';
$lang['mailbox']['mailboxes'] = 'Mailboxes';
$lang['mailbox']['mailbox'] = 'Mailbox';
$lang['mailbox']['resources'] = 'Resources';
$lang['mailbox']['mailbox_quota'] = 'Max. size of a mailbox';
$lang['mailbox']['domain_quota'] = 'Quota';
$lang['mailbox']['active'] = 'Active';
$lang['mailbox']['action'] = 'Action';
$lang['mailbox']['backup_mx'] = 'Backup MX';
$lang['mailbox']['domain_aliases'] = 'Domain aliases';
$lang['mailbox']['target_domain'] = 'Target domain';
$lang['mailbox']['target_address'] = 'Goto address';
$lang['mailbox']['username'] = 'Username';
$lang['mailbox']['fname'] = 'Full name';
$lang['mailbox']['filter_table'] = 'Filter table';
$lang['mailbox']['yes'] = '&#10003;';
$lang['mailbox']['no'] = '&#10005;';
$lang['mailbox']['in_use'] = 'In use (%)';
$lang['mailbox']['msg_num'] = 'Message #';
$lang['mailbox']['remove'] = 'Remove';
$lang['mailbox']['edit'] = 'Edit';
$lang['mailbox']['no_record'] = 'No record for object %s';
$lang['mailbox']['no_record_single'] = 'No record';
$lang['mailbox']['add_domain'] = 'Add domain';
$lang['mailbox']['add_domain_alias'] = 'Add domain alias';
$lang['mailbox']['add_mailbox'] = 'Add mailbox';
$lang['mailbox']['add_resource'] = 'Add resource';
$lang['mailbox']['add_alias'] = 'Add alias';
$lang['mailbox']['add_domain_record_first'] = 'Please add a domain first';
$lang['mailbox']['empty'] = 'No results';
$lang['mailbox']['toggle_all'] = 'Toggle all';
$lang['mailbox']['quick_actions'] = 'Actions';
$lang['mailbox']['activate'] = 'Activate';
$lang['mailbox']['deactivate'] = 'Deactivate';
$lang['mailbox']['owner'] = 'Owner';
$lang['mailbox']['mins_interval'] = 'Interval (min)';
$lang['mailbox']['last_run'] = 'Last run';
$lang['mailbox']['excludes'] = 'Excludes';
$lang['mailbox']['last_run_reset'] = 'Schedule next';
$lang['mailbox']['sieve_info'] = 'You can store multiple filters per user, but only one prefilter and one postfilter can be active at the same time.<br>
Each filter will be processed in the described order. Neither a failed script nor an issued "keep;" will stop processing of further scripts.<br>
<a href="https://github.com/mailcow/mailcow-dockerized/blob/master/data/conf/dovecot/global_sieve_before" target="_blank">Global sieve prefilter</a> → Prefilter → User scripts → Postfilter → <a href="https://github.com/mailcow/mailcow-dockerized/blob/master/data/conf/dovecot/global_sieve_after" target="_blank">Global sieve postfilter</a>';
$lang['info']['no_action'] = 'No action applicable';


$lang['edit']['sogo_visible'] = 'Alias is visible in SOGo';
$lang['edit']['sogo_visible_info'] = 'This option only affects objects, that can be displayed in SOGo (shared or non-shared alias addresses pointing to at least one local mailbox).';
$lang['mailbox']['sogo_visible'] = 'Alias is visible in SOGo';
$lang['mailbox']['sogo_visible_y'] = 'Show alias in SOGo';
$lang['mailbox']['sogo_visible_n'] = 'Hide alias in SOGo';
$lang['edit']['syncjob'] = 'Edit sync job';
$lang['edit']['hostname'] = 'Hostname';
$lang['edit']['encryption'] = 'Encryption';
$lang['edit']['maxage'] = 'Maximum age of messages in days that will be polled from remote<br><small>(0 = ignore age)</small>';
$lang['edit']['maxbytespersecond'] = 'Max. bytes per second <br><small>(0 = unlimited)</small>';
$lang['edit']['automap'] = 'Try to automap folders ("Sent items", "Sent" => "Sent" etc.)';
$lang['edit']['skipcrossduplicates'] = 'Skip duplicate messages across folders (first come, first serve)';
$lang['add']['automap'] = 'Try to automap folders ("Sent items", "Sent" => "Sent" etc.)';
$lang['add']['skipcrossduplicates'] = 'Skip duplicate messages across folders (first come, first serve)';
$lang['edit']['subfolder2'] = 'Sync into subfolder on destination<br><small>(empty = do not use subfolder)</small>';
$lang['edit']['mins_interval'] = 'Interval (min)';
$lang['edit']['exclude'] = 'Exclude objects (regex)';
$lang['edit']['save'] = 'Save changes';
$lang['edit']['username'] = 'Username';
$lang['edit']['max_mailboxes'] = 'Max. possible mailboxes';
$lang['edit']['title'] = 'Edit object';
$lang['edit']['target_address'] = 'Goto address/es <small>(comma-separated)</small>';
$lang['edit']['active'] = 'Active';
$lang['edit']['gal'] = 'Global Address List';
$lang['add']['gal'] = 'Global Address List';
$lang['edit']['gal_info'] = 'The GAL contains all objects of a domain and cannot be edited by any user. Free/busy information in SOGo is missing, if disabled! <b>Restart SOGo to apply changes.</b>';
$lang['add']['gal_info'] = 'The GAL contains all objects of a domain and cannot be edited by any user. Free/busy information in SOGo is missing, if disabled! <b>Restart SOGo to apply changes.</b>';
$lang['edit']['force_pw_update'] = 'Force password update at next login';
$lang['edit']['force_pw_update_info'] = 'This user will only be able to login to mailcow UI.';
$lang['edit']['sogo_access'] = 'Grant access to SOGo';
$lang['edit']['sogo_access_info'] = 'Grant or permit access to SOGo. This setting does neither affect access to all other services nor does it delete or change a users existing SOGo profile.';
$lang['edit']['target_domain'] = 'Target domain';
$lang['edit']['password'] = 'Password';
$lang['edit']['password_repeat'] = 'Confirmation password (repeat)';
$lang['edit']['domain_admin'] = 'Edit domain administrator';
$lang['edit']['domain'] = 'Edit domain';
$lang['edit']['edit_alias_domain'] = 'Edit Alias domain';
$lang['edit']['domains'] = 'Domains';
$lang['edit']['alias'] = 'Edit alias';
$lang['edit']['mailbox'] = 'Edit mailbox';
$lang['edit']['description'] = 'Description';
$lang['edit']['max_aliases'] = 'Max. aliases';
$lang['edit']['max_quota'] = 'Max. quota per mailbox (MiB)';
$lang['edit']['domain_quota'] = 'Domain quota';
$lang['edit']['backup_mx_options'] = 'Backup MX options';
$lang['edit']['relay_domain'] = 'Relay domain';
$lang['edit']['relay_all'] = 'Relay all recipients';
$lang['edit']['relay_all_info'] = '<small>If you choose <b>not</b> to relay all recipients, you will need to add a ("blind") mailbox for every single recipient that should be relayed.</small>';
$lang['edit']['full_name'] = 'Full name';
$lang['edit']['quota_mb'] = 'Quota (MiB)';
$lang['edit']['sender_acl'] = 'Allow to send as';
$lang['edit']['sender_acl_disabled'] = '↳ <span class="label label-danger">Sender check is disabled</span>';
$lang['user']['sender_acl_disabled'] = '<span class="label label-danger">Sender check is disabled</span>';
$lang['edit']['previous'] = 'Previous page';
$lang['edit']['unchanged_if_empty'] = 'If unchanged leave blank';
$lang['edit']['dont_check_sender_acl'] = "Disable sender check for domain %s (+ alias domains)";
$lang['edit']['multiple_bookings'] = 'Multiple bookings';
$lang['edit']['kind'] = 'Kind';
$lang['edit']['resource'] = 'Resource';
$lang['edit']['relayhost'] = 'Sender-dependent transports';
$lang['edit']['public_comment'] = 'Public comment';
$lang['mailbox']['public_comment'] = 'Public comment';
$lang['edit']['private_comment'] = 'Private comment';
$lang['mailbox']['private_comment'] = 'Private comment';
$lang['edit']['comment_info'] = 'A private comment is not visible to the user, while a public comment is shown as tooltip when hovering it in a users overview';
$lang['add']['public_comment'] = 'Public comment';
$lang['add']['private_comment'] = 'Private comment';
$lang['add']['comment_info'] = 'A private comment is not visible to the user, while a public comment is shown as tooltip when hovering it in a users overview';
$lang['acl']['spam_alias'] = 'Temporary aliases';
$lang['acl']['tls_policy'] = 'TLS policy';
$lang['acl']['spam_score'] = 'Spam score';
$lang['acl']['spam_policy'] = 'Blacklist/Whitelist';
$lang['acl']['delimiter_action'] = 'Delimiter action';
$lang['acl']['syncjobs'] = 'Sync jobs';
$lang['acl']['eas_reset'] = 'Reset EAS devices';
$lang['acl']['sogo_profile_reset'] = 'Reset SOGo profile';
$lang['acl']['quarantine'] = 'Quarantine actions';
$lang['acl']['quarantine_notification'] = 'Change quarantine notifications';
$lang['acl']['quarantine_attachments'] = 'Quarantine attachments';
$lang['acl']['alias_domains'] = 'Add alias domains';
$lang['acl']['login_as'] = 'Login as mailbox user';
$lang['acl']['bcc_maps'] = 'BCC maps';
$lang['acl']['filters'] = 'Filters';
$lang['acl']['ratelimit'] = 'Rate limit';
$lang['acl']['recipient_maps'] = 'Recipient maps';
$lang['acl']['unlimited_quota'] = 'Unlimited quota for mailboxes';
$lang['acl']['extend_sender_acl'] = 'Allow to extend sender ACL by external addresses';
$lang['acl']['prohibited'] = 'Prohibited by ACL';
$lang['acl']['sogo_access'] = 'Allow management of SOGo access';

$lang['edit']['extended_sender_acl'] = 'External sender addresses';
$lang['edit']['extended_sender_acl_info'] = 'A DKIM domain key should be imported, if available.<br>
  Remember to add this server to the corresponding SPF TXT record.<br>
  Whenever a domain or alias domain is added to this server, that overlaps with an external address, the external address is removed.<br>
  Use @domain.tld to allow to send as *@domain.tld.';
$lang['edit']['sender_acl_info'] = 'If mailbox user A is allowed to send as mailbox user B, the sender address is not automatically displayed as selectable "from" field in SOGo.<br>
  Mailbox user A needs to create a delegation in SOGo to allow mailbox user b to select their address as sender. This behaviour does not apply to alias addresses.';

$lang['mailbox']['quarantine_notification'] = 'Quarantine notifications';
$lang['mailbox']['never'] = 'Never';
$lang['mailbox']['hourly'] = 'Hourly';
$lang['mailbox']['daily'] = 'Daily';
$lang['mailbox']['weekly'] = 'Weekly';
$lang['user']['quarantine_notification'] = 'Quarantine notifications';
$lang['user']['never'] = 'Never';
$lang['user']['hourly'] = 'Hourly';
$lang['user']['daily'] = 'Daily';
$lang['user']['weekly'] = 'Weekly';
$lang['user']['quarantine_notification_info'] = 'Once a notification has been sent, items will be marked as "notified" and no further notifications will be sent for this particular item.';

$lang['add']['generate'] = 'generate';
$lang['add']['syncjob'] = 'Add sync job';
$lang['add']['syncjob_hint'] = 'Be aware that passwords need to be saved plain-text!';
$lang['add']['hostname'] = 'Host';
$lang['add']['destination'] = 'Destination';
$lang['add']['nexthop'] = 'Next hop';
$lang['edit']['nexthop'] = 'Next hop';
$lang['add']['port'] = 'Port';
$lang['add']['username'] = 'Username';
$lang['add']['enc_method'] = 'Encryption method';
$lang['add']['mins_interval'] = 'Polling interval (minutes)';
$lang['add']['exclude'] = 'Exclude objects (regex)';
$lang['add']['delete2duplicates'] = 'Delete duplicates on destination';
$lang['add']['delete1'] = 'Delete from source when completed';
$lang['add']['delete2'] = 'Delete messages on destination that are not on source';
$lang['add']['custom_params'] = 'Custom parameters';
$lang['add']['custom_params_hint'] = 'Right: --param=xy, wrong: --param xy';
$lang['add']['subscribeall'] = 'Subscribe all folders';
$lang['add']['timeout1'] = 'Timeout for connection to remote host';
$lang['add']['timeout2'] = 'Timeout for connection to local host';
$lang['edit']['timeout1'] = 'Timeout for connection to remote host';
$lang['edit']['timeout2'] = 'Timeout for connection to local host';

$lang['edit']['delete2duplicates'] = 'Delete duplicates on destination';
$lang['edit']['delete1'] = 'Delete from source when completed';
$lang['edit']['delete2'] = 'Delete messages on destination that are not on source';

$lang['add']['domain_matches_hostname'] = 'Domain %s matches hostname';
$lang['add']['domain'] = 'Domain';
$lang['add']['active'] = 'Active';
$lang['add']['multiple_bookings'] = 'Multiple bookings';
$lang['add']['description'] = 'Description';
$lang['add']['max_aliases'] = 'Max. possible aliases';
$lang['add']['max_mailboxes'] = 'Max. possible mailboxes';
$lang['add']['mailbox_quota_m'] = 'Max. quota per mailbox (MiB)';
$lang['add']['domain_quota_m'] = 'Total domain quota (MiB)';
$lang['add']['backup_mx_options'] = 'Backup MX options';
$lang['add']['relay_all'] = 'Relay all recipients';
$lang['add']['relay_domain'] = 'Relay this domain';
$lang['add']['relay_all_info'] = '<small>If you choose <b>not</b> to relay all recipients, you will need to add a ("blind") mailbox for every single recipient that should be relayed.</small>';
$lang['add']['alias_address'] = 'Alias address/es';
$lang['add']['alias_address_info'] = '<small>Full email address/es or @example.com, to catch all messages for a domain (comma-separated). <b>mailcow domains only</b>.</small>';
$lang['add']['alias_domain_info'] = '<small>Valid domain names only (comma-separated).</small>';
$lang['add']['target_address'] = 'Goto addresses';
$lang['add']['target_address_info'] = '<small>Full email address/es (comma-separated).</small>';
$lang['add']['alias_domain'] = 'Alias domain';
$lang['add']['select'] = 'Please select...';
$lang['add']['target_domain'] = 'Target domain';
$lang['add']['kind'] = 'Kind';
$lang['add']['mailbox_username'] = 'Username (left part of an email address)';
$lang['add']['full_name'] = 'Full name';
$lang['add']['quota_mb'] = 'Quota (MiB)';
$lang['add']['select_domain'] = 'Please select a domain first';
$lang['add']['password'] = 'Password';
$lang['add']['password_repeat'] = 'Confirmation password (repeat)';
$lang['add']['restart_sogo_hint'] = 'You will need to restart the SOGo service container after adding a new domain!';
$lang['add']['goto_null'] = 'Silently discard mail';
$lang['add']['goto_ham'] = 'Learn as <span class="text-success"><b>ham</b></span>';
$lang['add']['goto_spam'] = 'Learn as <span class="text-danger"><b>spam</b></span>';
$lang['add']['validation_success'] = 'Validated successfully';
$lang['add']['activate_filter_warn'] = 'All other filters will be deactivated, when active is checked.';
$lang['add']['validate'] = 'Validate';
$lang['mailbox']['add_filter'] = 'Add filter';
$lang['add']['sieve_desc'] = 'Short description';
$lang['edit']['sieve_desc'] = 'Short description';
$lang['add']['sieve_type'] = 'Filter type';
$lang['edit']['sieve_type'] = 'Filter type';
$lang['mailbox']['set_prefilter'] = 'Mark as prefilter';
$lang['mailbox']['set_postfilter'] = 'Mark as postfilter';
$lang['mailbox']['filters'] = 'Filters';
$lang['mailbox']['sync_jobs'] = 'Sync jobs';
$lang['mailbox']['inactive'] = 'Inactive';
$lang['edit']['validate_save'] = 'Validate and save';


$lang['login']['username'] = 'Username';
$lang['login']['password'] = 'Password';
$lang['login']['login'] = 'Login';
$lang['login']['delayed'] = 'Login was delayed by %s seconds.';

$lang['tfa']['tfa'] = "Two-factor authentication";
$lang['tfa']['set_tfa'] = "Set two-factor authentication method";
$lang['tfa']['yubi_otp'] = "Yubico OTP authentication";
$lang['tfa']['key_id'] = "An identifier for your YubiKey";
$lang['tfa']['init_u2f'] = "Initializing, please wait...";
$lang['tfa']['start_u2f_validation'] = "Start validation";
$lang['tfa']['reload_retry'] = "- (reload browser if the error persists)";
$lang['tfa']['key_id_totp'] = "An identifier for your key";
$lang['tfa']['error_code'] = "Error code";
$lang['tfa']['api_register'] = 'mailcow uses the Yubico Cloud API. Please get an API key for your key <a href="https://upgrade.yubico.com/getapikey/" target="_blank">here</a>';
$lang['tfa']['u2f'] = "U2F authentication";
$lang['tfa']['none'] = "Deactivate";
$lang['tfa']['delete_tfa'] = "Disable TFA";
$lang['tfa']['disable_tfa'] = "Disable TFA until next successful login";
$lang['tfa']['confirm'] = "Confirm";
$lang['tfa']['totp'] = "Time-based OTP (Google Authenticator, Authy, etc.)";
$lang['tfa']['select'] = "Please select";
$lang['tfa']['waiting_usb_auth'] = "<i>Waiting for USB device...</i><br><br>Please tap the button on your U2F USB device now.";
$lang['tfa']['waiting_usb_register'] = "<i>Waiting for USB device...</i><br><br>Please enter your password above and confirm your U2F registration by tapping the button on your U2F USB device.";
$lang['tfa']['scan_qr_code'] = "Please scan the following code with your authenticator app or enter the code manually.";
$lang['tfa']['enter_qr_code'] = "Your TOTP code if your device cannot scan QR codes";
$lang['tfa']['confirm_totp_token'] = "Please confirm your changes by entering the generated token";

$lang['admin']['rspamd-com_settings'] = '<a href="https://rspamd.com/doc/configuration/settings.html#settings-structure" target="_blank">Rspamd docs</a>
  - A setting name will be auto-generated, please see the example presets below.';

$lang['admin']['no_new_rows'] = 'No further rows available';
$lang['admin']['queue_manager'] = 'Queue manager';
$lang['admin']['additional_rows'] = ' additional rows were added'; // parses to 'n additional rows were added'
$lang['admin']['private_key'] = 'Private key';
$lang['admin']['import'] = 'Import';
$lang['admin']['duplicate'] = 'Duplicate';
$lang['admin']['import_private_key'] = 'Import private key';
$lang['admin']['duplicate_dkim'] = 'Duplicate DKIM record';
$lang['admin']['dkim_from'] = 'From';
$lang['admin']['dkim_to'] = 'To';
$lang['admin']['dkim_from_title'] = 'Source domain to copy data from';
$lang['admin']['dkim_to_title'] = 'Target domain/s - will be overwritten';
$lang['admin']['f2b_parameters'] = 'Fail2ban parameters';
$lang['admin']['f2b_ban_time'] = 'Ban time (s)';
$lang['admin']['f2b_max_attempts'] = 'Max. attempts';
$lang['admin']['f2b_retry_window'] = 'Retry window (s) for max. attempts';
$lang['admin']['f2b_netban_ipv4'] = 'IPv4 subnet size to apply ban on (8-32)';
$lang['admin']['f2b_netban_ipv6'] = 'IPv6 subnet size to apply ban on (8-128)';
$lang['admin']['f2b_whitelist'] = 'Whitelisted networks/hosts';
$lang['admin']['f2b_blacklist'] = 'Blacklisted networks/hosts';
$lang['admin']['f2b_list_info'] = 'A blacklisted host or network will always outweigh a whitelist entity. <b>List updates will take a few seconds to be applied.</b>';
$lang['admin']['search_domain_da'] = 'Search domains';
$lang['admin']['r_inactive'] = 'Inactive restrictions';
$lang['admin']['r_active'] = 'Active restrictions';
$lang['admin']['r_info'] = 'Greyed out/disabled elements on the list of active restrictions are not known as valid restrictions to mailcow and cannot be moved. Unknown restrictions will be set in order of appearance anyway. <br>You can add new elements in <code>inc/vars.local.inc.php</code> to be able to toggle them.';
$lang['admin']['dkim_key_length'] = 'DKIM key length (bits)';
$lang['admin']['dkim_key_valid'] = 'Key valid';
$lang['admin']['dkim_key_unused'] = 'Key unused';
$lang['admin']['dkim_key_missing'] = 'Key missing';
$lang['admin']['dkim_add_key'] = 'Add ARC/DKIM key';
$lang['admin']['dkim_keys'] = 'ARC/DKIM keys';
$lang['admin']['dkim_private_key'] = 'Private key';
$lang['admin']['dkim_domains_wo_keys'] = "Select domains with missing keys";
$lang['admin']['dkim_domains_selector'] = "Selector";
$lang['admin']['add'] = 'Add';
$lang['add']['add_domain_restart'] = 'Add domain and restart SOGo';
$lang['add']['add_domain_only'] = 'Add domain only';
$lang['admin']['configuration'] = 'Configuration';
$lang['admin']['password'] = 'Password';
$lang['admin']['password_repeat'] = 'Confirmation password (repeat)';
$lang['admin']['active'] = 'Active';
$lang['admin']['inactive'] = 'Inactive';
$lang['admin']['action'] = 'Action';
$lang['admin']['add_domain_admin'] = 'Add domain administrator';
$lang['admin']['add_admin'] = 'Add administrator';
$lang['admin']['add_settings_rule'] = 'Add settings rule';
$lang['admin']['rsetting_desc'] = 'Short description';
$lang['admin']['rsetting_content'] = 'Rule content';
$lang['admin']['rsetting_none'] = 'No rules available';
$lang['admin']['rsetting_no_selection'] = 'Please select a rule';
$lang['admin']['rsettings_preset_1'] = 'Disable all but DKIM and rate limit for authenticated users';
$lang['admin']['rsettings_preset_2'] = 'Postmasters want spam';
$lang['admin']['rsettings_insert_preset'] = 'Insert example preset "%s"';
$lang['admin']['rsetting_add_rule'] = 'Add rule';
$lang['admin']['queue_ays'] = 'Please confirm you want to delete all items from the current queue.';
$lang['admin']['arrival_time'] = 'Arrival time (server time)';
$lang['admin']['message_size'] = 'Message size';
$lang['admin']['sender'] = 'Sender';
$lang['admin']['recipients'] = 'Recipients';
$lang['admin']['admin_domains'] = 'Domain assignments';
$lang['admin']['domain_admins'] = 'Domain administrators';
$lang['admin']['flush_queue'] = 'Flush queue';
$lang['admin']['delete_queue'] = 'Delete all';
$lang['admin']['queue_deliver_mail'] = 'Deliver';
$lang['admin']['queue_hold_mail'] = 'Hold';
$lang['admin']['queue_unhold_mail'] = 'Unhold';
$lang['admin']['username'] = 'Username';
$lang['admin']['edit'] = 'Edit';
$lang['admin']['remove'] = 'Remove';
$lang['admin']['save'] = 'Save changes';
$lang['admin']['admin'] = 'Administrator';
$lang['admin']['admin_details'] = 'Edit administrator details';
$lang['admin']['unchanged_if_empty'] = 'If unchanged leave blank';
$lang['admin']['yes'] = '&#10003;';
$lang['admin']['no'] = '&#10005;';
$lang['admin']['access'] = 'Access';
$lang['admin']['no_record'] = 'No record';
$lang['admin']['filter_table'] = 'Filter table';
$lang['admin']['empty'] = 'No results';
$lang['admin']['time'] = 'Time';
$lang['admin']['last_applied'] = 'Last applied';
$lang['admin']['reset_limit'] = 'Remove hash';
$lang['admin']['hash_remove_info'] = 'Removing a ratelimit hash (if still existing) will reset its counter completely.<br>
  Each hash is indicated by an individual color.';
$lang['warning']['hash_not_found'] = 'Hash not found or already deleted';
$lang['success']['hash_deleted'] = 'Hash deleted';
$lang['admin']['authed_user'] = 'Auth. user';
$lang['admin']['priority'] = 'Priority';
$lang['admin']['message'] = 'Message';
$lang['admin']['rate_name'] = 'Rate name';
$lang['admin']['refresh'] = 'Refresh';
$lang['admin']['to_top'] = 'Back to top';
$lang['admin']['in_use_by'] = 'In use by';
$lang['admin']['forwarding_hosts'] = 'Forwarding Hosts';
$lang['admin']['forwarding_hosts_hint'] = 'Incoming messages are unconditionally accepted from any hosts listed here. These hosts are then not checked against DNSBLs or subjected to greylisting. Spam received from them is never rejected, but optionally it can be filed into the Junk folder. The most common use for this is to specify mail servers on which you have set up a rule that forwards incoming emails to your mailcow server.';
$lang['admin']['forwarding_hosts_add_hint'] = 'You can either specify IPv4/IPv6 addresses, networks in CIDR notation, host names (which will be resolved to IP addresses), or domain names (which will be resolved to IP addresses by querying SPF records or, in their absence, MX records).';
$lang['admin']['relayhosts_hint'] = 'Define sender-dependent transports to be able to select them in a domains configuration dialog.<br>
  The transport service is always "smtp:". A users individual outbound TLS policy setting is taken into account.<br>
  Affects selected domains including alias domains.';
$lang['admin']['transports_hint'] = '→ A transport map entry <b>overrules</b> a sender-dependent transport map</b>.<br>
→ Outbound TLS policy settings per-user are ignored and can only be enforced by TLS policy map entries.<br>
→ The transport service for defined transports is always "smtp:".<br>
→ Addresses matching "/localhost$/" will always be transported via "local:", therefore a "*" destination will not apply to those addresses.<br>
→ To determine credentials for an exemplary next hop "[host]:25", Postfix <b>always</b> queries for "host" before searching for "[host]:25". This behavior makes it impossible to use "host" and "[host]:25" at the same time.';
$lang['admin']['add_relayhost_hint'] = 'Please be aware that authentication data, if any, will be stored as plain text.';
$lang['admin']['add_transports_hint'] = 'Please be aware that authentication data, if any, will be stored as plain text.';
$lang['admin']['host'] = 'Host';
$lang['admin']['source'] = 'Source';
$lang['admin']['add_forwarding_host'] = 'Add forwarding host';
$lang['admin']['add_relayhost'] = 'Add sender-dependent transport';
$lang['admin']['add_transport'] = 'Add transport';
$lang['admin']['relayhosts'] = 'Sender-dependent transports';
$lang['admin']['transport_maps'] = 'Transport Maps';
$lang['admin']['routing'] = 'Routing';
$lang['admin']['credentials_transport_warning'] = '<b>Warning</b>: Adding a new transport map entry will update the credentials for all entries with a matching nexthop column.';

$lang['admin']['destination'] = 'Destination';
$lang['admin']['nexthop'] = 'Next hop';

$lang['admin']['oauth2_info'] = 'The OAuth2 implementation supports the grant type "Authorization Code" and issues refresh tokens.<br>
The server also automatically issues new refresh tokens, after a refresh token has been used.<br><br>
→ The default scope is <i>profile</i>. Only mailbox users can be authenticated against OAuth2. If the scope parameter is omitted, it falls back to <i>profile</i>.<br>
→ The <i>state</i> parameter is required to be sent by the client as part of the authorize request.<br><br>
Pathes for requests to the OAuth2 API: <br>
<ul>
  <li>Authorization endpoint: <code>/oauth/authorize</code></li>
  <li>Token endpoint: <code>/oauth/token</code></li>
  <li>Resource page:  <code>/oauth/profile</code></li>
</ul>
Regenerating the client secret will not expire existing authorization codes, but they will fail to renew their token.<br><br>
Revoking client tokens will cause immediate termination of all active sessions. All clients need to re-authenticate.';

$lang['admin']['oauth2_client_id'] = "Client ID";
$lang['admin']['oauth2_client_secret'] = "Client secret";
$lang['admin']['oauth2_redirect_uri'] = "Redirect URI";
$lang['admin']['oauth2_revoke_tokens'] = 'Revoke all client tokens';
$lang['admin']['oauth2_renew_secret'] = 'Generate new client secret';
$lang['edit']['client_id'] = 'Client ID';
$lang['edit']['client_secret'] = 'Client secret';
$lang['edit']['scope'] = 'Scope';
$lang['edit']['grant_types'] = 'Grant types';
$lang['edit']['redirect_uri'] = 'Redirect/Callback URL';
$lang['oauth2']['scope_ask_permission'] = 'An application asked for the following permissions';
$lang['oauth2']['profile'] = 'Profile';
$lang['oauth2']['profile_desc'] = 'View personal information: username, full name, created, modified, active';
$lang['oauth2']['permit'] = 'Authorize application';
$lang['oauth2']['authorize_app'] = 'Authorize application';
$lang['oauth2']['deny'] = 'Deny';
$lang['oauth2']['access_denied'] = 'Please login as mailbox owner to grant access via OAuth2.';


$lang['success']['forwarding_host_removed'] = "Forwarding host %s has been removed";
$lang['success']['forwarding_host_added'] = "Forwarding host %s has been added";
$lang['success']['relayhost_removed'] = "Map entry %s has been removed";
$lang['success']['relayhost_added'] = "Map entry %s has been added";
$lang['diagnostics']['dns_records'] = 'DNS Records';
$lang['diagnostics']['dns_records_24hours'] = 'Please note that changes made to DNS may take up to 24 hours to correctly have their current state reflected on this page. It is intended as a way for you to easily see how to configure your DNS records and to check whether all your records are correctly stored in DNS.';
$lang['diagnostics']['dns_records_name'] = 'Name';
$lang['diagnostics']['dns_records_type'] = 'Type';
$lang['diagnostics']['dns_records_data'] = 'Correct Data';
$lang['diagnostics']['dns_records_status'] = 'Current State';
$lang['diagnostics']['optional'] = 'This record is optional.';
$lang['diagnostics']['cname_from_a'] = 'Value derived from A/AAAA record. This is supported as long as the record points to the correct resource.';

$lang['admin']['relay_from'] = '"From:" address';
$lang['admin']['relay_run'] = "Run test";
$lang['admin']['api_allow_from'] = "Allow API access from these IPs (separated by comma or new line)";
$lang['admin']['api_key'] = "API key";
$lang['admin']['activate_api'] = "Activate API";
$lang['admin']['regen_api_key'] = "Regenerate API key";
$lang['admin']['ban_list_info'] = "See a list of banned IPs below: <b>network (remaining ban time) - [actions]</b>.<br />IPs queued to be unbanned will be removed from the active ban list within a few seconds.<br />Red labels indicate active permanent bans by blacklisting.";
$lang['admin']['unban_pending'] = "unban pending";
$lang['admin']['queue_unban'] = "queue unban";
$lang['admin']['no_active_bans'] = "No active bans";

$lang['admin']['quarantine'] = "Quarantine";
$lang['admin']['rspamd_settings_map'] = "Rspamd settings map";
$lang['admin']['quota_notifications'] = "Quota notifications";
$lang['admin']['quota_notifications_vars'] = "{{percent}} equals the current quota of the user<br>{{username}} is the mailbox name";
$lang['admin']['active_rspamd_settings_map'] = "Active settings map";
$lang['admin']['quota_notifications_info'] = "Quota notications are sent to users once when crossing 80% and once when crossing 95% usage.";
$lang['admin']['quarantine_retention_size'] = "Retentions per mailbox:<br><small>0 indicates <b>inactive</b>.</small>";
$lang['admin']['quarantine_max_size'] = "Maximum size in MiB (larger elements are discarded):<br><small>0 does <b>not</b> indicate unlimited.</small>";
$lang['admin']['quarantine_max_age'] = "Maximum age in days<br><small>Value must be equal to or greater than 1 day.</small>";
$lang['admin']['quarantine_exclude_domains'] = "Exclude domains and alias-domains";
$lang['admin']['quarantine_release_format'] = "Format of released items";
$lang['admin']['quarantine_release_format_raw'] = "Unmodified original";
$lang['admin']['quarantine_release_format_att'] = "As attachment";
$lang['admin']['quarantine_notification_sender'] = "Notification email sender";
$lang['admin']['quarantine_notification_subject'] = "Notification email subject";
$lang['admin']['quarantine_notification_html'] = "Notification email template:<br><small>Leave empty to restore default template.</small>";
$lang['admin']['quota_notification_sender'] = "Notification email sender";
$lang['admin']['quota_notification_subject'] = "Notification email subject";
$lang['admin']['quota_notification_html'] = "Notification email template:<br><small>Leave empty to restore default template.</small>";
$lang['admin']['ui_texts'] = "UI labels and texts";
$lang['admin']['help_text'] = "Override help text below login mask (HTML allowed)";
$lang['admin']['title_name'] = '"mailcow UI" website title';
$lang['admin']['main_name'] = '"mailcow UI" name';
$lang['admin']['apps_name'] = '"mailcow Apps" name';
$lang['admin']['ui_footer'] = 'Footer (HTML allowed)';

$lang['admin']['customize'] = "Customize";
$lang['admin']['change_logo'] = "Change logo";
$lang['admin']['logo_info'] = "Your image will be scaled to a height of 40px for the top navigation bar and a max. width of 250px for the start page. A scalable graphic is highly recommended.";
$lang['admin']['upload'] = "Upload";
$lang['admin']['app_links'] = "App links";
$lang['admin']['app_name'] = "App name";
$lang['admin']['link'] = "Link";
$lang['admin']['remove_row'] = "Remove row";
$lang['admin']['add_row'] = "Add row";
$lang['admin']['reset_default'] = "Reset to default";
$lang['admin']['merged_vars_hint'] = 'Greyed out rows were merged from <code>vars.(local.)inc.php</code> and cannot be modified.';
$lang['mailbox']['waiting'] = "Waiting";
$lang['mailbox']['status'] = "Status";
$lang['mailbox']['running'] = "Running";
$lang['mailbox']['enable_x'] = "Enable";
$lang['mailbox']['disable_x'] = "Disable";

$lang['edit']['spam_score'] = "Set a custom spam score";
$lang['user']['spam_score_reset'] = "Reset to server default";
$lang['edit']['spam_policy'] = "Add or remove items to white-/blacklist";
$lang['edit']['spam_alias'] = "Create or change time limited alias addresses";

$lang['danger']['comment_too_long'] = "Comment too long, max 160 chars allowed";
$lang['danger']['img_tmp_missing'] = "Cannot validate image file: Temporary file not found";
$lang['danger']['img_invalid'] = "Cannot validate image file";
$lang['danger']['invalid_mime_type'] = "Invalid mime type";
$lang['success']['upload_success'] = "File uploaded successfully";
$lang['success']['app_links'] = "Saved changes to app links";
$lang['success']['ui_texts'] = "Saved changes to UI texts";
$lang['success']['reset_main_logo'] = "Reset to default logo";
$lang['success']['items_released'] = "Selected items were released";
$lang['success']['item_released'] = "Item %s released";
$lang['danger']['imagick_exception'] = "Error: Imagick exception while reading image";
$lang['quarantine']['quarantine'] = "Quarantine";
$lang['quarantine']['learn_spam_delete'] = "Learn as spam and delete";
$lang['quarantine']['qinfo'] = 'The quarantine system will save rejected mail to the database, while the sender will <em>not</em> be given the impression of a delivered mail.
  <br>"' . $lang['quarantine']['learn_spam_delete'] . '" will learn a message as spam via Bayesian theorem and also calculate fuzzy hashes to deny similar messages in the future.
  <br>Please be aware that learning multiple messages can be - depending on your system - time consuming.';
$lang['quarantine']['download_eml'] = "Download (.eml)";
$lang['quarantine']['release'] = "Release";
$lang['quarantine']['empty'] = 'No results';
$lang['quarantine']['toggle_all'] = 'Toggle all';
$lang['quarantine']['quick_actions'] = 'Actions';
$lang['quarantine']['remove'] = 'Remove';
$lang['quarantine']['received'] = "Received";
$lang['quarantine']['action'] = "Action";
$lang['quarantine']['rcpt'] = "Recipient";
$lang['quarantine']['qid'] = "Rspamd QID";
$lang['quarantine']['sender'] = "Sender";
$lang['quarantine']['show_item'] = "Show item";
$lang['quarantine']['check_hash'] = "Search file hash @ VT";
$lang['quarantine']['qitem'] = "Quarantine item";
$lang['quarantine']['rspamd_result'] = "Rspamd result";
$lang['quarantine']['subj'] = "Subject";
$lang['quarantine']['recipients'] = "Recipients";
$lang['quarantine']['text_plain_content'] = "Content (text/plain)";
$lang['quarantine']['text_from_html_content'] = "Content (converted html)";
$lang['quarantine']['atts'] = "Attachments";
$lang['quarantine']['low_danger'] = "Low danger";
$lang['quarantine']['neutral_danger'] = "Neutral/no rating";
$lang['quarantine']['medium_danger'] = "Medium danger";
$lang['quarantine']['high_danger'] = "High";
$lang['quarantine']['danger'] = "Danger";
$lang['quarantine']['spam_score'] = "Score";
$lang['quarantine']['confirm_delete'] = "Confirm the deletion of this element.";
$lang['quarantine']['qhandler_success'] = "Request successfully sent to the system. You can now close the window.";
$lang['warning']['fuzzy_learn_error'] = "Fuzzy hash learn error: %s";
$lang['danger']['spam_learn_error'] = "Spam learn error: %s";
$lang['success']['qlearn_spam'] = "Message ID %s was learned as spam and deleted";

$lang['debug']['system_containers'] = 'System & Containers';
$lang['debug']['started_on'] = 'Started on';
$lang['debug']['jvm_memory_solr'] = 'JVM memory usage';
$lang['debug']['solr_status'] = 'Solr status';
$lang['debug']['solr_dead'] = 'Solr is starting, disabled or died.';
$lang['debug']['logs'] = 'Logs';
$lang['debug']['log_info'] = '<p>mailcow <b>in-memory logs</b> are collected in Redis lists and trimmed to LOG_LINES (%d) every minute to reduce hammering.
  <br>In-memory logs are not meant to be persistent. All applications that log in-memory, also log to the Docker daemon and therefore to the default logging driver.
  <br>The in-memory log type should be used for debugging minor issues with containers.</p>
  <p><b>External logs</b> are collected via API of the given application.</p>
  <p><b>Static logs</b> are mostly activity logs, that are not logged to the Dockerd but still need to be persistent (except for API logs).</p>';

$lang['debug']['in_memory_logs'] = 'In-memory logs';
$lang['debug']['external_logs'] = 'External logs';
$lang['debug']['static_logs'] = 'Static logs';
$lang['debug']['solr_uptime'] = 'Uptime';
$lang['debug']['solr_started_at'] = 'Started at';
$lang['debug']['solr_last_modified'] = 'Last modified';
$lang['debug']['solr_size'] = 'Size';
$lang['debug']['solr_docs'] = 'Docs';

$lang['debug']['disk_usage'] = 'Disk usage';
$lang['debug']['containers_info'] = "Container information";
$lang['debug']['restart_container'] = 'Restart';

$lang['quarantine']['release_body'] = "We have attached your message as eml file to this message.";
$lang['danger']['release_send_failed'] = "Message could not be released: %s";
$lang['quarantine']['release_subject'] = "Potentially damaging quarantine item %s";

$lang['mailbox']['bcc_map'] = "BCC map";
$lang['mailbox']['bcc_map_type'] = "BCC type";
$lang['mailbox']['bcc_type'] = "BCC type";
$lang['mailbox']['bcc_sender_map'] = "Sender map";
$lang['mailbox']['bcc_rcpt_map'] = "Recipient map";
$lang['mailbox']['bcc_local_dest'] = "Local destination";
$lang['mailbox']['bcc_destinations'] = "BCC destination";
$lang['mailbox']['bcc_destination'] = "BCC destination";
$lang['edit']['bcc_dest_format'] = 'BCC destination must be a single valid email address.';

$lang['mailbox']['bcc'] = "BCC";
$lang['mailbox']['bcc_maps'] = "BCC maps";
$lang['mailbox']['bcc_to_sender'] = "Switch to sender map type";
$lang['mailbox']['bcc_to_rcpt'] = "Switch to recipient map type";
$lang['mailbox']['add_bcc_entry'] = "Add BCC map";
$lang['mailbox']['add_tls_policy_map'] = "Add TLS policy map";
$lang['mailbox']['bcc_info'] = "BCC maps are used to silently forward copies of all messages to another address. A recipient map type entry is used, when the local destination acts as recipient of a mail. Sender maps conform to the same principle.<br/>
  The local destination will not be informed about a failed delivery.";
$lang['mailbox']['address_rewriting'] = 'Address rewriting';
$lang['mailbox']['recipient_maps'] = 'Recipient maps';
$lang['mailbox']['recipient_map'] = 'Recipient map';
$lang['mailbox']['recipient_map_info'] = 'Recipient maps are used to replace the destination address on a message before it is delivered.';
$lang['mailbox']['recipient_map_old_info'] = 'A recipient maps original destination must be valid email addresses or a domain name.';
$lang['mailbox']['recipient_map_new_info'] = 'Recipient map destination must be a valid email address.';
$lang['mailbox']['recipient_map_old'] = 'Original recipient';
$lang['mailbox']['recipient_map_new'] = 'New recipient';
$lang['danger']['invalid_recipient_map_new'] = 'Invalid new recipient specified: %s';
$lang['danger']['invalid_recipient_map_old'] = 'Invalid original recipient specified: %s';
$lang['danger']['recipient_map_entry_exists'] = 'A Recipient map entry "%s" exists';
$lang['success']['recipient_map_entry_saved'] = 'Recipient map entry "%s" has been saved';
$lang['success']['recipient_map_entry_deleted'] = 'Recipient map ID %s has been deleted';
$lang['danger']['tls_policy_map_entry_exists'] = 'A TLS policy map entry "%s" exists';
$lang['success']['tls_policy_map_entry_saved'] = 'TLS policy map entry "%s" has been saved';
$lang['success']['tls_policy_map_entry_deleted'] = 'TLS policy map ID %s has been deleted';
$lang['mailbox']['add_recipient_map_entry'] = 'Add recipient map';
$lang['danger']['tls_policy_map_parameter_invalid'] = "Policy parameter is invalid";
$lang['danger']['temp_error'] = "Temporary error";

$lang['admin']['sys_mails'] = 'System mails';
$lang['admin']['subject'] = 'Subject';
$lang['admin']['from'] = 'From';
$lang['admin']['include_exclude'] = 'Include/Exclude';
$lang['admin']['include_exclude_info'] = 'By default - with no selection - <b>all mailboxes</b> are addressed';
$lang['admin']['excludes'] = 'Excludes these recipients';
$lang['admin']['includes'] = 'Include these recipients';
$lang['admin']['text'] = 'Text';
$lang['admin']['activate_send'] = 'Activate send button';
$lang['admin']['send'] = 'Send';

$lang['warning']['ip_invalid'] = 'Skipped invalid IP: %s';
$lang['danger']['text_empty'] = 'Text must not be empty';
$lang['danger']['subject_empty'] = 'Subject must not be empty';
$lang['danger']['from_invalid'] = 'Sender must not be empty';
$lang['danger']['network_host_invalid'] = 'Invalid network or host: %s';

$lang['add']['mailbox_quota_def'] = 'Default mailbox quota';
$lang['edit']['mailbox_quota_def'] = 'Default mailbox quota';
$lang['danger']['mailbox_defquota_exceeds_mailbox_maxquota'] = 'Default quota exceeds max quota limit';
$lang['danger']['defquota_empty'] = 'Default quota per mailbox must not be 0.';
$lang['mailbox']['mailbox_defquota'] = 'Default mailbox size';

$lang['admin']['api_info'] = 'The API is a work in progress.';

$lang['admin']['guid_and_license'] = 'GUID & License';
$lang['admin']['guid'] = 'GUID - unique instance ID';
$lang['admin']['license_info'] = 'A license is not required but helps further development.<br><a href="https://www.servercow.de/mailcow?lang=en#sal" target="_blank" alt="SAL order">Register your GUID here</a> or <a href="https://www.servercow.de/mailcow?lang=en#support" target="_blank" alt="Support order">buy support for your mailcow installation.</a>';
$lang['admin']['validate_license_now'] = 'Validate GUID against license server';

$lang['admin']['customer_id'] = 'Customer ID';
$lang['admin']['service_id'] = 'Service ID';

$lang['admin']['lookup_mx'] = 'Match destination against MX (.outlook.com to route all mail targeted to a MX *.outlook.com over this hop)';
$lang['edit']['mbox_rl_info'] = 'This rate limit is applied on the SASL login name, it matches any "from" address used by the logged-in user. A mailbox rate limit overrides a domain-wide rate limit.';

$lang['add']['relayhost_wrapped_tls_info'] = 'Please do <b>not</b> use TLS-wrapped ports (mostly used on port 465).<br>
Use any non-wrapped port and issue STARTTLS. A TLS policy to enforce TLS can be created in "TLS policy maps".';

$lang['admin']['transport_dest_format'] = 'Syntax: example.org, .example.org, *, box@example.org (multiple values can be comma-separated)';

$lang['mailbox']['alias_domain_backupmx'] = 'Alias domain inactive for relay domain';

$lang['danger']['extra_acl_invalid'] = 'External sender address "%s" is invalid';
$lang['danger']['extra_acl_invalid_domain'] = 'External sender "%s" uses an invalid domain';
