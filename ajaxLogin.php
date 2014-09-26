<?php
session_start();
require('inc/Connection.class.php');

require_once('__conf.php');
require_once('i18n/i18n.php');
i18n_set_map('en', $LANG, false);

// Requests processing.
if(isset($_POST['logoff']))
{
	// --- Logoff process. -----------------------------------------------------
	if(isset($_SESSION['user'])) {
		unset($_SESSION['user']); // remove session data
		unset($_SESSION['hash']);
	}
	header('Content-Type: application/json');
	echo json_encode(array( 'status' => 'ok' )); // output status
}
else if(isset($_POST['user']) && isset($_POST['pass']))
{
	// --- Login process. ------------------------------------------------------
	$zabbix = Connection::GetZabbixApi(null); // no hash
	try {
		$hash = $zabbix->autenticar($_POST['user'], $_POST['pass']);
	} catch(Exception $e) {
		Connection::HttpError(401, sprintf(I('Zabbix login failed for user %s.'), $_POST['user']));
	}

	$_SESSION['user'] = $_POST['user']; // save session data
	$_SESSION['hash'] = $hash;
	header('Content-Type: application/json');
	echo json_encode(array( // output status
		'status' => 'ok',
		'user' => $_POST['user'],
		'hash' => $hash
	));
}
else
{
	// --- No request? ---------------------------------------------------------
	Connection::HttpError(400, I('No parameters... what do you want to do?'));
}