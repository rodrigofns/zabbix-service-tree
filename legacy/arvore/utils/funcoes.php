<?php

include_once conn . php;

function salvarPesoseLimiar($params) {
    $sql = "insert into service_weight (idservice,weight_normal,weight_information,weight_alert,weight_average,weight_major,weight_critical) values (" . $params['serviceid'] . "," . $params['weight_normal'] . "," . $params['weight_information'] . "," . $params['weight_alert'] . "," . $params['weight_average'] . "," . $params['weight_major'] . "," . $params['weight_critical'] . ") ";
    $sql = "insert into service_threshold  (idservice,threshold_normal,threshold_information,threshold_alert,threshold_average,threshold_major,threshold_critical) values (" . $params['serviceid'] . "," . $params['threshold_normal'] . "," . $params['threshold_information'] . "," . $params['threshold_alert'] . "," . $params['threshold_average'] . "," . $params['threshold_major'] . "," . $params['threshold_critical'] . ")";
    $result = @mysql_query($sql);
    if ($result) {
        return true;
    } else {
        return false;
    }
}

function atualizarPesoseLimiar($params) {
    
}

function removerPesoseLimiar($params) {
    
}