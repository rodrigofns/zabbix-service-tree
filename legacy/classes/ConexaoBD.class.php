<?php

class ConexaoBD {

    protected $conexao = "";
    static private $instancia;

    public function conectarBanco($host, $usuario, $senha, $database, $porta) {
        $this->conexao = mysql_connect($host . ":" . $porta, $usuario, $senha);
        if (!$this->conexao) {
            die("Erro na conexao com BD");
        } elseif (!mysql_select_db($database, $this->conexao)) {
            die("Erro na seleção do BD");
        }
    }

    public function query($query) {
        return mysql_query($query);
    }

    public function fetch_assoc($cursor) {
        $result = mysql_fetch_assoc($cursor);
        if (!$result) {
            mysql_free_result($cursor);
        }
        return $result;
    }

    public function fetch_array($cursor) {
        $result = mysql_fetch_array($cursor);
        if (!$result) {
            mysql_free_result($cursor);
        }
        return $result;
    }

    public function get_last_id() {
        return mysql_insert_id($this->conexao);
    }

    public function fecharConexao() {
        return mysql_close($this->conexao);
    }
    
    public function numLinhas($result) {
        return mysql_num_rows($result);
    }

    /**
     * busca a instância de conexão com banco de dados
     * @return ConexaoBD retorna instância do banco de dados
     */
    public static function getInstance() {
        if (!isset(self::$instancia)) {
            //ler arquivo de configuração
            $config = parse_ini_file(dirname(__FILE__) . '/../config.ini');
            $host = $config["host"];
            $usuario = $config["user"];
            $senha = $config["pass"];
            $banco = $config["database"];
            $porta = $config["port"];
            self::$instancia = new ConexaoBD();
            self::$instancia->conectarBanco($host, $usuario, $senha, $banco, $porta);
        }
        return self::$instancia;
    }

    /**
     * Fecha a conexão com o banco de dados e destroi a instância.
     */
    public static function close() {
        if (!isset(self::$instancia)) {
            self::$instancia->db_close();
            unset(self::$instancia);
        }
    }

}
