<?php

class Categorias{

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

    /* Retorna todas as categorias que um usuário tem na conta */
    public function retorna_categoria(){

        $conn = $this->conn;

        $sql = mysqli_query(
            
            $conn, "SELECT * FROM categorias
            WHERE id_usuarios='$this->id_usuarios'"
        
        ) or die("Erro BD");
        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        if($qtd < 1){

            return $this->retorna_json->retornaErro("Nenhuma categoria disponível para essa lista");
        

        }else{

            return $this->retorna_json->retorna_json($array);

        }

    }

    /* Adiciona uma nova categoria */
    public function add_categoria(){

        try {
            
            $conexao = $this->conn->prepare(

                "INSERT INTO categorias (nome, id_usuarios)
                VALUES (?, ?)"
    
            );

            if(!$conexao){

                throw new Exception ("Erro na conexão: " . $this->conn->error);

            };
    
            $conexao->bind_param("si", $this->nome, $this->id_usuarios);

            if(!$conexao->execute()){

                throw new Exception("Erro na execução: " . $conexao->error);

            };

            return $this->retorna_json->retorna_json(false);

        } catch (Exception $e) {

            error_log($e->getMessage()."\n", 3, 'erros.log');
            
            return $this->retorna_json->retornaErro($e->getMessage());

        }

    }

    /* Verifica se o usuário passado por id, é dono da categoria */
    private function verifica_titularidade_categoria(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT * FROM categorias
                WHERE id=?
                AND id_usuarios=?"
    
            );

            if(!$conexao){

                throw new Exception ("Erro de conexão: ".$this->conn->error);

            };
    
            $conexao->bind_param("ii", $this->id, $this->id_usuarios);

            if(!$conexao->execute()){

                throw new Exception ("Erro de execução: ".$conexao->error);

            };
    
            $sql = $conexao->get_result();
    
            $qtd = $sql->num_rows;
    
            if($qtd > 0){
    
                return true;
    
            }else{
    
                return false;
    
            }
            
        } catch (Exception $e) {

            error_log($e->getMessage()."\n", 3, 'erros.log');
            
            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    public function apagar_categoria(){

        try {

            if($this->verifica_titularidade_categoria() == false){

                throw new Exception ("Usuário sem acesso a categoria");

            };

            $conexao = $this->conn->prepare(

                "DELETE FROM categorias
                WHERE id=?"

            );

            if(!$conexao){

                throw new Exception ("Erro de conexão: ".$this->conn->error);

            };

            $conexao->bind_param("i", $this->id);

            if(!$conexao->execute()){

                throw new Exception ("Erro de execução: ".$conexao->error);

            };

            return $this->retorna_json->retorna_json(false);
            
        } catch (Exception $e) {

            error_log($e->getMessage()."\n", 3, 'erros.log');
            
            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

}

?>