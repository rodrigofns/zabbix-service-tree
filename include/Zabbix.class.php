<?php
/**
* Manipulação de baixo nível das requisições JSON-RPC do Zabbix.
* @author Roberto Falanga <roberto.falanga@serpro.gov.br>
* @author Rodrigo Dias <rodrigo.dias@serpro.gov.br>
* @package Persistencia
*/
class Zabbix
{
	const DEBUG      = false;
	const PAGINA_API = 'api_jsonrpc.php';
	const USER_AGENT = 'ZabbixAPI v1.0';
	const ID_ZABBIX  = 1;
	const TIMEOUT    = 30;

	private   $url       = '';   // string; URL do servidor do Zabbix
	private   $hash32    = null; // string; hash de conexão que vem do Zabbix após o login
	protected $versaoApi = null; // string; versão da API rodando no Zabbix

	/**
	* Construtor da classe.
	* @param string $url  URL da API do Zabbix.
	* @param string $hash Hash de conexão existente; opcional.
	* @param string $versaoApi  Versão da API do Zabbix a ser utilizada; opcional.
	*/
	function __construct($url, $hash=null, $versaoApi=null)
	{
		$this->url       = $url.(substr($url, -1) == '/' ? '' : '/');
		$this->hash32    = $hash;
		$this->versaoApi = $versaoApi;
	}

	/**
	* Verifica se a URL da API do servidor Zabbix existe.
	* @throws Exception
	*/
	function urlExiste()
	{
		$urlHeaders = @get_headers($this->url.self::PAGINA_API);
		if($urlHeaders[0] == 'HTTP/1.1 404 Not Found')
			throw new Exception('A URL "'.$this->url.self::PAGINA_API.'" para a API não existe.');
	}

	/**
	* Retorna o hash de conexão atual.
	* @return string Hash de conexão de 32 caracteres.
	*/
	public function hash()
	{
		return $this->hash32;
	}

	/**
	* Retorna a versão da API do Zabbix instalada neste servidor.
	* @return string  A versão da API.
	* @throws Exception
	*/
	public function versaoApi()
	{
		// Zabbix 1.8 ---> API 1.3 - http://www.zabbix.com/documentation/1.8/api/apiinfo/version
		// Zabbix 2.0 ---> API 1.4 - http://www.zabbix.com/documentation/2.0/manual/appendix/api/apiinfo/version
		// Nota: estranhamente, parece que é preciso estar autenticado (ter um hash) para
		//  conseguir descobrir qual a versão da API em uso. Por isso, esta verificação não
		//  é feita no início do sistema, mas no ato do login do usuário.
		return $this->versaoApi;
	}

	/**
	* Faz login no Zabbix; o usuário deve ter permissão de acesso à API.
	* @param  string $usuario Nome de usuário.
	* @param  string $senha   Senha do usuário.
	* @return string          Hash da conexão estabelecida, de 32 caracteres.
	* @throws Exception
	*/
	public function autenticar($usuario, $senha)
	{
		try {
			$this->hash32 = $this->pedir('user.login', array(
				'user' => $usuario,
				'password' => $senha ));
			$this->versaoApi = $this->pedir('apiinfo.version', array());
		}
		catch(Exception $e) {
			throw $e;
		}

		return $this->hash32;
	}

	/**
	* Faz uma requisição direta à API do Zabbix.
	* @param  string          $metodo Entidade e método a ser invocado, como "history.get".
	* @param  array[mixed]    $params Array associativo com os parâmetros da consulta (itemids, output, etc).
	* @return array[stdClass]         Array associativo com o resultado da requisição.
	* @throws Exception
	*/
	public function pedir($metodo, $params)
	{
		$dados = json_decode($this->_requisitar($metodo, $params));

		if(is_null($dados))
			throw new Exception('A API nao respondeu.');
		if(isset($dados->error))
			throw new Exception($dados->error->data);

		return $dados->result;
	}

	/**
	* Requisição de baixo nível.
	* @param  string       $metodo Entidade e método a ser invocado, como "history.get".
	* @param  array[mixed] $params Array associativo com os parâmetros da consulta (itemids, output, etc).
	* @return array[mixed]         Array associativo cru com o resultado da requisição.
	*/
	private function _requisitar($metodo, $params)
	{
		$apiRequest = array(
				'auth'    => $this->hash32,
				'method'  => $metodo,
				'id'      => self::ID_ZABBIX,
				'params'  => $params,
				'jsonrpc' => '2.0'
		);

		$curl = curl_init($this->url.self::PAGINA_API);
		curl_setopt_array($curl, array(
			CURLOPT_CUSTOMREQUEST  => 'POST',
			CURLOPT_ENCODING       => 'gzip',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => self::TIMEOUT,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_FRESH_CONNECT  => true,
			CURLOPT_POSTFIELDS     => json_encode($apiRequest),
			CURLOPT_HTTPHEADER     => array(
				'Content-Type: application/json-rpc',
				'User-Agent: '.self::USER_AGENT
			)
		));

		$req = curl_exec($curl);
		curl_close($curl);

		if(self::DEBUG) {
			$fdbg = @fopen(realpath('../persistencia').'/api.log', 'a+');
			fwrite($fdbg,
				date("Y-m-d H:i:s ------------------------------------------------------------\n").
				"--- REQUEST ---\n".
				print_r($apiRequest, true).
				"--- RESPONSE ---\n".
				print_r(json_decode($req), true)."\n"
			);
		}

		return $req;
	}
}