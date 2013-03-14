<?php

/*
 * * SERPRO
 * * Author: Felipe Vaz dos Reis
 * * Description: Show all Triggers with problem by Service in XML format
 */
require_once(dirname(__FILE__) . '/../../classes/ConexaoBD.class.php');
require_once(dirname(__FILE__) . '/funcoes.php');
require_once(dirname(__FILE__) . '/xml2json.php');

//$saida = "";

function processa_no($no, $tipo) {
    $retorno = array();
    $retorno['trigger'] = '';
    $retorno['saida'] = '';

    $triggerid = null;
    $name = null;
    $status = null;
    $trigger = null;
  //  $image = "";

    $host = null;
    $item = null;

    $query = "select name,triggerid,status,serviceid 
			from services 
			where serviceid =" . $no;

    $conexao = ConexaoBD::getInstance();
    $result = $conexao->query($query);

    while ($row = $conexao->fetch_assoc($result)) { //para cada filho
        $triggerid = $row['triggerid'];
        $name = $row['name'];
        $status = $row['status'];
        //$serviceid = $row['serviceid'];
    }

    $saida = "";

    if ($triggerid != null) {// is leaf
        $query = 'SELECT DISTINCT REPLACE(t.description,"{HOSTNAME}",h.host) as triggerdescription, h.host,h.hostid,t.expression,t.triggerid,s.status,i.description,t.lastchange 
				FROM triggers t, functions f, items i, hosts h, services s 
				WHERE f.triggerid=t.triggerid AND i.itemid=f.itemid AND h.hostid=i.hostid AND s.triggerid=t.triggerid AND s.status>0 AND t.triggerid=' . $triggerid;

        $result = $conexao->query($query);

        $can_render = false;

        while ($row = $conexao->fetch_assoc($result)) { //para cada filho
            $trigger = trataString($row['triggerdescription']);
            $host = $row['host'];
            $hostid = $row['hostid'];
            $status = $row['status'];
            $item = $row['description'];
            $time = $row['lastchange'];

            $can_render = true;
        }

        if ($can_render) {
            $saida .="<trigger>";

            $saida .='<triggerid>';
            $saida .=$triggerid;
            $saida .='</triggerid>';

            $saida .='<time>';
            $saida .=$time;
            $saida .='</time>';

            $saida .='<description>';
            $saida .=htmlspecialchars($trigger, ENT_QUOTES);
            $saida .='</description>';

            $saida .='<status>';
            $saida .=processa_status($status);
            $saida .='</status>';

            $saida .='<host>';
            $saida .=$host;
            $saida .='</host>';

            $saida .='<hostid>';
            $saida .=$hostid;
            $saida .='</hostid>';

            $saida .='<item>';
            $saida .=$item;
            $saida .='</item>';

            $saida .="</trigger>";
        }
    } else {
        $query = "select servicedownid 
				from services_links 
				where serviceupid = " . $no;
        $result = $conexao->query($query);

        while ($row = $conexao->fetch_assoc($result)) { //para cada filho
            $id = $row['servicedownid'];
            $saida .=processa_no($id, 1);
        }
    }

    return $saida;
}

$conexao = ConexaoBD::getInstance();
echo $final = '';
$final .= '<xml>';

$nome_servico = $_GET["nome"];
if ($nome_servico != null) {
    $id = null;
    $query = 'select serviceid 
			from services 
			where name = "' . $nome_servico . '"';
    $result = $conexao->query($query);
    while ($row = $conexao->fetch_assoc($result)) {
        $id = $row['serviceid'];
    }

    $final .= utf8_encode(processa_no($id, 0));
}

$final .= '</xml>';
echo $final;

ConexaoBD::close();
?>
