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
if($_POST['r'] == 'nodes')
{
	try {
		$stmt = Connection::QueryDatabase(Connection::GetDatabase(), '
			SELECT nodeid, name, ip, port
			FROM nodes
			ORDER BY name
		');
	} catch(Exception $e) {
		Connection::HttpError(500, I('Failed to query nodes.').'<br/>'.$e->getMessage());
	}
	$out = array();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC))
		$out[] = array('nodeid' => $row['nodeid'], 'name' => "$row[name] ($row[ip]:$row[port])");
	if(count($out) === 0)
		$out[] = array('nodeid' => '0', 'name' => "Local");
	header('Content-Type: application/json');
	echo json_encode($out); // list of nodes
}
else if($_POST['r'] == 'groups')
{
	$nodeId = $_POST['node'];
	$nodeFilter = ($nodeId == '0') ? '' : // filter by distributed node?
		"WHERE groupid BETWEEN {$nodeId}00000000000000 AND {$nodeId}99999999999999";
	try {
		$stmt = Connection::QueryDatabase(Connection::GetDatabase(), "
			SELECT groupid, name
			FROM groups
			$nodeFilter
			ORDER BY name
		");
	} catch(Exception $e) {
		Connection::HttpError(500, sprintf(I('Failed to query groups for node %s.'), $nodeId).
			'<br/>'.$e->getMessage());
	}
	$out = $stmt->fetchAll(PDO::FETCH_ASSOC);
	header('Content-Type: application/json');
	echo json_encode($out); // list of host groups
}
else if($_POST['r'] == 'hosts')
{
	try {
		$stmt = Connection::QueryDatabase(Connection::GetDatabase(), "
			SELECT h.hostid, h.name
			FROM hosts h
			INNER JOIN hosts_groups hg ON hg.hostid = h.hostid
			WHERE hg.groupid = $_POST[group]
			ORDER BY h.name
		");
	} catch(Exception $e) {
		Connection::HttpError(500, sprintf(I('Failed to query hosts for group %s.'), $_POST['group']).
			'<br/>'.$e->getMessage());
	}
	$out = $stmt->fetchAll(PDO::FETCH_ASSOC);
	header('Content-Type: application/json');
	echo json_encode($out); // list of hosts
}
else if($_POST['r'] == 'triggers')
{
	try {
		$stmt = Connection::QueryDatabase(Connection::GetDatabase(), "
			SELECT t.triggerid, REPLACE(t.description, '{HOSTNAME}', h.host) AS description
			FROM triggers t
			INNER JOIN functions f ON f.triggerid = t.triggerid
			INNER JOIN items i ON i.itemid = f.itemid AND i.hostid = $_POST[host]
			INNER JOIN hosts h ON h.hostid = i.hostid
			ORDER BY description
		");
	} catch(Exception $e) {
		Connection::HttpError(500, sprintf(I('Failed to query triggers host %s.'), $_POST['host']).
			'<br/>'.$e->getMessage());
	}
	$out = $stmt->fetchAll(PDO::FETCH_ASSOC);
	header('Content-Type: application/json');
	echo json_encode($out); // list of triggers
}