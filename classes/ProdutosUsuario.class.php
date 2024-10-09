<?php

class ProdutosUsuario{

    private $conn;

    public $id;
    public $nome;
    public $tipo_exibicao;
    public $id_fotos;
    public $id_usuarios;

    public function __construct($classeConexao){

        $this->conn = $classeConexao->getConexao();

    }

    /* SET */

    public function setNome($nome){

        $this->nome = $nome;

    }

    public function setTipo_exibicao($tipo_exibicao){

        $this->tipo_exibicao = $tipo_exibicao;

    }

    public function setId_fotos($id_fotos){

        $this->id_fotos = $id_fotos;

    }

    public function setId_usuarios($id_usuarios){

        $this->id_usuarios = $id_usuarios;

    }

    /* Métodos */

    public function criar_produtos_usuario(){

        $conn = $this->conn;

        if($this->id_fotos == NULL){

            $sql = mysqli_query(

                $conn,
                "INSERT INTO produtos_usuario (nome, tipo_exibicao, id_fotos, id_usuarios)
                VALUES ('$this->nome', '$this->tipo_exibicao', NULL, '$this->id_usuarios')"
    
            ) or die("Erro conexão");

        }else{

            $sql = mysqli_query(

                $conn,
                "INSERT INTO produtos_usuario (nome, tipo_exibicao, id_fotos, id_usuarios)
                VALUES ('$this->nome', '$this->tipo_exibicao', '$this->id_fotos', '$this->id_usuarios')"
    
            ) or die("Erro conexão");

        }

        $ultimo_registro = mysqli_insert_id($conn); //Retornando último registro adicionado na tabela produtos_usuarios

        return $ultimo_registro;

    }

}

?>