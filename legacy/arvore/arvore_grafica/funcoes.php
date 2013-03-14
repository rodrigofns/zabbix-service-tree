<?php

/**
 * Convert status information to Color
 */
function processa_status_cor($status) {
    if ($status == 0) {
        return '0x00FF00';
    } else if ($status == 1) {
        return '0x0000FF';
    } else if ($status == 2) {
        return '0xFFFF00';
    } else if ($status == 3) {
        return '0xFF9966';
    } else if ($status == 4) {
        return '0xFF0000';
    } else if ($status == 5) {
        return '0x990066';
    }
}

function processaNome($nome) {
    if (strlen($nome) > 16) {
        return substr($nome, 0, 16) . "...";
    }
    else
        return $nome;
}

/**
 * Remove accent from text
 */
function trataString($texto) {

    $trocarIsso = array('à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ü', 'ú', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'O', 'Ù', 'Ü', 'Ú', '?',);
    $porIsso = array('a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', '0', 'U', 'U', 'U', 'Y',);
    $titletext = str_replace($trocarIsso, $porIsso, $texto);
    return $titletext;
}

/* Traduz o codigo do status */

function processa_status($status) {
    if ($status == 0) {
        return "TRIGGER_SEVERITY_NOT_CLASSIFIED";
    } else if ($status == 1) {
        return "TRIGGER_SEVERITY_INFORMATION";
    } else if ($status == 2) {
        return "TRIGGER_SEVERITY_WARNING";
    } else if ($status == 3) {
        return "TRIGGER_SEVERITY_AVERAGE";
    } else if ($status == 4) {
        return "TRIGGER_SEVERITY_HIGH";
    } else if ($status == 5) {
        return "TRIGGER_SEVERITY_DISASTER";
    }
}


/**
 * Delete Service by Id
 */
/*
function deleteService($id) {
    $sql = 'delete from service_weight where idservice =' . $id;
    $res = mysql_query($sql);
    $sql = 'delete from service_threshold where idservice =' . $id;
    $res = mysql_query($sql);
    $sql = 'delete from service_icon where idservice =' . $id;
    $res = mysql_query($sql);
    $sql = 'delete from service_showtree where idservice =' . $id;
    $res = mysql_query($sql);
    $sql = 'delete FROM service_group where serviceid = ' . $id;
    $result = mysql_query($sql);
}
*/

/**
 * Update Service Action
 */
/*
function updateServiceAction($name, $serviceid, $status, $acao) {
    $sql = 'select * from service_action where idservice = ' . $serviceid . ' and status = ' . $status; //0
    $result = mysql_query($sql);
    $action = 0;
    $tem = false;

    while ($resultado = DBfetch($result)) {
        if ($acao == 0) {
            $sql = 'delete from service_action where idservice = ' . $serviceid . ' and status = ' . $status; //0
            mysql_query($sql);
        } else if ($acao != $resultado['idaction']) {
            $sql = 'update service_action SET idaction = ' . $acao . ' where idservice = ' . $serviceid . ' and status = ' . $status;
            mysql_query($sql);
        }

        $tem = true;
    }

    //Se nao tem entao cria
    if ($tem == false) {
        $triggerid = get_dbid('triggers', 'triggerid'); //pega o triggerid
        $sql = 'insert into triggers (triggerid, expression, description, url, status, value, priority, lastchange, dep_level, comments, error) values (' . $triggerid . ', "Service Status = ","' . $name . '","",0,1,5,0,0,"Trigger Service Action",0)';
        mysql_query($sql);

        $sql = 'SELECT nextid
			FROM ids i
			where table_name = "events"';
        $result = mysql_query($sql);

        $eventid = 999999;
        while ($resultado = DBfetch($result)) {
            $eventid = $resultado['nextid'];
            $nextid = $eventid++;

            $sql = 'update ids set nextid = ' . $nextid . ' where table_name = "events"';
            mysql_query($sql);

            $sql = 'insert into events (eventid,source,object,objectid,clock,value,acknowledged) values (' . $eventid . ',2,3,' . $triggerid . ',1312579667,1,0)';
            mysql_query($sql);

            $sql = 'insert into service_action (idservice,status,idaction,idtrigger,idevent) values (' . $serviceid . ', ' . $status . ', ' . $acao . ' ,    ' . $triggerid . '  ,' . $eventid . ' )';
            mysql_query($sql);
        }
    }
}
*/

/**
 * Add Service Action
 */
/*
function addServiceAction($name, $serviceid, $action, $status) {
    $triggerid = get_dbid('triggers', 'triggerid'); //pega o triggerid
    $sql = 'insert into triggers (triggerid, expression, description, url, status, value, priority, lastchange, dep_level, comments, error) values (' . $triggerid . ', "Service Status = ","' . $name . '","",0,1,5,0,0,"Trigger Service Action",0)';
    mysql_query($sql);

    $sql = 'SELECT nextid FROM ids i where table_name = "events"';
    $result = mysql_query($sql);

    $eventid = 999999;
    while ($resultado = DBfetch($result)) {
        $eventid = $resultado['nextid'];
        $nextid = $eventid++;

        $sql = 'update ids set nextid = ' . $nextid . ' where table_name = "events"';
        mysql_query($sql);

        $sql = 'insert into events (eventid,source,object,objectid,clock,value,acknowledged) values (' . $eventid . ',2,3,' . $triggerid . ',1312579667,1,0)';
        mysql_query($sql);

        $sql = 'insert into service_action (idservice,status,idaction,idtrigger,idevent) values (' . $serviceid . ', ' . $status . ', ' . $action . ' ,    ' . $triggerid . '  ,' . $eventid . ' )';
        mysql_query($sql);
    }
}
*/
?>