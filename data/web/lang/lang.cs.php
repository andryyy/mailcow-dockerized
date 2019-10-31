<?php
/*
 * Czech language file for mailcow
 *
 * Author: radek@fastlinux.eu (www.fastlinux.eu)
 * Author: filip@hajny.net
 *
 */

$lang['header']['apps'] = 'Aplikace';
$lang['footer']['loading'] = "Prosím čekejte...";
$lang['header']['restart_sogo'] = 'Restartovat SOGo';
$lang['header']['restart_netfilter'] = 'Restartovat netfilter';
$lang['footer']['restart_container'] = 'Restartovat kontejner';
$lang['footer']['restart_now'] = 'Restartovat nyní';
$lang['footer']['restarting_container'] = 'Restartuje se kontejner, může to chvilku trvat...';
$lang['footer']['restart_container_info'] = '<b>Důležité:</b> Šetrný restart může chvíli trvat, prosím čekejte...';

$lang['footer']['confirm_delete'] = 'Potvdit smazání';
$lang['footer']['delete_these_items'] = 'Prosím potvrďte změny objektu id:';
$lang['footer']['delete_now'] = 'Smazat';
$lang['footer']['cancel'] = 'Zrušit';

$lang['footer']['hibp_nok'] = 'Nalezeno! Toto je potenciálně nebezpečné heslo!';
$lang['footer']['hibp_ok'] = 'Nebyla nalezena žádná shoda.';

$lang['danger']['transport_dest_exists'] = 'Transportní cíl "%s" již existuje';
$lang['danger']['unlimited_quota_acl'] = "Neomeznou kvótu nepovoluje seznam oprávnění ACL";
$lang['danger']['mysql_error'] = "Chyba MySQL: %s";
$lang['danger']['redis_error'] = "Chyba Redis: %s";
$lang['danger']['unknown_tfa_method'] = "Neznámá 2FA metoda";
$lang['danger']['totp_verification_failed'] = "TOTP ověření selhalo";
$lang['success']['verified_totp_login'] = "TOTP přihlášení ověřeno";
$lang['danger']['u2f_verification_failed'] = "U2F ověření selhalo: %s";
$lang['success']['verified_u2f_login'] = "U2F přihlášení ověřeno";
$lang['success']['verified_yotp_login'] = "Yubico OTP přihlášení ověřeno";
$lang['danger']['yotp_verification_failed'] = "Yubico OTP ověření selhalo: %s";
$lang['danger']['ip_list_empty'] = "Seznam povolených IP nesmí být prázdný";
$lang['danger']['invalid_destination'] = "Formát cíle je neplatný";
$lang['danger']['invalid_nexthop'] = "Formát skoku (Next hop) je neplatný";
$lang['danger']['invalid_nexthop_authenticated'] = "Skok (Next hop) již existuje s rozdílnými přihlašovacími údaji, nejdříve prosím aktualizujte existující přihlašovací údaje tohoto skoku.";
$lang['danger']['next_hop_interferes'] = "%s koliduje se skokem %s";
$lang['danger']['next_hop_interferes_any'] = "Existující skok koliduje s %s";
$lang['danger']['rspamd_ui_pw_length'] = "Heslo pro Rspamd UI musí mít alespoň 6 znaků";
$lang['success']['rspamd_ui_pw_set'] = "Heslo k Rspamd UI nastaveno";
$lang['success']['queue_command_success'] = "Příkaz pro frontu úspěšně dokončen";
$lang['danger']['unknown'] = "Došlo k neznámé chybě";
$lang['danger']['malformed_username'] = "Neplatné uživatelské jméno";
$lang['info']['awaiting_tfa_confirmation'] = "Čeká se na potvrzení 2FA";
$lang['info']['session_expires'] = "Relace vyprší za 15 vteřin";
$lang['success']['logged_in_as'] = "Přihlášen jako %s";
$lang['danger']['login_failed'] = "Přihlášení selhalo";
$lang['danger']['set_acl_failed'] = "Chyba při nastavení ACL";
$lang['danger']['no_user_defined'] = "Žádný uživatel není definován";
$lang['danger']['script_empty'] = "Skript nesmí být prázdný";
$lang['danger']['sieve_error'] = "Chyba Sieve parseru: %s";
$lang['danger']['value_missing'] = "Prosím, uveďte všechny hodnoty";
$lang['danger']['filter_type'] = "Špatný typ filtru";
$lang['danger']['domain_cannot_match_hostname'] = "Doména a hostname nesmí být stejné";
$lang['warning']['domain_added_sogo_failed'] = "Doména přidána, ale selhal restart SOGo kontejneru, prosím zkontrolujte logy serveru.";
$lang['danger']['rl_timeframe'] = "Nesprávný časový rámec omezení provozu";
$lang['success']['rl_saved'] = "Omezení provozu pro objekt %s uloženo";
$lang['success']['acl_saved'] = "ACL pro objekt %s uloženo";
$lang['success']['deleted_syncjobs'] = "Smazány synchronizační úlohy: %s";
$lang['success']['deleted_syncjob'] = "Smazán synchronizační úloha ID %s";
$lang['success']['delete_filters'] = "Smazané filtry: %s";
$lang['success']['delete_filter'] = "Smazané filtry ID %s";
$lang['danger']['invalid_bcc_map_type'] = "Špatný typ BCC mapování";
$lang['danger']['bcc_empty'] = "BCC cíl nesmí být prázdný";
$lang['danger']['bcc_must_be_email'] = "BCC mapování %s není správná email adresa";
$lang['danger']['bcc_exists'] = "BCC mapování %s již existuje pro typ %s";
$lang['success']['bcc_saved'] = "Položka BCC mapování uložena";
$lang['success']['bcc_edited'] = "Položka BCC mapování %s upravena";
$lang['success']['bcc_deleted'] = "Smazané položky BCC mapování: %s";
$lang['danger']['private_key_error'] = "Chyba soukroméhop klíče: %s";
$lang['danger']['map_content_empty'] = "Obsah mapování nesmí být prázdný";
$lang['success']['settings_map_added'] = "Přidána položka mapování nastavení";
$lang['danger']['settings_map_invalid'] = "Položka mapování nastavení ID %s je špatná";
$lang['success']['settings_map_removed'] = "Položka mapování nastavení: %s smazána";
$lang['danger']['invalid_host'] = "Zadán neplatný hostitel: %s";
$lang['danger']['relayhost_invalid'] = "Položky %s je neplatná";
$lang['success']['saved_settings'] = "Nastavení uložena";
$lang['success']['db_init_complete'] = "Inicializace databáze dokončena";

$lang['warning']['session_ua'] = "Token formuláře není platný: User-Agent validation error";
$lang['warning']['session_token'] = "Token formuláře není platný: Token mismatch";

$lang['danger']['dkim_domain_or_sel_invalid'] = "DKIM nebo selektor doménu je neplatný: %s";
$lang['success']['dkim_removed'] = "DKIM klíč %s odebrán";
$lang['success']['dkim_added'] = "DKIM klíč %s uložen";
$lang['success']['dkim_duplicated'] = "DKIM klíč domény %s zkopírován do %s";
$lang['danger']['access_denied'] = "Přístup odepřen nebo jsou neplatná data ve formuláři";
$lang['danger']['domain_invalid'] = "Název domény je prázdný nebo neplatný";
$lang['danger']['mailbox_quota_exceeds_domain_quota'] = "Max. kvóta překročila limit domény";
$lang['danger']['object_is_not_numeric'] = "Hodnota %s není číslo";
$lang['success']['domain_added'] = "Přidána doména %s";
$lang['success']['items_deleted'] = "Položka %s úspěšně smazána";
$lang['success']['item_deleted'] = "Položka %s úspěšně smazána";
$lang['danger']['alias_empty'] = "Adresa aliasu nesmí být prázdná";
$lang['danger']['last_key'] = 'Nelze smazat poslední klíč';
$lang['danger']['goto_empty'] = "Cílová adresa nesmí být prázdná";
$lang['danger']['policy_list_from_exists'] = "Záznam s daným jménem již existuje";
$lang['danger']['policy_list_from_invalid'] = "Záznam má neplatný formát";
$lang['danger']['alias_invalid'] = "Adresa aliasu %s je neplatná";
$lang['danger']['goto_invalid'] = "Cílová adresa %s je neplatná";
$lang['danger']['alias_domain_invalid'] = "Doménový alias %s je neplatný";
$lang['danger']['target_domain_invalid'] = "Cílová doména %s je neplatná";
$lang['danger']['object_exists'] = "Objekt %s již existuje";
$lang['danger']['domain_exists'] = "Doména %s již existuje";
$lang['danger']['alias_goto_identical'] = "Alias a cílová adresa nesmějí být stejné";
$lang['danger']['aliasd_targetd_identical'] = "Doménový alias nesmí být stejný jako cílová doména: %s";
$lang['danger']['maxquota_empty'] = 'Max. kvóta poštovní schránky nesmí být 0.';
$lang['success']['alias_added'] = "Byl přidán alias %s";
$lang['success']['alias_modified'] = "Změny aliasu %s uloženy";
$lang['success']['mailbox_modified'] = "Změny poštovní schránky %s uloženy";
$lang['success']['resource_modified'] = "Změny poštovní schránky %s uloženy";
$lang['success']['object_modified'] = "Změny objektu %s uloženy";
$lang['success']['f2b_modified'] = "Změny parametrů Fail2ban uloženy";
$lang['danger']['targetd_not_found'] = "Cílová doména %s nenalezena";
$lang['danger']['targetd_relay_domain'] = "Cílová doména %s je předávaná";
$lang['success']['aliasd_added'] = "Přidán doménový alias %s";
$lang['success']['aliasd_modified'] = "Změny aliasu domény %s uloženy";
$lang['success']['domain_modified'] = "Změny domény %s uloženy";
$lang['success']['domain_admin_modified'] = "Změny správce domény %s uloženy";
$lang['success']['domain_admin_added'] = "Správce domény %s přidán";
$lang['success']['admin_added'] = "Správce %s přidán";
$lang['success']['admin_modified'] = "Změny správce uloženy";
$lang['success']['admin_api_modified'] = "Změna API uložena";
$lang['success']['license_modified'] = "Změny licence uloženy";
$lang['danger']['username_invalid'] = "Uživatelské jméno %s nelze použít";
$lang['danger']['password_mismatch'] = "Potvrzení hesla nesouhlasí";
$lang['danger']['password_complexity'] = "Heslo nesplňuje pravidla";
$lang['danger']['password_empty'] = "Heslo nesmí být prázdné";
$lang['danger']['login_failed'] = "Přihlášení selhalo";
$lang['danger']['mailbox_invalid'] = "Název poštovní chránky je neplatný";
$lang['danger']['description_invalid'] = 'Popis zdroje %s je neplatný';
$lang['danger']['resource_invalid'] = "Název zdroje %s je neplatný";
$lang['danger']['is_alias'] = "%s je již známa jako adresa aliasu";
$lang['danger']['is_alias_or_mailbox'] = "%s je již známa jako adresa aliasu, poštovní schránky nebo aliasu rozvedeného z aliasu domény.";
$lang['danger']['is_spam_alias'] = "%s je již známa jako adresa spamového aliasu";
$lang['danger']['quota_not_0_not_numeric'] = "Kvóta musí být číslo >= 0";
$lang['danger']['domain_not_found'] = 'Doména %s nebyla nalezena';
$lang['danger']['max_mailbox_exceeded'] = "Max. počet poštovních schránek překročen (%d z %d)";
$lang['danger']['max_alias_exceeded'] = 'Překročeno max. množství aliasů';
$lang['danger']['mailbox_quota_exceeded'] = "Kvóta překročila limit domény (max. %d MiB)";
$lang['danger']['mailbox_quota_left_exceeded'] = "Není dost volného místa (zbývá: %d MiB)";
$lang['success']['mailbox_added'] = "Poštovní schránka %s přidána";
$lang['success']['resource_added'] = "Zdroj %s přidán";
$lang['success']['domain_removed'] = "Doména %s odebrána";
$lang['success']['alias_removed'] = "Alias %s odebrán";
$lang['success']['alias_domain_removed'] = "Doménový alias %s odebrán";
$lang['success']['domain_admin_removed'] = "Správce domény %s odebrán";
$lang['success']['admin_removed'] = "Správce %s odebrán";
$lang['success']['mailbox_removed'] = "Poštovní schránka %s  odebrána";
$lang['success']['eas_reset'] = "ActiveSync zařízení uživatele %s vyresetováno";
$lang['success']['sogo_profile_reset'] = "SOGo profil uživatele %s vyresetován";
$lang['success']['resource_removed'] = "Zdroj %s odebrán";
$lang['warning']['cannot_delete_self'] = "Nelze smazat právě přihlášeného uživatele";
$lang['warning']['no_active_admin'] = "Nelze deaktivovat posledního aktivního správce";
$lang['danger']['max_quota_in_use'] = "Kvóta poštovní schránky musí být větší nebo rovna %d MiB";
$lang['danger']['domain_quota_m_in_use'] = "Kvóta domény musí být větší nebo rovna %s MiB";
$lang['danger']['mailboxes_in_use'] = "Max. počet poštovních schránek musí být větší nebo roven %d";
$lang['danger']['aliases_in_use'] = "Max. počet aliasů musí být větší nebo roven %d";
$lang['danger']['sender_acl_invalid'] = "Hodnota ACL odesílatele %s je neplatná";
$lang['danger']['domain_not_empty'] = "Nelze odebrat doménu, která není prázdná";
$lang['danger']['validity_missing'] = 'Zdejte dobu platnosti';
$lang['user']['loading'] = "Načítá se...";
$lang['user']['force_pw_update'] = 'Pro přístup k groupware funkcím <b>musíte změnit heslo</b>.';
$lang['user']['active_sieve'] = "Aktivní filtr";
$lang['user']['show_sieve_filters'] = "Zobrazit aktivní sieve filtr uživatele";
$lang['user']['no_active_filter'] = "Není k dispozici žádný aktivní filtr";
$lang['user']['messages'] = "zpráv"; // "123 messages"
$lang['user']['in_use'] = "Obsazeno";
$lang['user']['user_change_fn'] = "";
$lang['user']['user_settings'] = 'Uživatelské nastavení';
$lang['user']['mailbox_details'] = 'Podrobnosti poštovní schránky';
$lang['user']['change_password'] = 'Změnit heslo';
$lang['user']['client_configuration'] = 'Zobrazit průvodce nastavením e-mailových klientů a smartphonů';
$lang['user']['new_password'] = 'Nové heslo';
$lang['user']['save_changes'] = 'Uložit změny';
$lang['user']['password_now'] = 'Současné heslo (pro potvrzení změny)';
$lang['user']['new_password_repeat'] = 'Potvrzení nového hesla (opakujte)';
$lang['user']['new_password_description'] = 'Požadavek: min. délka 6 znaků, písmena a čísla.';
$lang['user']['spam_aliases'] = 'Dočasné e-mailové aliasy';
$lang['user']['alias'] = 'Alias';
$lang['user']['shared_aliases'] = 'Sdílené aliasy';
$lang['user']['shared_aliases_desc'] = 'Na sdílené aliasy se neuplatňuje uživatelské nastavení jako filtr spamu nebo pravidla šifrování. Nastavení filtrování spamu může provádět jen správce pro celou doménu.';
$lang['user']['direct_aliases'] = 'Přímé aliasy';
$lang['user']['direct_aliases_desc'] = 'Na přímé aliasy se uplatňuje filtr spamu a nastavení pravidel TLS';
$lang['user']['is_catch_all'] = 'Catch-all pro doménu/y';
$lang['user']['aliases_also_send_as'] = 'Smí odesílat také jako uživatel';
$lang['user']['aliases_send_as_all'] = 'Nekontrolovat přístup odesílatele pro následující doménu(y) a jejich aliasy domény:';
$lang['user']['alias_create_random'] = 'Generovat náhodný alias';
$lang['user']['alias_extend_all'] = 'Prodloužit aliasy o 1 hodinu';
$lang['user']['alias_valid_until'] = 'Platný do';
$lang['user']['alias_remove_all'] = 'Odstranit všechny aliasy';
$lang['user']['alias_time_left'] = 'Zbývající čas';
$lang['user']['alias_full_date'] = 'd.m.Y, H:i:s T';
$lang['user']['alias_select_validity'] = 'Doba platnosti';
$lang['user']['sync_jobs'] = 'Synchronizační úlohy';
$lang['user']['expire_in'] = 'Vyprší za';
$lang['user']['hour'] = 'hodinu';
$lang['user']['hours'] = 'hodin';
$lang['user']['day'] = 'den';
$lang['user']['week'] = 'týden';
$lang['user']['weeks'] = 'týdny';
$lang['user']['spamfilter'] = 'Filtr spamu';
$lang['admin']['spamfilter'] = 'Filtr spamu';
$lang['user']['spamfilter_wl'] = 'Seznam povolených adres (whitelist)';
$lang['user']['spamfilter_wl_desc'] = 'Povolené emailové adresy <b>nebudou nikdy klasifikovány jako spam</b>. Lze použít zástupné znaky (*). Filtr se použije pouze na přímé aliasy (s jednou cílovou poštovní schránkou), s výjimkou aliasů typu catch-all a samotné poštovní schránky.';
$lang['user']['spamfilter_bl'] = 'Seznam zakázaných adres (blacklist)';
$lang['user']['spamfilter_bl_desc'] = 'Zakázané emailové adresy <b>budou vždy klasifikovány jako spam a odmítnuty</b>. Lze použít zástupné znaky (*). Filtr se použije pouze na přímé aliasy (s jednou cílovou poštovní schránkou), s výjimkou aliasů typu catch-all a samotné poštovní schránky.';
$lang['user']['spamfilter_behavior'] = 'Hodnocení';
$lang['user']['spamfilter_table_rule'] = 'Pravidlo';
$lang['user']['spamfilter_table_action'] = 'Akce';
$lang['user']['spamfilter_table_empty'] = 'Žádná data k zobrazení';
$lang['user']['spamfilter_table_remove'] = 'smazat';
$lang['user']['spamfilter_table_add'] = 'Přidat položku';
$lang['user']['spamfilter_green'] = 'Zelená: tato zpráva není spam';
$lang['user']['spamfilter_yellow'] = 'Žlutá: tato zpráva může být spam, bude označena jako spam a přesunuta do složky nevyžádané pošty';
$lang['user']['spamfilter_red'] = 'Červená: Tato zpráva je spam a server ji odmítne';
$lang['user']['spamfilter_default_score'] = 'Výchozí hodnoty:';
$lang['user']['spamfilter_hint'] = 'První hodnota představuje "nízké spam skóre" a druhá "vysoké spam skóre".';
$lang['user']['spamfilter_table_domain_policy'] = "n/a (doménová politika)";
$lang['user']['waiting'] = "Čekání";
$lang['user']['status'] = "Stav";
$lang['user']['running'] = "Běží";

$lang['user']['tls_policy_warning'] = '<strong>Varování:</strong> Pokud se rozhodnete vynutit šifrovaný přenos pošty, může dojít ke ztrátě e-mailů.<br>Zprávy, které nesplňují tuto politiku, budou poštovním systémem odmítnuty.<br>Tato volba ovlivňuje primární e-mailovou adresu (přihlašovací jméno), všechny adresy odvozené z doménových aliasů i aliasy, jež mají tuto poštovní chránku jako cíl.';
$lang['user']['tls_policy'] = 'Politika šifrování';
$lang['user']['tls_enforce_in'] = 'Vynutit TLS pro příchozí poštu ';
$lang['user']['tls_enforce_out'] = 'Vynutit TLS pro odchozí poštu';
$lang['user']['no_record'] = 'Žádný záznam';


$lang['user']['tag_handling'] = 'Zacházení s označkovanou poštou';
$lang['user']['tag_in_subfolder'] = 'V podsložce';
$lang['user']['tag_in_subject'] = 'V předmětu';
$lang['user']['tag_in_none'] = 'Nedělat nic';
$lang['user']['tag_help_explain'] = 'V podsložce: v doručené poště bude vytvořena nová podsložka pojmenovaná po značce zprávy ("INBOX / Facebook").<br>
V předmětu: název značky bude přidáván k předmětu mailu, například: "[Facebook] Moje zprávy".';
$lang['user']['tag_help_example'] = 'Příklad e-mailové adresy se značkou: me<b>+Facebook</b>@example.org';

$lang['user']['eas_reset'] = 'Smazat mezipaměť zařízení ActiveSync';
$lang['user']['eas_reset_now'] = 'Smazat';
$lang['user']['eas_reset_help'] = 'Obnovení mezipaměti zařízení pomůže zpravidla obnovit poškozený profil služby ActiveSync.<br><b>Upozornění:</b> Všechna data budou opětovně stažena!';

$lang['user']['sogo_profile_reset'] = 'Resetovat profil SOGo';
$lang['user']['sogo_profile_reset_now'] = 'Resetovat profil';
$lang['user']['sogo_profile_reset_help'] = 'Tato volba odstraní uživatelský profil SOGo a <b>nenávratně vymaže všechna data</b>.';

$lang['user']['encryption'] = 'Šifrování';
$lang['user']['username'] = 'Uživatelské jméno';
$lang['user']['last_run'] = 'Naposledy spuštěno';
$lang['user']['excludes'] = 'Vyloučené';
$lang['user']['interval'] = 'Interval';
$lang['user']['active'] = 'Aktivní';
$lang['user']['action'] = 'Akce';
$lang['user']['edit'] = 'Upravit';
$lang['user']['remove'] = 'Smazat';
$lang['user']['create_syncjob'] = 'Vytvořit novou synchronizační úlohu';

$lang['start']['mailcow_apps_detail'] = 'Použijte aplikace pro přístup k e-mailům, kalendáři, kontaktům atd.';
$lang['start']['mailcow_panel_detail'] = '<b>Správci domén</b> mohou vytvářet, upravovat nebo mazat schránky a aliasy, upravovat parametry domén a zobrazovat další informace o svých přidělených doménách.<br>
<b>Uživatelé</b> mohou vytvářet dočasné aliasy (spam aliases), měnit svá hesla a nastavovat filtr spamu.';
$lang['start']['imap_smtp_server_auth_info'] = 'Použijte celou e-mailovou adresu a zvolte způsob ověření PLAIN.<br>
Přihlašovací údaje budou zašifrovány na straně serveru.';
$lang['start']['help'] = 'Zobrazit/skrýt panel nápovědy';
$lang['header']['mailcow_settings'] = 'Nastavení';
$lang['header']['administration'] = 'Hlavní nastavení';
$lang['header']['mailboxes'] = 'Nastavení pošty';
$lang['header']['user_settings'] = 'Uživatelská nastavení';
$lang['header']['quarantine'] = "Karanténa";
$lang['header']['debug'] = "Systémové informace";
$lang['quarantine']['disabled_by_config'] = "Funkce karanténa je momentálně vypnuta v nastavení systému.";
$lang['mailbox']['tls_policy_maps'] = 'Mapy TLS pravidel';
$lang['mailbox']['tls_policy_maps_long'] = 'Přepisování pravidel odchozího TLS';
$lang['mailbox']['tls_policy_maps_info'] = 'Tato mapa přepisuje pravidla odchozích TLS nezávisle na TLS nastavení uživatele.<br>
  Pro více informací prosím prostudujte <a href="http://www.postfix.org/postconf.5.html#smtp_tls_policy_maps" target="_blank">dokumentaci k "smtp_tls_policy_maps"</a>.';
$lang['mailbox']['tls_enforce_in'] = 'Vynutit TLS pro příchozí';
$lang['mailbox']['tls_enforce_out'] = 'Vynutit TLS pro odchozí';
$lang['mailbox']['tls_map_dest'] = 'Cíl';
$lang['mailbox']['tls_map_dest_info'] = 'Příklady: example.org, .example.org, [mail.example.org]:25';
$lang['mailbox']['tls_map_policy'] = 'Pravidlo';
$lang['mailbox']['tls_map_parameters'] = 'Parametry';
$lang['mailbox']['tls_map_parameters_info'] = 'Prázdné nebo parametry, například: protocols=!SSLv2 ciphers=medium exclude=3DES';
$lang['mailbox']['booking_0'] = 'Vždy volno';
$lang['mailbox']['booking_lt0'] = 'Neomezeno, ale po rezervaci se ukazuje jako obsazené';
$lang['mailbox']['booking_custom'] = 'Omezeno na pevný počet rezervací';
$lang['mailbox']['booking_0_short'] = 'Vždy volno';
$lang['mailbox']['booking_lt0_short'] = 'Volný limit';
$lang['mailbox']['booking_custom_short'] = 'Pevný limit';
$lang['mailbox']['domain'] = 'Doména';
$lang['mailbox']['spam_aliases'] = 'Dočasný alias';
$lang['mailbox']['multiple_bookings'] = 'Vícenásobné rezervace';
$lang['mailbox']['kind'] = 'Druh';
$lang['mailbox']['description'] = 'Popis';
$lang['mailbox']['alias'] = 'Alias';
$lang['mailbox']['aliases'] = 'Aliasy';
$lang['mailbox']['domains'] = 'Domény';
$lang['admin']['domain'] = 'Doména';
$lang['admin']['domain_s'] = 'Doména/y';
$lang['mailbox']['mailboxes'] = 'Poštovní schránky';
$lang['mailbox']['mailbox'] = 'Poštovní schránka';
$lang['mailbox']['resources'] = 'Zdroje';
$lang['mailbox']['mailbox_quota'] = 'Max. velikost schránky';
$lang['mailbox']['domain_quota'] = 'Kvóta';
$lang['mailbox']['active'] = 'Aktivní';
$lang['mailbox']['action'] = 'Akce';
$lang['mailbox']['backup_mx'] = 'Záložní MX';
$lang['mailbox']['domain_aliases'] = 'Doménové aliasy';
$lang['mailbox']['target_domain'] = 'Cílová doména';
$lang['mailbox']['target_address'] = 'Cílová adresa';
$lang['mailbox']['username'] = 'Uživatelské jméno';
$lang['mailbox']['fname'] = 'Celé jméno';
$lang['mailbox']['filter_table'] = 'Tabulka filtrů';
$lang['mailbox']['yes'] = '&#10003;';
$lang['mailbox']['no'] = '&#10005;';
$lang['mailbox']['in_use'] = 'Obsazeno (%)';
$lang['mailbox']['msg_num'] = 'Počet zpráv';
$lang['mailbox']['remove'] = 'Smazat';
$lang['mailbox']['edit'] = 'Upravit';
$lang['mailbox']['no_record'] = 'Žádný záznam pro objekt %s';
$lang['mailbox']['no_record_single'] = 'Žádný záznam';
$lang['mailbox']['add_domain'] = 'Přidat doménu';
$lang['mailbox']['add_domain_alias'] = 'Přidat doménový alias';
$lang['mailbox']['add_mailbox'] = 'Přidat poštovní schránku';
$lang['mailbox']['add_resource'] = 'Přidat zdroj';
$lang['mailbox']['add_alias'] = 'Přidat alias';
$lang['mailbox']['add_domain_record_first'] = 'Prosím nejdříve vytvořte doménu';
$lang['mailbox']['empty'] = 'Žádné výsledky';
$lang['mailbox']['toggle_all'] = 'Označit vše';
$lang['mailbox']['quick_actions'] = 'Akce';
$lang['mailbox']['activate'] = 'Zapnout';
$lang['mailbox']['deactivate'] = 'Vypnout';
$lang['mailbox']['owner'] = 'Vlastník';
$lang['mailbox']['mins_interval'] = 'Interval (min)';
$lang['mailbox']['last_run'] = 'Naposledy spuštěno';
$lang['mailbox']['excludes'] = 'Vyloučené';
$lang['mailbox']['last_run_reset'] = 'Plánovat další';
$lang['mailbox']['sieve_info'] = 'Můžete uložit více filtrů pro každého uživatele, ale současně může být aktivní pouze jeden prefilter a jeden postfilter.<br>
Každý filtr bude proveden v daném pořadí. Ani chyba při vykonávání skriptu nebo snaha o pozdržení nezastaví vykonání dalších skriptů.<br>
<a href="https://github.com/mailcow/mailcow-dockerized/blob/master/data/conf/dovecot/global_sieve_before" target="_blank">Global sieve prefilter</a> → Prefilter → Uživatelské skripty → Postfilter → <a href="https://github.com/mailcow/mailcow-dockerized/blob/master/data/conf/dovecot/global_sieve_after" target="_blank">Global sieve postfilter</a>';
$lang['info']['no_action'] = 'Žádná použitelná akce';


$lang['edit']['sogo_visible'] = 'Alias dostupný v SOGo';
$lang['edit']['sogo_visible_info'] = 'Tato volba určuje objekty, jež lze zobrazit v SOGo (sdílené nebo nesdílené aliasy, jež ukazuje alespoň na jednu schránku).';
$lang['mailbox']['sogo_visible'] = 'Alias dostupný v SOGo';
$lang['mailbox']['sogo_visible_y'] = 'Zobrazit alias v SOGo';
$lang['mailbox']['sogo_visible_n'] = 'Skrýt alias v SOGo';
$lang['edit']['syncjob'] = 'Upravit synchronizační úlohu';
$lang['edit']['hostname'] = 'Jméno hostitele';
$lang['edit']['encryption'] = 'Šifrování';
$lang['edit']['maxage'] = 'Maximální stáří stahovaných zpráv, ve dnech<br><small>(0 = ignorovat stáří)</small>';
$lang['edit']['maxbytespersecond'] = 'Max. bajtů za sekundu <br><small>(0 = neomezeno)</small>';
$lang['edit']['automap'] = 'Pokusit se automaticky mapovat složky ("Sent items", "Sent" => "Sent" atd.)';
$lang['edit']['skipcrossduplicates'] = 'Přeskočit duplicitní zprávy ("první přijde, první mele")';
$lang['add']['automap'] = 'Pokusit se automaticky mapovat složky ("Sent items", "Sent" => "Sent" atd.)';
$lang['add']['skipcrossduplicates'] = 'Přeskočit duplicitní zprávy ("první přijde, první mele")';
$lang['edit']['subfolder2'] = 'Synchronizace do podsložky v cílovém umístění<br><small>(prázdné = nepoužívat podsložku)</small>';
$lang['edit']['mins_interval'] = 'Interval (min)';
$lang['edit']['exclude'] = 'Vyloučit objekty (regex)';
$lang['edit']['save'] = 'Uložit změny';
$lang['edit']['username'] = 'Uživatelské jméno';
$lang['edit']['max_mailboxes'] = 'Max. počet poštovních schránek';
$lang['edit']['title'] = 'Úprava objektu';
$lang['edit']['target_address'] = 'Cílová adresa/y<br /> <small>(oddělte čárkou)</small>';
$lang['edit']['active'] = 'Aktivní';
$lang['edit']['gal'] = 'Globální seznam adres';
$lang['add']['gal'] = 'Globální seznam adres';
$lang['edit']['gal_info'] = 'Globální seznam adres obsahuje všechny objekty v doméně a uživatel jej nemůže měnit. Pokud je vypnut, budou v SOGo chybět informace o obsazenosti! <b>Při změně nutno restartovat SOGo.</b>';
$lang['add']['gal_info'] = 'Globální seznam adres obsahuje všechny objekty v doméně a uživatel jej nemůže měnit. Pokud je vypnut, budou v SOGo chybět informace o obsazenosti! <b>Při změně nutno restartovat SOGo.</b>';
$lang['edit']['force_pw_update'] = 'Vynutit změnu hesla při příštím přihlášení';
$lang['edit']['force_pw_update_info'] = 'Uživatel se bude moci přihlásit pouze do administrace účtu.';
$lang['edit']['sogo_access'] = 'Udělit přístup k SOGo';
$lang['edit']['sogo_access_info'] = 'Toto nastavení neovlivňuje přístup k ostatním službám, ani nezmění existující profil uživatele SOGo.';
$lang['edit']['target_domain'] = 'Cílová doména';
$lang['edit']['password'] = 'Heslo';
$lang['edit']['password_repeat'] = 'Potvrzení nového hesla (opakujte)';
$lang['edit']['domain_admin'] = 'Upravit správce domény';
$lang['edit']['domain'] = 'Úprava domény';
$lang['edit']['edit_alias_domain'] = 'Upravit doménový alias';
$lang['edit']['domains'] = 'Domény';
$lang['edit']['alias'] = 'Upravit alias';
$lang['edit']['mailbox'] = 'Úprava poštovní schránky';
$lang['edit']['description'] = 'Popis';
$lang['edit']['max_aliases'] = 'Max. počet aliasů';
$lang['edit']['max_quota'] = 'Max. kvóta poštovní schránky (MiB)';
$lang['edit']['domain_quota'] = 'Kvóta domény';
$lang['edit']['backup_mx_options'] = 'Možnosti záložního MX';
$lang['edit']['relay_domain'] = 'Předávání domény';
$lang['edit']['relay_all'] = 'Předávání všech příjemců';
$lang['edit']['relay_all_info'] = '<small>Pokud se rozhodnete <b>nepředávat</b> všechny příjemce, musíte přidat prázdnou poštovní schránku pro každého příjemce, který se má předávat.</small>';
$lang['edit']['full_name'] = 'Celé jméno';
$lang['edit']['quota_mb'] = 'Kvóta (MiB)';
$lang['edit']['sender_acl'] = 'Povolit odesílání jako';
$lang['edit']['sender_acl_disabled'] = '↳ <span class="label label-danger">Kontrola odesílatele vypnuta</span>';
$lang['user']['sender_acl_disabled'] = '<span class="label label-danger">Kontrola odesílatele vypnuta</span>';
$lang['edit']['previous'] = 'Předchozí stránka';
$lang['edit']['unchanged_if_empty'] = 'Pokud se nemění, ponechte prázdné';
$lang['edit']['dont_check_sender_acl'] = "Vypnout kontrolu odesílatele pro doménu %s (+ doménové aliasy)";
$lang['edit']['multiple_bookings'] = 'Vícenásobné rezervace';
$lang['edit']['kind'] = 'Druh';
$lang['edit']['resource'] = 'Zdroj';
$lang['edit']['relayhost'] = 'Předávání podle odesílatele';
$lang['edit']['public_comment'] = 'Veřejný komentář';
$lang['mailbox']['public_comment'] = 'Veřejný komentář';
$lang['edit']['private_comment'] = 'Soukromý komentář';
$lang['mailbox']['private_comment'] = 'Soukromý komentář';
$lang['edit']['comment_info'] = 'Soukromý komentář se nezobrazí uživateli; veřejný komentář se zobrazí jako nápověda při zastavení se kurzorem v přehledu uživatelů';
$lang['add']['public_comment'] = 'Veřejný komentář';
$lang['add']['private_comment'] = 'Soukromý komentář';
$lang['add']['comment_info'] = 'Soukromý komentář se nezobrazí uživateli; veřejný komentář se zobrazí jako nápověda při zastavení se kurzorem v přehledu uživatelů';
$lang['acl']['spam_alias'] = 'Dočasné aliasy';
$lang['acl']['tls_policy'] = 'Pravidla TLS';
$lang['acl']['spam_score'] = 'Skóre spamu';
$lang['acl']['spam_policy'] = 'Blacklist/Whitelist';
$lang['acl']['delimiter_action'] = 'Oddělovač akce';
$lang['acl']['syncjobs'] = 'Synchronizační úlohy';
$lang['acl']['eas_reset'] = 'Resetování EAS zařízení';
$lang['acl']['sogo_profile_reset'] = 'Resetování profilu SOGo';
$lang['acl']['quarantine'] = 'Karanténa';
$lang['acl']['quarantine_notification'] = 'Upozornění z karantény';
$lang['acl']['quarantine_attachments'] = 'Přílohy v karanténě';
$lang['acl']['alias_domains'] = 'Přidat doménové aliasy';
$lang['acl']['login_as'] = 'Přihlásit se jako uživatel poštovní schránky';
$lang['acl']['bcc_maps'] = 'BCC mapy';
$lang['acl']['filters'] = 'Filtry';
$lang['acl']['ratelimit'] = 'Omezení provozu';
$lang['acl']['recipient_maps'] = 'Mapy příjemců';
$lang['acl']['unlimited_quota'] = 'Neomezené kvóty pro poštovní schránky';
$lang['acl']['extend_sender_acl'] = 'Povolit rozšíření ACL odesílatele o externí adresy';
$lang['acl']['prohibited'] = 'Zakázáno z důvodu ACL';
$lang['acl']['sogo_access'] = 'Povolit správu přístupu do SOGo';

$lang['edit']['extended_sender_acl'] = 'Externí adresy odesílatele';
$lang['edit']['extended_sender_acl_info'] = 'Je dobré importovat DKIM klíč domény, pokud existuje.<br>
  Nezapomeňte přidat tento server do příslušného záznamu SPF TXT.<br>
  Je-li na tomto serveru vytvořena doména nebo doménový alias, který se shoduje s externí adresou, je tato externí adresa smazána.<br>
  Použije formát @domain.tld, chcete-li odesílat jako *@domain.tld.';
$lang['edit']['sender_acl_info'] = 'Má-li uživatel schránky A dovoleno odesílat jako uživatel schránky B, nezobrazuje se adresa odesílatele B v seznamu "Od" v SOGo automaticky.<br>
  Uživatel schránky A musí nejdříve v SOGo vytvořit pověření, jež umožní uživateli B vybrat svou adresu jako odesílatele. Tento mechanismus neplatí pro aliasy.';

$lang['mailbox']['quarantine_notification'] = 'Upozornění z karantény';
$lang['mailbox']['never'] = 'Nikdy';
$lang['mailbox']['hourly'] = 'Každou hodinu';
$lang['mailbox']['daily'] = 'Každý den';
$lang['mailbox']['weekly'] = 'Každý týden';
$lang['user']['quarantine_notification'] = 'Upozornění z karantény';
$lang['user']['never'] = 'Nikdy';
$lang['user']['hourly'] = 'Každou hodinu';
$lang['user']['daily'] = 'Každý den';
$lang['user']['weekly'] = 'Každý týden';
$lang['user']['quarantine_notification_info'] = 'Jakmile se upozornění odešle, budou příslušné položky vyznačeny jako "upozorněné" a nebude pro ně odesláno žádné další upozornění.';

$lang['add']['generate'] = 'generovat';
$lang['add']['syncjob'] = 'Přidat synchronizační úlohu';
$lang['add']['syncjob_hint'] = 'Upozornění: Heslo bude uloženo jako prostý text!';
$lang['add']['hostname'] = 'Jméno hostitele';
$lang['add']['destination'] = 'Cíl';
$lang['add']['nexthop'] = 'Další skok';
$lang['edit']['nexthop'] = 'Další skok';
$lang['add']['port'] = 'Port';
$lang['add']['username'] = 'Uživatelské jméno';
$lang['add']['enc_method'] = 'Způsob šifrování';
$lang['add']['mins_interval'] = 'Interval kontroly (minuty)';
$lang['add']['exclude'] = 'Vyloučené objekty (regex)';
$lang['add']['delete2duplicates'] = 'Odstranit duplicity v cílovém místě';
$lang['add']['delete1'] = 'Odstranit ze zdroje po dokončení';
$lang['add']['delete2'] = 'Smazat zprávy v cíli, které nejsou ve zdroji';
$lang['add']['custom_params'] = 'Vlastní parametry';
$lang['add']['custom_params_hint'] = 'Správně: --param=xy, špatně: --param xy';
$lang['add']['subscribeall'] = 'Odebírat všechny složky';
$lang['add']['timeout1'] = 'Časový limit pro připojení ke vzdálenému hostiteli';
$lang['add']['timeout2'] = 'Časový limit pro připojení k lokálnímu hostiteli';
$lang['edit']['timeout1'] = 'Časový limit pro připojení ke vzdálenému hostiteli';
$lang['edit']['timeout2'] = 'Časový limit pro připojení k lokálnímu hostiteli';

$lang['edit']['delete2duplicates'] = 'Odstranit duplicity v cílovém místě';
$lang['edit']['delete1'] = 'Odstranit ze zdroje po dokončení';
$lang['edit']['delete2'] = 'Smazat zprávy v cíli, které nejsou ve zdroji';

$lang['add']['domain_matches_hostname'] = 'Doména %s se shoduje s názvem hostitele';
$lang['add']['domain'] = 'Doména';
$lang['add']['active'] = 'Aktivní';
$lang['add']['multiple_bookings'] = 'Vícenásobné rezervace';
$lang['add']['description'] = 'Popis';
$lang['add']['max_aliases'] = 'Max. počet aliasů';
$lang['add']['max_mailboxes'] = 'Max. počet poštovních schránek';
$lang['add']['mailbox_quota_m'] = 'Max. kvóta poštovní schránky (MiB)';
$lang['add']['domain_quota_m'] = 'Celková kvóta domény (MiB)';
$lang['add']['backup_mx_options'] = 'Možnosti záložního MX';
$lang['add']['relay_all'] = 'Předávání všech příjemců';
$lang['add']['relay_domain'] = 'Předávání domény';
$lang['add']['relay_all_info'] = '<small>Pokud se rozhodnete <b>nepředávat</b> všechny příjemce, musíte přidat prázdnou poštovní schránku pro každého příjemce, který se má předávat.</small>';
$lang['add']['alias_address'] = 'Adresa/y aliasů';
$lang['add']['alias_address_info'] = '<small>Kompletní emailová adresa/y, nebo @example.com pro zachycení všech zpráv pro doménu (oddělené čárkami). <b>Pouze domény v systému mailcow</b>.</small>';
$lang['add']['alias_domain_info'] = '<small>Platné názvy domén (oddělené čárkami).</small>';
$lang['add']['target_address'] = 'Cílové adresy';
$lang['add']['target_address_info'] = '<small>Kompletní email adresa/y (oddělené čárkami).</small>';
$lang['add']['alias_domain'] = 'Doménový alias';
$lang['add']['select'] = 'Prosím vyberte...';
$lang['add']['target_domain'] = 'Cílová doména';
$lang['add']['kind'] = 'Druh';
$lang['add']['mailbox_username'] = 'Uživatelské jméno (levá část email adresy)';
$lang['add']['full_name'] = 'Celé jméno';
$lang['add']['quota_mb'] = 'Kvóta (MiB)';
$lang['add']['select_domain'] = 'Nejdříve vyberte doménu';
$lang['add']['password'] = 'Heslo';
$lang['add']['password_repeat'] = 'Potvrzení nového hesla (opakujte)';
$lang['add']['restart_sogo_hint'] = 'Po přidání nové domény je nutné restartovat SOGo kontejner!';
$lang['add']['goto_null'] = 'Tiše zahazovat poštu';
$lang['add']['goto_ham'] = 'Učit se jako <span class="text-success"><b>ham</b></span>';
$lang['add']['goto_spam'] = 'Učit se jako <span class="text-danger"><b>spam</b></span>';
$lang['add']['validation_success'] = 'Úspěšně ověřeno';
$lang['add']['activate_filter_warn'] = 'Pokud je zaškrtlá volba "Aktivní", budou všechny ostatní filtry deaktivovány.';
$lang['add']['validate'] = 'Ověřit';
$lang['mailbox']['add_filter'] = 'Přidat filtr';
$lang['add']['sieve_desc'] = 'Krátký popis';
$lang['edit']['sieve_desc'] = 'Krátký popis';
$lang['add']['sieve_type'] = 'Typ filtru';
$lang['edit']['sieve_type'] = 'Typ filtru';
$lang['mailbox']['set_prefilter'] = 'Označit jako pre-filtr';
$lang['mailbox']['set_postfilter'] = 'Označit jako post-filtr)';
$lang['mailbox']['filters'] = 'Filtry';
$lang['mailbox']['sync_jobs'] = 'Synchronizační úlohy';
$lang['mailbox']['inactive'] = 'Neaktivní';
$lang['edit']['validate_save'] = 'Ověřit a uložit';


$lang['login']['username'] = 'Uživatelské jméno';
$lang['login']['password'] = 'Heslo';
$lang['login']['login'] = 'Přihlásit';
$lang['login']['delayed'] = 'Přihlášení zpožděno o %s sekund.';

$lang['tfa']['tfa'] = "Dvoufaktorové ověření";
$lang['tfa']['set_tfa'] = "Nastavení způsobu dvoufaktorového ověření";
$lang['tfa']['yubi_otp'] = "Yubico OTP ověření";
$lang['tfa']['key_id'] = "Identifikátor YubiKey";
$lang['tfa']['init_u2f'] = "Probíhá inicializace, čekejte...";
$lang['tfa']['start_u2f_validation'] = "Zahájit inicializaci";
$lang['tfa']['reload_retry'] = "- (znovu načtěte stránku, opakuje-li se chyba)";
$lang['tfa']['key_id_totp'] = "Identifikátor klíče";
$lang['tfa']['error_code'] = "Kód chyby";
$lang['tfa']['api_register'] = 'mailcow používá Yubico Cloud API. Prosím získejte API klíč pro své Yubico <a href="https://upgrade.yubico.com/getapikey/" target="_blank">ZDE</a>';
$lang['tfa']['u2f'] = "U2F ověření";
$lang['tfa']['none'] = "Deaktivovat";
$lang['tfa']['delete_tfa'] = "Zakázat 2FA";
$lang['tfa']['disable_tfa'] = "Zakázat 2FA do příštího úspěšného přihlášení";
$lang['tfa']['confirm'] = "Potvrdit";
$lang['tfa']['totp'] = "Časově založené OTP (Google Authenticator, Authy apod.)";
$lang['tfa']['select'] = "Prosím vyberte...";
$lang['tfa']['waiting_usb_auth'] = "<i>Čeká se na USB zařízení...</i><br><br>Prosím stiskněte tlačítko na svém U2F USB zařízení.";
$lang['tfa']['waiting_usb_register'] = "<i>Čeká se na USB zařízení...</i><br><br>Prosím zadejte své heslo výše a potvrďte U2F registraci stiskem tlačítka na svém U2F USB zařízení.";
$lang['tfa']['scan_qr_code'] = "Prosím načtěte následující kód svou aplikací na ověření nebo zadejte kód ručně.";
$lang['tfa']['enter_qr_code'] = "Kód TOTP, pokud zařízení neumí číst QR kódy";
$lang['tfa']['confirm_totp_token'] = "Prosím potvrďte změny zadáním vygenerovaného tokenu";

$lang['admin']['rspamd-com_settings'] = '<a href="https://rspamd.com/doc/configuration/settings.html#settings-structure" target="_blank">Rspamd dokumentace</a>
  - Název nastavení bude automaticky vygenerován, viz níže uvedené předvolby.';

$lang['admin']['no_new_rows'] = 'Žádné další řádky nejsou k dispozici';
$lang['admin']['queue_manager'] = 'Správce fronty';
$lang['admin']['additional_rows'] = ' dalších řádků přidáno'; // parses to 'n additional rows were added'
$lang['admin']['private_key'] = 'Soukromý klíč';
$lang['admin']['import'] = 'Importovat';
$lang['admin']['duplicate'] = 'Duplikovat';
$lang['admin']['import_private_key'] = 'Importovat soukromý klíč';
$lang['admin']['duplicate_dkim'] = 'Duplikovat DKIM záznam';
$lang['admin']['dkim_from'] = 'Od';
$lang['admin']['dkim_to'] = 'Komu';
$lang['admin']['dkim_from_title'] = 'Zdrojová doména, z níž se budou kopírovat data';
$lang['admin']['dkim_to_title'] = 'Cílová doména/y - bude přepsáno';
$lang['admin']['f2b_parameters'] = 'Nastavení Fail2ban';
$lang['admin']['f2b_ban_time'] = 'Doba blokování (s)';
$lang['admin']['f2b_max_attempts'] = 'Max. pokusů';
$lang['admin']['f2b_retry_window'] = 'Časový horizont pro maximum pokusů (s)';
$lang['admin']['f2b_netban_ipv4'] = 'Rozsah IPv4 podsítě k zablokování (8-32)';
$lang['admin']['f2b_netban_ipv6'] = 'Rozsah IPv6 podsítě k zablokování (8-128)';
$lang['admin']['f2b_whitelist'] = 'Sítě/hostitelé na whitelistu';
$lang['admin']['f2b_blacklist'] = 'Sítě/hostitelé na blacklistu';
$lang['admin']['f2b_list_info'] = 'Síť nebo hostitelé na blacklistu mají vždy větší váhu než položky na whitelistu. Blacklist se sestavuje vždy při startu kontejneru.';
$lang['admin']['search_domain_da'] = 'Hledat domény';
$lang['admin']['r_inactive'] = 'Neaktivní omezení';
$lang['admin']['r_active'] = 'Aktivní omezení';
$lang['admin']['r_info'] = 'Šedé/vypnuté položky v seznamu aktivních omezení neuznává mailcow jako platná a nelze je přesouvat. Neznámá omezení budou však budou stejně seřazena tak, jak jdou za sebou. <br>Nové prvky lze přidat do <code>inc/vars.local.inc.php</code> a pak je zde přepínat.';
$lang['admin']['dkim_key_length'] = 'Délka DKIM klíče (v bitech)';
$lang['admin']['dkim_key_valid'] = 'Klíč je platný';
$lang['admin']['dkim_key_unused'] = 'Klíč nepoužitý';
$lang['admin']['dkim_key_missing'] = 'Klíč chybí';
$lang['admin']['dkim_add_key'] = 'Přidat ARC/DKIM klíč';
$lang['admin']['dkim_keys'] = 'ARC/DKIM klíče';
$lang['admin']['dkim_private_key'] = 'Soukromý klíč';
$lang['admin']['dkim_domains_wo_keys'] = "Vybrat domény bez klíče";
$lang['admin']['dkim_domains_selector'] = "Selektor";
$lang['admin']['add'] = 'Přidat';
$lang['add']['add_domain_restart'] = 'Přidat doménu a restartovat SOGo';
$lang['add']['add_domain_only'] = 'Přidat doménu';
$lang['admin']['configuration'] = 'Nastavení';
$lang['admin']['password'] = 'Heslo';
$lang['admin']['password_repeat'] = 'Potvrzení nového hesla (opakujte)';
$lang['admin']['active'] = 'Aktivní';
$lang['admin']['inactive'] = 'Neaktivní';
$lang['admin']['action'] = 'Akce';
$lang['admin']['add_domain_admin'] = 'Přidat správce domény';
$lang['admin']['add_admin'] = 'Přidat správce';
$lang['admin']['add_settings_rule'] = 'Přidat nastavení';
$lang['admin']['rsetting_desc'] = 'Krátký popis';
$lang['admin']['rsetting_content'] = 'Obsah pravidla';
$lang['admin']['rsetting_none'] = 'Žádné pravidlo není k dispozici';
$lang['admin']['rsetting_no_selection'] = 'Prosím vyberte pravidlo';
$lang['admin']['rsettings_preset_1'] = 'Pro přihlášené uživatele vypnout vše kromě DKIM a omezení provozu';
$lang['admin']['rsettings_preset_2'] = 'Postmasteři chtějí dostávat spam';
$lang['admin']['rsettings_insert_preset'] = 'Vložit příklad nastavení "%s"';
$lang['admin']['rsetting_add_rule'] = 'Přidat pravidlo';
$lang['admin']['queue_ays'] = 'Potvrďte prosím, že chcete odstranit všechny položky z aktuální fronty.';
$lang['admin']['arrival_time'] = 'Čas zařazení do fronty (čas na serveru)';
$lang['admin']['message_size'] = 'Velikost zprávy';
$lang['admin']['sender'] = 'Odesílatel';
$lang['admin']['recipients'] = 'Příjemci';
$lang['admin']['admin_domains'] = 'Přidělené domény';
$lang['admin']['domain_admins'] = 'Správci domén';
$lang['admin']['flush_queue'] = 'Vyprázdnit frontu (opětovně doručit)';
$lang['admin']['delete_queue'] = 'Smazat vše';
$lang['admin']['queue_deliver_mail'] = 'Doručit';
$lang['admin']['queue_hold_mail'] = "Zadržet";
$lang['admin']['queue_unhold_mail'] = 'Propustit';
$lang['admin']['username'] = 'Uživatelské jméno';
$lang['admin']['edit'] = 'Upravit';
$lang['admin']['remove'] = 'Smazat';
$lang['admin']['save'] = 'Uložit změny';
$lang['admin']['admin'] = 'Správce';
$lang['admin']['admin_details'] = 'Upravit správce';
$lang['admin']['unchanged_if_empty'] = 'Pokud se nemění, ponechte prázdné';
$lang['admin']['yes'] = '&#10003;';
$lang['admin']['no'] = '&#10005;';
$lang['admin']['access'] = 'Přístupy';
$lang['admin']['no_record'] = 'Žádný záznam';
$lang['admin']['filter_table'] = 'Tabulka filtrů';
$lang['admin']['empty'] = 'Žádné výsledky';
$lang['admin']['time'] = 'Čas';
$lang['admin']['last_applied'] = 'Naposledy použité';
$lang['admin']['reset_limit'] = 'Odebrat hash';
$lang['admin']['hash_remove_info'] = 'Odebrání hashe omezení provozu (pokud stále existuje) zcela vyresetuje jeho počítadlo.<br>
  Každý hash je označen jinou barvou.';
$lang['warning']['hash_not_found'] = 'Hash nenalezen';
$lang['success']['hash_deleted'] = 'Hash smazán';
$lang['admin']['authed_user'] = 'Přihlášený uživatel';
$lang['admin']['priority'] = 'Priorita';
$lang['admin']['message'] = 'Zpráva';
$lang['admin']['rate_name'] = 'Název';
$lang['admin']['refresh'] = 'Obnovit';
$lang['admin']['to_top'] = 'Zpět na začátek';
$lang['admin']['in_use_by'] = 'Používáno';
$lang['admin']['forwarding_hosts'] = 'Předávající hostitelé';
$lang['admin']['forwarding_hosts_hint'] = 'Příchozí zprávy od zde uvedených hostitelů jsou bezpodmínečně přijaty. U těchto hostitelů se nekontroluje DNSBL a nepoužije greylisting. Spam od těchto hostitelů se nikdy neodmítá, ale občas může skončit ve složce se spamem. Nejčastěji se zde uvádějí poštovní servery, jež předávají příchozí e-maily na tento poštovní server.';
$lang['admin']['forwarding_hosts_add_hint'] = 'Lze zadat IPv4/IPv6 adresy, sítě ve formátu CIDR, názvy hostitelů (budou převedeny na IP adresy) nebo názvy domén (budou převedeny na IP pomocí SPF záznamů, příp. MX záznamů).';
$lang['admin']['relayhosts_hint'] = 'Zde definujte transporty podle odesílatele, jež pak můžete použít v nastavení domény.<br>
Protokol transportu je vždy "smtp:". Bere se v potaz uživatelské nastavení odchozího TLS.';
$lang['admin']['transports_hint'] = '→ Položka transportní mapy <b>přebíjí</b> transportní mapu podle odesílatele</b>.<br>
→ Uživatelské nastavení odchozího TLS se ignoruje a lze je výhradně vynutit mapováním TLS pravidel.<br>
→ Protokol transportu je vždy "smtp:".<br>
→ Adresy, jež odpovídají výrazu "/localhost$/", se vždy předají přes "local:", takže nejsou zahrnuty do definice cíle "*".<br>
→ Pro stanovení přihlašovacích údajů dalšího skoku, např. "[host]:25", bude Postfix <b>vždy</b> hledat nejdříve "host" a teprve pak "[host]:25". Kvůli tomu nelze použít současně "host" a "[host]:25"';
$lang['admin']['add_relayhost_hint'] = 'Pozor: přihlašovací údaje se ukládají jako prostý text.';
$lang['admin']['add_transports_hint'] = 'Pozor: přihlašovací údaje se ukládají jako prostý text.';
$lang['admin']['host'] = 'Hostitel';
$lang['admin']['source'] = 'Zdroj';
$lang['admin']['add_forwarding_host'] = 'Přidat předávajícího hostitele';
$lang['admin']['add_relayhost'] = 'Přidat transport podle odesílatele';
$lang['admin']['add_transport'] = 'Přidat transport';
$lang['admin']['relayhosts'] = 'Transporty podle odesílatele';
$lang['admin']['transport_maps'] = 'Transportní mapy';
$lang['admin']['routing'] = 'Směrování';
$lang['admin']['credentials_transport_warning'] = '<b>Upozornění</b>: Přidání položky do transportní mapy aktualizuje také přihlašovací údaje všech záznamů s odpovídajícím skokem.';

$lang['admin']['destination'] = 'Cíl';
$lang['admin']['nexthop'] = 'Další skok';

$lang['admin']['oauth2_info'] = 'Implementace OAuth2 podporuje přidělení typu "Authorization Code" a vydává tokeny k obnovení.<br>
Server vydává tokeny k obnovení automaticky, jakmile byl předchozí token použit.<br><br>
→ Výchozím rozsahem je <i>profil</i>. Ověření přes OAuth2 mohou využít jen uživatelé poštovních schránek. Je-li rozsah vynechán, použije se <i>profil</i>.<br>
→ Klient je povinen uvést parametr <i>state</i> spolu s požadavkem na ověření.<br><br>
Cesty API pro požadavky na ověření OAuth2: <br>
<ul>
  <li>Koncový bod pro ověření: <code>/oauth/authorize</code></li>
  <li>Koncový bod pro token: <code>/oauth/token</code></li>
  <li>Stránka zdroje:  <code>/oauth/profile</code></li>
</ul>
Dojde-li ke znovuvytvoření tajného klíče klienta, nedojde ke zneplatnění stávajícíh ověřovacích kódů, nebude však už možné obnovit jejich token.<br><br>
Odvoláním klientského tokenů okamžitě ukončíte všechny aktivní relace a klienti se budou muset znovu přihlásit.';

$lang['admin']['oauth2_client_id'] = "ID klienta";
$lang['admin']['oauth2_client_secret'] = "Tajný klíč klienta";
$lang['admin']['oauth2_redirect_uri'] = "URI přesměrování";
$lang['admin']['oauth2_revoke_tokens'] = 'Odvolat všechny klientské tokeny';
$lang['admin']['oauth2_renew_secret'] = 'Vytvořit nový tajný klíč';
$lang['edit']['client_id'] = 'ID klienta';
$lang['edit']['client_secret'] = 'Tajný klíč klienta';
$lang['edit']['scope'] = 'Rozsah';
$lang['edit']['grant_types'] = 'Typy přidělení';
$lang['edit']['redirect_uri'] = 'URL přesměrování/odvolání';
$lang['oauth2']['scope_ask_permission'] = 'Aplikace požádala o následující oprávnění';
$lang['oauth2']['profile'] = 'Profil';
$lang['oauth2']['profile_desc'] = 'Zobrazit osobní údaje: uživ. jméno, jméno, datum vytvoření a úpravy, stav';
$lang['oauth2']['permit'] = 'Ověřit aplikaci';
$lang['oauth2']['authorize_app'] = 'Ověřit aplikaci';
$lang['oauth2']['deny'] = 'Zamítnout';
$lang['oauth2']['access_denied'] = 'K udělení přístupu se přihlašte jako vlastník poštovních schránky.';

$lang['success']['forwarding_host_removed'] = "Předávající hostitel %s odebrán";
$lang['success']['forwarding_host_added'] = "Předávající hostitel %s přidán";
$lang['success']['relayhost_removed'] = "Položka %s odebrána";
$lang['success']['relayhost_added'] = "Položky %s přidána";
$lang['diagnostics']['dns_records'] = 'DNS záznamy';
$lang['diagnostics']['dns_records_24hours'] = 'Upozornění: Změnám v systému DNS může trvat až 24 hodin, než se zde správně zobrazí jejich aktuální stav. Můžete zde snadno zjistit, jak nastavit DNS záznamy a zda jsou všechny záznamy správně uloženy.';
$lang['diagnostics']['dns_records_name'] = 'Název';
$lang['diagnostics']['dns_records_type'] = 'Typ';
$lang['diagnostics']['dns_records_data'] = 'Správný záznam';
$lang['diagnostics']['dns_records_status'] = 'Současný stav';
$lang['diagnostics']['optional'] = 'Tento záznam je volitelný.';
$lang['diagnostics']['cname_from_a'] = 'Hodnota odvozena z A/AAAA záznamu. Lze použít, pokud záznam ukazuje na správný zdroj.';

$lang['admin']['relay_from'] = 'Adresa "Od:"';
$lang['admin']['relay_run'] = "Provést test";
$lang['admin']['api_allow_from'] = "Povolit přístup k API z těchto IP adres (oddělte čárkou nebo řádkem)";
$lang['admin']['api_key'] = "API klíč";
$lang['admin']['activate_api'] = "Zapnout API";
$lang['admin']['regen_api_key'] = "Generovat API klíč";
$lang['admin']['ban_list_info'] = "Seznam blokovaných IP adres je zobrazen níže: <b>síť (zbývající čas blokování) - [akce]</b>.<br />IP adresy zařazené pro odblokování budou z aktivního seznamu odebrány během několika sekund.<br />Červeně označené položky jsou pernamentní bloky z blacklistu.";
$lang['admin']['unban_pending'] = "čeká na odblokování";
$lang['admin']['queue_unban'] = "odblokovat";
$lang['admin']['no_active_bans'] = "Žádná aktivní blokování";

$lang['admin']['quarantine'] = "Karanténa";
$lang['admin']['rspamd_settings_map'] = "Nastavení Rspamd";
$lang['admin']['quota_notifications'] = "Upozornění na kvóty";
$lang['admin']['quota_notifications_vars'] = "{{percent}} se rovná aktuální kvótě<br>{{username}} je jméno poštovní schránky";
$lang['admin']['active_rspamd_settings_map'] = "Aktivní nastavení";
$lang['admin']['quota_notifications_info'] = "Upozornění na kvótu se uživateli odesílají při překročení 80 % a 95 % limitu.";
$lang['admin']['quarantine_retention_size'] = "Počet zadržených zpráv na poštovní schránku<br />0 znamená <b>neaktivní</b>.";
$lang['admin']['quarantine_max_size'] = "Maximální velikost v MiB (větší prvky budou smazány)<br />0 <b>neznamená</b> neomezeno.";
$lang['admin']['quarantine_max_age'] = "Maximální stáří ve dnech<br><small>Hodnota musí být rovna nebo větší než 1 den.</small>";
$lang['admin']['quarantine_exclude_domains'] = "Vyloučené domény a doménové aliasy";
$lang['admin']['quarantine_release_format'] = "Formát propuštěných položek";
$lang['admin']['quarantine_release_format_raw'] = "Nezměněný originál";
$lang['admin']['quarantine_release_format_att'] = "Jako příloha";
$lang['admin']['quarantine_notification_sender'] = "Odesílatel upozornění";
$lang['admin']['quarantine_notification_subject'] = "Předmět upozornění";
$lang['admin']['quarantine_notification_html'] = "Šablona upozornění:<br><small>Ponechte prázdné, aby se obnovila výchozí šablona.</small>";
$lang['admin']['quota_notification_sender'] = "Odesílatel upozornění";
$lang['admin']['quota_notification_subject'] = "Předmět upozornění";
$lang['admin']['quota_notification_html'] = "Šablona upozornění:<br><small>Ponechte prázdné, aby se obnovila výchozí šablona.</small>";
$lang['admin']['ui_texts'] = "Hlavička a texty UI";
$lang['admin']['help_text'] = "Přepsat text nápovědy pod přihlašovacím formulářem (HTML povoleno)";
$lang['admin']['title_name'] = 'Titulek webu ("mailcow UI")';
$lang['admin']['main_name'] = 'Název webu ("mailcow UI")';
$lang['admin']['apps_name'] = 'Hlavička aplikací ("mailcow Apps")';
$lang['admin']['ui_footer'] = 'Pata stránka (HTML povoleno)';

$lang['admin']['customize'] = "Přizpůsobení";
$lang['admin']['change_logo'] = "Změnit logo";
$lang['admin']['logo_info'] = "Obrázek bude zmenšen na výšku 40 pixelů pro horní navigační lištu a na max. šířku 250 pixelů pro úvodní stránku.";
$lang['admin']['upload'] = "Nahrát";
$lang['admin']['app_links'] = "Odkazy na aplikace";
$lang['admin']['app_name'] = "Název aplikace";
$lang['admin']['link'] = "Odkaz";
$lang['admin']['remove_row'] = "Smazat řádek";
$lang['admin']['add_row'] = "Přidat řádek";
$lang['admin']['reset_default'] = "Obnovit výchozí nastavení";
$lang['admin']['merged_vars_hint'] = 'Šedé řádky byly přidány z <code>vars.(local.)inc.php</code> a zde je nelze upravit.';
$lang['mailbox']['waiting'] = "Čekání";
$lang['mailbox']['status'] = "Stav";
$lang['mailbox']['running'] = "Běží";
$lang['mailbox']['enable_x'] = "Zapnout";
$lang['mailbox']['disable_x'] = "Vypnout";

$lang['edit']['spam_score'] = "Nastavte vlastní skóre spamu";
$lang['user']['spam_score_reset'] = "Obnovit výchozí nastavení serveru";
$lang['edit']['spam_policy'] = "Přidat nebo odebrat položky whitelistu/blacklistu";
$lang['edit']['spam_alias'] = "Vytvořit nebo změnit dočasné aliasy";

$lang['danger']['comment_too_long'] = "Moc dlouhý komentář, max. 160 znaků";
$lang['danger']['img_tmp_missing'] = "Nelze ověřit soubor s obrázkem: dočasný soubor nebyl nalezen";
$lang['danger']['img_invalid'] = "Nelze ověřit soubor s obrázkem";
$lang['danger']['invalid_mime_type'] = "Špatný mime typ";
$lang['success']['upload_success'] = "Soubor úspěšně nahrán";
$lang['success']['app_links'] = "Změny odkazů na aplikace uloženy";
$lang['success']['ui_texts'] = "Změny UI textů uloženy";
$lang['success']['reset_main_logo'] = "Obnovit výchozí logo";
$lang['success']['items_released'] = "Vybraná položka propuštěna";
$lang['success']['item_released'] = "Položka %s propuštěna";
$lang['danger']['imagick_exception'] = "Chyba: Výjimka programu Imagick při čtení obrázku";
$lang['quarantine']['quarantine'] = "Karanténa";
$lang['quarantine']['learn_spam_delete'] = "Naučit jako spam a smazat";
$lang['quarantine']['qinfo'] = 'Karanténní systém uloží odmítnutou poštu do databáze a odesílatel <em>nebude</em> informován o nedoručené poště.
  <br>"' . $lang['quarantine']['learn_spam_delete'] . '" naučí systém, že zpráva je spam, pomocí Bayes teorému a také vypočítá "fuzzy hashes" pro odmítnutí podobných zpráv v budoucnu.
  <br>Upozornění: Učení se vícera zpráv najednou může být v závislosti na výkonu systému časově náročné.';
$lang['quarantine']['download_eml'] = "Stáhnout (.eml)";
$lang['quarantine']['release'] = "Propustit";
$lang['quarantine']['empty'] = 'Žádné výsledky';
$lang['quarantine']['toggle_all'] = 'Označit vše';
$lang['quarantine']['quick_actions'] = 'Akce';
$lang['quarantine']['remove'] = 'Smazat';
$lang['quarantine']['received'] = "Přijato";
$lang['quarantine']['action'] = "Akce";
$lang['quarantine']['rcpt'] = "Příjemce";
$lang['quarantine']['qid'] = "Rspamd QID";
$lang['quarantine']['sender'] = "Odesílatel";
$lang['quarantine']['show_item'] = "Zobrazit položku";
$lang['quarantine']['check_hash'] = "Hledat hash na serveru VT";
$lang['quarantine']['qitem'] = "Položka v karanténě";
$lang['quarantine']['rspamd_result'] = "Skóre Rspamd";
$lang['quarantine']['subj'] = "Předmět";
$lang['quarantine']['recipients'] = "Příjemci";
$lang['quarantine']['text_plain_content'] = "Obsah (text/plain)";
$lang['quarantine']['text_from_html_content'] = "Obsah (konvertované html)";
$lang['quarantine']['atts'] = "Přílohy";
$lang['quarantine']['low_danger'] = "Malé nebezpečí";
$lang['quarantine']['neutral_danger'] = "Neutrální nebo žádné hodnocení";
$lang['quarantine']['medium_danger'] = "Střední nebezpečí";
$lang['quarantine']['high_danger'] = "Vysoké nebezpečí";
$lang['quarantine']['danger'] = "Nebezpečí";
$lang['quarantine']['spam_score'] = "Skóre";
$lang['quarantine']['confirm_delete'] = "Potvrdit smazání prvku.";
$lang['quarantine']['qhandler_success'] = "Požadavek úspěšně přijat. Můžete nyní zavřít okno.";
$lang['warning']['fuzzy_learn_error'] = "Chyba při učení Fuzzy hash: %s";
$lang['danger']['spam_learn_error'] = "Chyba při učení spamu: %s";
$lang['success']['qlearn_spam'] = "Zpráva ID %s naučena jako spam a smazána";

$lang['debug']['system_containers'] = 'Systém a kontejnery';
$lang['debug']['started_on'] = 'Spuštěno';
$lang['debug']['jvm_memory_solr'] = 'Spotřeba paměti JVM';
$lang['debug']['solr_status'] = 'Stav Solr';
$lang['debug']['solr_dead'] = 'Solr se spouští, je vypnutý nebo spadl.';
$lang['debug']['logs'] = 'Logy';
$lang['debug']['log_info'] = '<p><b>Logy v paměti</b> jsou shromažďovány v Redis seznamech a jsou oříznuty na LOG_LINES (%d) každou minutu, aby se nepřetěžoval server.
  <br>Logy v paměti nemají být trvalé. Všechny aplikace, které logují do paměti, zároveň logují i do Docker služby podle nastavení logging driveru.
  <br>Logy v paměti lze použít pro ladění drobných problémů s kontejnery.</p>
  <p><b>Externí logy</b> jsou shromažďovány pomocí API dané aplikace.</p>
  <p><b>Statické logy</b> jsou většinou logy činností, které nejsou zaznamenávány do Docker služby, ale přesto je dobré je schraňovat (výjimkou jsou logy API).</p>';

$lang['debug']['in_memory_logs'] = 'Logy v paměti';
$lang['debug']['external_logs'] = 'Externí logy';
$lang['debug']['static_logs'] = 'Statické logy';
$lang['debug']['solr_uptime'] = 'Doba běhu';
$lang['debug']['solr_started_at'] = 'Spuštěn';
$lang['debug']['solr_last_modified'] = 'Naposledy změněn';
$lang['debug']['solr_size'] = 'Velikost';
$lang['debug']['solr_docs'] = 'Dokumentace';

$lang['debug']['disk_usage'] = 'Využití disku';
$lang['debug']['containers_info'] = "Informace o kontejnerech";
$lang['debug']['restart_container'] = 'Restartovat';

$lang['quarantine']['release_body'] = "Zpráva připojena jako příloha EML k této zprávě.";
$lang['danger']['release_send_failed'] = "Zprávu nelze propustit: %s";
$lang['quarantine']['release_subject'] = "Potenciálně škodlivá položka v karanténě %s";

$lang['mailbox']['bcc_map'] = "BCC mapování";
$lang['mailbox']['bcc_map_type'] = "Typ BCC";
$lang['mailbox']['bcc_type'] = "Typ BCC";
$lang['mailbox']['bcc_sender_map'] = "Mapa odesílatelů";
$lang['mailbox']['bcc_rcpt_map'] = "Mapa příjemců";
$lang['mailbox']['bcc_local_dest'] = "Místní cíl";
$lang['mailbox']['bcc_destinations'] = "BCC cíl";
$lang['mailbox']['bcc_destination'] = "BCC cíl";
$lang['edit']['bcc_dest_format'] = 'BCC cíl musí být jedna platná email adresa.';

$lang['mailbox']['bcc'] = "BCC";
$lang['mailbox']['bcc_maps'] = "BCC mapy";
$lang['mailbox']['bcc_to_sender'] = "Přepnout na mapu odesílatelů";
$lang['mailbox']['bcc_to_rcpt'] = "Přepnout na mapu příjemců";
$lang['mailbox']['add_bcc_entry'] = "Přidat BCC mapu";
$lang['mailbox']['add_tls_policy_map'] = "Přidat mapu TLS pravidel";
$lang['mailbox']['bcc_info'] = "Mapa BCC se používá pro tiché předávání kopií všech zpráv na jinou adresu. Mapa příjemců se použije, pokud je místní cíl příjemcem zprávy.<br/>
  Mapa odesílatelů podléhá obdobnému principu. Místní cíl nebude informován o neúspěšném doručení.";
$lang['mailbox']['address_rewriting'] = 'Přepisování adres';
$lang['mailbox']['recipient_maps'] = 'Mapy příjemců';
$lang['mailbox']['recipient_map'] = 'Mapa příjemce';
$lang['mailbox']['recipient_map_info'] = 'Mapy příjemců slouží k nahrazení cílové adresy zprávy před doručením.';
$lang['mailbox']['recipient_map_old_info'] = 'Původní příjemce musí být platná emailová adresa nebo název domény.';
$lang['mailbox']['recipient_map_new_info'] = 'Cílová adresa mapy příjemce musí být platná emailová adresa.';
$lang['mailbox']['recipient_map_old'] = 'Původní příjemce';
$lang['mailbox']['recipient_map_new'] = 'Nový přijemce';
$lang['danger']['invalid_recipient_map_new'] = 'Neplatný nový příjemce: %s';
$lang['danger']['invalid_recipient_map_old'] = 'Neplatný původní příjemce: %s';
$lang['danger']['recipient_map_entry_exists'] = 'Položka mapy příjemců "%s" již existuje';
$lang['success']['recipient_map_entry_saved'] = 'Položka mapy příjemců "%s" uložena';
$lang['success']['recipient_map_entry_deleted'] = 'Položka mapy ID %s smazána';
$lang['danger']['tls_policy_map_entry_exists'] = 'Položka mapy TLS pravidel "%s" již existuje';
$lang['success']['tls_policy_map_entry_saved'] = 'Položka mapy TLS pravidel "%s" uložena';
$lang['success']['tls_policy_map_entry_deleted'] = 'Položka mapy TLS pravidel ID %s smazána';
$lang['mailbox']['add_recipient_map_entry'] = 'Přidat mapu příjemce';
$lang['danger']['tls_policy_map_parameter_invalid'] = "Parametr pravidel TLS je neplatný";
$lang['danger']['temp_error'] = "Dočasná chyba";

$lang['admin']['sys_mails'] = 'Systémové zprávy';
$lang['admin']['subject'] = 'Předmět';
$lang['admin']['from'] = 'Od';
$lang['admin']['include_exclude'] = 'Zahrnout/Vyloučit';
$lang['admin']['include_exclude_info'] = 'Ve výchozím nastavení (bez výběru), jsou adresovány <b>všechny poštovní schránky</b>';
$lang['admin']['excludes'] = 'Vyloučit tyto příjemce';
$lang['admin']['includes'] = 'Zahrnout tyto přijemce';
$lang['admin']['text'] = 'Text';
$lang['admin']['activate_send'] = 'Povolit tlačítko "Odeslat"';
$lang['admin']['send'] = 'Odeslat';

$lang['warning']['ip_invalid'] = 'Přeskočena neplatná IP: %s';
$lang['danger']['text_empty'] = 'Text nesmí být prázdný';
$lang['danger']['subject_empty'] = 'Předmět nesmí být prázdný';
$lang['danger']['from_invalid'] = 'Odesílatel nesmí být prázdný';
$lang['danger']['network_host_invalid'] = 'Neplatná síť nebo hostitel: %s';

$lang['add']['mailbox_quota_def'] = 'Výchozí kvóta schránky';
$lang['edit']['mailbox_quota_def'] = 'Výchozí kvóta schránky';
$lang['danger']['mailbox_defquota_exceeds_mailbox_maxquota'] = 'Výchozí kvóta překračuje maximální kvótu schránky"';
$lang['danger']['defquota_empty'] = 'Výchozí kvóta schránky nesmí být 0.';
$lang['mailbox']['mailbox_defquota'] = 'Výchozí velikost schránky';

$lang['admin']['api_info'] = 'API je stále ve vývoji.';

$lang['admin']['guid_and_license'] = 'GUID a licence';
$lang['admin']['guid'] = 'GUID - unikátní ID licence';
$lang['admin']['license_info'] = 'Licence není povinná, pomůžete však dalšímu vývoji.<br><a href="https://www.servercow.de/mailcow?lang=en#sal" target="_blank" alt="SAL order">Registrujte si své GUID</a>, nebo si <a href="https://www.servercow.de/mailcow?lang=en#support" target="_blank" alt="Support order">zaplaťte podporu pro svou instalaci mailcow.</a>';
$lang['admin']['validate_license_now'] = 'Ověřit GUID na licenčním serveru';

$lang['admin']['customer_id'] = 'ID zákazníka';
$lang['admin']['service_id'] = 'ID podpory';

$lang['admin']['lookup_mx'] = 'Ověřit cíl proti MX záznamu (.outlook.com bude směrovat všechnu poštu pro MX *.outlook.com přes tento uzel)';
$lang['edit']['mbox_rl_info'] = 'Toto omezení provozu se vyhodnocuje podle přihlašovacího jména SASL, porovnává se s jakoukoliv adresou "od" použitou přihlášeným uživatelem. Omezení provozu poštovní schránku má prioritu před omezením provozu domény.';

$lang['add']['relayhost_wrapped_tls_info'] = '<b>Nepoužívejte</b> prosím porty s aktivním protokolem TLS (většinou port 465).<br>
Používejte porty bez TLS a pak pošlete příkaz STARTTLS. Pravidlo k vynucení užití TLS lze vytvořit pomocí mapy TLS pravidel.';

$lang['admin']['transport_dest_format'] = 'Formát: example.org, .example.org, *, box@example.org (vícero položek lze oddělit čárkou)';

$lang['mailbox']['alias_domain_backupmx'] = 'Doménový alias není aktivní pro předávanou doménu';

$lang['danger']['extra_acl_invalid'] = 'Externí adresa odesílatele "%s" je neplatná';
$lang['danger']['extra_acl_invalid_domain'] = 'Externí adresa odesílatele "%s" má neplatnou doménu';

