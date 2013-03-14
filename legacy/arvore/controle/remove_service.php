<?php
require_once(dirname(__FILE__) .'/../../classes/Zabbix.class.php');

$rpc = new Zabbix('http://localhost/zabbix/');


$serviceid  = $_REQUEST['id'];
try 
{
	$hash = $rpc->autenticar("admin", "zabbix");
        $result= $rpc->pedir('service.delete', array($serviceid));
	
        echo json_encode(array('success'=>true));	
}
catch(Exception $e) 
{
	throw $e; // autenticação falhou
        echo json_encode(array('msg'=>'Some errors occured.'));
}

