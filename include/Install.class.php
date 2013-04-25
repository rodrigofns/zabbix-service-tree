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
			$ress = $dbh->query('
				DROP TABLE IF EXISTS service_alert;
				CREATE TABLE service_alert (
					idservice bigint(20) unsigned NOT NULL,
					status    int(10) unsigned NOT NULL,
					idaction  bigint(20) unsigned NOT NULL,
					PRIMARY KEY (idservice)
				);
				DROP TABLE IF EXISTS service_icon;
				CREATE TABLE service_icon (
					idservice bigint(20) unsigned NOT NULL,
					idicon    bigint(20) unsigned NOT NULL,
					PRIMARY KEY (idservice)
				);
				DROP TABLE IF EXISTS service_map;
				CREATE TABLE service_map (
					idservicemap bigint(20) unsigned NOT NULL auto_increment,
					name         varchar(64) NOT NULL,
					mapa         blob NOT NULL,
					PRIMARY KEY (idservicemap),
					UNIQUE KEY idservicemap (idservicemap)
				);
				DROP TABLE IF EXISTS service_showtree;
				CREATE TABLE service_showtree (
					idservice bigint(20) unsigned NOT NULL,
					showtree  bigint(20) unsigned NOT NULL,
					PRIMARY KEY (idservice)
				);
				DROP TABLE IF EXISTS service_threshold;
				CREATE TABLE service_threshold (
					idservice             bigint(20) unsigned NOT NULL,
					threshold_normal      double default NULL,
					threshold_information double default NULL,
					threshold_alert       double default NULL,
					threshold_average     double default NULL,
					threshold_major       double default NULL,
					threshold_critical    double default NULL,
					PRIMARY KEY (idservice)
				);
				DROP TABLE IF EXISTS service_weight;
				CREATE TABLE service_weight (
					idservice          bigint(20) unsigned NOT NULL,
					weight_normal      double default NULL,
					weight_information double default NULL,
					weight_alert       double default NULL,
					weight_average     double default NULL,
					weight_major       double default NULL,
					weight_critical    double default NULL,
					PRIMARY KEY (idservice)
				);
			');
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
		$ress = $dbh->query("SHOW TABLES LIKE '$tableName'");
		if(!$ress) {
			$err = $dbh->errorInfo();
			Connection::HttpError(500, "Failed to check if table exists.<br/>$err[0] $err[2]");
		}
		$tableFound = false;
		while($row = $ress->fetch(PDO::FETCH_NUM)) $tableFound = true;
		return $tableFound;
	}
}