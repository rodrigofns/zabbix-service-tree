<?php
require_once('Zabbix.class.php');

/**
 * Manages database and Zabbix API connections.
 * Uses legacy config.ini file for all settings.
 */
class Connection
{
	/**
	 * Outputs an HTTP error code and halts the script.
	 * @param int    $code HTTP error code to be set.
	 * @param string $msg  Text message to be output.
	 */
	public static function HttpError($code, $msg)
	{
		error_log($msg, 0); // also log to Apache
		$httpErr = array(
			400 => 'Bad Request',
			401 => 'Unauthorized',
			500 => 'Internal Server Error'
		);
		header('Content-type:text/html; charset=UTF-8');
		header(sprintf('HTTP/1.1 %d %s', $code, $httpErr[$code]));
		die($msg);
	}

	/**
	 * Returns the current base URL to be used on cURL requests.
	 * @return string
	 */
	public static function BaseUrl()
	{
		$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http').'://'.
			$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		return substr($url, 0, strrpos($url, '/') + 1); // includes final slash
	}

	/**
	 * Returns a PDO object connecting to database, according to legacy config.ini file.
	 * @return PDO
	 */
	public static function GetDatabase()
	{
		$dbConf = self::_LoadZabbixConf();
		$pdoStr = sprintf('%s:host=%s;dbname=%s',
			strtolower($dbConf['TYPE']), $dbConf['SERVER'], $dbConf['DATABASE']);
		try {
			$dbh = new PDO($pdoStr, $dbConf['USER'], $dbConf['PASSWORD']);
			$dbh->exec('SET NAMES utf8');
		} catch(PDOException $e) {
			self::HttpError(500, $e->getMessage());
		}
		return $dbh; // returns a PDO connection object
	}

	/**
	 * Returns a Zabbix API RPC object, according to legacy config.ini file.
	 * @param  string $hash Zabbix session hash.
	 * @return Zabbix
	 */
	public static function GetZabbixApi($hash)
	{
		$dbConf = self::_LoadZabbixConf();
		return new Zabbix($dbConf['API'], $hash);
	}

	/**
	 * Returns the parsed zabbix.conf file.
	 * @return array
	 */
	private static function _LoadZabbixConf()
	{
		include(dirname(__FILE__).'/../__conf.php');
		if(!is_readable($ZABBIX_CONF)) {
			self::HttpError(500, 'Failed to read Zabbix conf, see settings in __conf.php file.');
			return false;
		}
		include($ZABBIX_CONF);
		if(!isset($DB)) {
			self::HttpError(500, 'Zabbix conf looks wrong, see settings in __conf.php file.');
			return false;
		}
		$DB['API'] = $ZABBIX_API; // push API URL into return array
		return $DB; // Zabbix associative array with all database settings
	}
}