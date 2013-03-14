<?php
require_once(dirname(__FILE__) .'/../../classes/Zabbix.class.php');

$rpc = new Zabbix('http://localhost/zabbix/');
$dadosUsuario = null;

$serviceid  = $_REQUEST['id'];
$service= array("serviceid" => $serviceid);

try 
{
	$hash = $rpc->autenticar("admin", "zabbix");
        
        if($serviceid != 0){
            $result= $rpc->pedir('service.get', array( 'output' => 'extend', 'filter' => $service));
        }
        
        // Display parent services list
        if (isset($_REQUEST['pservices'])) {
            $result = $rpc->pedir('service.get', array( 'output' => 'extend'));
            foreach($result as $index=>$service){
                if($service->triggerid != 0){ // remove leaf nodes
                    unset($result[$index]);
                }
                
            }
        }
        
        
	echo json_encode($result);	
}
catch(Exception $e) 
{
	throw $e; // autenticação falhou
}

