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

    public function setId($id){

        $this->id = $id;

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

    public function altera_nome_mercado(){

        try {

            $conexao = $this->conn->prepare(

                "UPDATE mercados
                SET nome=?
                WHERE id=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("si", $this->nome, $this->id);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            return $this->retorna_json->retorna_json(null);
            
        } catch (Exception $e) {

            error_log("Classe Mercados - Métodos: altera_nome_mercado - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    /* Verifica se o id do mercado, pertence ao usuário */
    public function verifica_mercado_usuario(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT * FROM mercados
                WHERE id_usuarios=?
                AND id=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("ii", $this->id_usuarios, $this->id);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();
            $qtd = $sql->num_rows;

            if($qtd > 0){

                return true;

            }else{

                return false;

            }
            
        } catch (Exception $e) {

            error_log("Classe Mercados - Métodos: verifica_mercado_usuario - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    public function apaga_mercado(){

        try {

            $conexao = $this->conn->prepare(

                "DELETE FROM mercados
                WHERE id=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("i", $this->id);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            return $this->retorna_json->retorna_json(null);
            
        } catch (Exception $e) {

            error_log("Classe Mercados - Métodos: apaga_mercado - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

}

?>