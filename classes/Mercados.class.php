<?php

class Mercados{

    private $conn;
    private $retorna_json;

    public $id;
    public $nome;
    public $id_usuarios;

    public function __construct($classeRetornosJson, $classeConexao){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;
    }

    public function setNome($nome){

        $this->nome = $nome;

    }

    public function setIdUsuarios($id_usuarios){

        $this->id_usuarios = $id_usuarios;

    }

    /* Retorna todos os mercados do usuário */
    public function retorna_mercado(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT * FROM mercados
                WHERE id_usuarios=?"

            );

            if($conexao === false){

                throw new Exception("Erro na conexão: ".$this->conn->error);

            }

            $conexao->bind_param("i", $this->id_usuarios);

            if(!$conexao->execute()){

                throw new Exception("Erro na execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();
            $qtd = $sql->num_rows;

            if($qtd < 1){

                throw new Exception("Nenhum resultado encontrado");

            }else{

                while($result = $sql->fetch_assoc()){

                    $array[] = $result;

                }

                return $this->retorna_json->retorna_json($array);

            }
            
        } catch (Exception $e) {

            error_log("Classe Mercados - Métodos: retorna_mercado - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    /* Cadastra um novo mercado para o usuário */
    public function cadastra_mercado(){

        try {

            $conexao = $this->conn->prepare(

                "INSERT INTO mercados
                (nome, id_usuarios)
                VALUES (?, ?)"

            );

            if($conexao === false){

                throw new Exception("Erro na conexão: ".$this->conn->error);

            }

            $conexao->bind_param("si", $this->nome, $this->id_usuarios);

            if(!$conexao->execute()){

                throw new Exception("Erro na execução: ".$conexao->error);

            }

            return $this->conn->insert_id;
            
        } catch (Exception $e) {

            error_log("Classe Mercados - Métodos: cadastra_mercado - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    /* Verifica se existe um mercado cadastrado com um determinado nome */
    public function verifica_nome_mercado(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT id FROM mercados
                WHERE nome=?
                AND id_usuarios=?"

            );

            if($conexao === false){

                throw new Exception("Erro na conexão: ".$this->conn->error);

            }

            $conexao->bind_param("si", $this->nome, $this->id_usuarios);

            if(!$conexao->execute()){

                throw new Exception("Erro na execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();
            $qtd = $sql->num_rows;

            if($qtd < 1){

                return false;

            }else{

                $result = $sql->fetch_assoc();

                $id_mercado = $result["id"];

                return $id_mercado;

            }
            
        } catch (Exception $e) {

            error_log("Classe Mercados - Métodos: verifica_nome_mercado - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

}

?>