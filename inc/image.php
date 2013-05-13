<?php
require_once('Connection.class.php');

if(isset($_GET['id']))
{
	// Retrieves an image by ID from Zabbix database.

	$dbh = Connection::GetDatabase();
	$stmt = $dbh->prepare('
		SELECT image
		FROM images
		WHERE imageid = :imageId
	');
	$stmt->bindParam(':imageId', $_GET['id']);
	if(!$stmt->execute()) {
		$err = $stmt->errorInfo();
		Connection::HttpError(500, "Failed to query image for '$_GET[id]'.<br/>$err[0] $err[2]");
	}
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	header('Content-Type: image/png');
	echo $row['image'];
}