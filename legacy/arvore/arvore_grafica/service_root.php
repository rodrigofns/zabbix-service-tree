<?php

require_once(dirname(__FILE__) . '/../../classes/ConexaoBD.class.php');

$services = "";
$conexao = ConexaoBD::getInstance();
$sql = " SELECT distinct(name) as name
	   FROM services s 
	  inner join services_links sl on s.serviceid = sl.serviceupid 
	  WHERE not exists 
	     (SELECT * 
	     FROM services_links sl 
	     WHERE s.serviceid = sl.servicedownid)";

$result = $conexao->query($sql);
while ($row = $conexao->fetch_assoc($result)) {
    $services[] = array('optionValue' => $row['name'], 'optionDisplay' => $row['name']);
}
ConexaoBD::close();
echo json_encode($services);

