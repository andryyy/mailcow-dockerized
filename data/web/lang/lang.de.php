<?php
/*
 * German language file
 */

$lang['footer']['loading'] = 'Einen Moment bitte...';
$lang['header']['restart_sogo'] = 'SOGo neustarten';
$lang['header']['restart_netfilter'] = 'Netfilter neustarten';
$lang['footer']['restart_container'] = 'Container neustarten';
$lang['footer']['restart_now'] = 'Jetzt neustarten';
$lang['footer']['restarting_container'] = 'Container wird neugestartet, bitte warten...';
$lang['footer']['restart_container_info'] = '<b>Wichtig:</b> Der Neustart eines Containers kann eine Weile in Anspruch nehmen.';

$lang['footer']['confirm_delete'] = 'Löschen bestätigen';
$lang['footer']['delete_these_items'] = 'Sind Sie sicher, dass die Änderungen an Elementen mit folgender ID durchgeführt werden sollen?';
$lang['footer']['delete_now'] = 'Jetzt löschen';
$lang['footer']['cancel'] = 'Abbrechen';

$lang['footer']['hibp_nok'] = 'Übereinstimmung gefunden! Dieses Passwort ist potenziell gefährlich!';
$lang['footer']['hibp_ok'] = 'Keine Übereinstimmung gefunden.';

$lang['danger']['transport_dest_exists'] = 'Transport Maps Ziel "%s" existiert bereits';
$lang['danger']['unlimited_quota_acl'] = "Unendliche Quota untersagt durch ACL";
$lang['danger']['mysql_error'] = "MySQL Fehler: %s";
$lang['danger']['redis_error'] = "Redis Fehler: %s";
$lang['danger']['unknown_tfa_method'] = "Unbekannte TFA Methode";
$lang['danger']['totp_verification_failed'] = "TOTP Verifizierung fehlgeschlagen";
$lang['success']['verified_totp_login'] = "TOTP Anmeldung verifiziert";
$lang['danger']['u2f_verification_failed'] = "U2F Verifizierung fehlgeschlagen: %s";
$lang['success']['verified_u2f_login'] = "U2F Anmeldung verifiziert";
$lang['success']['verified_yotp_login'] = "Yubico OTP Anmeldung verifiziert";
$lang['danger']['yotp_verification_failed'] = "Yubico OTP Verifizierung fehlgeschlagen: %s";
$lang['danger']['ip_list_empty'] = "Liste erlaubter IPs darf nicht leer sein";
$lang['danger']['invalid_destination'] = 'Ziel-Format "%s" ist ungültig';
$lang['danger']['invalid_nexthop'] = "Next Hop ist ungültig";
$lang['danger']['invalid_nexthop_authenticated'] = 'Dieser Next Hop existiert bereits mit abweichenden Authentifizierungsdaten. Die bestehenden Authentifizierungsdaten dieses "Next Hops" müssen vorab angepasst werden.';
$lang['danger']['next_hop_interferes'] = "%s verhindert das Hinzufügen von Next Hop %s";
$lang['danger']['next_hop_interferes_any'] = "Ein vorhandener Eintrag verhindert das Hinzufügen von Next Hop %s";
$lang['danger']['rspamd_ui_pw_length'] = "Rspamd UI Passwort muss mindestens 6 Zeichen lang sein";
$lang['success']['rspamd_ui_pw_set'] = "Rspamd UI Passwort wurde gesetzt";
$lang['success']['queue_command_success'] = "Queue-Aufgabe erfolgreich ausgeführt";
$lang['danger']['unknown'] = "Ein unbekannter Fehler trat auf";
$lang['danger']['malformed_username'] = "Benutzername hat ein falsches Format";
$lang['info']['awaiting_tfa_confirmation'] = "Warte auf TFA Verifizierung";
$lang['info']['session_expires'] = "Die Sitzung wird in etwa 15 Sekunden beendet";
$lang['success']['logged_in_as'] = "Eingeloggt als %s";
$lang['danger']['login_failed'] = "Anmeldung fehlgeschlagen";
$lang['danger']['set_acl_failed'] = "ACL konnte nicht gesetzt werden";
$lang['danger']['no_user_defined'] = "Kein Benutzer definiert";
$lang['danger']['script_empty'] = "Script darf nicht leer sein";
$lang['danger']['sieve_error'] = "Sieve Parser: %s";
$lang['danger']['value_missing'] = "Bitte alle Felder ausfüllen";
$lang['danger']['filter_type'] = "Falscher Filtertyp";
$lang['danger']['domain_cannot_match_hostname'] = "Domain darf nicht dem Hostnamen entsprechen";
$lang['warning']['domain_added_sogo_failed'] = "Domain wurde hinzugefügt, aber SOGo konnte nicht neugestartet werden";
$lang['danger']['rl_timeframe'] = "Ratelimit Zeitraum ist inkorrekt";
$lang['success']['rl_saved'] = "Ratelimit für Objekt %s wurde gesetzt";
$lang['success']['acl_saved'] = "ACL für Objekt %s wurde gesetzt";
$lang['success']['deleted_syncjobs'] = "Syncjobs gelöscht: %s";
$lang['success']['deleted_syncjob'] = "Syncjobs ID %s gelöscht";
$lang['success']['delete_filters'] = "Filter gelöscht: %s";
$lang['success']['delete_filter'] = "Filter ID %s wurde gelöscht";
$lang['danger']['invalid_bcc_map_type'] = "Ungültiger BCC Map-Typ";
$lang['danger']['bcc_empty'] = "BCC Ziel darf nicht leer sein";
$lang['danger']['bcc_must_be_email'] = "BCC Ziel %s ist keine gültige E-Mail-Adresse";
$lang['danger']['bcc_exists'] = "Ein BCC Map Eintrag %s existiert bereits als Typ %s";
$lang['success']['bcc_saved'] = "BCC Map Eintrag wurde gespeichert";
$lang['success']['bcc_edited'] = "BCC Map Eintrag %s wurde geändert";
$lang['success']['bcc_deleted'] = "BCC Map Einträge gelöscht: %s";
$lang['danger']['private_key_error'] = "Schlüsselfehler: %s";
$lang['danger']['map_content_empty'] = "Inhalt darf nicht leer sein";
$lang['success']['settings_map_added'] = "Regel wurde gespeichert";
$lang['danger']['settings_map_invalid'] = "Regel ID %s ist ungültig";
$lang['success']['settings_map_removed'] = "Regeln wurden entfernt: %s";
$lang['danger']['invalid_host'] = "Ungültiger Host: %s";
$lang['danger']['relayhost_invalid'] = "Mapeintrag %s ist ungültig";
$lang['success']['saved_settings'] = "Regel wurde gespeichert";

$lang['danger']['dkim_domain_or_sel_invalid'] = 'DKIM-Domain oder Selektor nicht korrekt: %s';
$lang['success']['dkim_removed'] = 'DKIM-Key %s wurde entfernt';
$lang['success']['dkim_added'] = 'DKIM-Key %s wurde hinzugefügt';
$lang['success']['dkim_duplicated'] = "DKIM-Key der Domain %s wurde auf Domain %s kopiert";
$lang['danger']['access_denied'] = 'Zugriff verweigert oder unvollständige/ungültige Daten';
$lang['danger']['domain_invalid'] = 'Domainname ist leer oder ungültig';
$lang['danger']['mailbox_quota_exceeds_domain_quota'] = 'Maximale Größe für Mailboxen überschreitet das Domain Speicherlimit';
$lang['danger']['object_is_not_numeric'] = 'Wert %s ist nicht numerisch';
$lang['success']['domain_added'] = 'Domain %s wurde angelegt';
$lang['success']['items_deleted'] = "Objekt(e) %s wurde(n) erfolgreich entfernt";
$lang['success']['item_deleted'] = "Objekt %s wurde entfernt";
$lang['danger']['alias_empty'] = 'Alias-Adresse darf nicht leer sein';
$lang['danger']['goto_empty'] = 'Ziel-Adresse darf nicht leer sein';
$lang['danger']['policy_list_from_exists'] = 'Ein Eintrag mit diesem Wert existiert bereits';
$lang['danger']['policy_list_from_invalid'] = 'Eintrag hat ein ungültiges Format';
$lang['danger']['alias_invalid'] = 'Alias-Adresse %s ist ungültig';
$lang['danger']['goto_invalid'] = 'Ziel-Adresse %s ist ungültig';
$lang['danger']['last_key'] = 'Letzter Key kann nicht gelöscht werden';
$lang['danger']['alias_domain_invalid'] = 'Alias-Domain %s ist ungültig';
$lang['danger']['target_domain_invalid'] = 'Ziel-Domain %s ist ungültig';
$lang['danger']['object_exists'] = 'Objekt %s existiert bereits';
$lang['danger']['domain_exists'] = 'Domain %s existiert bereits';
$lang['danger']['alias_goto_identical'] = 'Alias- und Ziel-Adresse dürfen nicht identisch sein';
$lang['danger']['aliasd_targetd_identical'] = 'Alias-Domain darf nicht gleich Ziel-Domain sein: %s';
$lang['danger']['maxquota_empty'] = 'Max. Speicherplatz pro Mailbox darf nicht 0 sein.';
$lang['success']['alias_added'] = 'Alias-Adresse %s wurden angelegt';
$lang['success']['alias_modified'] = 'Änderungen an Alias %s wurden gespeichert';
$lang['success']['aliasd_modified'] = 'Änderungen an Alias-Domain %s wurden gespeichert';
$lang['success']['mailbox_modified'] = 'Änderungen an Mailbox %s wurden gespeichert';
$lang['success']['resource_modified'] = "Änderungen an Ressource %s wurden gespeichert";
$lang['success']['object_modified'] = "Änderungen an Objekt %s wurden gespeichert";
$lang['success']['f2b_modified'] = "Änderungen an Fail2ban-Parametern wurden gespeichert";
$lang['danger']['targetd_not_found'] = 'Ziel-Domain %s nicht gefunden';
$lang['danger']['targetd_relay_domain'] = 'Ziel-Domain %s ist eine Relay-Domain';
$lang['success']['aliasd_added'] = 'Alias-Domain %s wurde angelegt';
$lang['success']['aliasd_modified'] = 'Änderungen an Alias-Domain %s wurden gespeichert';
$lang['success']['domain_modified'] = 'Änderungen an Domain %s wurden gespeichert';
$lang['success']['domain_admin_modified'] = 'Änderungen an Domain-Administrator %s wurden gespeichert';
$lang['success']['domain_admin_added'] = 'Domain-Administrator %s wurde angelegt';
$lang['success']['admin_added'] = 'Administrator %s wurde angelegt';
$lang['success']['admin_modified'] = 'Änderungen am Administrator wurden gespeichert';
$lang['success']['admin_api_modified'] = "Änderungen an API wurden gespeichert";
$lang['success']['license_modified'] = "Änderungen an Lizenz wurden gespeichert";
$lang['danger']['username_invalid'] = 'Benutzername %s kann nicht verwendet werden';
$lang['danger']['password_mismatch'] = 'Passwort-Wiederholung stimmt nicht überein';
$lang['danger']['password_complexity'] = 'Passwort entspricht nicht den Richtlinien';
$lang['danger']['password_empty'] = 'Passwort darf nicht leer sein';
$lang['danger']['login_failed'] = 'Anmeldung fehlgeschlagen';
$lang['danger']['mailbox_invalid'] = 'Mailboxname ist ungültig';
$lang['danger']['resource_invalid'] = 'Ressourcenname %s ist ungültig';
$lang['danger']['description_invalid'] = 'Ressourcenbeschreibung für %s ist ungültig';
$lang['danger']['is_alias'] = '%s lautet bereits eine Alias-Adresse';
$lang['danger']['is_alias_or_mailbox'] = "Eine Mailbox, ein Alias oder eine sich aus einer Alias-Domain ergebende Adresse mit dem Namen %s ist bereits vorhanden";
$lang['danger']['is_spam_alias'] = '%s lautet bereits eine Spam-Alias-Adresse';
$lang['danger']['quota_not_0_not_numeric'] = 'Speicherplatz muss numerisch und >= 0 sein';
$lang['danger']['domain_not_found'] = 'Domain %s nicht gefunden';
$lang['danger']['max_mailbox_exceeded'] = 'Anzahl an Mailboxen überschritten (%d von %d)';
$lang['danger']['max_alias_exceeded'] = 'Anzahl an Alias-Adressen überschritten';
$lang['danger']['mailbox_quota_exceeded'] = 'Speicherplatz überschreitet das Limit (max. %d MiB)';
$lang['danger']['mailbox_quota_left_exceeded'] = 'Nicht genügend Speicherplatz vorhanden (Speicherplatz anwendbar: %d MiB)';
$lang['success']['mailbox_added'] = 'Mailbox %s wurde angelegt';
$lang['success']['resource_added'] = 'Ressource %s wurde angelegt';
$lang['success']['domain_removed'] = 'Domain %s wurde entfernt';
$lang['success']['alias_removed'] = 'Alias-Adresse %s wurde entfernt';
$lang['success']['alias_domain_removed'] = 'Alias-Domain %s wurde entfernt';
$lang['success']['domain_admin_removed'] = 'Domain-Administrator %s wurde entfernt';
$lang['success']['admin_removed'] = 'Administrator %s wurde entfernt';
$lang['success']['mailbox_removed'] = 'Mailbox %s wurde entfernt';
$lang['success']['eas_reset'] = "ActiveSync Gerät des Benutzers %s wurde zurückgesetzt";
$lang['success']['sogo_profile_reset'] = "ActiveSync Gerät des Benutzers %s wurde zurückgesetzt";
$lang['success']['resource_removed'] = 'Ressource %s wurde entfernt';
$lang['warning']['cannot_delete_self'] = 'Kann derzeit eingeloggten Benutzer nicht entfernen';
$lang['warning']['no_active_admin'] = 'Kann letzten aktiven Administrator nicht deaktivieren';
$lang['danger']['max_quota_in_use'] = 'Mailbox Speicherplatzlimit muss größer oder gleich %d MiB sein';
$lang['danger']['domain_quota_m_in_use'] = 'Domain Speicherplatzlimit muss größer oder gleich %d MiB sein';
$lang['danger']['mailboxes_in_use'] = 'Maximale Anzahl an Mailboxen muss größer oder gleich %d sein';
$lang['danger']['aliases_in_use'] = 'Maximale Anzahl an Aliassen muss größer oder gleich %d sein';
$lang['danger']['sender_acl_invalid'] = 'Sender ACL %s ist ungültig';
$lang['danger']['domain_not_empty'] = 'Domain %s ist nicht leer';
$lang['danger']['validity_missing'] = 'Bitte geben Sie eine Gültigkeitsdauer an';
$lang['user']['loading'] = "Lade...";
$lang['user']['force_pw_update'] = 'Das Passwort für diesen Benutzer <b>muss</b> geändert werden, damit die Zugriffssperre auf die Groupwarekomponenten wieder freigeschaltet wird.';
$lang['user']['active_sieve'] = "Aktiver Filter";
$lang['user']['show_sieve_filters'] = "Zeige aktiven Filter des Benutzers";
$lang['user']['no_active_filter'] = "Kein aktiver Filter vorhanden";
$lang['user']['messages'] = "Nachrichten";
$lang['user']['in_use'] = "Verwendet";
$lang['user']['user_change_fn'] = '';
$lang['user']['user_settings'] = 'Benutzereinstellungen';
$lang['user']['mailbox_details'] = 'Mailbox-Details';
$lang['user']['change_password'] = 'Passwort ändern';
$lang['user']['client_configuration'] = 'Konfigurationsanleitungen für E-Mail-Programme und Smartphones anzeigen';
$lang['user']['new_password'] = 'Neues Passwort';
$lang['user']['save_changes'] = 'Änderungen speichern';
$lang['user']['password_now'] = 'Aktuelles Passwort (Änderungen bestätigen)';
$lang['user']['new_password_repeat'] = 'Neues Passwort (Wiederholung)';
$lang['user']['new_password_description'] = 'Mindestanforderung: 6 Zeichen lang, Buchstaben und Zahlen.';
$lang['user']['spam_aliases'] = 'Temporäre E-Mail Aliasse';
$lang['user']['alias'] = 'Alias';
$lang['user']['shared_aliases'] = 'Geteilte Alias-Adressen';
$lang['user']['shared_aliases_desc'] = 'Geteilte Alias-Adressen werden nicht bei benutzerdefinierten Einstellungen, wie die des Spam-Filters oder der Verschlüsselungsrichtlinie, berücksichtigt. Entsprechende Spam-Filter können lediglich von einem Administrator vorgenommen werden.';
$lang['user']['direct_aliases'] = 'Direkte Alias-Adressen';
$lang['user']['direct_aliases_desc'] = 'Nur direkte Alias-Adressen werden für benutzerdefinierte Einstellungen berücksichtigt.';
$lang['user']['is_catch_all'] = 'Ist Catch-All Adresse für Domain(s)';
$lang['user']['aliases_also_send_as'] = 'Darf außerdem versenden als Benutzer';
$lang['user']['aliases_send_as_all'] = 'Absender für folgende Domains und zugehörige Alias-Domains nicht prüfen';
$lang['user']['alias_create_random'] = 'Zufälligen Alias generieren';
$lang['user']['alias_extend_all'] = 'Gültigkeit +1h';
$lang['user']['alias_valid_until'] = 'Gültig bis';
$lang['user']['alias_remove_all'] = 'Alle entfernen';
$lang['user']['alias_time_left'] = 'Zeit verbleibend';
$lang['user']['alias_full_date'] = 'd.m.Y, H:i:s T';
$lang['user']['alias_select_validity'] = 'Bitte Gültigkeit auswählen';
$lang['user']['sync_jobs'] = 'Sync Jobs';
$lang['user']['expire_in'] = 'Ungültig in';
$lang['user']['hour'] = 'Stunde';
$lang['user']['hours'] = 'Stunden';
$lang['user']['day'] = 'Tag';
$lang['user']['week'] = 'Woche';
$lang['user']['weeks'] = 'Wochen';
$lang['user']['spamfilter'] = 'Spamfilter';
$lang['admin']['spamfilter'] = 'Spamfilter';
$lang['user']['spamfilter_wl'] = 'Whitelist';
$lang['user']['spamfilter_wl_desc'] = 'Für E-Mail-Adressen, die vom Spamfilter <b>nicht</b> erfasst werden sollen. Die Verwendung von Wildcards ist gestattet. Ein Filter funktioniert lediglich für direkte nicht-"Catch All" Alias-Adressen (Alias-Adressen mit lediglich einer Mailbox als Ziel-Adresse) sowie die Mailbox-Adresse selbst.';
$lang['user']['spamfilter_bl'] = 'Blacklist';
$lang['user']['spamfilter_bl_desc'] = 'Für E-Mail-Adressen, die vom Spamfilter <b>immer</b> als Spam erfasst und abgelehnt werden. Die Verwendung von Wildcards ist gestattet. Ein Filter funktioniert lediglich für direkte nicht-"Catch All" Alias-Adressen (Alias-Adressen mit lediglich einer Mailbox als Ziel-Adresse) sowie die Mailbox-Adresse selbst.';
$lang['user']['spamfilter_table_rule'] = 'Regel';
$lang['user']['spamfilter_table_action'] = 'Aktion';
$lang['user']['spamfilter_table_empty'] = 'Keine Einträge vorhanden';
$lang['user']['spamfilter_table_remove'] = 'Entfernen';
$lang['user']['spamfilter_table_add'] = 'Eintrag hinzufügen';
$lang['user']['spamfilter_behavior'] = 'Bewertung';
$lang['user']['spamfilter_green'] = 'Grün: Die Nachricht ist kein Spam';
$lang['user']['spamfilter_yellow'] = 'Gelb: Die Nachricht ist vielleicht Spam, wird als Spam markiert und in den Junk-Ordner verschoben';
$lang['user']['spamfilter_red'] = 'Rot: Die Nachricht ist eindeutig Spam und wird vom Server abgelehnt';
$lang['user']['spamfilter_default_score'] = 'Standardwert';
$lang['user']['spamfilter_hint'] = 'Der erste Wert beschreibt den "low spam score", der zweite Wert den "high spam score".';
$lang['user']['spamfilter_table_domain_policy'] = "n.v. (Domainrichtlinie)";
$lang['user']['waiting'] = "Warte auf Ausführung";
$lang['user']['status'] = "Status";
$lang['user']['running'] = "Wird ausgeführt";

$lang['user']['tls_policy_warning'] = '<strong>Vorsicht:</strong> Entscheiden Sie sich unverschlüsselte Verbindungen abzulehnen, kann dies dazu führen, dass Kontakte Sie nicht mehr erreichen.<br>Nachrichten, die die Richtlinie nicht erfüllen, werden durch einen Hard-Fail im Mailsystem abgewiesen.<br>Diese Einstellung ist aktiv für die primäre Mailbox, für alle Alias-Adressen, die dieser Mailbox <b>direkt zugeordnet</b> sind (lediglich eine einzige Ziel-Adresse) und der Adressen, die sich aus Alias-Domains ergeben. Ausgeschlossen sind temporäre Aliasse ("Spam-Alias-Adressen"), Catch-All Alias-Adressen sowie Alias-Adressen mit mehreren Zielen.';
$lang['user']['tls_policy'] = 'Verschlüsselungsrichtlinie';
$lang['user']['tls_enforce_in'] = 'TLS eingehend erzwingen';
$lang['user']['tls_enforce_out'] = 'TLS ausgehend erzwingen';
$lang['user']['no_record'] = 'Kein Eintrag';


$lang['user']['tag_handling'] = 'Umgang mit getaggten E-Mails steuern';
$lang['user']['tag_in_subfolder'] = 'In Unterordner';
$lang['user']['tag_in_subject'] = 'In Betreff';
$lang['user']['tag_in_none'] = 'Nichts tun';
$lang['user']['tag_help_explain'] = 'Als Unterordner: Es wird ein Ordner mit dem Namen des Tags unterhalb der Inbox erstellt ("INBOX/Facebook").<br>
In Betreff: Der Name des Tags wird dem Betreff angefügt, etwa "[Facebook] Meine Neuigkeiten".';
$lang['user']['tag_help_example'] = 'Beispiel für eine getaggte E-Mail-Adresse: ich<b>+Facebook</b>@example.org';

$lang['user']['eas_reset'] = 'ActiveSync Geräte-Cache zurücksetzen';
$lang['user']['eas_reset_now'] = 'Jetzt zurücksetzen';
$lang['user']['eas_reset_help'] = 'In vielen Fällen kann ein ActiveSync Profil durch das Zurücksetzen des Caches repariert werden.<br><b>Vorsicht:</b> Alle Elemente werden erneut heruntergeladen!';

$lang['user']['sogo_profile_reset'] = 'SOGo Profil zurücksetzen';
$lang['user']['sogo_profile_reset_now'] = 'Profil jetzt zurücksetzen';
$lang['user']['sogo_profile_reset_help'] = 'Das Profil wird inklusive <strong>aller</strong> Kalender- und Kontaktdaten <b>unwiederbringlich gelöscht</b>.';

$lang['user']['encryption'] = 'Verschlüsselung';
$lang['user']['username'] = 'Benutzername';
$lang['user']['last_run'] = 'Letzte Ausführung';
$lang['user']['excludes'] = 'Ausschlüsse';
$lang['user']['interval'] = 'Intervall';
$lang['user']['active'] = 'Aktiv';
$lang['user']['action'] = 'Aktion';
$lang['user']['edit'] = 'Bearbeiten';
$lang['user']['remove'] = 'Entfernen';
$lang['user']['create_syncjob'] = 'Neuen Sync-Job erstellen';

$lang['start']['mailcow_apps_detail'] = 'Verwenden Sie mailcow Apps, um E-Mails abzurufen, Kalender und Kontakte zu verwalten und vieles mehr.';
$lang['start']['mailcow_panel_detail'] = '<b>Domain-Administratoren</b> erstellen, verändern oder löschen Mailboxen, verwalten die Domäne und sehen sonstige Einstellungen ein.<br>
	Als <b>Mailbox-Benutzer</b> erstellen Sie hier zeitlich limitierte Aliasse, ändern das Verhalten des Spamfilters, setzen ein neues Passwort und vieles mehr.';
$lang['start']['imap_smtp_server_auth_info'] = 'Bitte verwenden Sie Ihre vollständige E-Mail-Adresse sowie das PLAIN-Authentifizierungsverfahren.<br>
Ihre Anmeldedaten werden durch die obligatorische Verschlüsselung entgegen des Begriffes "PLAIN" nicht unverschlüsselt übertragen.';
$lang['start']['help'] = 'Hilfe ein-/ausblenden';
$lang['header']['mailcow_settings'] = 'Konfiguration';
$lang['header']['administration'] = 'Server-Konfiguration';
$lang['header']['mailboxes'] = 'E-Mail-Setup';
$lang['header']['user_settings'] = 'Benutzereinstellungen';
$lang['header']['quarantine'] = "Quarantäne";
$lang['header']['debug'] = "Systeminformation";
$lang['quarantine']['disabled_by_config'] = "Die derzeitige Konfiguration deaktiviert die Funktion des Quarantäne-Systems.";
$lang['mailbox']['tls_policy_maps'] = 'TLS-Richtlinien';
$lang['mailbox']['tls_policy_maps_long'] = 'Ausgehende TLS-Richtlinien';
$lang['mailbox']['tls_policy_maps_info'] = 'Nachstehende Richtlinien erzwingen TLS-Transportregeln unabhängig von TLS-Richtlinieneinstellungen eines Benutzers.<br>
  Für weitere Informationen zur Syntax sollte <a href="http://www.postfix.org/postconf.5.html#smtp_tls_policy_maps" target="_blank">die "smtp_tls_policy_maps" Dokumentation</a> konsultiert werden.';
$lang['mailbox']['tls_enforce_in'] = 'TLS eingehend erzwingen';
$lang['mailbox']['tls_enforce_out'] = 'TLS ausgehend erzwingen';
$lang['mailbox']['tls_map_dest'] = 'Ziel';
$lang['mailbox']['tls_map_dest_info'] = 'Beispiele: example.org, .example.org, [mail.example.org]:25';
$lang['mailbox']['tls_map_policy'] = 'Richtlinie';
$lang['mailbox']['tls_map_parameters'] = 'Parameter';
$lang['mailbox']['tls_map_parameters_info'] = 'Leer oder Parameter, Beispiele: protocols=!SSLv2 ciphers=medium exclude=3DES';
$lang['mailbox']['booking_0'] = 'Immer als verfügbar anzeigen';
$lang['mailbox']['booking_lt0'] = 'Unbegrenzt, jedoch anzeigen, wenn gebucht';
$lang['mailbox']['booking_custom'] = 'Benutzerdefiniertes Limit';
$lang['mailbox']['booking_0_short'] = 'Immer verfügbar';
$lang['mailbox']['booking_lt0_short'] = 'Weiches Limit';
$lang['mailbox']['booking_custom_short'] = 'Hartes Limit';
$lang['mailbox']['domain'] = 'Domain';
$lang['mailbox']['spam_aliases'] = 'Temp. Alias';
$lang['mailbox']['alias'] = 'Alias';
$lang['mailbox']['aliases'] = 'Aliasse';
$lang['mailbox']['multiple_bookings'] = 'Mehrfachbuchen';
$lang['mailbox']['kind'] = 'Art';
$lang['mailbox']['description'] = 'Beschreibung';
$lang['mailbox']['resources'] = 'Ressourcen';
$lang['mailbox']['domains'] = 'Domains';
$lang['admin']['domain_s'] = 'Domain(s)';
$lang['mailbox']['mailbox'] = 'Mailbox';
$lang['mailbox']['mailboxes'] = 'Mailboxen';
$lang['mailbox']['mailbox_quota'] = 'Max. Größe einer Mailbox';
$lang['mailbox']['domain_quota'] = 'Gesamtspeicher';
$lang['mailbox']['active'] = 'Aktiv';
$lang['mailbox']['action'] = 'Aktion';
$lang['mailbox']['backup_mx'] = 'Backup MX';
$lang['mailbox']['domain_aliases'] = 'Domain-Aliasse';
$lang['mailbox']['target_domain'] = 'Ziel-Domain';
$lang['mailbox']['target_address'] = 'Ziel-Adresse';
$lang['mailbox']['username'] = 'Benutzername';
$lang['mailbox']['fname'] = 'Name';
$lang['mailbox']['filter_table'] = 'Filtern';
$lang['mailbox']['in_use'] = 'Prozentualer Gebrauch';
$lang['mailbox']['msg_num'] = 'Anzahl Nachrichten';
$lang['mailbox']['remove'] = 'Entfernen';
$lang['mailbox']['edit'] = 'Bearbeiten';
$lang['mailbox']['no_record'] = 'Kein Eintrag für Objekt %s';
$lang['mailbox']['no_record_single'] = 'Kein Eintrag';
$lang['mailbox']['add_domain'] = 'Domain hinzufügen';
$lang['mailbox']['add_domain_alias'] = 'Domain-Alias hinzufügen';
$lang['mailbox']['add_mailbox'] = 'Mailbox hinzufügen';
$lang['mailbox']['add_resource'] = 'Ressource hinzufügen';
$lang['mailbox']['add_alias'] = 'Alias hinzufügen';
$lang['mailbox']['empty'] = 'Keine Einträge vorhanden';
$lang['mailbox']['toggle_all'] = 'Alle';
$lang['mailbox']['quick_actions'] = 'Aktionen';
$lang['mailbox']['activate'] = 'Aktivieren';
$lang['mailbox']['deactivate'] = 'Deaktivieren';
$lang['mailbox']['owner'] = 'Besitzer';
$lang['mailbox']['mins_interval'] = 'Intervall (min)';
$lang['mailbox']['last_run'] = 'Letzte Ausführung';
$lang['mailbox']['last_run_reset'] = 'Als nächstes ausführen';
$lang['mailbox']['excludes'] = 'Ausschlüsse';
$lang['mailbox']['sieve_info'] = 'Es können mehrere Filter pro Benutzer existieren, aber nur ein Filter eines Typs (Pre-/Postfilter) kann gleichzeitig aktiv sein.<br>
Die Ausführung erfolgt in nachstehender Reihenfolge. Ein fehlgeschlagenes Script sowie der Befehl "keep;" stoppen die weitere Verarbeitung <b>nicht</b>.<br>
<a href="https://github.com/mailcow/mailcow-dockerized/blob/master/data/conf/dovecot/global_sieve_before" target="_blank">Global sieve prefilter</a> → Prefilter → User scripts → Postfilter → <a href="https://github.com/mailcow/mailcow-dockerized/blob/master/data/conf/dovecot/global_sieve_after" target="_blank">Global sieve postfilter</a>';

$lang['info']['no_action'] = 'Keine Aktion anwendbar';

$lang['edit']['sogo_visible'] = 'Alias in SOGo sichtbar';
$lang['edit']['sogo_visible_info'] = 'Diese Option hat lediglich Einfluss auf Objekte, die in SOGo darstellbar sind (geteilte oder nicht-geteilte Alias-Adressen mit dem Ziel mindestens einer lokalen Mailbox).';
$lang['mailbox']['sogo_visible'] = 'Alias Sichtbarkeit in SOGo';
$lang['mailbox']['sogo_visible_y'] = 'Alias in SOGo anzeigen';
$lang['mailbox']['sogo_visible_n'] = 'Alias in SOGo verbergen';
$lang['edit']['syncjob'] = 'Sync-Job bearbeiten';
$lang['edit']['save'] = 'Änderungen speichern';
$lang['edit']['username'] = 'Benutzername';
$lang['edit']['hostname'] = 'Servername';
$lang['edit']['encryption'] = 'Verschlüsselung';
$lang['edit']['maxage'] = 'Maximales Alter in Tagen einer Nachricht, die kopiert werden soll</br ><small>(0 = alle Nachrichten kopieren)</small>';
$lang['edit']['subfolder2'] = 'Ziel-Ordner<br><small>(leer = kein Unterordner)</small>';
$lang['edit']['mins_interval'] = 'Intervall (min)';
$lang['edit']['maxbytespersecond'] = 'Max. Übertragungsrate in Bytes/s (0 für unlimitiert)';
$lang['edit']['automap'] = 'Ordner automatisch mappen ("Sent items", "Sent" => "Sent" etc.)';
$lang['edit']['skipcrossduplicates'] = 'Duplikate auch über Ordner hinweg überspringen ("first come, first serve")';
$lang['add']['automap'] = 'Ordner automatisch mappen ("Sent items", "Sent" => "Sent" etc.)';
$lang['add']['skipcrossduplicates'] = 'Duplikate auch über Ordner hinweg überspringen ("first come, first serve")';
$lang['edit']['exclude'] = 'Elemente ausschließen (Regex)';
$lang['edit']['max_mailboxes'] = 'Max. Mailboxanzahl';
$lang['edit']['title'] = 'Objekt bearbeiten';
$lang['edit']['target_address'] = 'Ziel-Adresse(n) <small>(getrennt durch Komma)</small>';
$lang['edit']['active'] = 'Aktiv';
$lang['add']['gal'] = 'Globales Adressbuch';
$lang['edit']['gal'] = 'Globales Adressbuch';
$lang['add']['gal_info'] = 'Das Globale Adressbuch enthält alle Objekte einer Domain und kann durch keinen Benutzer geändert werden. Die Verfügbarkeitsinformation in SOGo ist nur bei eingeschaltetem globalen Adressbuch ersichtlich! <b>Zum Anwenden einer Änderung muss SOGo neugestartet werden.</b>';
$lang['edit']['gal_info'] = 'Das Globale Adressbuch enthält alle Objekte einer Domain und kann durch keinen Benutzer geändert werden. Die Verfügbarkeitsinformation in SOGo ist nur bei eingeschaltetem globalen Adressbuch ersichtlich <b>Zum Anwenden einer Änderung muss SOGo neugestartet werden.</b>';
$lang['edit']['force_pw_update'] = 'Erzwinge Passwortänderung bei nächstem Login';
$lang['edit']['force_pw_update_info'] = 'Dem Benutzer wird lediglich der Zugang zur mailcow UI ermöglicht.';
$lang['edit']['sogo_access'] = 'SOGo Zugriffsrecht';
$lang['edit']['sogo_access_info'] = 'Zugriff auf SOGo erlauben oder verbieten. Diese Einstellung hat weder Einfluss auf den Zugang sonstiger Dienste noch entfernt sie ein vorhandenes SOGo Benutzerprofil.';
$lang['edit']['target_domain'] = 'Ziel-Domain';
$lang['edit']['password'] = 'Passwort';
$lang['edit']['password_repeat'] = 'Passwort (Wiederholung)';
$lang['edit']['domain_admin'] = 'Domain-Administrator bearbeiten';
$lang['edit']['domain'] = 'Domain bearbeiten';
$lang['edit']['edit_alias_domain'] = 'Alias-Domain bearbeiten';
$lang['edit']['domains'] = 'Domains';
$lang['edit']['alias'] = 'Alias bearbeiten';
$lang['edit']['mailbox'] = 'Mailbox bearbeiten';
$lang['edit']['description'] = 'Beschreibung';
$lang['edit']['max_aliases'] = 'Max. Aliasse';
$lang['edit']['max_quota'] = 'Max. Größe per Mailbox (MiB)';
$lang['edit']['domain_quota'] = 'Domain Speicherplatz gesamt (MiB)';
$lang['edit']['backup_mx_options'] = 'Backup MX Optionen';
$lang['edit']['relay_domain'] = 'Diese Domain relayen';
$lang['edit']['relay_all'] = 'Alle Empfänger-Adressen relayen';
$lang['edit']['relay_all_info'] = '<small>Wenn <b>nicht</b> alle Empfänger-Adressen relayt werden sollen, müssen "blinde" Mailboxen für jede Adresse, die relayt werden soll, erstellen werden.</small>';
$lang['edit']['full_name'] = 'Voller Name';
$lang['edit']['quota_mb'] = 'Speicherplatz (MiB)';
$lang['edit']['sender_acl'] = 'Darf Nachrichten versenden als';
$lang['edit']['sender_acl_disabled'] = '↳ <span class="label label-danger">Absenderprüfung deaktiviert</span>';
$lang['user']['sender_acl_disabled'] = '<span class="label label-danger">Absenderprüfung deaktiviert</span>';
$lang['edit']['previous'] = 'Vorherige Seite';
$lang['edit']['unchanged_if_empty'] = 'Unverändert, wenn leer';
$lang['edit']['dont_check_sender_acl'] = 'Absender für Domain %s u. Alias-Dom. nicht prüfen';
$lang['edit']['multiple_bookings'] = 'Mehrfaches Buchen';
$lang['edit']['kind'] = 'Art';
$lang['edit']['resource'] = 'Ressource';
$lang['edit']['public_comment'] = 'Öffentlicher Kommentar';
$lang['mailbox']['public_comment'] = 'Öffentlicher Kommentar';
$lang['edit']['private_comment'] = 'Privater Kommentar';
$lang['mailbox']['private_comment'] = 'Privater Kommentar';
$lang['edit']['comment_info'] = 'Ein privater Kommentar ist für den Benutzer nicht einsehbar. Ein öffentlicher Kommentar wird als Tooltip im Interface des Benutzers angezeigt.';
$lang['add']['public_comment'] = 'Öffentlicher Kommentar';
$lang['add']['private_comment'] = 'Privater Kommentar';
$lang['add']['comment_info'] = 'Ein privater Kommentar ist für den Benutzer nicht einsehbar. Ein öffentlicher Kommentar wird als Tooltip im Interface des Benutzers angezeigt.';

$lang['acl']['spam_alias'] = 'Temporäre E-Mail Aliasse';
$lang['acl']['tls_policy'] = 'Verschlüsselungsrichtlinie';
$lang['acl']['spam_score'] = 'Spam-Bewertung';
$lang['acl']['spam_policy'] = 'Blacklist/Whitelist';
$lang['acl']['delimiter_action'] = 'Delimiter Aktionen (tags)';
$lang['acl']['syncjobs'] = 'Sync Jobs';
$lang['acl']['eas_reset'] = 'EAS-Cache zurücksetzen';
$lang['acl']['sogo_profile_reset'] = 'SOGo Profil zurücksetzen';
$lang['acl']['quarantine'] = 'Quarantäne-Aktionen';
$lang['acl']['quarantine_notification'] = 'Ändern der Quarantäne-Benachrichtigung';
$lang['acl']['quarantine_attachments'] = 'Anhänge aus Quarantäne';
$lang['acl']['alias_domains'] = 'Alias-Domains hinzufügen';
$lang['acl']['login_as'] = 'Einloggen als Mailbox-Benutzer';
$lang['acl']['bcc_maps'] = 'BCC Maps';
$lang['acl']['filters'] = 'Filter';
$lang['acl']['ratelimit'] = 'Rate limit';
$lang['acl']['recipient_maps'] = 'Empfängerumschreibungen';
$lang['acl']['unlimited_quota'] = 'Unendliche Quota für Mailboxen';
$lang['acl']['extend_sender_acl'] = 'Eingabe externer Absenderadressen erlauben';
$lang['acl']['prohibited'] = 'Untersagt durch Richtlinie';
$lang['acl']['sogo_access'] = 'Verwalten des SOGo Zugriffsrechts erlauben';

$lang['edit']['extended_sender_acl'] = 'Externe Absenderadressen';
$lang['edit']['extended_sender_acl_info'] = 'Der DKIM Domainkey der externen Absenderdomain sollte in diesen Server importiert werden, falls vorhanden.<br>
  Wird SPF verwendet, muss diesem Server der Versand gestattet werden.<br>
  Wird eine Domain oder Alias-Domain zu diesem Server hinzugefügt, die sich mit der externen Absenderadresse überschneidet, wird der externe Absender hier entfernt.<br>
  Ein Eintrag @domain.tld erlaubt den Versand als *@domain.tld';
$lang['edit']['sender_acl_info'] = 'Wird einem Mailbox-Benutzer A der Versand als Mailbox-Benutzer B gestattet, so erscheint der Absender <b>nicht</b> automatisch in SOGo zur Auswahl.<br>
  In SOGo muss zusätzlich eine Delegation eingerichtet werden. Dieses Verhalten trifft nicht auf Alias-Adressen zu.';

$lang['mailbox']['quarantine_notification'] = 'Quarantäne-Benachrichtigung';
$lang['mailbox']['never'] = 'Niemals';
$lang['mailbox']['hourly'] = 'Stündlich';
$lang['mailbox']['daily'] = 'Täglich';
$lang['mailbox']['weekly'] = 'Wöchentlich';
$lang['user']['quarantine_notification'] = 'Quarantäne-Benachrichtigung';
$lang['user']['never'] = 'Niemals';
$lang['user']['hourly'] = 'Stündlich';
$lang['user']['daily'] = 'Täglich';
$lang['user']['weekly'] = 'Wöchentlich';
$lang['user']['quarantine_notification_info'] = 'Wurde über eine E-Mail in Quarantäne informiert, wird sie als "benachrichtigt" markiert und keine weitere Benachrichtigung zu dieser E-Mail versendet.';

$lang['add']['generate'] = 'generieren';
$lang['add']['syncjob'] = 'Syncjob hinzufügen';
$lang['add']['syncjob_hint'] = 'Passwörter werden unverschlüsselt abgelegt!';
$lang['add']['hostname'] = 'Host';
$lang['add']['destination'] = 'Ziel';
$lang['add']['nexthop'] = 'Next Hop';
$lang['edit']['nexthop'] = 'Next Hop';
$lang['add']['port'] = 'Port';
$lang['add']['username'] = 'Benutzername';
$lang['add']['enc_method'] = 'Verschlüsselung';
$lang['add']['mins_interval'] = 'Abrufintervall (Minuten)';
$lang['add']['exclude'] = 'Elemente ausschließen (Regex)';
$lang['add']['delete2duplicates'] = 'Lösche Duplikate im Ziel';
$lang['add']['delete1'] = 'Lösche Nachricht nach Übertragung vom Quell-Server';
$lang['add']['delete2'] = 'Lösche Nachrichten von Ziel-Server, die nicht auf Quell-Server vorhanden sind';
$lang['add']['custom_params'] = 'Eigene Parameter';
$lang['add']['custom_params_hint'] = 'Richtig: --param=xy, falsch: --param xy';
$lang['add']['subscribeall'] = 'Alle synchronisierten Ordner abonnieren';
$lang['add']['timeout1'] = 'Timeout für Verbindung zum Remote-Host';
$lang['add']['timeout2'] = 'Timeout für Verbindung zum lokalen Host';

$lang['edit']['delete2duplicates'] = 'Lösche Duplikate im Ziel';
$lang['edit']['delete1'] = 'Lösche Nachricht nach Übertragung vom Quell-Server';
$lang['edit']['delete2'] = 'Lösche Nachrichten von Ziel-Server, die nicht auf Quell-Server vorhanden sind';

$lang['add']['domain_matches_hostname'] = 'Domain %s darf nicht dem Hostnamen entsprechen';
$lang['add']['domain'] = 'Domain';
$lang['add']['active'] = 'Aktiv';
$lang['add']['multiple_bookings'] = 'Mehrfaches Buchen möglich';
$lang['add']['description'] = 'Beschreibung';
$lang['add']['max_aliases'] = 'Max. mögliche Aliasse';
$lang['add']['max_mailboxes'] = 'Max. mögliche Mailboxen';
$lang['add']['mailbox_quota_m'] = 'Max. Speicherplatz pro Mailbox (MiB)';
$lang['add']['domain_quota_m'] = 'Domain Speicherplatz gesamt (MiB)';
$lang['add']['backup_mx_options'] = 'Backup MX Optionen';
$lang['add']['relay_all'] = 'Alle Empfänger-Adressen relayen';
$lang['add']['relay_domain'] = 'Relay Domain';
$lang['add']['relay_all_info'] = '<small>Wenn Sie <b>nicht</b> alle Empfänger-Adressen relayen möchten, müssen Sie eine Mailbox für jede Adresse, die relayt werden soll, erstellen.</small>';
$lang['add']['alias_address'] = 'Alias-Adresse(n)';
$lang['add']['alias_address_info'] = '<small>Vollständige E-Mail-Adresse(n) oder @example.com, um alle Nachrichten einer Domain weiterzuleiten. Getrennt durch Komma. <b>Nur eigene Domains</b>.</small>';
$lang['add']['alias_domain_info'] = '<small>Nur gültige Domains. Getrennt durch Komma.</small>';
$lang['add']['target_address'] = 'Ziel-Adresse(n)';
$lang['add']['target_address_info'] = '<small>Vollständige E-Mail-Adresse(n). Getrennt durch Komma.</small>';
$lang['add']['alias_domain'] = 'Alias-Domain';
$lang['add']['select'] = 'Bitte auswählen';
$lang['add']['target_domain'] = 'Ziel-Domain';
$lang['add']['kind'] = 'Art';
$lang['add']['mailbox_username'] = 'Benutzername (linker Teil der E-Mail-Adresse)';
$lang['add']['full_name'] = 'Vor- und Nachname';
$lang['add']['quota_mb'] = 'Speicherplatz (MiB)';
$lang['add']['select_domain'] = 'Bitte zuerst eine Domain auswählen';
$lang['add']['password'] = 'Passwort';
$lang['add']['password_repeat'] = 'Passwort (Wiederholung)';
$lang['add']['restart_sogo_hint'] = 'Der SOGo Container muss nach dem Hinzufügen einer neuen Domain neugestartet werden!';
$lang['add']['goto_null'] = 'Nachrichten sofort verwerfen';
$lang['add']['goto_ham'] = 'Nachrichten als <span class="text-success"><b>Ham</b></span> lernen';
$lang['add']['goto_spam'] = 'Nachrichten als <span class="text-danger"><b>Spam</b></span> lernen';
$lang['add']['validation_success'] = 'Erfolgreich validiert';
$lang['add']['activate_filter_warn'] = 'Alle anderen Filter diesen Typs werden deaktiviert, falls dieses Script aktiv markiert wird.';
$lang['add']['validate'] = 'Validieren';
$lang['mailbox']['add_filter'] = 'Filter erstellen';
$lang['add']['sieve_desc'] = 'Kurze Beschreibung';
$lang['edit']['sieve_desc'] = 'Kurze Beschreibung';
$lang['add']['sieve_type'] = 'Filtertyp';
$lang['edit']['sieve_type'] = 'Filtertyp';
$lang['mailbox']['set_prefilter'] = 'Als Prefilter markieren';
$lang['mailbox']['set_postfilter'] = 'Als Postfilter markieren';
$lang['mailbox']['filters'] = 'Filter';
$lang['mailbox']['sync_jobs'] = 'Synchronisationen';
$lang['mailbox']['inactive'] = 'Inaktiv';
$lang['edit']['validate_save'] = 'Validieren und speichern';


$lang['login']['username'] = 'Benutzername';
$lang['login']['password'] = 'Passwort';
$lang['login']['login'] = 'Anmelden';
$lang['login']['delayed'] = 'Login wurde zur Sicherheit um %s Sekunde/n verzögert.';

$lang['tfa']['tfa'] = "Zwei-Faktor-Authentifizierung";
$lang['tfa']['set_tfa'] = "Konfiguriere Zwei-Faktor-Authentifizierungsmethode";
$lang['tfa']['yubi_otp'] = "Yubico OTP Authentifizierung";
$lang['tfa']['key_id'] = "Ein Name für diesen YubiKey";
$lang['tfa']['init_u2f'] = "Initialisiere, bitte warten...";
$lang['tfa']['start_u2f_validation'] = "Starte Validierung";
$lang['tfa']['error_code'] = "Fehlercode";
$lang['tfa']['reload_retry'] = "- (bei persistierendem Fehler, bitte Browserfenster neuladen)";
$lang['tfa']['key_id_totp'] = "Ein eindeutiger Name";
$lang['tfa']['api_register'] = 'mailcow verwendet die Yubico Cloud API. Ein API-Key für den Yubico Stick kann <a href="https://upgrade.yubico.com/getapikey/" target="_blank">hier</a> bezogen werden.';
$lang['tfa']['u2f'] = "U2F Authentifizierung";
$lang['tfa']['totp'] = "TOTP Authentifizierung";
$lang['tfa']['none'] = "Deaktiviert";
$lang['tfa']['delete_tfa'] = "Deaktiviere 2FA";
$lang['tfa']['disable_tfa'] = "Deaktiviere 2FA bis zur nächsten erfolgreichen Anmeldung";
$lang['tfa']['confirm'] = "Bestätigen";
$lang['tfa']['totp'] = "Time-based OTP (Google Authenticator etc.)";
$lang['tfa']['select'] = "Bitte auswählen";
$lang['tfa']['waiting_usb_auth'] = "<i>Warte auf USB-Gerät...</i><br><br>Bitte jetzt den vorgesehenen Taster des U2F USB-Gerätes berühren.";
$lang['tfa']['waiting_usb_register'] = "<i>Warte auf USB-Gerät...</i><br><br>Bitte zuerst das obere Passwortfeld ausfüllen und erst dann den vorgesehenen Taster des U2F USB-Gerätes berühren.";
$lang['tfa']['scan_qr_code'] = "Bitte scannen Sie jetzt den angezeigten QR-Code:.";
$lang['tfa']['enter_qr_code'] = "Falls Sie den angezeigten QR-Code nicht scannen können, verwenden Sie bitte nachstehenden Sicherheitsschlüssel";
$lang['tfa']['confirm_totp_token'] = "Bitte bestätigen Sie die Änderung durch Eingabe eines generierten Tokens";

$lang['admin']['rspamd-com_settings'] = '<a href="https://rspamd.com/doc/configuration/settings.html#settings-structure" target="_blank">Rspamd docs</a>
  - Ein Name wird automatisch generiert. Beispielinhalte zur Einsicht stehen nachstehend bereit.';
  
$lang['admin']['no_new_rows'] = 'Keine weiteren Zeilen vorhanden';
$lang['admin']['additional_rows'] = ' zusätzliche Zeilen geladen'; // parses to 'n additional rows were added'
$lang['admin']['private_key'] = 'Private Key';
$lang['admin']['import'] = 'Importieren';
$lang['admin']['duplicate'] = 'Duplizieren';
$lang['admin']['import_private_key'] = 'Private Key importieren';
$lang['admin']['duplicate_dkim'] = 'DKIM duplizieren';
$lang['admin']['f2b_parameters'] = 'Fail2ban-Parameter';
$lang['admin']['f2b_ban_time'] = 'Bannzeit (s)';
$lang['admin']['f2b_max_attempts'] = 'Max. Versuche';
$lang['admin']['f2b_retry_window'] = 'Wiederholungen im Zeitraum von (s)';
$lang['admin']['f2b_netban_ipv4'] = 'Netzbereich für IPv4-Bans (8-32)';
$lang['admin']['f2b_netban_ipv6'] = 'Netzbereich für IPv6-Bans (8-128)';
$lang['admin']['f2b_whitelist'] = 'Whitelist für Netzwerke und Hosts';
$lang['admin']['r_inactive'] = 'Inaktive Restriktionen';
$lang['admin']['r_active'] = 'Aktive Restriktionen';
$lang['admin']['r_info'] = 'Ausgegraute/deaktivierte Elemente sind mailcow nicht bekannt und können nicht in die Liste inaktiver Elemente verschoben werden. Unbekannte Restriktionen werden trotzdem in Reihenfolge der Erscheinung gesetzt.<br>Sie können ein Element in der Datei <code>inc/vars.local.inc.php</code> als bekannt hinzufügen, um es zu bewegen.';
$lang['admin']['save'] = 'Änderungen speichern';
$lang['admin']['dkim_add_key'] = 'ARC/DKIM-Key hinzufügen';
$lang['admin']['dkim_keys'] = 'ARC/DKIM-Keys';
$lang['admin']['dkim_key_valid'] = 'Key gültig';
$lang['admin']['dkim_key_unused'] = 'Key ohne Zuweisung';
$lang['admin']['dkim_key_missing'] = 'Key fehlt';
$lang['admin']['dkim_from'] = 'Von';
$lang['admin']['dkim_to'] = 'Nach';
$lang['admin']['dkim_from_title'] = 'Quellobjekt für Duplizierung';
$lang['admin']['dkim_to_title'] = 'Ziel-Objekt(e) werden überschrieben';
$lang['admin']['dkim_domains_wo_keys'] = "Domains mit fehlenden Keys auswählen";
$lang['admin']['add'] = 'Hinzufügen';
$lang['admin']['queue_manager'] = 'Queue Manager';
$lang['add']['add_domain_restart'] = 'Domain hinzufügen und SOGo neustarten';
$lang['add']['add_domain_only'] = 'Nur Domain hinzufügen';
$lang['admin']['configuration'] = 'Konfiguration';
$lang['admin']['password'] = 'Passwort';
$lang['admin']['password_repeat'] = 'Passwort (Wiederholung)';
$lang['admin']['active'] = 'Aktiv';
$lang['admin']['inactive'] = 'Inaktiv';
$lang['admin']['action'] = 'Aktion';
$lang['admin']['add_domain_admin'] = 'Domain-Administrator hinzufügen';
$lang['admin']['domain_admin'] = 'Administrator hinzufügen';
$lang['admin']['add_settings_rule'] = 'Rspamd Regel hinzufügen';
$lang['admin']['rsetting_desc'] = 'Kurze Beschreibung';
$lang['admin']['rsetting_content'] = 'Regelinhalt';
$lang['admin']['rsetting_none'] = 'Keine Regel hinterlegt';
$lang['admin']['rsetting_no_selection'] = 'Bitte eine Regel auswählen';
$lang['admin']['rsettings_preset_1'] = 'Alles außer DKIM und Ratelimits für authentifizierte Benutzer deaktivieren"';
$lang['admin']['rsettings_preset_2'] = 'Spam an Postmaster-Addressen nicht blockieren';
$lang['admin']['rsettings_insert_preset'] = 'Beispiel "%s" laden';
$lang['admin']['rsetting_add_rule'] = 'Regel hinzufügen';
$lang['admin']['admin_domains'] = 'Domain-Zuweisungen';
$lang['admin']['queue_ays'] = 'Soll die derzeitige Queue wirklich komplett bereinigt werden?';
$lang['admin']['arrival_time'] = 'Ankunftszeit (Serverzeit)';
$lang['admin']['message_size'] = 'Nachrichtengröße';
$lang['admin']['sender'] = 'Sender';
$lang['admin']['recipients'] = 'Empfänger';
$lang['admin']['flush_queue'] = 'Flush Queue';
$lang['admin']['delete_queue'] = 'Alle löschen';
$lang['admin']['domain_admins'] = 'Domain-Administratoren';
$lang['admin']['username'] = 'Benutzername';
$lang['admin']['edit'] = 'Bearbeiten';
$lang['admin']['remove'] = 'Entfernen';
$lang['admin']['save'] = 'Änderungen speichern';
$lang['admin']['admin'] = 'Administrator';
$lang['admin']['admin_details'] = 'Administrator bearbeiten';
$lang['admin']['unchanged_if_empty'] = 'Unverändert, wenn leer';
$lang['admin']['access'] = 'Zugang';
$lang['admin']['no_record'] = 'Kein Eintrag';
$lang['admin']['filter_table'] = 'Tabelle Filtern';
$lang['admin']['empty'] = 'Keine Einträge vorhanden';
$lang['admin']['time'] = 'Zeit';
$lang['admin']['last_applied'] = 'Zuletzt angewendet';
$lang['admin']['reset_limit'] = 'Hash entfernen';
$lang['admin']['hash_remove_info'] = 'Das Entfernen eines Ratelimit Hashes - sofern noch existent - bewirkt den Reset gezählter Nachrichten dieses Elements.<br>
  Jeder Hash wird durch eine eindeutige Farbe gekennzeichnet.';
$lang['warning']['hash_not_found'] = 'Hash nicht gefunden. Möglicherweise wurde dieser bereits gelöscht.';
$lang['success']['hash_deleted'] = 'Hash wurde gelöscht';
$lang['admin']['authed_user'] = 'Auth. Benutzer';
$lang['admin']['priority'] = 'Gewichtung';
$lang['admin']['refresh'] = 'Neu laden';
$lang['admin']['to_top'] = 'Nach oben';
$lang['admin']['in_use_by'] = 'Verwendet von';
$lang['admin']['rate_name'] = 'Rate name';
$lang['admin']['message'] = 'Nachricht';
$lang['admin']['forwarding_hosts'] = 'Weiterleitungs-Hosts';
$lang['admin']['forwarding_hosts_hint'] = 'Eingehende Nachrichten werden von den hier gelisteten Hosts bedingungslos akzeptiert. Diese Hosts werden dann nicht mit DNSBLs abgeglichen oder Greylisting unterworfen. Von ihnen empfangener Spam wird nie abgelehnt, optional kann er aber in den Spam-Ordner einsortiert werden. Die übliche Verwendung für diese Funktion ist, um Mailserver anzugeben, auf denen eine Weiterleitung zu Ihrem mailcow-Server eingerichtet wurde.';
$lang['admin']['forwarding_hosts_add_hint'] = 'Sie können entweder IPv4/IPv6-Adressen, Netzwerke in CIDR-Notation, Hostnamen (die zu IP-Adressen aufgelöst werden), oder Domainnamen (die zu IP-Adressen aufgelöst werden, indem ihr SPF-Record abgefragt wird oder, in dessen Abwesenheit, ihre MX-Records) angeben.';
$lang['admin']['relayhosts_hint'] = 'Erstellen Sie senderabhängige Transporte, um diese im Einstellungsdialog einer Domain auszuwählen.<br>
  Der Transporttyp lautet immer "smtp:". Benutzereinstellungen bezüglich Verschlüsselungsrichtlinie werden beim Transport berücksichtigt.<br>
  Gilt neben ausgewählter Domain auch für untergeordnete Alias-Domains.';
$lang['admin']['transports_hint'] = '→ Transport Maps <b>überwiegen</b> senderabhängige Transport Maps.<br>
→ Transport Maps ignorieren Mailbox-Einstellungen für ausgehende Verschlüsselung. Eine serverweite TLS-Richtlinie wird jedoch angewendet.<br>
→ Der Transport erfolgt immer via "smtp:".<br>
→ Adressen, die mit "/localhost$/" übereinstimmen, werden immer via "local:" transportiert, daher sind sie von einer Zieldefinition "*" ausgeschlossen.<br>
→ Die Authentifizierung wird anhand des "Next hop" Parameters ermittelt. Hierbei würde bei einem beispielhaften Wert "[host]:25" immer zuerst "host" abfragt und <b>erst im Anschluss</b> "[host]:25". Dieses Verhalten schließt die <b>gleichzeitige Verwendung</b> von Einträgen der Art "host" sowie "[host]:25" aus.';
$lang['admin']['add_relayhost_hint'] = 'Bitte beachten Sie, dass Anmeldedaten unverschlüsselt gespeichert werden.<br>
  Angelegte Transporte dieser Art sind <b>senderabhängig</b> und müssen erst einer Domain zugewiesen werden, bevor sie als Transport verwendet werden.<br>
  Diese Einstellungen entsprechen demnach <i>nicht</i> dem "relayhost" Parameter in Postfix.';
$lang['admin']['add_transports_hint'] = 'Bitte beachten Sie, dass Anmeldedaten unverschlüsselt gespeichert werden.';
$lang['admin']['host'] = 'Host';
$lang['admin']['source'] = 'Quelle';
$lang['admin']['add_forwarding_host'] = 'Weiterleitungs-Host hinzufügen';
$lang['admin']['add_relayhost'] = 'Senderabhängigen Transport hinzufügen';
$lang['admin']['add_transport'] = 'Transport hinzufügen';
$lang['admin']['relayhosts'] = 'Senderabhängige Transport Maps';
$lang['admin']['transport_maps'] = 'Transport Maps';
$lang['admin']['routing'] = 'Routing';
$lang['admin']['credentials_transport_warning'] = '<b>Warnung</b>: Das Hinzufügen einer neuen Regel bewirkt die Aktualisierung der Authentifizierungsdaten aller vorhandenen Einträge mit identischem Host.';

$lang['admin']['destination'] = 'Ziel';
$lang['admin']['nexthop'] = 'Next Hop';

$lang['admin']['api_allow_from'] = "IP-Adressen für Zugriff";
$lang['admin']['api_key'] = "API-Key";
$lang['admin']['activate_api'] = "API aktivieren";
$lang['admin']['regen_api_key'] = "API-Key regenerieren";
$lang['admin']['ban_list_info'] = "Übersicht ausgesperrter Netzwerke: <b>Netzwerk (verbleibende Banzeit) - [Aktionen]</b>.<br />IPs, die zum Entsperren eingereiht werden, verlassen die Liste aktiver Bans nach wenigen Sekunden.<br />Rote Labels sind Indikatoren für aktive Blacklisteinträge.";
$lang['admin']['unban_pending'] = "ausstehend";
$lang['admin']['queue_unban'] = "Entsperren einreihen";
$lang['admin']['no_active_bans'] = "Keine aktiven Bans";

$lang['admin']['quota_notifications'] = "Quota Benachrichtigungen";
$lang['admin']['quota_notifications_vars'] = "{{percent}} entspricht der aktuellen Quota in Prozent<br>{{username}} entspricht dem Mailbox-Namen";
$lang['admin']['rspamd_settings_map'] = "Rspamd Settings Map";
$lang['admin']['quarantine'] = "Quarantäne";
$lang['admin']['active_rspamd_settings_map'] = "Derzeit aktive Settings Map";
$lang['admin']['quota_notifications_info'] = "Quota Benachrichtigungen werden an Mailboxen versendet, die 80 respektive 95 Prozent der zur Verfügung stehenden Quota überschreiten.";
$lang['admin']['quarantine_retention_size'] = "Rückhaltungen pro Mailbox:<br><small>0 bedeutet <b>inaktiv</b>.</small>";
$lang['admin']['quarantine_max_size'] = "Maximale Größe in MiB (größere Elemente werden verworfen):<br><small>0 bedeutet <b>nicht</b> unlimitiert.</small>";
$lang['admin']['quarantine_max_age'] = "Maximales Alter in Tagen<br><small>Wert muss größer oder gleich 1 Tag sein.</small>";
$lang['admin']['quarantine_exclude_domains'] = "Domains und Alias-Domains ausschließen";
$lang['admin']['quarantine_notification_sender'] = "Benachrichtigungs-E-Mail Absender";
$lang['admin']['quota_notification_sender'] = "Benachrichtigungs-E-Mail Absender";
$lang['admin']['quarantine_notification_subject'] = "Benachrichtigungs-E-Mail Betreff";
$lang['admin']['quota_notification_subject'] = "Benachrichtigungs-E-Mail Betreff";
$lang['admin']['quarantine_notification_html'] = "Benachrichtigungs-E-Mail Inhalt:<br><small>Leer lassen, um Standard-Template wiederherzustellen.</small>";
$lang['admin']['quota_notification_html'] = "Benachrichtigungs-E-Mail Inhalt:<br><small>Leer lassen, um Standard-Template wiederherzustellen.</small>";
$lang['admin']['quarantine_release_format'] = "Format freigegebener Mails";
$lang['admin']['quarantine_release_format_raw'] = "Unverändertes Original";
$lang['admin']['quarantine_release_format_att'] = "Als Anhang";

$lang['success']['forwarding_host_removed'] = "Weiterleitungs-Host %s wurde entfernt";
$lang['success']['forwarding_host_added'] = "Weiterleitungs-Host %s wurde hinzugefügt";
$lang['success']['relayhost_removed'] = "Mapeintrag %s wurde entfernt";
$lang['success']['relayhost_added'] = "Mapeintrag %s wurde hinzugefügt";
$lang['diagnostics']['dns_records'] = 'DNS-Einträge';
$lang['diagnostics']['dns_records_24hours'] = 'Bitte beachten Sie, dass es bis zu 24 Stunden dauern kann, bis Änderungen an Ihren DNS-Einträgen als aktueller Status auf dieser Seite dargestellt werden. Diese Seite ist nur als Hilfsmittel gedacht, um die korrekten Werte für DNS-Einträge anzuzeigen und zu überprüfen, ob die Daten im DNS hinterlegt sind.';
$lang['diagnostics']['dns_records_name'] = 'Name';
$lang['diagnostics']['dns_records_type'] = 'Typ';
$lang['diagnostics']['dns_records_data'] = 'Korrekte Daten';
$lang['diagnostics']['dns_records_status'] = 'Aktueller Status';
$lang['diagnostics']['optional'] = 'Dieser Eintrag ist optional.';
$lang['diagnostics']['cname_from_a'] = 'Wert abgeleitet von A/AAAA Eintrag. Wird unterstützt, sofern der Eintrag auf die korrekte Ressource zeigt.';
$lang['admin']['relay_from'] = "Absenderadresse";
$lang['admin']['relay_run'] = "Test durchführen";
$lang['mailbox']['waiting'] = "Wartend";
$lang['mailbox']['status'] = "Status";
$lang['mailbox']['running'] = "In Ausführung";
$lang['mailbox']['enable_x'] = "Aktiviere";
$lang['mailbox']['disable_x'] = "Deaktiviere";

$lang['admin']['ui_texts'] = "UI Label und Texte";
$lang['admin']['help_text'] = "Hilfstext unter Login-Maske (HTML zulässig)";
$lang['admin']['title_name'] = '"mailcow UI" Webseiten Titel';
$lang['admin']['main_name'] = '"mailcow UI" Name';
$lang['admin']['apps_name'] = '"mailcow Apps" Name';
$lang['admin']['ui_footer'] = 'Footer (HTML zulässig)';
$lang['admin']['oauth2_info'] = 'Die OAuth2 Implementierung unterstützt den Grant Type "Authorization Code" mit Refresh Tokens.<br>
Der Server wird automatisch einen neuen Refresh Token ausstellen, sobald ein vorheriger Token gegen einen Access Token eingetauscht wurde.<br><br>
→ Der Standard Scope lautet <i>profile</i>. Nur Mailbox-Benutzer können sich gegen OAuth2 authentifizieren. Wird kein Scope angegeben, verwendet das System per Standard <i>profile</i>.<br>
→ Der <i>state</i> Parameter wird im Zuge des Autorisierungsprozesses benötigt.<br><br>
Die Pfade für die OAuth2 API lauten wie folgt: <br>
<ul>
  <li>Authorization Endpoint: <code>/oauth/authorize</code></li>
  <li>Token Endpoint: <code>/oauth/token</code></li>
  <li>Resource Page:  <code>/oauth/profile</code></li>
</ul>
Die Regenerierung des Client Secrets wird vorhandene Authorization Codes nicht invalidieren, dennoch wird der Renew des Access Tokens durch einen Refresh Token nicht mehr gelingen.<br><br>
Das Entfernen aller Client Tokens verursacht die umgehende Terminierung aller aktiven OAuth2 Sessions. Clients müssen sich erneut gegen die OAuth2 Anwendung authentifizieren.';

$lang['admin']['oauth2_client_id'] = "Client ID";
$lang['admin']['oauth2_client_secret'] = "Client Secret";
$lang['admin']['oauth2_redirect_uri'] = "Redirect URI";
$lang['admin']['oauth2_revoke_tokens'] = 'Alle Client Tokens entfernen';
$lang['admin']['oauth2_renew_secret'] = 'Neues Client Secret generieren';
$lang['edit']['client_id'] = 'Client ID';
$lang['edit']['client_secret'] = 'Client Secret';
$lang['edit']['scope'] = 'Scope';
$lang['edit']['grant_types'] = 'Grant types';
$lang['edit']['redirect_uri'] = 'Redirect/Callback URL';
$lang['oauth2']['scope_ask_permission'] = 'Eine Anwendung hat um die folgenden Berechtigungen gebeten';
$lang['oauth2']['profile'] = 'Profil';
$lang['oauth2']['profile_desc'] = 'Persönliche Informationen anzeigen: Benutzername, Name, Erstellzeitpunkt, Änderungszeitpunkt, Status';
$lang['oauth2']['permit'] = 'Anwendung autorisieren';
$lang['oauth2']['authorize_app'] = 'Anwendung autorisieren';
$lang['oauth2']['deny'] = 'Ablehnen';
$lang['oauth2']['access_denied'] = 'Bitte als Mailbox-Nutzer einloggen, um den Zugriff via OAuth2 zu erlauben.';


$lang['admin']['customize'] = "UI Anpassung";
$lang['admin']['change_logo'] = "Logo ändern";
$lang['admin']['logo_info'] = "Die hochgeladene Grafik wird für die Navigationsleiste auf eine Höhe von 40px skaliert. Für die Darstellung auf der Login-Maske beträgt die skalierte Breite maximal 250px. Eine frei skalierbare Grafik (etwa SVG) wird empfohlen.";
$lang['admin']['upload'] = "Hochladen";
$lang['admin']['app_links'] = "App Links";
$lang['admin']['app_name'] = "App Name";
$lang['admin']['link'] = "Link";
$lang['admin']['remove_row'] = "Entfernen";
$lang['admin']['add_row'] = "Reihe hinzufügen";
$lang['admin']['reset_default'] = "Zurücksetzen auf Standard";
$lang['admin']['merged_vars_hint'] = 'Ausgegraute Reihen wurden aus der Datei <code>vars.(local.)inc.php</code> gelesen und können hier nicht verändert werden.';

$lang['edit']['spam_score'] = "Einen benutzerdefiniterten Spam-Score festlegen";
$lang['user']['spam_score_reset'] = "Auf Server-Standard zurücksetzen";
$lang['edit']['spam_policy'] = "Hinzufügen und Entfernen von Einträgen in White- und Blacklists";
$lang['edit']['spam_alias'] = "Anpassen temporärer Alias-Adressen";

$lang['danger']['img_tmp_missing'] = "Grafik konnte nicht validiert werden: Erstellung temporärer Datei fehlgeschlagen";
$lang['danger']['comment_too_long'] = "Kommentarfeld darf maximal 160 Zeichen enthalten";
$lang['danger']['img_invalid'] = "Grafik konnte nicht validiert werden";
$lang['danger']['invalid_mime_type'] = "Grafik konnte nicht validiert werden: Ungültiger MIME-Type";
$lang['success']['upload_success'] = "Datei wurde erfolgreich hochgeladen";
$lang['success']['app_links'] = "Änderungen an App Links wurden gespeichert";
$lang['success']['ui_texts'] = "Änderungen an UI-Texten";
$lang['success']['reset_main_logo'] = "Standardgrafik wurde wiederhergestellt";
$lang['success']['items_released'] = "Ausgewählte Objekte wurden an Mailbox versendet";
$lang['danger']['imagick_exception'] = "Fataler Bildverarbeitungsfehler";
$lang['quarantine']['learn_spam_delete'] = "Als Spam lernen und löschen";
$lang['quarantine']['quarantine'] = "Quarantäne";
$lang['quarantine']['qinfo'] = 'Das Quarantänesystem speichert abgelehnte Nachrichten in der Datenbank. Dem Sender wird <em>nicht</em> signalisiert, dass seine E-Mail zugestellt wurde.
  <br>"' . $lang['quarantine']['learn_spam_delete'] . '" lernt Nachrichten nach bayesscher Statistik als Spam und erstellt Fuzzy Hashes ausgehend von der jeweiligen Nachricht, um ähnliche Inhalte zukünftig zu unterbinden.
  <br>Der Prozess des Lernens kann abhängig vom System zeitintensiv sein.';
$lang['quarantine']['download_eml'] = "Herunterladen (.eml)";
$lang['quarantine']['release'] = "Freigeben";
$lang['quarantine']['empty'] = 'Keine Einträge';
$lang['quarantine']['toggle_all'] = 'Alle auswählen';
$lang['quarantine']['quick_actions'] = 'Aktionen';
$lang['quarantine']['remove'] = 'Entfernen';
$lang['quarantine']['received'] = "Empfangen";
$lang['quarantine']['action'] = "Aktion";
$lang['quarantine']['rcpt'] = "Empfänger";
$lang['quarantine']['qid'] = "Rspamd QID";
$lang['quarantine']['sender'] = "Sender";
$lang['quarantine']['show_item'] = "Details";
$lang['quarantine']['check_hash'] = "Checksumme auf VirusTotal suchen";
$lang['quarantine']['qitem'] = "Quarantäneeintrag";
$lang['quarantine']['rspamd_result'] = "Rspamd Ergebnis";
$lang['quarantine']['subj'] = "Betreff";
$lang['quarantine']['recipients'] = "Empfänger";
$lang['quarantine']['text_plain_content'] = "Inhalt (text/plain)";
$lang['quarantine']['text_from_html_content'] = "Inhalt (html, konvertiert)";
$lang['quarantine']['atts'] = "Anhänge";
$lang['quarantine']['low_danger'] = "Niedrige Gefahr";
$lang['quarantine']['neutral_danger'] = "Neutral/ohne Bewertung";
$lang['quarantine']['medium_danger'] = "Mittlere Gefahr";
$lang['quarantine']['high_danger'] = "Hohe Gefahr";
$lang['quarantine']['danger'] = "Gefahr";
$lang['quarantine']['spam_score'] = "Bewertung";
$lang['quarantine']['qhandler_success'] = "Aktion wurde an das System übergeben. Sie dürfen dieses Fenster nun schließen.";
$lang['warning']['fuzzy_learn_error'] = "Fuzzy Lernfehler: %s";
$lang['danger']['spam_learn_error'] = "Spam Lernfehler: %s";
$lang['success']['qlearn_spam'] = "Nachricht ID %s wurde als Spam gelernt und gelöscht";

$lang['debug']['log_info'] = '<p>mailcow <b>in-memory Logs</b> werden in Redis Listen gespeichert, die maximale Anzahl der Einträge pro Anwendung richtet sich nach LOG_LINES (%d).
  <br>In-memory Logs sind vergänglich und nicht zur ständigen Aufbewahrung bestimmt. Alle Anwendungen, die in-memory protokollieren, schreiben ebenso in den Docker Daemon.
  <br>Das in-memory Protokoll versteht sich als schnelle Übersicht zum Debugging eines Containers, für komplexere Protokolle sollte der Docker Daemon konsultiert werden.</p>
  <p><b>Externe Logs</b> werden via API externer Applikationen bezogen.</p>
  <p><b>Statische Logs</b> sind weitestgehend Aktivitätsprotokolle, die nicht in den Docker Daemon geschrieben werden, jedoch permanent verfügbar sein müssen (ausgeschlossen API Logs).</p>';

$lang['debug']['in_memory_logs'] = 'In-memory Logs';
$lang['debug']['started_on'] = 'Gestartet am';
$lang['debug']['jvm_memory_solr'] = 'JVM Speicherauslastung';
$lang['debug']['external_logs'] = 'Externe Logs';
$lang['debug']['static_logs'] = 'Statische Logs';
$lang['debug']['solr_status'] = 'Solr Status';
$lang['debug']['solr_dead'] = 'Solr startet, ist deaktiviert oder temporär nicht erreichbar.';
$lang['debug']['solr_uptime'] = 'Uptime';
$lang['debug']['solr_started_at'] = 'Gestartet am';
$lang['debug']['solr_last_modified'] = 'Zuletzt geändert';
$lang['debug']['solr_size'] = 'Größe';
$lang['debug']['solr_docs'] = 'Dokumente';

$lang['quarantine']['release_body'] = "Die ursprüngliche Nachricht wurde als EML-Datei im Anhang hinterlegt.";
$lang['danger']['release_send_failed'] = "Die Nachricht konnte nicht versendet werden: %s";
$lang['quarantine']['release_subject'] = "Potentiell schädliche Nachricht aus Quarantäne: %s";

$lang['mailbox']['bcc_map'] = "BCC Map";
$lang['mailbox']['bcc_map_type'] = "BCC Typ";
$lang['mailbox']['bcc_type'] = "BCC Typ";
$lang['mailbox']['bcc_sender_map'] = "Senderabhängig";
$lang['mailbox']['bcc_rcpt_map'] = "Empfängerabhängig";
$lang['mailbox']['bcc_local_dest'] = "Lokales Ziel";
$lang['mailbox']['bcc_destinations'] = "BCC-Ziel";
$lang['mailbox']['bcc_destination'] = "BCC-Ziel";
$lang['edit']['bcc_dest_format'] = 'BCC-Ziel muss eine gültige E-Mail-Adresse sein.';

$lang['mailbox']['bcc'] = "BCC";
$lang['mailbox']['bcc_maps'] = "BCC-Maps";
$lang['mailbox']['bcc_to_sender'] = "Map senderabhängig verwenden";
$lang['mailbox']['bcc_to_rcpt'] = "Map empfängerabhängig verwenden";
$lang['mailbox']['add_bcc_entry'] = "BCC-Eintrag hinzufügen";
$lang['mailbox']['bcc_info'] = "Eine empfängerabhängige Map wird verwendet, wenn die BCC-Map Eintragung auf den Eingang einer E-Mail auf das lokale Ziel reagieren soll. Senderabhängige Maps verfahren nach dem gleichen Prinzip.<br/>
  Das lokale Ziel wird bei Fehlzustellungen an ein BCC-Ziel nicht informiert.";
$lang['mailbox']['address_rewriting'] = 'Adressumschreibung';

$lang['mailbox']['recipient_maps'] = 'Empfängerumschreibungen';
$lang['mailbox']['recipient_map'] = 'Empfängerumschreibung';
$lang['mailbox']['recipient_map_info'] = 'Empfängerumschreibung ersetzen den Empfänger einer E-Mail vor dem Versand.';
$lang['mailbox']['recipient_map_old_info'] = 'Der originale Empfänger muss eine E-Mail-Adresse oder ein Domainname sein.';
$lang['mailbox']['recipient_map_new_info'] = 'Der neue Empfänger muss eine E-Mail-Adresse sein.';
$lang['mailbox']['recipient_map_old'] = 'Original Empfänger';
$lang['mailbox']['recipient_map_new'] = 'Neuer Empfänger';
$lang['mailbox']['add_recipient_map_entry'] = 'Empfängerumschreibung hinzufügen';
$lang['danger']['invalid_recipient_map_new'] = 'Neuer Empfänger "%s" ist ungültig';
$lang['danger']['invalid_recipient_map_old'] = 'Originaler Empfänger "%s" ist ungültig';
$lang['danger']['recipient_map_entry_exists'] = 'Eine Empfängerumschreibung für Objekt "%s" existiert bereits';
$lang['success']['recipient_map_entry_saved'] = 'Empfängerumschreibung für Objekt "%s" wurde gespeichert';
$lang['success']['recipient_map_entry_deleted'] = 'Empfängerumschreibung mit der ID %s wurde gelöscht';
$lang['danger']['tls_policy_map_entry_exists'] = 'Eine TLS-Richtlinie "%s" existiert bereits';
$lang['success']['tls_policy_map_entry_saved'] = 'TLS-Richtlinieneintrag "%s" wurde gespeichert';
$lang['success']['tls_policy_map_entry_deleted'] = 'TLS-Richtlinie mit der ID %s wurde gelöscht';
$lang['mailbox']['add_tls_policy_map'] = "TLS-Richtlinieneintrag hinzufügen";
$lang['danger']['tls_policy_map_parameter_invalid'] = "Parameter ist ungültig";
$lang['danger']['temp_error'] = "Temporärer Fehler";

$lang['admin']['sys_mails'] = 'System-E-Mails';
$lang['admin']['subject'] = 'Betreff';
$lang['admin']['from'] = 'Absender';
$lang['admin']['include_exclude'] = 'Ein- und Ausschlüsse';
$lang['admin']['include_exclude_info'] = 'Ohne Auswahl werden alle Mailboxen adressiert.';
$lang['admin']['excludes'] = 'Diese Empfänger ausschließen';
$lang['admin']['includes'] = 'Diese Empfänger einschließen';
$lang['admin']['text'] = 'Text';
$lang['admin']['activate_send'] = 'Senden-Button freischalten';
$lang['admin']['send'] = 'Senden';

$lang['warning']['ip_invalid'] = 'Ungültige IP übersprungen: %s';
$lang['danger']['text_empty'] = 'Text darf nicht leer sein';
$lang['danger']['subject_empty'] = 'Betreff darf nicht leer sein';
$lang['danger']['from_invalid'] = 'Die Absenderadresse muss eine gültige E-Mail-Adresse sein';
$lang['danger']['network_host_invalid'] = 'Netzwerk oder Host ungültig: %s';

$lang['add']['mailbox_quota_def'] = 'Standard-Quota einer Mailbox';
$lang['edit']['mailbox_quota_def'] = 'Standard-Quota einer Mailbox';
$lang['danger']['mailbox_defquota_exceeds_mailbox_maxquota'] = 'Standard-Quota überschreitet das Limit der maximal erlaubten Größe einer Mailbox';
$lang['danger']['defquota_empty'] = 'Standard-Quota darf nicht 0 sein';
$lang['mailbox']['mailbox_defquota'] = 'Standard-Quota';

$lang['admin']['api_info'] = 'Das API befindet sich noch in Entwicklung, eine Dokumentation ist ausstehend.';

$lang['admin']['guid_and_license'] = 'GUID & Lizenz';
$lang['admin']['guid'] = 'GUID - Eindeutige Instanz-ID';
$lang['admin']['license_info'] = 'Eine Lizenz ist nicht erforderlich, hilft jedoch der Entwicklung mailcows.<br><a href="https://www.servercow.de/mailcow#sal" target="_blank" alt="SAL Bestellung">Hier kann die mailcow GUID registriert werden.</a> Alternativ ist <a href="https://www.servercow.de/mailcow#support" target="_blank" alt="SAL Bestellung">die Bestellung von Support-Paketen möglich</a>.';
$lang['admin']['validate_license_now'] = 'GUID erneut verifizieren';
$lang['admin']['customer_id'] = 'Kunde';
$lang['admin']['service_id'] = 'Service';

$lang['admin']['lookup_mx'] = 'Ziel gegen MX prüfen (etwa .outlook.com, um alle Ziele mit MX *.outlook.com zu routen)';
$lang['edit']['mbox_rl_info'] = 'Dieses Limit wird auf den SASL Loginnamen angewendet und betrifft daher alle Absenderadressen, die der eingeloggte Benutzer verwendet. Bei Mailbox Ratelimit überwiegt ein Domain-weites Ratelimit.';

$lang['add']['relayhost_wrapped_tls_info'] = 'Bitte <b>keine</b> TLS-wrapped Ports verwenden (etwa SMTPS via Port 465/tcp).<br>
Der Transport wird stattdessen STARTTLS anfordern, um TLS zu verwenden. TLS kann unter "TLS Policy Maps" erzwungen werden.';

$lang['admin']['transport_dest_format'] = 'Syntax: example.org, .example.org, *, box@example.org (mehrere Werte getrennt durch Komma einzugeben)';

$lang['mailbox']['alias_domain_backupmx'] = 'Alias-Domain für Relay-Domain inaktiv';

$lang['danger']['extra_acl_invalid'] = 'Externe Absenderadresse "%s" ist ungültig';
$lang['danger']['extra_acl_invalid_domain'] = 'Externe Absenderadresse "%s" verwendet eine ungültige Domain';
