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

	UpdateOrInsert($dbh, 'service_threshold',
		array('idservice', $data->id),
		array(
			array('threshold_normal',      $data->threshold_normal),
			array('threshold_information', $data->threshold_information),
			array('threshold_alert',       $data->threshold_alert),
			array('threshold_average',     $data->threshold_average),
			array('threshold_major',       $data->threshold_major),
			array('threshold_critical',    $data->threshold_critical)
		)
	);
	UpdateOrInsert($dbh, 'service_weight',
		array('idservice', $data->id),
		array(
			array('weight_normal',      $data->weight_normal),
			array('weight_information', $data->weight_information),
			array('weight_alert',       $data->weight_alert),
			array('weight_average',     $data->weight_average),
			array('weight_major',       $data->weight_major),
			array('weight_critical',    $data->weight_critical)
		)
	);

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


// Auxiliar function.
function UpdateOrInsert($dbh, $tableName, $idPair, $valuePairs)
{
	$stmt = $dbh->prepare("SELECT 1 FROM $tableName WHERE {$idPair[0]} = {$idPair[1]}"); // row exists?
	if(!$stmt->execute()) {
		$err = $stmt->errorInfo();
		Connection::HttpError(500, "Failed to check row in $tableName.<br/>$err[0] $err[2]");
	}
	$rowFound = false;
	while($row = $stmt->fetch(PDO::FETCH_NUM)) $rowFound = true;
	$sql = '';
	if($rowFound) { // row exists, let's UPDATE
		$sql = "UPDATE $tableName SET ";
		foreach($valuePairs as $valuePair)
			$sql .= $valuePair[0].' = '.$valuePair[1].',';
		$sql = rtrim($sql, ',');
		$sql .= " WHERE {$idPair[0]} = {$idPair[1]}";
	} else { // row doesn't exist, let's INSERT
		$sql = "INSERT INTO $tableName ({$idPair[0]},";
		foreach($valuePairs as $valuePair)
			$sql .= $valuePair[0].',';
		$sql = rtrim($sql, ',');
		$sql .= ") VALUES ({$idPair[1]},";
		foreach($valuePairs as $valuePair)
			$sql .= $valuePair[1].',';
		$sql = rtrim($sql, ',');
		$sql .= ')';
	}
	$stmt = $dbh->prepare($sql); // finally run the query
	if(!$stmt->execute()) {
		$err = $stmt->errorInfo();
		Connection::HttpError(500, 'Failed to '.
			($rowFound ? 'UPDATE' : 'INSERT').
			" $tableName with {$idPair[0]}={$idPair[1]}.<br/>$err[0] $err[2]");
	}
}