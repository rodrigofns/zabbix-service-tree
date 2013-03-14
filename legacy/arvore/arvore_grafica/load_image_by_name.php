<?php

/*
 * * SERPRO
 * * Author: Felipe Vaz dos Reis
 * * Description: Load image from image table by Service Name
 */
require_once(dirname(__FILE__) . '/../../classes/ConexaoBD.class.php');

header('Content-Type: image/png');

$link = ConexaoBD::getInstance();

$service = $_GET["service"];

$sql = '    select i.image,i.name 
		from images i 
		inner join service_icon si on si.idicon = i.imageid 
		inner join services s on s.serviceid = si.idservice 
		where s.name = "' . $service . '"';

$limite = $link->query("$sql");

if ($sql = $link->fetch_array($limite)) {

    $nome = $sql["name"];
    $foto = $sql["image"];

    echo $foto;
}
ConexaoBD::close();