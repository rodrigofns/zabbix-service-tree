<?php
/**
 *  Implementação do codigo responsável pela construção da arvore de servico pelo zabbix

 *  @author Felipe Vaz dos Reis
 *  @version 1.2
 *
 */

require_once(dirname(__FILE__) . '/../../classes/ConexaoBD.class.php');
require_once(dirname(__FILE__) . '/funcoes.php');

header("Content-Type: application/json; charset=ISO-8859-1");

$conexao = "";

function processa_no($no,$tipo)
{
	$conexao = ConexaoBD::getInstance();
/*
        $method = $_SERVER['SERVER_PORT'] == 80 ? "http://" : "https://";
        $var = urldecode($method.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']));
*/
	$retorno = array();
	$retorno['trigger'] = '';
	$retorno['triggerid'] = '';
	$retorno['saida'] = '';

	$triggerid = null;
	$name = null;
	$status = null;
	$trigger = null;
	$image = "";
	$triggerid2 = null;

	$query  = "SELECT name,triggerid,status,serviceid
			FROM services
			WHERE serviceid =".$no;

	$result = $conexao->query($query);

	while ($row = $conexao->fetch_assoc($result)) //para cada filho
	{
		$triggerid = $row['triggerid'];
		$name = $row['name'];
		$status = $row['status'];
		$serviceid = $row['serviceid'];
	}

	$query  = "SELECT i.imageid
			FROM service_icon si
			inner join images i on si.idicon = i.imageid
			WHERE si.idservice = ".$no;

	$result = $conexao->query($query);

	while ($row = $conexao->fetch_assoc($result))  //para cada filho
	{
		 $image = $row['imageid'];
	}

	$saida ="";

	//Se é folha
	if($triggerid != null)
	{
		$query  = 'SELECT DISTINCT REPLACE(t.description,"{HOSTNAME}",h.host) as description, h.host,t.lastchange, t.expression,t.triggerid
				FROM triggers t,
					functions f,
					items i,
					hosts h,
					services s
				WHERE     f.triggerid=t.triggerid AND
						i.itemid=f.itemid AND
						h.hostid=i.hostid AND
						s.triggerid=t.triggerid
						AND s.status>0
						AND t.triggerid='.$triggerid;

		$result = $conexao->query($query);
                
		while ($row = $conexao->fetch_assoc($result)) //para cada filho
		{
			$triggerid2 = $row['triggerid'];
			$trigger = trataString($row['description']);
			$retorno['trigger'] = $trigger;
			$retorno['triggerid'] = $triggerid2;

		}


		//serpro
		$saida.='{';
		$saida .='"id":'.$no.',';
		$saida .='"name":"'.$name.'",';
		$saida .='"size":"120 MB",';
		$saida .='"date":"'.$trigger.'"';//'"date":"'.$trigger.'",';
		$saida.='},';
	}
	else
	{

		$query  = "SELECT servicedownid
				FROM services_links
				WHERE serviceupid = ".$no;
		$result = $conexao->query($query);
		$num_rows = $conexao->numLinhas($result);

		$saida .="{";
		$saida .='"id":'.$no.',';
		$saida .='"name":"'.$name.'",';
		$saida .='"size":"'.$num_rows.' ",';
		$saida .='"date":"'.$trigger.'",';

		if($num_rows>0)
		{
			$saida .='"children":[';

			while ($row = $conexao->fetch_assoc($result)) //para cada filho
			{
				$id = $row['servicedownid'];

				$retorno = processa_no($id,$tipo+1);
				$saida .=$retorno['saida'];
			}

			$saida = substr($saida, 0, -1);

			$saida .=']';
		}
		else
		{
			$saida = substr($saida, 0, -1);
		}

		$saida .='},';
	}

	$retorno['saida'] = $saida;
	return $retorno;
}

$nome_servico = $_GET["nome"];

$conexao = ConexaoBD::getInstance();

if($nome_servico != null)
{
	$id = null;

	$query  = 'SELECT serviceid
			FROM services
			WHERE name = "'.$nome_servico.'"';
	$result = $conexao->query($query);


	while ($row = $conexao->fetch_assoc($result)) 
	{
		$id = $row['serviceid'];
	}

	echo '[{';
	echo '"id":0,';
	echo '"name":"root",';
	echo '"size":"0",';
	echo '"date":"",';

	echo '"children":[';

	$retorno = processa_no($id,0);

	echo utf8_encode(substr($retorno['saida'], 0, -1));

	echo ']}]';
}
else // Carrega toda a arvore com todos os serviços
{

	$query = 'SELECT distinct(name) , serviceid
			FROM services s
			inner join services_links sl on s.serviceid = sl.serviceupid
			WHERE not exists
				(SELECT *
				FROM services_links sl
				WHERE s.serviceid = sl.servicedownid)';
	$result = $conexao->query($query);

	echo '[{';
	echo '"id":0,';
	echo '"name":"root",';
	echo '"size":"0",';
	echo '"date":"",';

	echo '"children":[';

	$teste="";

	while ($row = $conexao->fetch_assoc($result)) 
	{
		$id = $row['serviceid'];
		$retorno = processa_no($id,0);
		$teste .= utf8_encode($retorno['saida']);
	}

	echo substr($teste, 0, -1);
	echo ']}]';

}
ConexaoBD::close();
