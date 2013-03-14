<?php

/*
 * * SERPRO
 * * Author: Felipe Vaz dos Reis
 * * Description:
 */
require_once(dirname(__FILE__) . '/../../classes/ConexaoBD.class.php');
require_once(dirname(__FILE__) . '/funcoes.php');
$status = -1;
$id = $_GET["id"];

$link = ConexaoBD::getInstance();

$sql = "	SELECT status 
		FROM services 
		WHERE serviceid = " . $id;
$limite = $link->query($sql);

if ($sql = $link->fetch_array($limite)) {
    $status = $sql["status"];
    echo processa_status_cor($status);
}

if ($status == -1) {
    echo '0xFFFFFF';
}

ConexaoBD::close();