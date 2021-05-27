<?php
require_once __DIR__ . '/../inc/prerequisites.inc.php';
$request = OAuth2\Request::createFromGlobals();
$oauth2_server->handleTokenRequest($request)->send();
