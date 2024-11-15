<?php

class Compras{

    private $conn;
    private $retorna_json;
    private $class_mercado;
    private $class_produtos_compra;
    private $class_usuarios_listas;
    private $class_historico;

    public $id;
    public $data;
    public $id_mercados;
    public $id_usuarios;

    public function __construct($class_usuarios_listas, $class_produtos_compra, $class_mercado, $classeRetornosJson, $classeConexao, $class_historico){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;
        $this->class_mercado = $class_mercado;
        $this->class_produtos_compra = $class_produtos_compra;
        $this->class_usuarios_listas = $class_usuarios_listas;
        $this->class_historico = $class_historico;
    }

    public function setData($data){

        $this->data = $data;

    }

    public function setIdMercados($id_mercados){

        $this->id_mercados = $id_mercados;

    }

    public function setIdUsuarios($id_usuarios){

        $this->id_usuarios = $id_usuarios;

    }

    /* Cadastra a compra */
    function cadastra_compra($id_lista, $nome_mercado){

        try {

            $this->class_usuarios_listas->setIdListas($id_lista);
            $this->class_usuarios_listas->setIdUsuarios($this->id_usuarios);
            $verifica_usuarios_listas = $this->class_usuarios_listas->verifica_usuario_lista();

            if($verifica_usuarios_listas == false){

                throw new Exception("Usuário não tem acesso a lista definida");

            }

            $this->class_mercado->setNome($nome_mercado);
            $this->class_mercado->setIdUsuarios($this->id_usuarios);

            $verifica_mercado = $this->class_mercado->verifica_nome_mercado();

            /* Verificando se o nome do mercado, passado pelo usuário, já existe, ou se
            será preciso criar um novo registro. */
            if($verifica_mercado == false){

                $this->id_mercados = $this->class_mercado->cadastra_mercado();

            }else{

                $this->id_mercados = $verifica_mercado;

            }

            $conexao = $this->conn->prepare(

                "INSERT INTO compras (data, id_usuarios, id_mercados)
                VALUES (?, ?, ?)"

            );

            if($conexao === false){

                throw new Exception("Erro na conexão: ".$this->conn->error);

            }

            $conexao->bind_param("sii", $this->data, $this->id_usuarios, $this->id_mercados);

            if(!$conexao->execute()){

                throw new Exception("Erro na execução: ".$conexao->error);

            }

            $id_compra = $this->conn->insert_id;

            $this->class_produtos_compra->setIdCompras($id_compra);

            $this->class_produtos_compra->cadastrar_produtos($id_lista);

            /* Histórico */

            $this->class_historico->setData("today");
            $this->class_historico->setTipo(8);
            $this->class_historico->setMsg("efetuou uma compra.");
            $this->class_historico->setIdListas(intval($id_lista));
            $this->class_historico->setIdCompras(intval($id_compra));
            $this->class_historico->setIdUsuarios(intval($this->id_usuarios));

            $this->class_historico->incluir_historico();

            return $this->retorna_json->retorna_json(null);
            
        } catch (Exception $e) {

            error_log("Classe Compras - Métodos: cadastra_compra - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    /* Retorna as compras do usuário */
    public function retorna_compras($filtro_data, $data_1, $data_2){

        try {

            switch($filtro_data){

                case "mes_atual":

                    $conexao = $this->conn->prepare(

                        "SELECT compras.id AS id, compras.data, mercados.id AS id_mercado, mercados.nome AS nome_mercado FROM compras
                        LEFT JOIN mercados ON mercados.id=compras.id_mercados
                        WHERE compras.id_usuarios=?
                        AND DATE_FORMAT(compras.data, '%Y-%m')=DATE_FORMAT(CURRENT_DATE, '%Y-%m')
                        ORDER BY compras.id desc"
        
                    );

                break;

                case "mes_passado":

                    $conexao = $this->conn->prepare(

                        "SELECT compras.id AS id, compras.data, mercados.id AS id_mercado, mercados.nome AS nome_mercado FROM compras
                        LEFT JOIN mercados ON mercados.id=compras.id_mercados
                        WHERE compras.id_usuarios=?
                        AND DATE_FORMAT(compras.data, '%Y-%m')=DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH), '%Y-%m')
                        ORDER BY compras.id desc"
        
                    );

                break;

                case "escolher_datas":

                    $conexao = $this->conn->prepare(

                        "SELECT compras.id AS id, compras.data, mercados.id AS id_mercado, mercados.nome AS nome_mercado FROM compras
                        LEFT JOIN mercados ON mercados.id=compras.id_mercados
                        WHERE compras.id_usuarios=?
                        AND DATE(compras.data) BETWEEN ? AND ?
                        ORDER BY compras.id desc"
        
                    );

                break;

                default:

                    $conexao = $this->conn->prepare(

                        "SELECT compras.id AS id, compras.data, mercados.id AS id_mercado, mercados.nome AS nome_mercado FROM compras
                        INNER JOIN mercados ON mercados.id=compras.id_mercados
                        WHERE compras.id_usuarios=? ORDER BY compras.id desc"
        
                    );

                break;

            }

            if($conexao === false){

                throw new Exception("Erro na conexão: ".$this->conn->error);

            }

            if($filtro_data == "escolher_datas"){

                $conexao->bind_param("iss", $this->id_usuarios, $data_1, $data_2);

            }else{

                $conexao->bind_param("i", $this->id_usuarios);

            }

            if(!$conexao->execute()){

                throw new Exception("Erro na execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();

            if($sql->num_rows < 1){

                throw new Exception("Nenhum lista disponível para esse usuário.");

            }

            while($result = $sql->fetch_assoc()){

                $array[] = $result;

            }

            /* Inserindo e formatando os campos data e hora */
            foreach($array as &$navegacao){

                $data_time = $navegacao["data"];

                $dia = substr($data_time, 8, 2);
                $mes = substr($data_time, 5, 2);
                $ano = substr($data_time, 0, 4);

                $novo_horario = substr($data_time, 11, 5);

                $nova_data = $dia."/".$mes."/".$ano;

                $this->class_produtos_compra->setIdCompras($navegacao["id"]);

                $qtd_itens = $this->class_produtos_compra->retorna_qtd_produtos();
                $valor_compra = $this->class_produtos_compra->retorna_valor_compra();

                $navegacao["data"] = $nova_data;
                $navegacao["horas"] = $novo_horario;
                $navegacao["qtd_itens"] = $qtd_itens;
                $navegacao["valor_compra"] = $valor_compra;
            }

            return $this->retorna_json->retorna_json($array);
            
        } catch (Exception $e) {

            error_log("Classe Compras - Métodos: retorna_compras - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }
        
    }

}

?>