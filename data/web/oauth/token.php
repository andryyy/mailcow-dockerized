<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';
$request = OAuth2\Request::createFromGlobals();
$oauth2_server->handleTokenRequest($request)->send();
