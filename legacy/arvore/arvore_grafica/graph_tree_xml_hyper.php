<?php

/**
 * * SERPRO
 * * Author: Felipe Vaz dos Reis
 * * Description: Read Database information and generate XML to HyperBolic View
 */
require_once(dirname(__FILE__) . '/../../classes/ConexaoBD.class.php');
require_once(dirname(__FILE__) . '/funcoes.php');

header("Content-Type: application/xml; charset=UTF-8");

$conexao = "";
function processa_no($no, $tipo) {
    $conexao = ConexaoBD::getInstance();

    $method = $_SERVER['SERVER_PORT'] == 80 ? "http://" : "https://";
    $var = urldecode($method.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']));

    $triggerid = null;
    $name = null;
    $status = null;
    $trigger = null;
    $image = "";

    $query = "SELECT name,triggerid,status,serviceid 
			FROM services 
			WHERE serviceid =" . $no;
    $result = $conexao->query($query);
    while ($row = $conexao->fetch_assoc($result)) {

        $triggerid = $row['triggerid'];
        $serviceid = $row['serviceid'];
        $name = trataString($row['name']);
        $status = $row['status'];
    }

    $query = "SELECT i.imageid 
			FROM service_icon si 
			inner join images i on si.idicon = i.imageid 
			WHERE si.idservice = " . $no;

    $result = $conexao->query($query);
    while ($row = $conexao->fetch_assoc($result)) {
        $image = $row['imageid'];
    }

    if ($triggerid != null) {// is leaf
        $query = 'SELECT DISTINCT REPLACE(t.description,"{HOSTNAME}",h.host) as description, h.host,t.expression,t.triggerid 
				FROM triggers t, 
					functions f, 
					items i, 
					hosts h, 
					services s 
				WHERE f.triggerid=t.triggerid AND 
						i.itemid=f.itemid AND 
						h.hostid=i.hostid AND 
						s.triggerid=t.triggerid AND 
						s.status>0 AND 
						t.triggerid=' . $triggerid;

        $result = $conexao->query($query);
        while ($row = $conexao->fetch_assoc($result)) {
            $trigger = htmlentities($row['description']);
            $retorno['trigger'] = $trigger;
        }

        $saida .= '<node id="' . $serviceid . '" backcolor="' . processa_status_cor($status) . '" >';
        $saida .= '<label>' . $name . '</label>';
        $saida .= '<img src="' . $var . '/load_image.php?imageid=' . $image . '"/>';
        $saida .="</node>";
    } else { //not is leaf
        $saida .= '<node id="' . $serviceid . '" backcolor="' . processa_status_cor($status) . '" >';
        $saida .= '<label>' . $name . '</label>';
        $saida .= '<img src="' . $var . '/load_image.php?imageid=' . $image . '"/>';

        $query = "SELECT servicedownid 
				FROM services_links 
				WHERE serviceupid = " . $no;
        $result = $conexao->query($query);

        while ($row = $conexao->fetch_assoc($result)) {
            $id = $row['servicedownid'];

            $retorno = processa_no($id, $tipo + 1);
            $saida.=$retorno['saida'];

            if ($trigger != '' && $retorno['trigger'] != '') {
                $trigger.='<BR>' . $retorno['trigger'];
            } else {
                $trigger.=$retorno['trigger'];
            }
        }

        $saida .='<content>';
        $saida .=htmlentities($trigger);
        $saida .='</content>';

        $saida .="</node>";
    }

    $retorno['saida'] = $saida;
    $retorno['trigger'] = $trigger;

    return $retorno;
}

echo '<?xml version="1.0" encoding="UTF-8" standalone="no"?>';
echo '<treebolic focus-on-hover="false" popupmenu="true" statusbar="true" toolbar="true" tooltip="true">';
echo '<tree backcolor="ffffff" expansion="0.9" fontface="Tahoma Gras" fontsize="20" fontsizestep="2" forecolor="000000" orientation="radial" preserve-orientation="true" sweep="1.2">';
echo '<nodes backcolor="ffffff" forecolor="ffffff">';
echo '<img src="bball.png"/>';
echo '<default.treeedge color="9999ff" FROMterminator="c" stroke="dash" toterminator="t">';
echo '<img src="jump.png"/>';
echo '</default.treeedge>';

$conexao = ConexaoBD::getInstance();

$nome_servico = $_GET["nome"];

if ($nome_servico != null) {
    $id = null;

    $query = 'SELECT serviceid 
			FROM services 
			WHERE name = "' . $nome_servico . '"';
    $result = $conexao->query($query);
    while ($row = $conexao->fetch_assoc($result)) {
        $id = $row['serviceid'];
    }

    $retorno = processa_no($id, 0);
    echo utf8_encode($retorno['saida']);
}

echo '</nodes> ';
echo '</tree>';
echo '</treebolic>';

ConexaoBD::close();