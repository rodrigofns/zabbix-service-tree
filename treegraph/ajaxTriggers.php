<?php
session_start();
require('../include/Connection.class.php');

// Preliminar setup.
{
	// Validate authenticated client.
	if(!isset($_SESSION['user']))
		Connection::HttpError(401, 'You are not logged in, or your session expired.');

	// Validate request type.
	if(!isset($_POST['r']))
		Connection::HttpError(400, 'Request not specified (groups/hosts/triggers).');
}

// Process requests.
if($_POST['r'] == 'nodes') {
	// --- list of nodes -------------------------------------------------------
	$dbh = Connection::GetDatabase();
	$stmt = $dbh->prepare('SELECT nodeid, name, ip, port FROM nodes');
	if(!$stmt->execute())
		Connection::HttpError(500, 'Failed to query nodes.');
	$out = array();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC))
		$out[] = array('nodeid' => $row['nodeid'], 'name' => "$row[name] ($row[ip]:$row[port])");
	header('Content-Type: application/json');
	echo json_encode($out);
}
else if($_POST['r'] == 'groups') {
	// --- list of host groups -------------------------------------------------
	processAndSendRpc('hostgroup.get', array(
		'nodeids' => $_POST['node'], // filter by distributed node
		'selectHosts' => 'count',
		'real_hosts' => true,
		'output' => 'extend'
	));
}
else if($_POST['r'] == 'hosts') {
	// --- list of hosts -------------------------------------------------------
	if(!isset($_POST['group']))
		Connection::HttpError(400, 'Hosts request; no group ID specified.');
	processAndSendRpc('host.get', array(
		'output' => 'extend',
		'groupids' => $_POST['group'] // filter by group ID
	));
}
else if($_POST['r'] == 'triggers') {
	// --- list of host triggers -----------------------------------------------
	if(!isset($_POST['host']))
		Connection::HttpError(400, 'Triggers request; no host ID specified.');
	processAndSendRpc('trigger.get', array(
		'expandDescription' => true,
		'output' => 'extend',
		'hostids' => $_POST['host'] // filter by host ID
	));
}

// Hard-work RPC functions.
function processAndSendRpc($method, $params) {
	$zabbix = Connection::GetZabbixApi($_SESSION['hash']);
	$res = processRpc($zabbix, $method, $params);
	header('Content-Type: application/json');
	echo json_encode($res); // directly output JSON content
}
function processRpc($zabbix, $method, $params) {
	try {
		return $zabbix->pedir($method, $params);
	} catch(Exception $e) {
		Connection::HttpError(500, $e->getMessage());
	}
}