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
			$bigint = ($dbh->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') ? 'BIGINT' : 'BIGINT UNSIGNED';
			$int = ($dbh->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') ? 'INTEGER' : 'INT UNSIGNED';
			try {
				$dbh->beginTransaction();
				$dbh->exec("
					DROP TABLE IF EXISTS service_alert;
					CREATE TABLE service_alert (
						idservice $bigint NOT NULL,
						status    $int NOT NULL,
						idaction  $bigint NOT NULL,
						PRIMARY KEY (idservice)
					);");
				$dbh->exec("
					DROP TABLE IF EXISTS service_icon;
					CREATE TABLE service_icon (
						idservice $bigint NOT NULL,
						idicon    $bigint NOT NULL,
						PRIMARY KEY (idservice)
					);");
				$dbh->exec("
					DROP TABLE IF EXISTS service_showtree;
					CREATE TABLE service_showtree (
						idservice $bigint NOT NULL,
						showtree  $bigint NOT NULL,
						PRIMARY KEY (idservice)
					);");
				$dbh->exec("
					DROP TABLE IF EXISTS service_threshold;
					CREATE TABLE service_threshold (
						idservice             $bigint NOT NULL,
						threshold_normal      DOUBLE PRECISION DEFAULT 0,
						threshold_information DOUBLE PRECISION DEFAULT 1,
						threshold_alert       DOUBLE PRECISION DEFAULT 10,
						threshold_average     DOUBLE PRECISION DEFAULT 100,
						threshold_major       DOUBLE PRECISION DEFAULT 1000,
						threshold_critical    DOUBLE PRECISION DEFAULT 10000,
						PRIMARY KEY (idservice)
					);");
				$dbh->exec("
					DROP TABLE IF EXISTS service_weight;
					CREATE TABLE service_weight (
						idservice          $bigint NOT NULL,
						weight_normal      DOUBLE PRECISION DEFAULT 0,
						weight_information DOUBLE PRECISION DEFAULT 1,
						weight_alert       DOUBLE PRECISION DEFAULT 10,
						weight_average     DOUBLE PRECISION DEFAULT 100,
						weight_major       DOUBLE PRECISION DEFAULT 1000,
						weight_critical    DOUBLE PRECISION DEFAULT 10000,
						PRIMARY KEY (idservice)
					);");
				$dbh->commit();
			} catch(Exception $e) {
				$dbh->rollback();
				Connection::HttpError(500, 'Failed to create tables.<br/>'.$e->getMessage());
			}

/* This table is not used anywhere, and the auto_increment column is MySQL only; so we left it out.
DROP TABLE IF EXISTS service_map;
CREATE TABLE service_map (
	idservicemap $bigint NOT NULL auto_increment,
	name         VARCHAR(64) NOT NULL,
	mapa         BLOB        NOT NULL,
	PRIMARY KEY (idservicemap),
	UNIQUE KEY idservicemap (idservicemap)
);*/

			/*if(!$ress) {
				$err = $dbh->errorInfo();
				Connection::HttpError(500, "Failed to create tables.<br/>$err[0] $err[2]");
			}*/
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