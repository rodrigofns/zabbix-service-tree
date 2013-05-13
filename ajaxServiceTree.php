<?php
session_start();
require('inc/Connection.class.php');
require('inc/ServiceTree.class.php');
require('inc/StatusColor.class.php');

if(isset($_REQUEST['root']))
{
	// List of root service names.
	$dbh = Connection::GetDatabase();
	header('Content-Type: application/json');
	echo json_encode(ServiceTree::GetRootList($dbh));
}
else if( (isset($_REQUEST['serviceId']) && $_REQUEST['serviceId'] != '') ||
	(isset($_REQUEST['serviceName']) && $_REQUEST['serviceName'] != '') )
{
	// Build and return the service tree.
	// Service may be queried by its ID or name.
	$dbh = Connection::GetDatabase();
	$serviceId = (isset($_REQUEST['serviceId']) && $_REQUEST['serviceId'] != '') ?
		$_REQUEST['serviceId'] : ServiceTree::GetIdByName($dbh, $_REQUEST['serviceName']);

	// Output JSON data.
	$statusCount = array(0, 0, 0, 0, 0, 0);
	$root = ServiceTree::GetAllToHtml5($dbh, $serviceId, $statusCount);

	header('Content-Type: application/json');
	echo json_encode(array(
		'tree'        => $root,
		'statusCount' => $statusCount
	));
}
else
{
	Connection::HttpError(400, 'Service tree: no parameters, nothing to query.');
}