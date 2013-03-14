<?php
session_start();
require('../include/Connection.class.php');
require('../include/ServiceTree.class.php');

// Preliminar setup.
{
	// Validate authenticated client.
	if(!isset($_SESSION['user']))
		Connection::HttpError(401, 'You are not logged in, or your session expired.');

	// Establish database connection.
	$dbh = Connection::GetDatabase();
}

// Process requests.
if(isset($_POST['save']))
{
	// --- Save service information. -------------------------------------------
	$data = (object)$_POST['data'];

	if($data->triggerid == '') $data->triggerid = null;

	$stmt = $dbh->prepare('
		UPDATE service_threshold SET
		threshold_normal      = :normal,
		threshold_information = :information,
		threshold_alert       = :alert,
		threshold_average     = :average,
		threshold_major       = :major,
		threshold_critical    = :critical
		WHERE idservice = :serviceid');
	$stmt->bindParam(':normal',      $data->threshold_normal);
	$stmt->bindParam(':information', $data->threshold_information);
	$stmt->bindParam(':alert',       $data->threshold_alert);
	$stmt->bindParam(':average',     $data->threshold_average);
	$stmt->bindParam(':major',       $data->threshold_major);
	$stmt->bindParam(':critical',    $data->threshold_critical);
	$stmt->bindParam(':serviceid',   $data->id);
	if(!$stmt->execute()) {
		$err = $stmt->errorInfo();
		Connection::HttpError(500, "Failed to update service_threshold with id={$data->id}.<br/>$err[2]");
	}

	$stmt = $dbh->prepare('
		UPDATE service_weight SET
		weight_normal      = :normal,
		weight_information = :information,
		weight_alert       = :alert,
		weight_average     = :average,
		weight_major       = :major,
		weight_critical    = :critical
		WHERE idservice = :serviceid');
	$stmt->bindParam(':normal',      $data->weight_normal);
	$stmt->bindParam(':information', $data->weight_information);
	$stmt->bindParam(':alert',       $data->weight_alert);
	$stmt->bindParam(':average',     $data->weight_average);
	$stmt->bindParam(':major',       $data->weight_major);
	$stmt->bindParam(':critical',    $data->weight_critical);
	$stmt->bindParam(':serviceid',   $data->id);
	if(!$stmt->execute()) {
		$err = $stmt->errorInfo();
		Connection::HttpError(500, "Failed to update service_weight with id={$data->id}.<br/>$err[2]");
	}

	// https://www.zabbix.com/documentation/2.0/manual/appendix/api/service/definitions#it_service
	$stmt = $dbh->prepare('
		UPDATE services SET
		name      = :name,
		algorithm = :algorithm,
		triggerid = :triggerid,
		showsla   = :showsla,
		goodsla   = :goodsla
		WHERE serviceid = :serviceid');
	$stmt->bindParam(':name',      $data->name);
	//$stmt->bindParam(':status',    $data->status);
	$stmt->bindParam(':algorithm', $data->algorithm);
	$stmt->bindParam(':triggerid', $data->triggerid);
	$stmt->bindParam(':showsla',   $data->showsla);
	$stmt->bindParam(':goodsla',   $data->goodsla);
	//$stmt->bindParam(':sortorder', $data->status);
	$stmt->bindParam(':serviceid', $data->id);
	if(!$stmt->execute()) {
		$err = $stmt->errorInfo();
		Connection::HttpError(500, "Failed to update service with id={$data->id}.<br/>$err[2]");
	}

	header('Content-Type: application/json');
	echo json_encode(array( 'status' => 'ok' )); // output status
}
else if(isset($_REQUEST['serviceId']))
{
	// --- Query service information. ------------------------------------------
	header('Content-Type: application/json');
	echo json_encode( ServiceTree::GetInfo($dbh, $_REQUEST['serviceId']) );
}
else
{
	// --- No request? ---------------------------------------------------------
	Connection::HttpError(400,
		'No retrieve/save service request... what are you trying to do?');
}