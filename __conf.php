<?php

// The whole system loads the settings from the Zabbix
// configurations indicated below.

// Don't forget to set log_errors=On in php.ini .
// Log is likely to be stored in /var/log/apache2/error.log .

$ZABBIX_CONF = '/rede/www/zabbix-2.0.2-masternode/conf/zabbix.conf.php';
$ZABBIX_API = 'http://localhost/zabbix/zabbix-2.0.2-masternode';