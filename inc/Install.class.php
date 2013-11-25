<?php
require_once('Connection.class.php');

require_once('__conf.php');
require_once('i18n/i18n.php');
i18n_set_map('en', $LANG, false);

/**
 * Internal routines used on the application installation.
 */
class Install
{
	/**
	 * Checks if the required tables exist in Zabbix database.
	 * @param PDO $dbh  Database connection handle.
	 */
	public static function CheckDbTables(PDO $dbh)
	{
		if(!self::_TableExists($dbh, 'service_threshold')) {
			error_log(I('The 5 additional tables will be created in Zabbix database.'), 0);
			$bigint = ($dbh->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') ? 'BIGINT' : 'BIGINT UNSIGNED';
			$int = ($dbh->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') ? 'INTEGER' : 'INT UNSIGNED';
			try {
				if(!$dbh->beginTransaction())
					self::_TellDbError($dbh, I('Failed to init transaction for tables creation.'));
				$dbh->exec('DROP TABLE IF EXISTS service_alert');
				$dbh->exec("CREATE TABLE service_alert (
					idservice $bigint NOT NULL,
					status    $int NOT NULL,
					idaction  $bigint NOT NULL,
					PRIMARY KEY (idservice)
				)");
				$dbh->exec('DROP TABLE IF EXISTS service_icon');
				$dbh->exec("CREATE TABLE service_icon (
					idservice $bigint NOT NULL,
					idicon    $bigint NOT NULL,
					PRIMARY KEY (idservice)
				)");
				$dbh->exec('DROP TABLE IF EXISTS service_showtree');
				$dbh->exec("CREATE TABLE service_showtree (
					idservice $bigint NOT NULL,
					showtree  $bigint NOT NULL,
					PRIMARY KEY (idservice)
				)");
				$dbh->exec('DROP TABLE IF EXISTS service_threshold');
				$dbh->exec("CREATE TABLE service_threshold (
					idservice             $bigint NOT NULL,
					threshold_normal      DOUBLE PRECISION DEFAULT 0,
					threshold_information DOUBLE PRECISION DEFAULT 1,
					threshold_alert       DOUBLE PRECISION DEFAULT 10,
					threshold_average     DOUBLE PRECISION DEFAULT 100,
					threshold_major       DOUBLE PRECISION DEFAULT 1000,
					threshold_critical    DOUBLE PRECISION DEFAULT 10000,
					PRIMARY KEY (idservice)
				)");
				$dbh->exec('DROP TABLE IF EXISTS service_weight');
				$dbh->exec("CREATE TABLE service_weight (
					idservice          $bigint NOT NULL,
					weight_normal      DOUBLE PRECISION DEFAULT 0,
					weight_information DOUBLE PRECISION DEFAULT 1,
					weight_alert       DOUBLE PRECISION DEFAULT 10,
					weight_average     DOUBLE PRECISION DEFAULT 100,
					weight_major       DOUBLE PRECISION DEFAULT 1000,
					weight_critical    DOUBLE PRECISION DEFAULT 10000,
					PRIMARY KEY (idservice)
				)");
				if(!$dbh->commit())
					self::_TellDbError($dbh, I('Failed to commit tables creation.'));
			} catch(Exception $e) {
				$dbh->rollback();
				Connection::HttpError(500, I('Failed to create tables.').'<br/>'.$e->getMessage());
			}
		}
	}

	/**
	 * Checks if a given table exists in database.
	 * @param  PDO    $dbh       Database connection handle.
	 * @param  string $tableName Name of the table to be checked.
	 * @return bool
	 */
	private static function _TableExists(PDO $dbh, $tableName)
	{
		$stmt = $dbh->prepare('SELECT 1 FROM '.$tableName);
		if(!$stmt->execute())
			return false;
		return $stmt->fetch(PDO::FETCH_NUM) != false;
	}

	/**
	 * Outputs latest database error.
	 * @param PDO    $dbh Database connection handle.
	 * @param string $msg Additional text to be outputted.
	 */
	private static function _TellDbError(PDO $dbh, $msg)
	{
		$err = $dbh->errorInfo();
		Connection::HttpError(500, "$msg<br/>$err[0] $err[2]");
	}
}