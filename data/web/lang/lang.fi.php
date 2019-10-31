<?php
/*
 * Finnish language file
 */

$lang['header']['apps'] = 'Sovellukset';
$lang['footer']['loading'] = "Ole hyvä ja odota...";
$lang['header']['restart_sogo'] = 'Uudelleen käynnistä SOGo';
$lang['header']['restart_netfilter'] = 'Uudelleen käynnistä netfilter';
$lang['footer']['restart_container'] = 'Uudelleen käynnistä moottori';
$lang['footer']['restart_now'] = 'Käynnistä uudelleen nyt';
$lang['footer']['restarting_container'] = 'Uudelleen käynnistä container, tämä saattaa kestää jonkin aikaa...';
$lang['footer']['restart_container_info'] = '<b>Tärkeää:</b> Uudelleenkäynnistys voi kestää jonkin aikaa, odota, kunnes se päättyy.';

$lang['footer']['confirm_delete'] = 'Poiston vahvistaminen';
$lang['footer']['delete_these_items'] = 'Vahvista muutokset seuraavaan objekti tunnukseen';
$lang['footer']['delete_now'] = 'Poista nyt';
$lang['footer']['cancel'] = 'Peruuta';

$lang['footer']['hibp_nok'] = 'Löytyi! Tämä on mahdollisesti vaarallinen salasana!';
$lang['footer']['hibp_ok'] = 'Vastaavuutta ei löytynyt.';

$lang['danger']['transport_dest_exists'] = 'Kuljetuksen määränpää "%s" olemassa';
$lang['danger']['unlimited_quota_acl'] = "Rajoittamaton kiintiö kielletty ACL";
$lang['danger']['mysql_error'] = "MySQL virhe: %s";
$lang['danger']['redis_error'] = "Redis virhe: %s";
$lang['danger']['unknown_tfa_method'] = "Tuntematon TFA-menetelmä";
$lang['danger']['totp_verification_failed'] = "TOTP-vahvistus epäonnistui";
$lang['success']['verified_totp_login'] = "Vahvistettu TOTP-kirjautuminen";
$lang['danger']['u2f_verification_failed'] = "U2F vahvistaminen epäonnistui: %s";
$lang['success']['verified_u2f_login'] = "Vahvistettu U2F kirjautuminen";
$lang['success']['verified_yotp_login'] = "Vahvistettu Yubico OTP kirjautuminen";
$lang['danger']['yotp_verification_failed'] = "Yubico OTP todentaminen epäonnistui: %s";
$lang['danger']['ip_list_empty'] = "Sallittujen IP-luetteloiden luettelo ei voi olla tyhjä";
$lang['danger']['invalid_destination'] = 'Kohteen muoto "%s" ei kelpaa';
$lang['danger']['invalid_nexthop'] = "Seuraava hop-muoto ei kelpaa";
$lang['danger']['invalid_nexthop_authenticated'] = "Seuraava hop on olemassa eri tunniste tiedoilla, Päivitä nykyiset valtuudet tämän seuraavan hop ensin.";
$lang['danger']['next_hop_interferes'] = "%s liitännät nexthopin kanssa %s";
$lang['danger']['next_hop_interferes_any'] = "Olemassa oleva seuraava hop liitännät %s";
$lang['danger']['rspamd_ui_pw_length'] = "Rspamd UI salasana pitäisi olla vähintään 6 merkkiä pitkä";
$lang['success']['rspamd_ui_pw_set'] = "Rspamd UI salasana onnistuneesti asetettu";
$lang['success']['queue_command_success'] = "Jonon komento onnistui";
$lang['danger']['unknown'] = "Ilmeni tuntematon virhe";
$lang['danger']['malformed_username'] = "Virheellisesti muotoiltu käyttäjä nimi";
$lang['info']['awaiting_tfa_confirmation'] = "Odotetaan TFA-vahvistusta";
$lang['info']['session_expires'] = "Istunto vanhenee noin 15 sekunnin kuluttua.";
$lang['success']['logged_in_as'] = "Kirjautuneena sisään %s";
$lang['danger']['login_failed'] = "Kirjautuminen epäonnistui";
$lang['danger']['set_acl_failed'] = "ACL-joukon asettaminen epäonnistui";
$lang['danger']['no_user_defined'] = "Käyttäjää ei tunnistettu";
$lang['danger']['script_empty'] = "Komento sarja ei voi olla tyhjä";
$lang['danger']['sieve_error'] = "Sieve parser-virhe: %s";
$lang['danger']['value_missing'] = "Anna kaikki arvot";
$lang['danger']['filter_type'] = "Väärä suodattimen tyyppi";
$lang['danger']['domain_cannot_match_hostname'] = "Verkkotunnus alue ei voi vastata isäntä nimeä";
$lang['warning']['domain_added_sogo_failed'] = "Lisätty Domain mutta ei käynnistänyt SOGoa, tarkista palvelimen lokit.";
$lang['danger']['rl_timeframe'] = "Nopeus rajoitus aika kehys on väärä";
$lang['success']['rl_saved'] = "Objektin nopeus rajoitus %s tallenettu";
$lang['success']['acl_saved'] = "Objektin ACL %s tallenettu";
$lang['success']['deleted_syncjobs'] = "Poistetut syncjobs: %s";
$lang['success']['deleted_syncjob'] = "Poistettu syncjob-tunnus %s";
$lang['success']['delete_filters'] = "Poistetut suodattimet: %s";
$lang['success']['delete_filter'] = "Poistettujen suodattimien tunnus %s";
$lang['danger']['invalid_bcc_map_type'] = "Virheellinen piilo kopio osoite tyyppi";
$lang['danger']['bcc_empty'] = "Piilo kopion osoite kohde ei voi olla tyhjä";
$lang['danger']['bcc_must_be_email'] = "Piilo kopion kohde %s ei ole kelvollinen sähköposti osoite";
$lang['danger']['bcc_exists'] = "Piilo kopio-osoite %s on olemassa tyypille %s";
$lang['success']['bcc_saved'] = "Piilo kopio osoite merkintä tallennettu";
$lang['success']['bcc_edited'] = "Piilo kopio osoite merkintä %s muokattu";
$lang['success']['bcc_deleted'] = "Piilo kopio osoite merkinnät poistettu: %s";
$lang['danger']['private_key_error'] = "Yksityisen avaimen virhe: %s";
$lang['danger']['map_content_empty'] = "Osoitteen sisältö ei voi olla tyhjä";
$lang['success']['settings_map_added'] = "Lisätty asetus osoitteelle merkintä";
$lang['danger']['settings_map_invalid'] = "Asetus osoite tunnus %s väärin";
$lang['success']['settings_map_removed'] = "Poistettu asetus osoite tunnus %s";
$lang['danger']['invalid_host'] = "Virheellinen isäntä määritetty: %s";
$lang['danger']['relayhost_invalid'] = "Osoite %s on väärin";
$lang['success']['saved_settings'] = "Asetukset tallenettu";
$lang['success']['db_init_complete'] = "Tieto kannan alustus on valmis";

$lang['warning']['session_ua'] = "Lomakkeen tunnus sanoma ei kelpaa: käyttäjä agentin tarkistus virhe";
$lang['warning']['session_token'] = "Lomakkeen tunnus sanoma ei kelpaa: tunnus sanoman risti riita";

$lang['danger']['dkim_domain_or_sel_invalid'] = "DKIM-verkko tunnus tai-valitsin on virheellinen: %s";
$lang['success']['dkim_removed'] = "DKIM avain %s on poistettu";
$lang['success']['dkim_added'] = "DKIM avain %s on tallennettu";
$lang['success']['dkim_duplicated'] = "DKIM-avain verkkotunnus alueelle %s on kopioitu %s";
$lang['danger']['access_denied'] = "Käyttö estetty tai lomake tiedot eivät kelpaa";
$lang['danger']['domain_invalid'] = "Verkkotunnus alueen nimi on tyhjä tai virheellinen";
$lang['danger']['mailbox_quota_exceeds_domain_quota'] = "Maksimi. kiintiö ylittää verkkotunnus alueen kiintiö rajoituksen";
$lang['danger']['object_is_not_numeric'] = "Arvo %s ei ole numeerinen";
$lang['success']['domain_added'] = "Lisätty verkko tunnus %s";
$lang['success']['items_deleted'] = "Nimike %s poistettu onnistuneesti";
$lang['success']['item_deleted'] = "Nimike %s poistettu onnistuneesti";
$lang['danger']['alias_empty'] = "Alias-osoite ei saa olla tyhjä";
$lang['danger']['last_key'] = 'Viimeistä avainta ei voi poistaa';
$lang['danger']['goto_empty'] = "Siirretty osoitteeseen-kenttä ei saa olla tyhjä";
$lang['danger']['policy_list_from_exists'] = "Tietue, jolla on määritetty nimi, on olemassa";
$lang['danger']['policy_list_from_invalid'] = "Tietueen muoto ei kelpaa";
$lang['danger']['alias_invalid'] = "Aliaksen osoite %s on virheellinen";
$lang['danger']['goto_invalid'] = "Siirretty osoitteeseen-kenttä %s on virheellinen";
$lang['danger']['alias_domain_invalid'] = "Alias-verkkotunnus alue %s on virheellinen";
$lang['danger']['target_domain_invalid'] = "Kohde verkkotunnus alue %s on virheellinen";
$lang['danger']['object_exists'] = "Objecti %s on jo olemassa";
$lang['danger']['domain_exists'] = "Verkkotunnus %s on jo olemassa";
$lang['danger']['alias_goto_identical'] = "Alias ja siiretty osoite ei saa olla identtinen";
$lang['danger']['aliasd_targetd_identical'] = "Alias-verkkotunnus alue ei saa olla sama kuin kohde verkkotunnus: %s";
$lang['danger']['maxquota_empty'] = 'Maksimi. kiintiö posti laatikkoa kohden ei saa olla 0.';
$lang['success']['alias_added'] = "Alias osoite %s on lisätty";
$lang['success']['alias_modified'] = "Muutokset alias osoitteseen %s on tallennettu";
$lang['success']['mailbox_modified'] = "Posti laatikon muutokset %s on tallennettu";
$lang['success']['resource_modified'] = "Posti laatikon muutokset %s on tallennettu";
$lang['success']['object_modified'] = "Objektiin tehdyt muutokset %s on tallennettu";
$lang['success']['f2b_modified'] = "Fail2ban parametrien muutokset on tallennettu";
$lang['danger']['targetd_not_found'] = "Kohde verkkotunnus aluetta %s ei löydy";
$lang['danger']['targetd_relay_domain'] = "Kohteella %s on jo relay verkkotunnus";
$lang['success']['aliasd_added'] = "Lisätty alias-verkkotunnus alue %s";
$lang['success']['aliasd_modified'] = "Aliaksen verkkotunnus alueeseen tehdyt muutokset %s on tallennettu";
$lang['success']['domain_modified'] = "Muutokset verkkotunnus alueeseen %s on tallennettu";
$lang['success']['domain_admin_modified'] = "Verkkotunnus järjestelmänvalvojan muutokset %s on tallennettu";
$lang['success']['domain_admin_added'] = "Verkkotunnus alueen järjestelmänvalvoja %s on tallennettu";
$lang['success']['admin_added'] = "Järjestelmänvalvoja %s on lisätty";
$lang['success']['admin_modified'] = "Järjestelmänvalvojan muutokset on tallennettu";
$lang['success']['admin_api_modified'] = "API-muutokset on tallennettu";
$lang['success']['license_modified'] = "Käyttö oikeuden muutokset on tallennettu";
$lang['danger']['username_invalid'] = "Käyttäjätunnusta %s ei voi käyttää";
$lang['danger']['password_mismatch'] = "Vahvistuksen salasana ei täsmää";
$lang['danger']['password_complexity'] = "Salasana ei vastaa käytäntöä";
$lang['danger']['password_empty'] = "Salasana ei saa olla tyhjä";
$lang['danger']['login_failed'] = "Kirjautuminen epäonnistui";
$lang['danger']['mailbox_invalid'] = "Posti laatikon nimi ei kelpaa";
$lang['danger']['description_invalid'] = 'Resurssin kuvaus %s on virheellinen';
$lang['danger']['resource_invalid'] = "Resurssin nimi %s on virheellinen";
$lang['danger']['is_alias'] = "%s tunnetaan jo alias-osoitteena";
$lang['danger']['is_alias_or_mailbox'] = "%s kutsutaan jo aliakseksi, posti laatikkoksi tai alias-osoitteeksi, joka on laajennettu aliaksen verkkotunnus alueelta.";
$lang['danger']['is_spam_alias'] = "%s on jo tunnettu roska postin alias-osoitteena";
$lang['danger']['quota_not_0_not_numeric'] = "Kiintiön on oltava numeerinen ja >= 0";
$lang['danger']['domain_not_found'] = 'Verkkotunnusta %s ei löydy';
$lang['danger']['max_mailbox_exceeded'] = "Maksimi määrä. posti laatikoita saavutettu (%d of %d)";
$lang['danger']['max_alias_exceeded'] = 'Maksimi määrä. aliaksia saavutettu';
$lang['danger']['mailbox_quota_exceeded'] = "Kiintiö ylittää verkkotunnus alueen rajan (maksimi. %d MiB)";
$lang['danger']['mailbox_quota_left_exceeded'] = "Liian vähän tilaa jäljellä (tilaa jäljellä: %d MiB)";
$lang['success']['mailbox_added'] = "Postilaatikko %s on lisätty";
$lang['success']['resource_added'] = "Resurssi %s on lisätty";
$lang['success']['domain_removed'] = "Verkkotunnus %s on poistettu";
$lang['success']['alias_removed'] = "Alias %s on poistettu";
$lang['success']['alias_domain_removed'] = "Alias verkkotunnus %s on poistettu";
$lang['success']['domain_admin_removed'] = "Verkkotunnuksen ylläpitäjä %s on poistettu";
$lang['success']['admin_removed'] = "Ylläpitäjä %s on poistettu";
$lang['success']['mailbox_removed'] = "Postilaatikko %s on poistettu";
$lang['success']['eas_reset'] = "ActiveSync-laitteet käyttäjälle %s nollataan";
$lang['success']['sogo_profile_reset'] = "SOGo profiili käyttäjälle %s nollataan";
$lang['success']['resource_removed'] = "Resurssi %s on poistettu";
$lang['warning']['cannot_delete_self'] = "Kirjautuneen käyttäjän poistaminen ei onnistu";
$lang['warning']['no_active_admin'] = "Viimeistä aktiivista järjestelmänvalvojaa ei voi poistaa käytöstä";
$lang['danger']['max_quota_in_use'] = "Posti laatikon kiintiön on oltava suurempi tai yhtä suuri kuin %d MiB";
$lang['danger']['domain_quota_m_in_use'] = "Verkkotunnus alueen kiintiön on oltava suurempi tai yhtä suuri kuin %s MiB";
$lang['danger']['mailboxes_in_use'] = "Maksimi. posti laatikoiden on oltava suurempia tai yhtä suuria kuin %d";
$lang['danger']['aliases_in_use'] = "Maksimi. aliaksia on oltava suurempi tai yhtä suuri kuin %d";
$lang['danger']['sender_acl_invalid'] = "Lähettäjän ACL-arvo %s on virheellinen";
$lang['danger']['domain_not_empty'] = "Ei-tyhjän verkkotunnuksen alueen poistaminen ei onnistu %s";
$lang['danger']['validity_missing'] = 'Anna voimassaolo aika';
$lang['user']['loading'] = "Ladataan...";
$lang['user']['force_pw_update'] = 'Sinun <b>täytyy muistaa</b> määrittää uusi salasana, jotta voit käyttää Groupware liittyviä palveluita.';
$lang['user']['active_sieve'] = "Aktiivinen suodatin";
$lang['user']['show_sieve_filters'] = "Näytä aktiivisen käyttäjän sieve suodatin";
$lang['user']['no_active_filter'] = "Aktiivisia suodattimia ei ole käytettävissä";
$lang['user']['messages'] = "Kpl viestejä yhteensä"; // "123 messages"
$lang['user']['in_use'] = "Käytetty tila";
$lang['user']['user_change_fn'] = "";
$lang['user']['user_settings'] = 'Käyttäjän asetukset';
$lang['user']['mailbox_details'] = 'Posti laatikon hallinta';
$lang['user']['change_password'] = 'Vaihda salasana';
$lang['user']['client_configuration'] = 'Näytä sähköposti ohjelmien ja älypuhelimien konfigurointi oppaat';
$lang['user']['new_password'] = 'Uusi salasana';
$lang['user']['save_changes'] = 'Tallenna muutokset';
$lang['user']['password_now'] = 'Nykyinen salasana (Vahvista muutokset)';
$lang['user']['new_password_repeat'] = 'Vahvista salasana (anna uudelleen)';
$lang['user']['new_password_description'] = 'Vaatimus: 6 merkkiä pitkä, kirjaimia ja numeroita.';
$lang['user']['spam_aliases'] = 'Tilapäiset sähköposti alias-tunnukset';
$lang['user']['alias'] = 'Alias-tunnus';
$lang['user']['shared_aliases'] = 'Jaetut aliaksen osoitteet';
$lang['user']['shared_aliases_desc'] = 'Käyttäjäkohtaiset asetukset, kuten roska posti suodatin tai salaus käytäntö, eivät vaikuta jaettuihin alias-sähköposti tunnuksiin. Vastaavat roska posti suodattimet voi tehdä vain järjestelmänvalvoja verkkoaluelaajuisiksi käytännöiksi.';
$lang['user']['direct_aliases'] = 'Suorat alias osoitteet';
$lang['user']['direct_aliases_desc'] = 'Roska posti suodatus-ja TLS-käytäntö asetukset vaikuttavat suora aliaksen osoitteisiin.';
$lang['user']['is_catch_all'] = '(catch-all)Saalista-kaikki verkkotunnuksen alueilta';
$lang['user']['aliases_also_send_as'] = 'Myös antaa lähettää käyttäjänä';
$lang['user']['aliases_send_as_all'] = 'Älä tarkista lähettäjän käyttö oikeuksia seuraaville verkkotunnus alueille ja sen alias-verkko tunnuksille';
$lang['user']['alias_create_random'] = 'Luo satunnainen alias';
$lang['user']['alias_extend_all'] = 'Laajenna aliaksia 1 tunti';
$lang['user']['alias_valid_until'] = 'Voimassa';
$lang['user']['alias_remove_all'] = 'Poista kaikki aliakset';
$lang['user']['alias_time_left'] = 'Aikaa jäljellä';
$lang['user']['alias_full_date'] = 'd.m.Y, H:i:s T';
$lang['user']['alias_select_validity'] = 'Voimassaolo aika';
$lang['user']['sync_jobs'] = 'Synkronoi työt';
$lang['user']['expire_in'] = 'Vanhenee';
$lang['user']['hour'] = 'Tunti';
$lang['user']['hours'] = 'Tuntia';
$lang['user']['day'] = 'Päivä';
$lang['user']['week'] = 'Viikko';
$lang['user']['weeks'] = 'Viikkoa';
$lang['user']['spamfilter'] = 'Spam suodatin';
$lang['admin']['spamfilter'] = 'Spam suodatin';
$lang['user']['spamfilter_wl'] = 'Sallittujen lista';
$lang['user']['spamfilter_wl_desc'] = 'Sallittujen sähköposti osoitteita<b> ei koskaan</b> luokitella roska postiksi. Yleismerkkejä, voidaan käyttää. Suodatinta käytetään vain suora aliaksia (aliaksia, joilla on yksi kohde posti laatikko), lukuun ottamatta ( catch-all )saalistaa-kaikki aliaksia ja itse posti laatikkoa..';
$lang['user']['spamfilter_bl'] = 'Mustalista';
$lang['user']['spamfilter_bl_desc'] = 'Mustalla listalla sähköposti osoitteet <b>aina</b> luokitellaam roska postiksi ja hylätään. Yleismerkkejä voidaan käyttää. Suodatinta käytetään vain suorissa aliaksilla (aliaksia, joilla on yksi kohde posti laatikko), lukuun ottamatta ( catch-all )saalistaa-kaikki aliaksia ja itse posti laatikko';
$lang['user']['spamfilter_behavior'] = 'Roskaposti - pisteytys';
$lang['user']['spamfilter_table_rule'] = 'Sääntö';
$lang['user']['spamfilter_table_action'] = 'Toiminnot';
$lang['user']['spamfilter_table_empty'] = 'Ei tietoja näytettäväksi';
$lang['user']['spamfilter_table_remove'] = 'poista';
$lang['user']['spamfilter_table_add'] = 'Lisää kohde';
$lang['user']['spamfilter_green'] = 'Vihreä: Tämä viesti ei ole roska postia';
$lang['user']['spamfilter_yellow'] = 'Keltainen: Tämä viesti voi olla roska postia, merkitään roska postiksi ja siirretään roska posti kansioon';
$lang['user']['spamfilter_red'] = 'Punainen: Tämä viesti on roska postia ja palvelin hylkää sen';
$lang['user']['spamfilter_default_score'] = 'Oletusarvot';
$lang['user']['spamfilter_hint'] = 'Ensimmäinen arvo kuvaa "alhaista spam pistettä", ja toinen edustaa "korkeaa roska postin pistettä".';
$lang['user']['spamfilter_table_domain_policy'] = "n/a (verkkotunnus alueen käytäntö)";
$lang['user']['waiting'] = "Odottaa";
$lang['user']['status'] = "Tila";
$lang['user']['running'] = "Käynnissä";

$lang['user']['tls_policy_warning'] = '<strong>Varoitus:</strong> Jos päätät valvoa salatun sähköpostin siirtoa, saatat menettää sähkö posteja.<br>Viestit, jotka eivät tyydytä käytäntöä, ( bounced)  sähkö posti järjestelmän kanssa vaikealla epäonnistumalla.<br>Tämä asetus koskee ensisijaista sähkö posti osoitettasi (kirjautumisnimi), kaikki osoitteet johdettu alias domainit sekä alias osoitteet <b>vain tämä yksittäinen posti laatikko</b> kohteena.';
$lang['user']['tls_policy'] = 'Salaus käytäntö';
$lang['user']['tls_enforce_in'] = 'Pakota TLS saapuva';
$lang['user']['tls_enforce_out'] = 'Pakota TLS-lähtevä';
$lang['user']['no_record'] = 'Ei tietuetta';


$lang['user']['tag_handling'] = 'Merkittyjen Sähkö posti viestien käsittelyn määrittäminen';
$lang['user']['tag_in_subfolder'] = 'Alikansioon';
$lang['user']['tag_in_subject'] = 'Aiheen';
$lang['user']['tag_in_none'] = 'Ei mitään';
$lang['user']['tag_help_explain'] = 'Alikansio: uusi tunnisteen jälkeen nimetty alikansio luodaan Saapuneet-kansion alapuolelle ("Saapuneet/Facebook").<br>
Aihe: tunnisteiden nimi lisätään sähkö postit-aiheeseen, esimerkiksi: "[Facebook] My News".';
$lang['user']['tag_help_example'] = 'Esimerkki merkitylle Sähkö posti osoitteeksi: me<b>+Facebook</b>@example.org';

$lang['user']['eas_reset'] = 'Tyhjennä ActiveSync-laitteen väli muisti';
$lang['user']['eas_reset_now'] = 'Tyhjennä';
$lang['user']['eas_reset_help'] = 'Monissa tapauksissa laitteen väli muistin nollaaminen auttaa palauttamaan rikki menneen ActiveSync-profiilin.<br><b>Huomio:</b> Kaikki elementit ladataan uudelleen!';

$lang['user']['sogo_profile_reset'] = ' Tyhjennä SOGo - Webmail profile';
$lang['user']['sogo_profile_reset_now'] = 'Tyhjennä profiili';
$lang['user']['sogo_profile_reset_help'] = 'Tämä tuhoaa käyttäjän SOGo Webmail-profiilin ja <b>poistaa kaikki yhteys-ja kalenteri tiedot..</b>.';

$lang['user']['encryption'] = 'Salaus';
$lang['user']['username'] = 'Käyttäjätunnus';
$lang['user']['last_run'] = 'Viimeisin suoritus';
$lang['user']['excludes'] = 'Sulkee pois';
$lang['user']['interval'] = 'Aikaväli';
$lang['user']['active'] = 'Aktiivinen';
$lang['user']['action'] = 'Toiminnot';
$lang['user']['edit'] = 'Muokkaa';
$lang['user']['remove'] = 'Poistaa';
$lang['user']['create_syncjob'] = 'Luo uusi synkronointi työ';

$lang['start']['mailcow_apps_detail'] = 'Käytä mailcow-sovellusta, kun haluat käyttää sähköposteja, kalenteria, yhteys tietoja ja paljon muuta';
$lang['start']['mailcow_panel_detail'] = '<b>Verkkotunnus alueen järjestelmänvalvojat</b> Luo, muokkaa tai poistaa posti laatikoita ja aliaksia, muita verkko tunnuksia ja lue lisä tietoja niiden määritetyista verkko tunnuksista.<br>
<b>Posti laatikon käyttäjät</b> pystyvät luomaan rajoitetun ajan aliaksia (roska posti asetuksia), muuttamaan salasanaa ja roska posti suodattimen asetuksia.';
$lang['start']['imap_smtp_server_auth_info'] = 'Käytä täydellistä sähkö posti osoitetta ja tavallista todennus mekanismia.<br>
Palvelin puolen pakollinen salaus salaa kirjautumistietosi.';
$lang['start']['help'] = 'Näytä/Piilota help paneeli';
$lang['header']['mailcow_settings'] = 'Kokoonpano';
$lang['header']['administration'] = 'Kokoonpanon & tiedot';
$lang['header']['mailboxes'] = 'Verkkotunnuksien asetukset';
$lang['header']['user_settings'] = 'Käyttäjän asetukset';
$lang['header']['quarantine'] = "Karanteeni";
$lang['header']['debug'] = "Järjestelmä tiedot";
$lang['quarantine']['disabled_by_config'] = "Nykyinen järjestelmän kokoonpano poistaa karanteeni toiminnot käytöstä.";
$lang['mailbox']['tls_policy_maps'] = 'TLS-käytännön asetukset';
$lang['mailbox']['tls_policy_maps_long'] = 'Lähtevän TLS-käytäntöjen asetuksien ohitukset';
$lang['mailbox']['tls_policy_maps_info'] = 'Nämä käytännön asetukset ohittavat lähtevät TLS-kuljetus säännöt riippumatta käyttäjien TLS-käytäntö asetuksista.<br>
  Tarkista <a href="http://www.postfix.org/postconf.5.html#smtp_tls_policy_maps" target="_blank">the "smtp_tls_policy_maps" docs</a> lisä tietoja.';
$lang['mailbox']['tls_enforce_in'] = 'Pakota TLS saapuva';
$lang['mailbox']['tls_enforce_out'] = 'Pakota TLS-lähtevä';
$lang['mailbox']['tls_map_dest'] = 'Kohde';
$lang['mailbox']['tls_map_dest_info'] = 'Esimerkkejä: example.org,. example.org, [Mail. example. org]: 25';
$lang['mailbox']['tls_map_policy'] = 'Käytäntö';
$lang['mailbox']['tls_map_parameters'] = 'Parametrit';
$lang['mailbox']['tls_map_parameters_info'] = 'Tyhjät tai parametrit, esimerkiksi: protokollat=!SSLv2 ciphers=medium exclude=3DES';
$lang['mailbox']['booking_0'] = 'Näytä aina vapaana';
$lang['mailbox']['booking_lt0'] = 'Rajoittamaton, mutta näytä varattu, kun varattu';
$lang['mailbox']['booking_custom'] = 'Rajoitettu määrä varauksia mukautettuun määrään';
$lang['mailbox']['booking_0_short'] = 'Aina ilmainen';
$lang['mailbox']['booking_lt0_short'] = 'Pehmeä rajoitus';
$lang['mailbox']['booking_custom_short'] = 'Kova raja';
$lang['mailbox']['domain'] = 'Verkkotunnukset';
$lang['mailbox']['spam_aliases'] = 'Temp. alias';
$lang['mailbox']['multiple_bookings'] = 'Useita varauksia';
$lang['mailbox']['kind'] = 'Sellainen';
$lang['mailbox']['description'] = 'Kuvaus';
$lang['mailbox']['alias'] = 'Alias';
$lang['mailbox']['aliases'] = 'Sähköposti tilien aliakset';
$lang['mailbox']['domains'] = 'Verkkotunnukset';
$lang['admin']['domain'] = 'Verkkotunnukset';
$lang['admin']['domain_s'] = 'Verkkotunnukset';
$lang['mailbox']['mailboxes'] = 'Sähköposti tilit';
$lang['mailbox']['mailbox'] = 'Postilaatikko';
$lang['mailbox']['resources'] = 'Resursseja';
$lang['mailbox']['mailbox_quota'] = 'Kiintiön koko';
$lang['mailbox']['domain_quota'] = 'Kiintiö';
$lang['mailbox']['active'] = 'Aktiivinen';
$lang['mailbox']['action'] = 'Toiminnot';
$lang['mailbox']['backup_mx'] = 'Varmuuskopiointi MX';
$lang['mailbox']['domain_aliases'] = 'Domain alueiden aliakset';
$lang['mailbox']['target_domain'] = 'Kohde verkkotunnus alue';
$lang['mailbox']['target_address'] = 'Siiretty osoitteseen';
$lang['mailbox']['username'] = 'Käyttäjätunnus';
$lang['mailbox']['fname'] = 'Koko nimi';
$lang['mailbox']['filter_table'] = 'Suodata taulu';
$lang['mailbox']['yes'] = '&#10003;';
$lang['mailbox']['no'] = '&#10005;';
$lang['mailbox']['in_use'] = 'Käytössä (%)';
$lang['mailbox']['msg_num'] = 'Viestejä #';
$lang['mailbox']['remove'] = 'Poista';
$lang['mailbox']['edit'] = 'Muokkaa';
$lang['mailbox']['no_record'] = 'Ei tietuetta objektille %s';
$lang['mailbox']['no_record_single'] = 'Ei tietuetta';
$lang['mailbox']['add_domain'] = 'Lisää verkkotunnus alue';
$lang['mailbox']['add_domain_alias'] = 'Lisää verkkotunnus alueen alias';
$lang['mailbox']['add_mailbox'] = 'Lisää postilaatikko';
$lang['mailbox']['add_resource'] = 'Lisää resurssi';
$lang['mailbox']['add_alias'] = 'Lisää alias';
$lang['mailbox']['add_domain_record_first'] = 'Lisää ensin verkkotunnus alue';
$lang['mailbox']['empty'] = 'Ei tuloksia';
$lang['mailbox']['toggle_all'] = 'Valitse kaikki';
$lang['mailbox']['quick_actions'] = 'Toiminnot';
$lang['mailbox']['activate'] = 'Aktivoi';
$lang['mailbox']['deactivate'] = 'Deaktivoi';
$lang['mailbox']['owner'] = 'Omistaja';
$lang['mailbox']['mins_interval'] = 'Aikaväli (min)';
$lang['mailbox']['last_run'] = 'Viimeisin suoritus';
$lang['mailbox']['excludes'] = 'Sulkea pois';
$lang['mailbox']['last_run_reset'] = 'Ajoita seuraava';
$lang['mailbox']['sieve_info'] = 'Voit tallentaa useita suodattimia käyttäjää kohden, mutta vain yksi esisuodatin ja yksi postfilter voivat olla aktiivisia samanaikaisesti<br>
Kukin suodatin käsitellään kuvatussa järjestyksessä. Epäonnistunut komento sarja tai annettu "Keep;" ei lopeta muiden komento sarjojen käsittelyä.<br>
<a href="https://github.com/mailcow/mailcow-dockerized/blob/master/data/conf/dovecot/global_sieve_before" target="_blank">Yleinen sieve esisuodatin</a> → Esisuodatin → Käyttäjän komento sarjat → Postfilter → <a href="https://github.com/mailcow/mailcow-dockerized/blob/master/data/conf/dovecot/global_sieve_after" target="_blank">Yleisset sieve postfilterit</a>';
$lang['info']['no_action'] = 'Mitään toimenpidettä ei voida soveltaa';


$lang['edit']['sogo_visible'] = 'Alias näkyy SOGo-kohteessa';
$lang['edit']['sogo_visible_info'] = 'Tämä asetus vaikuttaa vain objekteihin, jotka voidaan näyttää SOGo (jaetut tai ei-jaetut alias-osoitteet, jotka viittaavat vähintään yhteen paikalliseen posti laatikkoon).';
$lang['mailbox']['sogo_visible'] = 'Alias näkyy SOGo-kohteessa';
$lang['mailbox']['sogo_visible_y'] = 'Näytä alias SOGo-kohteessa';
$lang['mailbox']['sogo_visible_n'] = 'Piilota alias SOGo-kohteessa';
$lang['edit']['syncjob'] = 'Muokkaa synkronointi työtä';
$lang['edit']['hostname'] = 'Hostname';
$lang['edit']['encryption'] = 'Salaus';
$lang['edit']['maxage'] = 'Viestien enimmäisikä päivinä, jotka lähetetään kyselyllä etänä<br><small>(0 = ignore age)</small>';
$lang['edit']['maxbytespersecond'] = 'Maksimi. tavua sekunnissa <br><small>(0 = rajoittamaton)</small>';
$lang['edit']['automap'] = 'Yritä yhdistää kansioita automaattisesti("Lähetetyt kohteet", "Lähetti" => "Lähetti" etc.)';
$lang['edit']['skipcrossduplicates'] = 'Ohita päällekkäiset viestit kansioiden välillä (ensimmäinen tulee, ensimmäinen palvella)';
$lang['add']['automap'] = 'Yritä yhdistää kansioita automaattisesti("Lähetetyt kohteet", "Lähetti" => "Lähetti" etc.)';
$lang['add']['skipcrossduplicates'] = 'Ohita päällekkäiset viestit kansioiden välillä (ensimmäinen tulee, ensimmäinen palvella)';
$lang['edit']['subfolder2'] = 'Synkronoi kohteen alikansioon<br><small>(tyhjä = älä käytä alikansioon)</small>';
$lang['edit']['mins_interval'] = 'Aikaväli (min)';
$lang['edit']['exclude'] = 'Objektien jättäminen pois (regex)';
$lang['edit']['save'] = 'Tallenna muutokset';
$lang['edit']['username'] = 'Käyttäjätunnus';
$lang['edit']['max_mailboxes'] = 'Maksimi. mahdolliset posti laatikot';
$lang['edit']['title'] = 'Muokkaa objektia';
$lang['edit']['target_address'] = 'Siirry osoitteeseen/es <small>(pilkulla-erotettu)</small>';
$lang['edit']['active'] = 'Aktiivinen';
$lang['edit']['gal'] = 'Yleinen osoite luettelo';
$lang['add']['gal'] = 'Yleinen osoite luettelo';
$lang['edit']['gal_info'] = 'GAL ( kalenteri ) sisältää kaikki verkkotunnus alueen objektit, eikä yksikään käyttäjä voi muokata sitä. Vapaat ja varatut ajat-tiedot SOGosta puuttuu, jos se on poistettu käytöstä! <b>Uudelleen käynnistä SOGo ja ota muutokset käyttöön.</b>';
$lang['add']['gal_info'] = 'GAL ( kalenteri ) sisältää kaikki verkkotunnus alueen objektit, eikä yksikään käyttäjä voi muokata sitä. Vapaat ja varatut ajat-tiedot SOGosta puuttuu, jos se on poistettu käytöstä! <b>Uudelleen käynnistä SOGo ja ota muutokset käyttöön.</b>';
$lang['edit']['force_pw_update'] = 'Pakota salasanan vaihto seuraavan sisään kirjautumisen jälkeen';
$lang['edit']['force_pw_update_info'] = 'Tämä käyttäjä voi kirjautua vain mailcow UI - hallintaan.';
$lang['edit']['sogo_access'] = 'Myönnä pääsy SOGo-kohteeseen';
$lang['edit']['sogo_access_info'] = 'Myönnä tai salli pääsy SOGo-palveluun. Tämä asetus ei vaikuta pääsyä muihin palveluihin eikä se poista tai muuta käyttäjiä jo olemassa SOGo profiilissa';
$lang['edit']['target_domain'] = 'Kohde verkkotunnus alue';
$lang['edit']['password'] = 'Salasana';
$lang['edit']['password_repeat'] = 'Vahvista salasana (anna uudelleen syöttämäsi salasana)';
$lang['edit']['domain_admin'] = 'Muokkaa verkkotunnus alueen järjestelmänvalvojaa';
$lang['edit']['domain'] = 'Muokkaa verkkotunnus aluetta';
$lang['edit']['edit_alias_domain'] = 'Alias-verkkotunnus alueen muokkaaminen';
$lang['edit']['domains'] = 'Verkkotunnukset';
$lang['edit']['alias'] = 'Muokkaa aliasta';
$lang['edit']['mailbox'] = 'Muokkaa sähköposti tiliä';
$lang['edit']['description'] = 'Kuvaus';
$lang['edit']['max_aliases'] = 'Maks. Aliaksia';
$lang['edit']['max_quota'] = 'Maks. Kiintiö sähköposti laatikkoa kohden (MiB)';
$lang['edit']['domain_quota'] = 'Verkkotunnus alueen kiintiö';
$lang['edit']['backup_mx_options'] = 'Varmuus kopioinnin MX-asetukset';
$lang['edit']['relay_domain'] = 'Välitys verkkotunnus alue';
$lang['edit']['relay_all'] = 'Välittää kaikki vastaanottajat';
$lang['edit']['relay_all_info'] = '<small>Jos valitset <b>ei</b> jos haluat välittää kaikki vastaanottajat, sinun on lisättävä ("sokea") posti laatikko jokaiselle vastaanottajalle, joka on tarkoitus välittää.</small>';
$lang['edit']['full_name'] = 'Koko nimi';
$lang['edit']['quota_mb'] = 'Kiintiö (MiB)';
$lang['edit']['sender_acl'] = 'Salli lähettää nimellä';
$lang['edit']['sender_acl_disabled'] = '↳ <span class="label label-danger">Lähettäjän tarkistus on poistettu käytöstä</span>';
$lang['user']['sender_acl_disabled'] = '<span class="label label-danger">Lähettäjän tarkistus on poistettu käytöstä</span>';
$lang['edit']['previous'] = 'Edellinen sivu';
$lang['edit']['unchanged_if_empty'] = 'Jos muuttumaton jätä tyhjäksi';
$lang['edit']['dont_check_sender_acl'] = "Poista lähettäjän verkkotunnus alueen tarkistus käytöstä %s (+ alias-verkkotunnukset)";
$lang['edit']['multiple_bookings'] = 'Useita varauksia';
$lang['edit']['kind'] = 'Kiltti';
$lang['edit']['resource'] = 'Resurssi';
$lang['edit']['relayhost'] = 'Lähettäjä riippuvainen kuljetus';
$lang['edit']['public_comment'] = 'Julkinen kommentti';
$lang['mailbox']['public_comment'] = 'Julkinen kommentti';
$lang['edit']['private_comment'] = 'Yksityinen kommentti';
$lang['mailbox']['private_comment'] = 'Yksityinen kommentti';
$lang['edit']['comment_info'] = 'Yksityinen kommentti ei ole näkyvissä käyttäjälle, kun taas julkinen kommentti näytetään työkaluvihjeenä siirtäessäsi sitä käyttäjän katsaukseen';
$lang['add']['public_comment'] = 'Julkinen kommentti';
$lang['add']['private_comment'] = 'Yksityinen kommentti';
$lang['add']['comment_info'] = 'Yksityinen kommentti ei ole näkyvissä käyttäjälle, kun taas julkinen kommentti näytetään työkaluvihjeenä siirtäessäsi sitä käyttäjän katsaukseen';
$lang['acl']['spam_alias'] = 'Väliaikaiset aliakset';
$lang['acl']['tls_policy'] = 'TLS-käytäntö';
$lang['acl']['spam_score'] = 'Roskapostitulos';
$lang['acl']['spam_policy'] = 'Musta lista / sallitut lista';
$lang['acl']['delimiter_action'] = 'Rajoittimen toiminta';
$lang['acl']['syncjobs'] = 'Synkronoi työt';
$lang['acl']['eas_reset'] = 'Nollaa EAS-laitteet';
$lang['acl']['sogo_profile_reset'] = 'Nollaa SOGo-profiili';
$lang['acl']['quarantine'] = 'Karanteenitoimet';
$lang['acl']['quarantine_notification'] = 'Muuta karanteeni-ilmoituksia';
$lang['acl']['quarantine_attachments'] = 'Karanteeniliitteet';
$lang['acl']['alias_domains'] = 'Lisää alias-verkkotunnukset';
$lang['acl']['login_as'] = 'Kirjaudu sisään sähkö postilaatikon käyttäjänä';
$lang['acl']['bcc_maps'] = 'BCC-kartat';
$lang['acl']['filters'] = 'Suodattimet';
$lang['acl']['ratelimit'] = 'Määrä raja';
$lang['acl']['recipient_maps'] = 'Vastaanottajakartat';
$lang['acl']['unlimited_quota'] = 'Rajoittamaton kiintiö sähkö postilaatikoille';
$lang['acl']['extend_sender_acl'] = 'Anna laajentaa lähettäjän ACL ulkoisilla osoitteilla';
$lang['acl']['prohibited'] = 'ACL on kieltänyt sen';
$lang['acl']['sogo_access'] = 'Salli SOGo-pääsyn hallintaan';

$lang['edit']['extended_sender_acl'] = 'Ulkoisten lähettäjien osoitteet';
$lang['edit']['extended_sender_acl_info'] = 'DKIM-verkkotunnuksen avain tulisi tuoda, jos se on käytettävissä.<br>
Muista lisätä tämä palvelin vastaavaan SPF TXT-tietueeseen.<br>
Aina kun verkkotunnus tai aliaksen verkkotunnus lisätään tähän palvelimeen, joka on päällekkäinen ulkoisen osoitteen kanssa, ulkoinen osoite poistetaan.<br>
Käytä @ domain.tld antaaksesi lähettää muodossa *@domain.tld.';
$lang['edit']['sender_acl_info'] = 'Jos postilaatikon käyttäjän A sallitaan lähettävän postilaatikon käyttäjäksi B, lähettäjän osoitetta ei näytetä automaattisesti valittavana "alkaen" -kentässä SOGossa.<br>
Sähkö postilaatikon käyttäjän A on luotava valtuutus SOGoon, jotta sähkö postilaatikon käyttäjä b voi valita osoitteen lähettäjäksi. Tämä käyttäytyminen ei koske alias-osoitteita';

$lang['mailbox']['quarantine_notification'] = 'Karanteeni-ilmoitukset';
$lang['mailbox']['never'] = 'Ei koskaan';
$lang['mailbox']['hourly'] = 'Tunnin välein';
$lang['mailbox']['daily'] = 'Päivittäin';
$lang['mailbox']['weekly'] = 'Viikoittain';
$lang['user']['quarantine_notification'] = 'Karanteeni-ilmoitukset';
$lang['user']['never'] = 'Ei koskaan';
$lang['user']['hourly'] = 'Tunnin välein';
$lang['user']['daily'] = 'Päivittäin';
$lang['user']['weekly'] = 'Viikoittain';
$lang['user']['quarantine_notification_info'] = 'Kun ilmoitus on lähetetty, nimikkeet merkitään "Ilmoituksen" lisä ilmoituksia ei lähetetä tälle tietylle nimikkeelle.';

$lang['add']['generate'] = 'Tuota';
$lang['add']['syncjob'] = 'Lisää synkronointityö';
$lang['add']['syncjob_hint'] = 'Huomaa, että salasanat on tallennettava pelkkänä tekstinä!';
$lang['add']['hostname'] = 'Host';
$lang['add']['destination'] = 'Määränpää';
$lang['add']['nexthop'] = 'Seuraava pysäkki';
$lang['edit']['nexthop'] = 'Seuraava pysäkki';
$lang['add']['port'] = 'Portti';
$lang['add']['username'] = 'Käyttäjätunnus';
$lang['add']['enc_method'] = 'Salausmenetelmä';
$lang['add']['mins_interval'] = 'Äänestysväli (minuutit)';
$lang['add']['exclude'] = 'Sulje pois objects (regex)';
$lang['add']['delete2duplicates'] = 'Poista päällekkäisyydet määräpaikasta';
$lang['add']['delete1'] = 'Poista lähteestä, kun se on valmis';
$lang['add']['delete2'] = 'Poista määränpään viestit, joita ei ole lähteessä';
$lang['add']['custom_params'] = 'Muokatut parametrit';
$lang['add']['custom_params_hint'] = 'Oikea: --param=xy, väärä: --param xy';
$lang['add']['subscribeall'] = 'Tilaa kaikki kansiot';
$lang['add']['timeout1'] = 'Yhteyden aikakatkaisu etäisäntään';
$lang['add']['timeout2'] = 'Aikakatkaisu yhteyden muodostamiseen paikalliseen isäntään';
$lang['edit']['timeout1'] = 'Yhteyden aikakatkaisu etäisäntään';
$lang['edit']['timeout2'] = 'Aikakatkaisu yhteyden muodostamiseen paikalliseen isäntään';

$lang['edit']['delete2duplicates'] = 'Poista päällekkäisyydet määräpaikasta';
$lang['edit']['delete1'] = 'Poista lähteestä, kun se on valmis';
$lang['edit']['delete2'] = 'Poista määränpään viestit, joita ei ole lähteessä';

$lang['add']['domain_matches_hostname'] = 'Verkkotunnus %s vastaa isäntänimeä';
$lang['add']['domain'] = 'Verkkotunnus';
$lang['add']['active'] = 'Aktiivinen';
$lang['add']['multiple_bookings'] = 'Useita varauksia';
$lang['add']['description'] = 'Kuvaus';
$lang['add']['max_aliases'] = 'Max. mahdolliset aliakset';
$lang['add']['max_mailboxes'] = 'Max. mahdolliset sähkö postilaatikot';
$lang['add']['mailbox_quota_m'] = 'Max. kiintiö sähkö postilaatikkoa kohti (MiB)';
$lang['add']['domain_quota_m'] = 'Verkkotunnuksen kokonaiskiintiö (MiB)';
$lang['add']['backup_mx_options'] = 'Varmuuskopioi MX-asetukset';
$lang['add']['relay_all'] = 'Välitä kaikki vastaanottajat';
$lang['add']['relay_domain'] = 'Välitä tämä verkkotunnus';
$lang['add']['relay_all_info'] = '<small>Jos valitset <b>ei</b> jos haluat välittää kaikki vastaanottajat, sinun on lisättävä ("sokea") posti laatikko jokaiselle vastaanottajalle, joka on tarkoitus välittää.</small>';
$lang['add']['alias_address'] = 'Alias osoite';
$lang['add']['alias_address_info'] = '<small>Koko sähköpostiosoite tai @ esimerkki.com, jotta kaikki verkkotunnuksen viestit saadaan (pilkuilla erotetut). <b>mailcow vain verkkotunnukset</b>.</small>';
$lang['add']['alias_domain_info'] = '<small>Vain kelvolliset verkkotunnukset (pilkuin erotetut).</small>';
$lang['add']['target_address'] = 'Siirry osoitteisiin';
$lang['add']['target_address_info'] = '<small>Koko sähköpostiosoite (pilkuilla erotettu).</small>';
$lang['add']['alias_domain'] = 'Alias-verkkotunnus';
$lang['add']['select'] = 'Ole hyvä ja valitse...';
$lang['add']['target_domain'] = 'Kohdetunnus';
$lang['add']['kind'] = 'Kiltti';
$lang['add']['mailbox_username'] = 'Käyttäjätunnus (sähköpostiosoitteen osa)';
$lang['add']['full_name'] = 'Koko nimi';
$lang['add']['quota_mb'] = 'Kiintiö (MiB)';
$lang['add']['select_domain'] = 'Valitse ensin verkkotunnus';
$lang['add']['password'] = 'Salasana';
$lang['add']['password_repeat'] = 'Vahvista salasana (anna uudelleen salasanasi)';
$lang['add']['restart_sogo_hint'] = 'Sinun on käynnistettävä SOGo-palvelusäiliö uudelleen, kun olet lisännyt uuden verkkotunnuksen!';
$lang['add']['goto_null'] = 'Hylkää posti hiljaa';
$lang['add']['goto_ham'] = 'Opi nimellä <span class="text-success"><b>ham</b></span>';
$lang['add']['goto_spam'] = 'Opi nimellä <span class="text-danger"><b>spam</b></span>';
$lang['add']['validation_success'] = 'Vahvistettu onnistuneesti';
$lang['add']['activate_filter_warn'] = 'Kaikki muut suodattimet deaktivoidaan, kun aktiivinen on valittu.';
$lang['add']['validate'] = 'Vahvista';
$lang['mailbox']['add_filter'] = 'Lisää suodatin';
$lang['add']['sieve_desc'] = 'Lyhyt kuvaus';
$lang['edit']['sieve_desc'] = 'Lyhyt kuvaus';
$lang['add']['sieve_type'] = 'Suodattimen tyyppi';
$lang['edit']['sieve_type'] = 'Suodattimen tyyppi';
$lang['mailbox']['set_prefilter'] = 'Merkitse esisuodattimeksi';
$lang['mailbox']['set_postfilter'] = 'Merkitse esisuodattimeksi';
$lang['mailbox']['filters'] = 'Suodattimet';
$lang['mailbox']['sync_jobs'] = 'Synkronoi työt';
$lang['mailbox']['inactive'] = 'Epäaktiivinen';
$lang['edit']['validate_save'] = 'Vahvista ja tallenna';


$lang['login']['username'] = 'Käyttäjätunnus';
$lang['login']['password'] = 'Salasana';
$lang['login']['login'] = 'Kirjaudu';
$lang['login']['delayed'] = 'Kirjautuminen viivästyi %s sekunttia.';

$lang['tfa']['tfa'] = "Kaksiosainen todennus";
$lang['tfa']['set_tfa'] = "Määritä kaksiosainen todennus menetelmä";
$lang['tfa']['yubi_otp'] = "Yubico OTP-todennus";
$lang['tfa']['key_id'] = "Tunniste YubiKey";
$lang['tfa']['init_u2f'] = "Alustetaan, odota...";
$lang['tfa']['start_u2f_validation'] = "Aloita oikeellisuus tarkistus";
$lang['tfa']['reload_retry'] = "- (Lataa selain uudelleen, jos virhe toistuu)";
$lang['tfa']['key_id_totp'] = "Avaimen tunnus";
$lang['tfa']['error_code'] = "Virhekoodi";
$lang['tfa']['api_register'] = 'mailcow käyttää Yubico Cloud API. Saat avaimesi API-avaimen <a href="https://upgrade.yubico.com/getapikey/" target="_blank">täältä</a>';
$lang['tfa']['u2f'] = "U2F todennus";
$lang['tfa']['none'] = "Poista";
$lang['tfa']['delete_tfa'] = "Poista TFA käytöstä";
$lang['tfa']['disable_tfa'] = "Poista TFA käytöstä seuraavaan onnistuneen kirjautumisen jälkeen";
$lang['tfa']['confirm'] = "Vahvista";
$lang['tfa']['totp'] = "Aikapohjainen OTP (Google Authenticator, Authy jne.)";
$lang['tfa']['select'] = "Valitse";
$lang['tfa']['waiting_usb_auth'] = "<i>Odotetaan USB-laitetta...</i><br><br>Napauta painiketta U2F USB-laitteessa nyt";
$lang['tfa']['waiting_usb_register'] = "<i>Odotetaan USB-laitetta...</i><br><br>Anna salasanasi yltä ja vahvista U2F-rekisteröinti napauttamalla painiketta U2F USB-laitteessa.";
$lang['tfa']['scan_qr_code'] = "Tarkista seuraava koodi Authenticator-sovelluksella tai Syötä koodi manuaalisesti.";
$lang['tfa']['enter_qr_code'] = "TOTP-koodisi, jos laitteesi ei pysty tarkistamaan QR-koodeja";
$lang['tfa']['confirm_totp_token'] = "Vahvista tekemäsi muutokset syöttämällä luotu tunnus";

$lang['admin']['rspamd-com_settings'] = '<a href="https://rspamd.com/doc/configuration/settings.html#settings-structure" target="_blank">Rspamd docs</a>
  - Asetus nimi luodaan automaattisesti, Katso esimerkki esiasetukset alla.';

$lang['admin']['no_new_rows'] = 'Muita rivejä ei ole käytettävissä';
$lang['admin']['queue_manager'] = 'Jonon hallinta';
$lang['admin']['additional_rows'] = ' lisä rivejä lisättiin'; // parses to 'n additional rows were added'
$lang['admin']['private_key'] = 'Yksityinen avain';
$lang['admin']['import'] = 'Tuo';
$lang['admin']['duplicate'] = 'Kaksoiskappale';
$lang['admin']['import_private_key'] = 'Tuo yksityinen avain';
$lang['admin']['duplicate_dkim'] = 'DKIM-tietueen kaksoiskappale';
$lang['admin']['dkim_from'] = 'Lähettäjä';
$lang['admin']['dkim_to'] = 'Kenelle';
$lang['admin']['dkim_from_title'] = 'Lähde verkkotunnus alue, josta tiedot kopioidaan';
$lang['admin']['dkim_to_title'] = 'Kohde verkkotunnus alue/s-Korvataan';
$lang['admin']['f2b_parameters'] = 'Fail2ban parametrit';
$lang['admin']['f2b_ban_time'] = 'Ban aika (s)';
$lang['admin']['f2b_max_attempts'] = 'Maksmi. Yritykset';
$lang['admin']['f2b_retry_window'] = 'Yritä uudelleen-ikkuna (s) Max. Yrittää';
$lang['admin']['f2b_netban_ipv4'] = 'IPv4 aliverkon koko kiellon soveltamiseksi (8-32)';
$lang['admin']['f2b_netban_ipv6'] = 'IPv6 aliverkon koko kiellon soveltamiseksi (8-128)';
$lang['admin']['f2b_whitelist'] = 'Sallitut verkot/isännät';
$lang['admin']['f2b_blacklist'] = 'Mustalla listalla verkot/isännät';
$lang['admin']['f2b_list_info'] = 'Mustalla listalla oleva isäntä tai verkko on aina suurempi kuin sallitut-entiteetti. <b>Luettelon päivitykset otetaan käyttöön muutaman sekunnin kuluttua.</b>';
$lang['admin']['search_domain_da'] = 'Etsi verkko tunnuksia';
$lang['admin']['r_inactive'] = 'Passiiviset rajoitukset';
$lang['admin']['r_active'] = 'Aktiiviset rajoitukset';
$lang['admin']['r_info'] = 'Aktiivisten rajoitusten luettelossa harmaana olevat/käytöstä poistetut elementit eivät ole MailCow kelvollisia rajoituksia, eikä niitä voi siirtää.Tuntemattomat rajoitukset asetetaan joka tapa uksessa ulkonäön mukaisessa järjestyksessä <br>Voit lisätä uusia elementtejä <code>inc/vars.local.inc.php</code> voi vaihtaa niitä.';
$lang['admin']['dkim_key_length'] = 'DKIM avaimen pituus (bits)';
$lang['admin']['dkim_key_valid'] = 'Avain kelpaa';
$lang['admin']['dkim_key_unused'] = 'Avain käyttämätön';
$lang['admin']['dkim_key_missing'] = 'Avain puuttuu';
$lang['admin']['dkim_add_key'] = 'Lisää ARC/DKIM-avain';
$lang['admin']['dkim_keys'] = 'ARC/DKIM avain';
$lang['admin']['dkim_private_key'] = 'Yksityinen avain';
$lang['admin']['dkim_domains_wo_keys'] = "Valitse verkkotunnus alueet joilta puuttuu avaimet";
$lang['admin']['dkim_domains_selector'] = "Valitsin";
$lang['admin']['add'] = 'Lisää';
$lang['add']['add_domain_restart'] = 'Lisää verkkotunnus alue ja käynnistä SOGo uudelleen';
$lang['add']['add_domain_only'] = 'Lisää vain verkkotunnus alue';
$lang['admin']['configuration'] = 'Asetukset';
$lang['admin']['password'] = 'Salasana';
$lang['admin']['password_repeat'] = 'Vahvista salasana (anna salasanasi uudelleen)';
$lang['admin']['active'] = 'Aktiivinen';
$lang['admin']['inactive'] = 'Passiivinen';
$lang['admin']['action'] = 'Toiminnot';
$lang['admin']['add_domain_admin'] = 'Lisää varkkotunnus järjestelmänvalvoja';
$lang['admin']['add_admin'] = 'Lisää järjestelmänvalvoja';
$lang['admin']['add_settings_rule'] = 'Lisää asetukset-sääntö';
$lang['admin']['rsetting_desc'] = 'Lyhyt kuvaus';
$lang['admin']['rsetting_content'] = 'Säännön sisältö';
$lang['admin']['rsetting_none'] = 'Sääntöjä ei ole käytettävissä';
$lang['admin']['rsetting_no_selection'] = 'Valitse sääntö';
$lang['admin']['rsettings_preset_1'] = 'Poista käytöstä kaikki paitsi DKIM-ja Rate Limit-oikeudet todennetuille käyttäjille';
$lang['admin']['rsettings_preset_2'] = 'Postimaisteri haluaa roska postia';
$lang['admin']['rsettings_insert_preset'] = 'Lisää esimerkki esimääritetty "%s"';
$lang['admin']['rsetting_add_rule'] = 'Lisää sääntö';
$lang['admin']['queue_ays'] = 'Vahvista, että haluat poistaa kaikki nykyisen jonon kohteet.';
$lang['admin']['arrival_time'] = 'Saapumisaika (palvelimen aika)';
$lang['admin']['message_size'] = 'Viestin koko';
$lang['admin']['sender'] = 'Lähettäjä';
$lang['admin']['recipients'] = 'Vastaanottajat';
$lang['admin']['admin_domains'] = 'Verkkotunnus alueen määritykset';
$lang['admin']['domain_admins'] = 'Verkkotunnuksien järjestelmänvalvojat';
$lang['admin']['flush_queue'] = 'Tyhjennä jono';
$lang['admin']['delete_queue'] = 'Poista kaikki';
$lang['admin']['queue_deliver_mail'] = 'Toimittaa';
$lang['admin']['queue_hold_mail'] = 'Pidossa';
$lang['admin']['queue_unhold_mail'] = 'Poista pidosta';
$lang['admin']['username'] = 'Käyttäjätunnus';
$lang['admin']['edit'] = 'Muokata';
$lang['admin']['remove'] = 'Poista';
$lang['admin']['save'] = 'Tallenna muutokset';
$lang['admin']['admin'] = 'Järjestelmänvalvoja';
$lang['admin']['admin_details'] = 'Muokkaa järjestelmän ylläpitäjien tietoja';
$lang['admin']['unchanged_if_empty'] = 'Jollei muutoksia, jätä tyhjäksi';
$lang['admin']['yes'] = '&#10003;';
$lang['admin']['no'] = '&#10005;';
$lang['admin']['access'] = 'Hallinta';
$lang['admin']['no_record'] = 'Merkintöjä ei löydy';
$lang['admin']['filter_table'] = 'Suodata taulukko';
$lang['admin']['empty'] = 'Ei tuloksia';
$lang['admin']['time'] = 'Aika';
$lang['admin']['last_applied'] = 'Viimeksi käytetty';
$lang['admin']['reset_limit'] = 'Poista hash';
$lang['admin']['hash_remove_info'] = 'Nopeusrajasiirron (jos se on edelleen olemassa) poistaminen nollaa laskurinsa kokonaan.<br>
Jokainen hash on merkitty yksittäisellä värillä.';
$lang['warning']['hash_not_found'] = 'Hash ei löydy tai se on jo poistettu';
$lang['success']['hash_deleted'] = 'Hash poistettu';
$lang['admin']['authed_user'] = 'tunnistautunut käyttäjä';
$lang['admin']['priority'] = 'Prioriteetti';
$lang['admin']['message'] = 'Viesti';
$lang['admin']['rate_name'] = 'Rate nimi';
$lang['admin']['refresh'] = 'Virkistä';
$lang['admin']['to_top'] = 'Takaisin alkuun';
$lang['admin']['in_use_by'] = 'Käytössä';
$lang['admin']['forwarding_hosts'] = 'Palveluntarjoajien välittäminen';
$lang['admin']['forwarding_hosts_hint'] = 'Saapuvat viestit hyväksytään ehdoitta kaikilta täällä luetelluilta isännöimiltä. Näitä isäntiä ei sitten tarkisteta DNSBL: ien suhteen, eikä heille suoriteta tyyliluettelointia. Heiltä vastaanotettua roskapostia ei koskaan hylätä, mutta valinnaisesti se voidaan tallentaa Roskakori-kansioon. Yleisin käyttö tähän tarkoitukseen on määrittää postipalvelimet, joille olet asettanut säännön, joka välittää tulevat sähköpostit mailcow-palvelimellesi.';
$lang['admin']['forwarding_hosts_add_hint'] = 'Voit joko määrittää IPv4 / IPv6-osoitteet, verkot CIDR-merkinnässä, isäntänimet (jotka määritetään IP-osoitteiksi) tai verkkotunnusten nimet (jotka määritetään IP-osoitteiksi kyselyllä SPF-tietueista tai niiden puuttuessa MX-tietueista). .';
$lang['admin']['relayhosts_hint'] = 'Määritä lähettäjäriippuvat siirrot, jotta ne voidaan valita verkkotunnusten määritysvalintaikkunassa.<br>
  Kuljetuspalvelu on aina "smtp:". Käyttäjien henkilökohtainen lähtevän TLS-käytäntöasetus otetaan huomioon.<br>
  Vaikuttaa valittuihin verkkotunnuksiin, mukaan lukien alias-verkkotunnukset.';
$lang['admin']['transports_hint'] = '→ Kuljetuskartta <b>kumoaa</b> lähettäjäriippuvainen kuljetuskartan</b>.<br>
→ Lähtevät TLS-käytäntöasetukset käyttäjän kohdalla jätetään huomioimatta, ja ne voidaan panna täytäntöön vain TLS-käytäntökarttatietueilla<br>
→ Määritettyjen kuljetusten kuljetuspalvelu on aina "smtp:".<br>
→ Osoitteet vastaavat "/localhost$/" kuljetetaan aina "local:", siksi "*" määränpäätä ei sovelleta näihin osoitteisiin.<br>
→ Seuraavan hypyn esimerkkitietojen määrittäminen "[host]:25", Postfix <b>aina</b> kyselee "host" ennen etsimistä "[host]:25". Tämä käyttäytyminen tekee käytöstä mahdotonta "host" ja "[host]:25" samaan aikaan.';
$lang['admin']['add_relayhost_hint'] = 'Huomaa, että todennustiedot, jos niitä on, tallennetaan selkeänä tekstinä.';
$lang['admin']['add_transports_hint'] = 'Huomaa, että todennustiedot, jos niitä on, tallennetaan selkeänä tekstinä.';
$lang['admin']['host'] = 'Host';
$lang['admin']['source'] = 'Lähde';
$lang['admin']['add_forwarding_host'] = 'Lisää edelleenlähetys host';
$lang['admin']['add_relayhost'] = 'Lisää lähettäjäriippuvainen kuljetus';
$lang['admin']['add_transport'] = 'Lisää kuljetus';
$lang['admin']['relayhosts'] = 'Lähettäjältä riippuvaiset kuljetukset';
$lang['admin']['transport_maps'] = 'Kuljetus kartat';
$lang['admin']['routing'] = 'Reititys';
$lang['admin']['credentials_transport_warning'] = '<b>Varoitus</b>: Uuden kuljetuskarttatietueen lisääminen päivittää kaikkien merkintöjen käyttöoikeustiedot vastaavalla nexthop-sarakkeella.';

$lang['admin']['destination'] = 'Määränpää';
$lang['admin']['nexthop'] = 'Seuraava pysäkki';

$lang['admin']['oauth2_info'] = 'OAuth2-toteutus tukee avustustyyppiä "Authorization Code" ja antaa päivitysmerkkejä.<br>
Palvelin antaa myös automaattisesti uusia päivitysmerkkejä, kun päivitystunnus on käytetty.<br><br>
→ Oletusalue on <i>profiili</i>. Vain postilaatikon käyttäjät voidaan todentaa OAuth2: lla. Jos laajuusparametri jätetään pois, se palaa takaisin <i>profiili</i>.<br>
→ Käännös <i>tila</i> Asiakkaan on lähetettävä parametri osana valtuutuspyyntöä.<br><br>
Paikkauspyynnöt OAuth2 API: lle: <br>
<ul>
  <li>Valtuutuksen päätepiste: <code>/oauth/authorize</code></li>
  <li>Token-päätepiste: <code>/oauth/token</code></li>
  <li>Resurssisivu:  <code>/oauth/profile</code></li>
</ul>

Asiakassalaisuuden uudelleenluominen ei vanhenna olemassa olevia valtuuskoodeja, mutta he eivät pysty uusimaan tunnustaan.<br><br>
Asiakastunnusten peruuttaminen aiheuttaa kaikkien aktiivisten istuntojen välittömän lopettamisen. Kaikkien asiakkaiden on todennettava uudelleen.';

$lang['admin']['oauth2_client_id'] = "Asiakastunnus ID";
$lang['admin']['oauth2_client_secret'] = "Asiakas salaisuus";
$lang['admin']['oauth2_redirect_uri'] = "Ohjaa URI";
$lang['admin']['oauth2_revoke_tokens'] = 'Peruuta kaikki asiakasmerkit';
$lang['admin']['oauth2_renew_secret'] = 'Luo uusi asiakas salaisuus';
$lang['edit']['client_id'] = 'Asiakastunnus ID';
$lang['edit']['client_secret'] = 'Asiakas salaisuus';
$lang['edit']['scope'] = 'Laajuus';
$lang['edit']['grant_types'] = 'Oikeus tyypiy';
$lang['edit']['redirect_uri'] = 'Uudelleenohjaus / takaisinsoitto-URL';
$lang['oauth2']['scope_ask_permission'] = 'Sovellus pyysi seuraavia oikeuksia';
$lang['oauth2']['profile'] = 'Profiili';
$lang['oauth2']['profile_desc'] = 'Tarkastele henkilökohtaisia ​​tietoja: käyttäjänimi, koko nimi, luotu, muokattu, aktiivinen';
$lang['oauth2']['permit'] = 'Valtuuta sovellus';
$lang['oauth2']['authorize_app'] = 'Valtuuta sovellus';
$lang['oauth2']['deny'] = 'Kiellä';
$lang['oauth2']['access_denied'] = 'Kirjaudu sisään postilaatikon omistajana myöntääksesi käyttöoikeuden OAuth2: n kautta.';


$lang['success']['forwarding_host_removed'] = "Välitys host %s on poistettu";
$lang['success']['forwarding_host_added'] = "Välitys host %s on lisätty";
$lang['success']['relayhost_removed'] = "Map merkintä %s on poistettu";
$lang['success']['relayhost_added'] = "Map merkintä %s on lisätty";
$lang['diagnostics']['dns_records'] = 'DNS Tiedot';
$lang['diagnostics']['dns_records_24hours'] = 'Huomaa, että DNS: ään tehdyt muutokset saattavat viedä jopa 24 tuntia, jotta niiden nykyinen tila heijastuisi oikein tällä sivulle. Se on tarkoitettu tavaksi, jolla voit helposti nähdä kuinka määrittää DNS-tietueesi ja tarkistaa, tallennetaanko kaikki tietueesi oikein DNS: ään.';
$lang['diagnostics']['dns_records_name'] = 'Nimi';
$lang['diagnostics']['dns_records_type'] = 'Tyyppi';
$lang['diagnostics']['dns_records_data'] = 'Oikeat tiedot';
$lang['diagnostics']['dns_records_status'] = 'Nykyinen tila';
$lang['diagnostics']['optional'] = 'Tämä tietue on valinnainen.';
$lang['diagnostics']['cname_from_a'] = 'Arvo johdettu A / AAAA-tietueesta. Tätä tuetaan niin kauan kuin tietue osoittaa oikealle resurssille.';

$lang['admin']['relay_from'] = '"Lähettäjä:" osoite';
$lang['admin']['relay_run'] = "Suorita testi";
$lang['admin']['api_allow_from'] = "Salli API-käyttöoikeus näiltä IP-osoitteilta (erotettu pilkulla tai uudella rivillä)";
$lang['admin']['api_key'] = "API avain";
$lang['admin']['activate_api'] = "Aktivoi API";
$lang['admin']['regen_api_key'] = "Luo API-avain uudelleen";
$lang['admin']['ban_list_info'] = "Katso luettelo kielletyistä IP-osoitteista alta: <b>verkko (jäljellä oleva kieltoaika) - [toiminnot]</b>.<br />Pysäyttämättömäksi jonotetut IP: t poistetaan aktiivisesta kiellosta muutamassa sekunnissa.<br />Punaiset etiketit osoittavat aktiiviset pysyvät kiellot mustalla listalla.";
$lang['admin']['unban_pending'] = "unbanned odottamassa";
$lang['admin']['queue_unban'] = "jono unban";
$lang['admin']['no_active_bans'] = "Ei aktiivisia kieltoja";

$lang['admin']['quarantine'] = "Karanteeni";
$lang['admin']['rspamd_settings_map'] = "Rspamd-asetukset";
$lang['admin']['quota_notifications'] = "Kiintiöilmoitukset";
$lang['admin']['quota_notifications_vars'] = "{{percent}} on yhtä suuri kuin käyttäjän nykyinen kiintiö<br>{{username}} on postilaatikon nimi";
$lang['admin']['active_rspamd_settings_map'] = "Aktiiviset asetukset";
$lang['admin']['quota_notifications_info'] = "Kiintiöilmoitukset lähetetään käyttäjille kerran ylittäessään 80% ja kerran ylittäessään 95% käytöstä.";
$lang['admin']['quarantine_retention_size'] = " Pidätykset per postilaatikko:<br><small>0 ilmaisee <b>inactive</b>.</small>";
$lang['admin']['quarantine_max_size'] = "Suurin koko MiB: nä (suuremmat elementit hylätään):<br><small>0 tekee <b>ei</b> ilmoittaa rajoittamaton.</small>";
$lang['admin']['quarantine_max_age'] = "Enimmäisikä päivinä<br><small>Arvon on oltava vähintään 1 päivä.</small>";
$lang['admin']['quarantine_exclude_domains'] = "Sulje verkkotunnukset ja alias-verkkotunnukset pois";
$lang['admin']['quarantine_release_format'] = "Julkaistujen tuotteiden muoto";
$lang['admin']['quarantine_release_format_raw'] = "Muuttamaton alkuperäinen";
$lang['admin']['quarantine_release_format_att'] = "Liitteenä";
$lang['admin']['quarantine_notification_sender'] = "Ilmoitusviestin lähettäjä";
$lang['admin']['quarantine_notification_subject'] = "Ilmoitusviestin aihe";
$lang['admin']['quarantine_notification_html'] = "Ilmoitusviestin malli:<br><small>Jätä tyhjä palauttaaksesi oletusmallin.</small>";
$lang['admin']['quota_notification_sender'] = "Ilmoitusviestin lähettäjä";
$lang['admin']['quota_notification_subject'] = "Ilmoitusviestin aihe";
$lang['admin']['quota_notification_html'] = "Ilmoitusviestin malli:<br><small>Jätä tyhjä palauttaaksesi oletusmallin.</small>";
$lang['admin']['ui_texts'] = "Käyttöliittymä etiketit ja tekstit";
$lang['admin']['help_text'] = "Ohita kirjautumismaskin alla oleva ohjeteksti (HTML sallittu)";
$lang['admin']['title_name'] = '"mailcow UI "-sivuston otsikko';
$lang['admin']['main_name'] = '"mailcow UI "nimi';
$lang['admin']['apps_name'] = '"mailcow Apps "nimi';
$lang['admin']['ui_footer'] = 'Alatunniste (HTML sallittu)';

$lang['admin']['customize'] = "Muokkaa";
$lang['admin']['change_logo'] = "Vaihda logo";
$lang['admin']['logo_info'] = "Kuvasi skaalataan ylimmän navigointipalkin 40 pikselin korkeudelle ja korkeintaan. leveys 250 sivua aloitussivulle. Skaalautuva grafiikka on erittäin suositeltavaa.";
$lang['admin']['upload'] = "Lataa";
$lang['admin']['app_links'] = "Sovelluslinkit";
$lang['admin']['app_name'] = "Sovelluksen nimi";
$lang['admin']['link'] = "Linkki";
$lang['admin']['remove_row'] = "Poista rivi";
$lang['admin']['add_row'] = "Lisää rivi";
$lang['admin']['reset_default'] = "Palauta oletusasetuksiin";
$lang['admin']['merged_vars_hint'] = 'Harmaantuneet rivit yhdistettiin <code>vars.(local.)inc.php</code> eikä sitä voida muokata.';
$lang['mailbox']['waiting'] = "Odotetaan..";
$lang['mailbox']['status'] = "Tila";
$lang['mailbox']['running'] = "Running";
$lang['mailbox']['enable_x'] = "Ota käyttöön";
$lang['mailbox']['disable_x'] = "Poista käytöstä";

$lang['edit']['spam_score'] = "Mukautetun roska posti arvon asettaminen";
$lang['user']['spam_score_reset'] = "Palauta palvelimen oletus asetuksiin";
$lang['edit']['spam_policy'] = "Lisää tai Poista kohteita sallituista-/mustalta listalta";
$lang['edit']['spam_alias'] = "Luo tai muuta aika rajoitettuja aliaksen osoitteita";

$lang['danger']['comment_too_long'] = "Kommentoi liian kauan, enintään 160 merkkiä sallittu";
$lang['danger']['img_tmp_missing'] = "Kuvatiedostoa ei voi vahvistaa: Väliaikaista tiedostoa ei löydy";
$lang['danger']['img_invalid'] = "Kuvatiedostoa ei voi vahvistaa";
$lang['danger']['invalid_mime_type'] = "Virheellinen mime-tyyppi";
$lang['success']['upload_success'] = "Tiedosto ladattu onnistuneesti";
$lang['success']['app_links'] = "Tallennettu sovelluslinkkien muutokset";
$lang['success']['ui_texts'] = "Tallennettu käyttöliittymätekstien muutokset";
$lang['success']['reset_main_logo'] = "Palauta oletuslogo";
$lang['success']['items_released'] = "Valitut kohteet julkaistiin";
$lang['success']['item_released'] = "erä %s julkaisi";
$lang['danger']['imagick_exception'] = "Virhe: Imagick poikkeus kuvaa luettaessa";
$lang['quarantine']['quarantine'] = "Karanteeni";
$lang['quarantine']['learn_spam_delete'] = "Opeta roska postiksi ja poista";
$lang['quarantine']['qinfo'] = 'Karanteeni järjestelmä tallentaa hylätyn sähköpostin tieto kantaan, kun lähettäjä <em>ei</em> annetaan vaikutelma toimitetuksi sähköposti.
  <br>"' . $lang['quarantine']['learn_spam_delete'] . '" oppii viestin roska postiksi Bayes-teoreettilta ja laskee myös sumea hash kieltää samanlaisia viestejä tulevaisuudessa.
  <br>Huomaa, että useiden viestien oppiminen voi olla-riippuen järjestelmän ajasta.';
$lang['quarantine']['download_eml'] = "Lataa (.eml)";
$lang['quarantine']['release'] = "Vapauttaa";
$lang['quarantine']['empty'] = 'Ei tuloksia';
$lang['quarantine']['toggle_all'] = 'Valitse kaikki';
$lang['quarantine']['quick_actions'] = 'Toiminnot';
$lang['quarantine']['remove'] = 'Poista';
$lang['quarantine']['received'] = "Vastaanotettu";
$lang['quarantine']['action'] = "Toiminnot";
$lang['quarantine']['rcpt'] = "Vastaanottaja";
$lang['quarantine']['qid'] = "Rspamd QID";
$lang['quarantine']['sender'] = "Lähettäjä";
$lang['quarantine']['show_item'] = "Näytä tuote";
$lang['quarantine']['check_hash'] = "Hae tiedosto hash @ VT";
$lang['quarantine']['qitem'] = "Laita karanteeniin";
$lang['quarantine']['rspamd_result'] = "Rspamd-tulos";
$lang['quarantine']['subj'] = "Aihe";
$lang['quarantine']['recipients'] = "Vastaanottajat";
$lang['quarantine']['text_plain_content'] = "Sisältö (teksti / tavallinen)";
$lang['quarantine']['text_from_html_content'] = "Sisältö (muunnettu html)";
$lang['quarantine']['atts'] = "Liitteet";
$lang['quarantine']['low_danger'] = "Matala vaara";
$lang['quarantine']['neutral_danger'] = "Neutraali/ei pisteystä";
$lang['quarantine']['medium_danger'] = "Alhainen vaara";
$lang['quarantine']['high_danger'] = "Korkea";
$lang['quarantine']['danger'] = "Vaarallinen";
$lang['quarantine']['spam_score'] = "Pisteet";
$lang['quarantine']['confirm_delete'] = "Vahvista tämän elementin poistaminen.";
$lang['quarantine']['qhandler_success'] = "Pyyntö lähetettiin järjestelmään onnistuneesti. Voit nyt sulkea ikkunan.";
$lang['warning']['fuzzy_learn_error'] = "Sumea hash-oppimisvirhe: %s";
$lang['danger']['spam_learn_error'] = "Roskapostin oppimisvirhe: %s";
$lang['success']['qlearn_spam'] = "Viestin tunnus %s opittiin roskapostiksi ja poistettiin";

$lang['debug']['system_containers'] = 'Systeemi & Säiliöt';
$lang['debug']['started_on'] = 'Aloitettiin';
$lang['debug']['jvm_memory_solr'] = 'JVM-muistin käyttö';
$lang['debug']['solr_status'] = 'Solr-tila';
$lang['debug']['solr_dead'] = 'Solr käynnistyy, on poissa käytöstä tai kuoli.';
$lang['debug']['logs'] = 'Logit tausta palveluista';
$lang['debug']['log_info'] = '<p>mailcow <b>muistissa olevat lokit</b> kerätään Redis-luetteloihin ja leikataan LOG_LINES (%d) joka minuutti lyömisen vähentämiseksi.
  <br>Muistissa olevien lokien ei ole tarkoitus olla pysyviä. Kaikki sovellukset, jotka kirjautuvat muistiin, kirjautuvat myös Docker-daemoniin ja siten oletusarvoiseen lokitiedostoon.
  <br>Muistin lokityyppiä olisi käytettävä virheiden virheenkorjaukseen säilöissä.</p>
  <p><b>Ulkoiset lokit</b> kerätään annetun sovelluksen API: n kautta.</p>
  <p><b>Staattiset lokit</b> ovat useimmiten toimintalokkeja, joita ei kirjata Dockerdiin, mutta joiden on silti oltava pysyviä (paitsi API-lokit).</p>';

$lang['debug']['in_memory_logs'] = 'Muistissa olevat lokit';
$lang['debug']['external_logs'] = 'Ulkoiset loki';
$lang['debug']['static_logs'] = 'Staattiset lokit';
$lang['debug']['solr_uptime'] = 'Päällä';
$lang['debug']['solr_started_at'] = 'Käynnistetty';
$lang['debug']['solr_last_modified'] = 'Viimeksi muokattu';
$lang['debug']['solr_size'] = 'Koko';
$lang['debug']['solr_docs'] = 'Docs';

$lang['debug']['disk_usage'] = 'Levyn käyttö';
$lang['debug']['containers_info'] = "Säilön tiedot";
$lang['debug']['restart_container'] = 'Uudelleen käynnistä';

$lang['quarantine']['release_body'] = "Olemme liittäneet viestisi eml-tiedostona tähän viestiin.";
$lang['danger']['release_send_failed'] = "Viestiä ei voitu vapauttaa: %s";
$lang['quarantine']['release_subject'] = "Mahdollisesti vahingoittava karanteeni aihe %s";

$lang['mailbox']['bcc_map'] = "BCC piilo-kopio";
$lang['mailbox']['bcc_map_type'] = "Piilo kopio-tyyppi";
$lang['mailbox']['bcc_type'] = "Piilo kopio-tyyppi";
$lang['mailbox']['bcc_sender_map'] = "Lähettäjän kartta";
$lang['mailbox']['bcc_rcpt_map'] = "Vastaanottajan kartta";
$lang['mailbox']['bcc_local_dest'] = "Paikallinen kohde";
$lang['mailbox']['bcc_destinations'] = "Piilo kopion kohde";
$lang['mailbox']['bcc_destination'] = "Piilo kopion kohde";
$lang['edit']['bcc_dest_format'] = 'BCC-piilo-kopio kohteen on oltava yksittäinen kelvollinen sähköposti osoite.';

$lang['mailbox']['bcc'] = "BCC piilo-kopio";
$lang['mailbox']['bcc_maps'] = "Piilo kopio-kartat";
$lang['mailbox']['bcc_to_sender'] = "Vaihda lähettäjän kartan tyyppi";
$lang['mailbox']['bcc_to_rcpt'] = "Siirry vastaanottajan yhdistämis kartan tyyppi";
$lang['mailbox']['add_bcc_entry'] = "Lisää piilo kopio kartta";
$lang['mailbox']['add_tls_policy_map'] = "Lisää TLS-käytäntö kartta";
$lang['mailbox']['bcc_info'] = "Piilo kopio karttoja käytetään kaikkien viestien kopioiden hiljaisesti eteenpäin toiseen osoitteeseen.Vastaanottajan yhdistämis määrityksen tyyppi merkintää käytetään, kun paikallinen kohde toimii sähkö postin vastaanottajana.Lähettäjän kartat noudattavat samaa periaatetta.<br/>
  Paikalliseen kohteeseen ei saada tietoja epäonnistuneesta toimituksesta.";
$lang['mailbox']['address_rewriting'] = 'Osoitteen uudelleenkirjoittaminen';
$lang['mailbox']['recipient_maps'] = 'Vastaanottajien yhdistämis määritykset';
$lang['mailbox']['recipient_map'] = 'Vastaanottajien yhdistämis määritykset';
$lang['mailbox']['recipient_map_info'] = 'Vastaanottajan karttoja käytetään korvaamaan viestin kohde osoite ennen sen toimittamista.';
$lang['mailbox']['recipient_map_old_info'] = 'Vastaanottajan yhdistämis määritysten alkuperäisen kohteen on oltava kelvollinen sähköposti osoite tai verkkotunnus alueen nimi.';
$lang['mailbox']['recipient_map_new_info'] = 'Vastaanottajan yhdistämis kartan kohteen on oltava kelvollinen sähköposti osoite.';
$lang['mailbox']['recipient_map_old'] = 'Alkuperäinen vastaanottaja';
$lang['mailbox']['recipient_map_new'] = 'Uusi vastaanottaja';
$lang['danger']['invalid_recipient_map_new'] = 'Määritetty uusi vastaanottaja ei kelpaa: %s';
$lang['danger']['invalid_recipient_map_old'] = 'Määritetty alkuperäinen vastaanottaja ei kelpaa: %s';
$lang['danger']['recipient_map_entry_exists'] = 'Vastaanottajan kartta merkintä "%s" on olemassa';
$lang['success']['recipient_map_entry_saved'] = 'Vastaanottajan yhdistämis määrityksen merkintä "%s" on tallennettu';
$lang['success']['recipient_map_entry_deleted'] = 'Vastaanottajan yhdistämis kartan tunnus %s on poistettu';
$lang['danger']['tls_policy_map_entry_exists'] = 'TLS-käytäntö kartan merkintä "%s" on olemassa';
$lang['success']['tls_policy_map_entry_saved'] = 'TLS-käytäntö kartan merkintä "%s" on tallennettu';
$lang['success']['tls_policy_map_entry_deleted'] = 'TLS-käytäntö kartan tunnus %s on poistettu';
$lang['mailbox']['add_recipient_map_entry'] = 'Lisää vastaanottajan kartta';
$lang['danger']['tls_policy_map_parameter_invalid'] = "Käytäntö parametri ei kelpaa";
$lang['danger']['temp_error'] = "Tilapäinen virhe";

$lang['admin']['sys_mails'] = 'Järjestelmän sähköpostit';
$lang['admin']['subject'] = 'Aihe';
$lang['admin']['from'] = 'Lähettäjä';
$lang['admin']['include_exclude'] = 'Sisällytä/jätä pois';
$lang['admin']['include_exclude_info'] = 'Oletusarvoisesti-ilman valintaa - <b>kaikki posti laatikot</b> käsitellään';
$lang['admin']['excludes'] = 'Sulkee pois nämä vastaanottajat';
$lang['admin']['includes'] = 'Sisällytä nämä vastaanottajat';
$lang['admin']['text'] = 'Tekstisi';
$lang['admin']['activate_send'] = 'Aktivoi Lähetä-painike';
$lang['admin']['send'] = 'Lähetä';

$lang['warning']['ip_invalid'] = 'Ohitettu virheellinen IP-osoite: %s';
$lang['danger']['text_empty'] = 'Teksti ei saa olla tyhjä';
$lang['danger']['subject_empty'] = 'Aihe ei saa olla tyhjä';
$lang['danger']['from_invalid'] = 'Lähettäjä ei saa olla tyhjä';
$lang['danger']['network_host_invalid'] = 'Verkko tai isäntä ei kelpaa: %s';

$lang['add']['mailbox_quota_def'] = 'Sähköpostin oletus kiintiö';
$lang['edit']['mailbox_quota_def'] = 'Sähköpostin oletus kiintiö';
$lang['danger']['mailbox_defquota_exceeds_mailbox_maxquota'] = 'Oletus kiintiö ylittää kiintiön enimmäisarvo asetuksen';
$lang['danger']['defquota_empty'] = 'Sähköpostin oletus kiintiö ei saa olla 0.';
$lang['mailbox']['mailbox_defquota'] = 'Tilin koko';

$lang['admin']['api_info'] = 'API on keskeneräin työ.';

$lang['admin']['guid_and_license'] = 'GUID &-lisenssi';
$lang['admin']['guid'] = 'GUID-yksilöllinen tunnus ID';
$lang['admin']['license_info'] = 'Lisenssiä ei vaadita, mutta se auttaa kehittämään ohjelmaa jatkossa.<br><a href="https://www.servercow.de/mailcow?lang=en#sal" target="_blank" alt="SAL order">Rekisteröi GUID-tunnus täältä</a> or <a href="https://www.servercow.de/mailcow?lang=en#support" target="_blank" alt="Support order">Osta tuki mailcow-asennusta varten.</a>';
$lang['admin']['validate_license_now'] = 'Vahvista GUID-tunnus lisenssi palvelinta vastaan';

$lang['admin']['customer_id'] = 'Asiakkaan tunnus ID';
$lang['admin']['service_id'] = 'Palvelun tunnus ID';

$lang['admin']['lookup_mx'] = 'Määränpää vastaan MX (. outlook.com reitittää kaikki Mail suunnattu MX *. outlook.com yli tämän hop)';
$lang['edit']['mbox_rl_info'] = 'Tätä nopeutta käytetään SASL-Kirjautumisnimessä, se vastaa mitä tahansa kirjautuneen käyttäjän käyttämän osoitteen "from"-osoitetta.Sähköpostin nopeus rajoitus ohittaa toimialuelaajuisen kappale rajoituksen.';

$lang['add']['relayhost_wrapped_tls_info'] = 'Tee <b>ei</b> käytä TLS-käärittyjä portteja (käytetään enimmäkseen portissa 465).<br>
Käytä mitä tahansa ei-rivitettyä porttia ja ongelma-STARTTLS. TLS-käytäntö voidaan pakottaa TLS-käytäntöön "TLS-käytäntö kartoissa"".';

$lang['admin']['transport_dest_format'] = 'Syntaksi: example.org,. example.org, *, box@example.org (useita arvoja voidaan erottaa pilkulla)';

$lang['mailbox']['alias_domain_backupmx'] = 'Alias verkkotunnus alue passiivinen välitys verkkotunnus alueella';

$lang['danger']['extra_acl_invalid'] = 'Ulkoisen lähettäjän osoite "%s" on virheellinen';
$lang['danger']['extra_acl_invalid_domain'] = 'Ulkoinen Lähettäjä "%s" käyttää virheellistä verkkotunnus aluetta';
