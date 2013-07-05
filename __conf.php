<?php

// This constant appears undefined in some Zabbix instances, so let's use the hammer.
if(!defined('IMAGE_FORMAT_PNG'))
	define('IMAGE_FORMAT_PNG', 'PNG');

// The whole system loads the settings from the Zabbix
// configurations indicated below.

// Don't forget to set log_errors=On in php.ini .
// Log is likely to be stored in /var/log/apache2/error.log .

$ZABBIX_CONF = '/rede/www/zabbix-2.0.6/conf/zabbix.conf.php';
$ZABBIX_API = 'http://localhost/zabbix/zabbix-2.0.6';