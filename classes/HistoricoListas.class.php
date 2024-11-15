<?php

class HistoricoListas{

    private $conn;
    private $retorna_json;

    public $id;
    public $tipo;
    public $msg;
    public $id_listas;
    public $id_compras;
    public $id_usuarios;

    public function __construct($classeRetornosJson, $classeConexao){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;

    }

    /* Setters */

    public function setData($data){

        $formato = "Y-m-d H:i";
        $data_atual = date("Y-m-d H:i");

        if($data == "today"){

            $this->data = $data_atual;

        }else{

            $dataObj = DateTime::createFromFormat($formato, $data);

            if ($dataObj && $dataObj->format($formato) === $data) {

                $this->data = $data;

            }else{

                throw new InvalidArgumentException("O valor deve ser uma data no formato correto: $formato.");

            }
    

        }

    }

    /* Tipos de dado:
    1 = Lista criada
    2 = Lista editada
    3 = Item adicionado
    4 = Item apagado
    5 - Item editado
    6 = Usuario inserido
    7 = Usuário saiu
    8 - Compra efetuada */
    public function setTipo($tipo){

        if(is_int($tipo)){

            $this->tipo = $tipo;

        }else{

            throw new InvalidArgumentException("O valor do tipo deve ser um número inteiro.");

        }

    }

    public function setMsg($msg){

        if(is_string($msg)){

            $this->msg = $msg;

        }else{

            throw new InvalidArgumentException("O valor da msg deve ser uma string.");

        }

    }

    public function setIdListas($id_listas){

        if(is_int($id_listas)){

            $this->id_listas = $id_listas;

        }else{

            throw new InvalidArgumentException("O valor do id da lista deve ser um número inteiro.");

        }

    }

    public function setIdCompras($id_compras){

        if(is_int($id_compras)){

            $this->id_compras = $id_compras;

        }else{

            $this->id_compras = null;

        }

    }

    public function setIdUsuarios($id_usuarios){

        if(is_int($id_usuarios)){

            $this->id_usuarios = $id_usuarios;

        }else{

            throw new InvalidArgumentException("O valor do id do usuário deve ser um número inteiro.");

        }

    }

    /* Métodos */

    public function incluir_historico(){

        try {

            $conexao = $this->conn->prepare(

                "INSERT INTO historico_listas
                (data, tipo, msg, id_listas, id_compras, id_usuarios)
                VALUES (?, ?, ?, ?, ?, ?)"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("sisiii", $this->data, $this->tipo, $this->msg, $this->id_listas, $this->id_compras, $this->id_usuarios);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            return $this->retorna_json->retorna_json(null);
            
        } catch (Exception $e) {

            error_log("Classe HistoricoListas - Métodos: incluir_historico - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    public function retorna_historico(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT historico_listas.id, DATE_FORMAT(historico_listas.data, '%d/%m/%Y - %H:%m') AS data, historico_listas.tipo, historico_listas.msg,
                historico_listas.id_compras, historico_listas.id_usuarios AS id_usuario, usuarios.nome AS nome_usuario FROM historico_listas
                INNER JOIN usuarios ON usuarios.id=historico_listas.id_usuarios
                WHERE id_listas=?
                ORDER BY historico_listas.id ASC"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("i", $this->id_listas);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();

            if($sql->num_rows < 1){

                return $this->retorna_json->retorna_json(null);

            }else{

                while($resultado = $sql->fetch_assoc()){

                    $array[] = $resultado;

                }

                return $this->retorna_json->retorna_json($array);

            }
            
        } catch (Exception $e) {

            error_log("Classe HistoricoListas - Métodos: retorna_historico - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

}

?>