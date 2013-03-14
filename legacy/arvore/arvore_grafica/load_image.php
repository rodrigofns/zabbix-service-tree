<?php

/*
 * * SERPRO
 * * Author: Felipe Vaz dos Reis
 * * Description: Load image from image table
 */
require_once(dirname(__FILE__) . '/../../classes/ConexaoBD.class.php');

$link = ConexaoBD::getInstance();

$imageid = $_GET["imageid"];

$link = ConexaoBD::getInstance();

$sql = "SELECT * FROM images where imageid = " . $imageid;
$result = $link->query($sql);
if ($sql = $link->fetch_array($result)) {
    header('Content-Type: image/png');
    echo $sql["image"];
}
ConexaoBD::close();
?>	
