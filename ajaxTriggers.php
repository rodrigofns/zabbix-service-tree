<?php
session_start();
require('inc/Connection.class.php');

require_once('__conf.php');
require_once('i18n/i18n.php');
i18n_set_map('en', $LANG, false);

// Preliminar setup.
{
	// Validate authenticated client.
	if(!isset($_SESSION['user']))
		Connection::HttpError(401, I('You are not logged in, or your session expired.'));

	// Validate request type.
	if(!isset($_POST['r']))
		Connection::HttpError(400, I('Request not specified (groups/hosts/triggers).'));
}

// Process requests.
if($_POST['r'] == 'nodes') {
	// --- list of nodes -------------------------------------------------------
	$dbh = Connection::GetDatabase();
	$stmt = $dbh->prepare('SELECT nodeid, name, ip, port FROM nodes');
	if(!$stmt->execute())
		Connection::HttpError(500, I('Failed to query nodes.'));
	$out = array();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC))
		$out[] = array('nodeid' => $row['nodeid'], 'name' => "$row[name] ($row[ip]:$row[port])");

	$count = count($out);
        if (!$count) 
                $out[] = array('nodeid' => '0', 'name' => "Local"); 

	header('Content-Type: application/json');
	echo json_encode($out);
}
else if($_POST['r'] == 'groups') {
        
	$node = $_POST['node'];
        if ($node == "0")
         {
         ProcessAndSendRpc('hostgroup.get', array(
 		'selectHosts' => 'count',
 		'real_hosts' => true,
 		'output' => 'extend'
 	));
         }
         else {
	// --- list of host groups -------------------------------------------------
	ProcessAndSendRpc('hostgroup.get', array(
		'nodeids' => $node, // filter by distributed node
		'selectHosts' => 'count',
		'real_hosts' => true,
		'output' => 'extend'
	));
       }
}
else if($_POST['r'] == 'hosts') {
	// --- list of hosts -------------------------------------------------------
	if(!isset($_POST['group']))
		Connection::HttpError(400, I('Hosts request; no group ID specified.'));
	ProcessAndSendRpc('host.get', array(
		'output' => 'extend',
		'groupids' => $_POST['group'] // filter by group ID
	));
}
else if($_POST['r'] == 'triggers') {
	// --- list of host triggers -----------------------------------------------
	if(!isset($_POST['host']))
		Connection::HttpError(400, I('Triggers request; no host ID specified.'));
	ProcessAndSendRpc('trigger.get', array(
		'expandDescription' => true,
		'output' => 'extend',
		'hostids' => $_POST['host'] // filter by host ID
	));
}

// Hard-work RPC functions.
function ProcessAndSendRpc($method, $params) {
	$zabbix = Connection::GetZabbixApi($_SESSION['hash']);
	$res = ProcessRpc($zabbix, $method, $params);
	header('Content-Type: application/json');
	echo json_encode($res); // directly output JSON content
}
function ProcessRpc($zabbix, $method, $params) {
	try {
		return $zabbix->pedir($method, $params);
	} catch(Exception $e) {
		Connection::HttpError(500, $e->getMessage());
	}
}
