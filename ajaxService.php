<?php
session_start();
require('inc/Connection.class.php');
require('inc/ServiceTree.class.php');

// Preliminar setup.
{
	// Validate authenticated client.
	if(!isset($_SESSION['user']))
		Connection::HttpError(401, I('You are not logged in, or your session expired.'));

	// Establish database connection.
	$dbh = Connection::GetDatabase();
}


// Process requests.
if(isset($_POST['save']))
{
	// --- Save service information. -------------------------------------------
	$data = (object)$_POST['data'];
	if($data->triggerid == '') $data->triggerid = null;

	try {
		$dbh->beginTransaction();
		Connection::QueryDatabase($dbh, '
			UPDATE service_threshold SET
			threshold_normal = ?,
			threshold_information = ?,
			threshold_alert = ?,
			threshold_average = ?,
			threshold_major = ?,
			threshold_critical = ?
			WHERE idservice = ?
		', $data->threshold_normal, $data->threshold_information, $data->threshold_alert,
			$data->threshold_average, $data->threshold_major, $data->threshold_critical,
			$data->id);
		Connection::QueryDatabase($dbh, '
			UPDATE service_weight SET
			weight_normal = ?,
			weight_information = ?,
			weight_alert = ?,
			weight_average = ?,
			weight_major = ?,
			weight_critical = ?
			WHERE idservice = ?
		', $data->weight_normal, $data->weight_information, $data->weight_alert,
			$data->weight_average, $data->weight_major, $data->weight_critical,
			$data->id);
		Connection::QueryDatabase($dbh, '
			UPDATE services SET
			name = ?,
			algorithm = ?,
			triggerid = ?,
			showsla = ?,
			goodsla = ?
			WHERE serviceid = ?
		', $data->name, $data->algorithm, $data->triggerid,
			$data->showsla, $data->goodsla, $data->id);
		$dbh->commit();
	} catch(Exception $e) {
		$dbh->rollback();
		Connection::HttpError(500,
			sprintf(I('Failed to update service with id=%s.<br/>%s'), $data->id, $e->getMessage()) );
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
		I('No retrieve/save service request... what are you trying to do?'));
}