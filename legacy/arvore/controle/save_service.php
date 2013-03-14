<?php

require_once(dirname(__FILE__) .'/../../classes/Zabbix.class.php');

$rpc = new Zabbix('http://localhost/zabbix/');
$dadosUsuario = null;

$serviceid  = $_REQUEST['id'];
$name 		= $_REQUEST['name'];
$parentid	= $_REQUEST['parentid'];
$algoritmo 	= $_REQUEST['cb_algoritmo'];
$showsla 		= $_REQUEST['showsla'];
$goodsla 		= $_REQUEST['goodsla'];
$triggerid 		= $_REQUEST['triggerid'];
$sortorder 		= $_REQUEST['sortorder'];

//
$peso_normal 	= $_REQUEST['peso_normal'];
$peso_info 		= $_REQUEST['peso_info'];
$peso_alerta 		= $_REQUEST['peso_alerta'];
$peso_media 		= $_REQUEST['peso_media'];
$peso_alta	 	= $_REQUEST['peso_alta'];
$peso_desastre 	= $_REQUEST['peso_desastre'];

$limiar_normal 	= $_REQUEST['limiar_normal'];
$limiar_info 		= $_REQUEST['limiar_info'];
$limiar_alerta 	= $_REQUEST['limiar_alerta'];
$limiar_media 	= $_REQUEST['limiar_media'];
$limiar_alta 		= $_REQUEST['limiar_alta'];
$limiar_desastre 	= $_REQUEST['limiar_desastre'];

//
$params = array(	
				"serviceid" => $_REQUEST['serviceid'],
				"peso_normal" => $_REQUEST['peso_normal'],
				"peso_info" =>$_REQUEST['peso_info'],
				"peso_alerta" => $_REQUEST['peso_alerta'],
				"peso_media" => $_REQUEST['peso_media'],
				"peso_alta" => $_REQUEST['peso_alta'],
				"peso_desastre" => $_REQUEST['peso_desastre'],
				"limiar_normal" => $_REQUEST['limiar_normal'],
				"limiar_info" =>$_REQUEST['limiar_info'],
				"limiar_alerta" => $_REQUEST['limiar_alerta'],
				"limiar_media" => $_REQUEST['limiar_media'],
				"limiar_alta" => $_REQUEST['limiar_alta'],
				"limiar_desastre" => $_REQUEST['limiar_desastre']				
				);


$service = array(	"name" => $name,
				"algorithm" => $algoritmo,
				"parentid" => $parentid,
				"status" => "0",
				"triggerid" => $triggerid,
				"showsla" => $showsla,
				"goodsla" => $goodsla,
				"sortorder" => $sortorder
				);

/*
$service = array(	"name" => $name,
				"algorithm" => "0",
				"parentid" => "2",
				"status" => "0",
				"triggerid" => "0",
				"showsla" => "0",
				"goodsla" => "99.9000",
				"sortorder" => "0"				
				);  
*/
//

try 
{
	$hash = $rpc->autenticar("admin", "zabbix");
	//var_dump($service)
	$result= $rpc->pedir('service.create', $service);
	echo json_encode(array('success'=>true));
}
catch(Exception $e) 
{
	throw $e; // autenticação falhou
	echo json_encode(array('msg'=>'Some errors occured.'));
}
/*
$id = $result['serviceids'];

//$sql = "insert into users(firstname,lastname,phone,email) values('$firstname','$lastname','$phone','$email')";
$sql = "insert into service_weight (idservice,weight_normal,weight_information,weight_alert,weight_average,weight_major,weight_critical) values('$id ','$peso_normal','$peso_info','$peso_media','$peso_alta','$peso_desastre')";
$sql = "insert into service_threshold (idservice,threshold_normal,threshold_information,threshold_alert,threshold_average,threshold_major,threshold_critical) values('$id ','$limiar_normal','$limiar_info','$limiar_media','$limiar_alta','$limiar_desastre')";
$result = @mysql_query($sql);
if ($result){
	echo json_encode(array('success'=>true));
} else {
	echo json_encode(array('msg'=>'Some errors occured.'));
}
*/

?>