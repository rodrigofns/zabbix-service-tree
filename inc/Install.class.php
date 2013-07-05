<?php
require_once('Connection.class.php');

/**
 * Internal routines used on the application installation.
 */
class Install
{
	/**
	 * Checks if the required tables exist in Zabbix database.
	 */
	public static function CheckDbTables()
	{
		$dbh = Connection::GetDatabase();
		if(!self::_TableExists($dbh, 'service_threshold')) {
			error_log('The 5 additional tables will be created in Zabbix database.', 0);
			$ress = $dbh->query('

DROP TABLE IF EXISTS service_alert;
CREATE TABLE service_alert (
	idservice BIGINT(20) UNSIGNED NOT NULL,
	status    INT(10)    UNSIGNED NOT NULL,
	idaction  BIGINT(20) UNSIGNED NOT NULL,
	PRIMARY KEY (idservice)
);
DROP TABLE IF EXISTS service_icon;
CREATE TABLE service_icon (
	idservice BIGINT(20) UNSIGNED NOT NULL,
	idicon    BIGINT(20) UNSIGNED NOT NULL,
	PRIMARY KEY (idservice)
);
DROP TABLE IF EXISTS service_showtree;
CREATE TABLE service_showtree (
	idservice BIGINT(20) UNSIGNED NOT NULL,
	showtree  BIGINT(20) UNSIGNED NOT NULL,
	PRIMARY KEY (idservice)
);
DROP TABLE IF EXISTS service_threshold;
CREATE TABLE service_threshold (
	idservice             BIGINT(20) UNSIGNED NOT NULL,
	threshold_normal      DOUBLE PRECISION DEFAULT NULL,
	threshold_information DOUBLE PRECISION DEFAULT NULL,
	threshold_alert       DOUBLE PRECISION DEFAULT NULL,
	threshold_average     DOUBLE PRECISION DEFAULT NULL,
	threshold_major       DOUBLE PRECISION DEFAULT NULL,
	threshold_critical    DOUBLE PRECISION DEFAULT NULL,
	PRIMARY KEY (idservice)
);
DROP TABLE IF EXISTS service_weight;
CREATE TABLE service_weight (
	idservice          BIGINT(20) UNSIGNED NOT NULL,
	weight_normal      DOUBLE PRECISION DEFAULT NULL,
	weight_information DOUBLE PRECISION DEFAULT NULL,
	weight_alert       DOUBLE PRECISION DEFAULT NULL,
	weight_average     DOUBLE PRECISION DEFAULT NULL,
	weight_major       DOUBLE PRECISION DEFAULT NULL,
	weight_critical    DOUBLE PRECISION DEFAULT NULL,
	PRIMARY KEY (idservice)
);

			');

/* This table is not used anywhere, and the auto_increment column is MySQL only; so we left it out.
DROP TABLE IF EXISTS service_map;
CREATE TABLE service_map (
	idservicemap BIGINT(20)  UNSIGNED NOT NULL auto_increment,
	name         VARCHAR(64) NOT NULL,
	mapa         BLOB        NOT NULL,
	PRIMARY KEY (idservicemap),
	UNIQUE KEY idservicemap (idservicemap)
);*/

			if(!$ress) {
				$err = $dbh->errorInfo();
				Connection::HttpError(500, "Failed to create tables.<br/>$err[0] $err[2]");
			}
		}
	}

	/**
	 * Checks if a given table exists in database.
	 * @param  PDO    $dbh       Database connection handle.
	 * @param  string $tableName Name of the table to be checked.
	 * @return bool
	 */
	private static function _TableExists($dbh, $tableName)
	{
		$stmt = $dbh->prepare('SELECT 1 FROM '.$tableName);
		if(!$stmt->execute())
			return false;
		return $stmt->fetch(PDO::FETCH_NUM) != false;
	}
}