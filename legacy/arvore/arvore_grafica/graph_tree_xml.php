<?php
/**
 * Implementação do codigo responsável pela construção da arvore de servico pelo zabbix.
 * @author Felipe Vaz dos Reis
 * @author Rodrigo Dias (comentários no código)
 * @version 1.2
 */

require_once(dirname(__FILE__).'/../../classes/ConexaoBD.class.php');
require_once(dirname(__FILE__).'/funcoes.php');

header('Content-Type: application/xml; charset=ISO-8859-1');
$conexao = '';

/**
 * Função recursiva para processar cada serviço.
 * @param $no   ID do serviço.
 * @param $tipo Profundidade do nó na árvore.
 */
function processa_no($no, $tipo)
{
	$conexao = ConexaoBD::getInstance();

	$method = $_SERVER['SERVER_PORT'] == 80 ? 'http://' : 'https://';
	$var = urldecode($method.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']));

	$retorno = array();
	$retorno['trigger'] = '';
	$retorno['triggerid'] = '';
	$retorno['saida'] = '';

	$triggerid = null;
	$name = null;
	$status = null;
	$trigger = null;
	$image = '';
	$triggerid2 = null;

	$query = "SELECT name,triggerid,status,serviceid
		FROM services
		WHERE serviceid =" . $no;

	$result = $conexao->query($query);

	while($row = $conexao->fetch_assoc($result)) { // dados do serviço pesquisado
		$triggerid = $row['triggerid'];
		$name = $row['name'];
		$status = $row['status'];
		$serviceid = $row['serviceid'];
	}

	$query = "SELECT i.imageid
		FROM service_icon si
		inner join images i on si.idicon = i.imageid
		WHERE si.idservice = " . $no; // ícone do serviço pesquisado

	$result = $conexao->query($query);

	while($row = $conexao->fetch_assoc($result))
		$image = $row['imageid'];

	$saida = '';

	if($triggerid != null) { // considera folha um serviço que tenha trigger
		$query = 'SELECT DISTINCT REPLACE(t.description,"{HOSTNAME}",h.host) as description, h.host,t.lastchange, t.expression,t.triggerid
			FROM triggers t,
				functions f,
				items i,
				hosts h,
				services s
			WHERE f.triggerid=t.triggerid AND
				i.itemid=f.itemid AND
				h.hostid=i.hostid AND
				s.triggerid=t.triggerid
				AND s.status>0
				AND t.triggerid=' . $triggerid;

        $result = $conexao->query($query);
        while ($row = $conexao->fetch_assoc($result)) { //para cada filho
            $triggerid2 = $row['triggerid'];
            $trigger = trataString($row['description']);
            $retorno['trigger'] = $trigger;
            $retorno['triggerid'] = $triggerid2;
        }

        $saida .= '<leaf>'.
			'<attribute name="name" value="'.processaNome($name).'"/>'.
			'<attribute name="status" value="'.$status.'"/>'.
			'<attribute name="image" value="'.$var."/load_image.php?imageid=".$image.'"/>'.
			'<attribute name="name_full" value="'.$name.'"/>'.
			'<attribute name="type" value="'.$serviceid.'"/>'.
			'<attribute name="alt" value="'.$tipo.'"/>'.
			'<attribute name="trigger" value="'.htmlspecialchars($trigger, ENT_QUOTES).'"/>'.
			'<attribute name="idtrigger" value="'.$triggerid2.'"/>'.
			'</leaf>';
    }
	else { // serviço sem trigger, considera não-folha
		$saida .= '<branch>'.
			'<attribute name="name" value="'.processaNome($name).'"/>'.
			'<attribute name="status" value="'.$status.'"/>'.
			'<attribute name="image" value="'.$var."/load_image.php?imageid=".$image.'"/>'.
			'<attribute name="name_full" value="'.$name.'"/>'.
			'<attribute name="type" value="'.$serviceid.'"/>'.
			'<attribute name="alt" value="'.$tipo.'"/>';

		$query = 'SELECT servicedownid
			FROM services_links
			WHERE serviceupid = '.$no; // pesquisa os filhos deste serviço
		$result = $conexao->query($query);

		while($row = $conexao->fetch_assoc($result)) { // para cada serviço filho
			$id = $row['servicedownid']; // pega o ID do serviço filho
			$retorno = processa_no($id, $tipo + 1); // chama função recursiva para processar o filho
			$saida .= $retorno['saida'];

			if($trigger != '' && $retorno['trigger'] != '') {
				$trigger.='9999' . $retorno['trigger'];
			} else {
				$trigger.= $retorno['trigger'];
			}

			if($triggerid2 != '' & $retorno['triggerid'] != '') {
				$triggerid2 .= 'B' . $retorno['triggerid'];
			} else {
				$triggerid2 .= $retorno['triggerid'];
			}
		}

        $saida .= '<attribute name="trigger" value="'.$trigger.'"/>'.
			'<attribute name="idtrigger" value="'.$triggerid2.'"/>'.
			'</branch>';
    } // fim não-folha

	$query = "SELECT showtree
		FROM service_showtree
		WHERE idservice = " . $no; // verifica o flag showtree
	$result = $conexao->query($query);
	while($row = $conexao->fetch_assoc($result))
		$showtree = $row['showtree'];

    if ($showtree)
        $retorno['saida'] = $saida;
    else
        $retorno['saida'] = ''; // se o showtree for FALSE, retorna vazio

    $retorno['trigger'] = $trigger;
    $retorno['triggerid'] = $triggerid2;

    return $retorno;
}

$conexao = ConexaoBD::getInstance();

echo '<tree>'.
	'<declarations>'.
	'<attributeDecl name="name" type="String"/>'.
	'<attributeDecl name="status" type="String"/>'.
	'<attributeDecl name="image" type="String"/>'.
	'<attributeDecl name="name_full" type="String"/>'.
	'<attributeDecl name="type" type="String"/>'.
	'<attributeDecl name="alt" type="String"/>'.
	'<attributeDecl name="trigger" type="String"/>'.
	'<attributeDecl name="idtrigger" type="String"/>'.
	'</declarations>';

if(isset($_GET['nome'])) { // se foi passado o nome do nó raiz, busca o ID deste serviço
    $id = null;
    $query = "SELECT serviceid FROM services WHERE name = '$_GET[nome]'";
    $result = $conexao->query($query);
    while($row = $conexao->fetch_assoc($result))
        $id = $row['serviceid'];
    $retorno = processa_no($id, 0); // chama função recursiva
    echo utf8_encode($retorno['saida']);
}

echo '</tree>';
ConexaoBD::close();