<?php

class Usuarios{

    private $conn;
    public $id;
    public $nome;
    public $email;
    public $senha;

    public function __construct($classeConexao){

        $this->conn = $classeConexao->getConexao();

    }

    protected function getId(){

        return $this->id;

    }

    public function setId($id){

        $this->id = $id;

    }

    /* Verifica se o id do usuário passado na URL, é válido */
    public function verifica_usuario(){

        $conn = $this->conn;

        $sql = mysqli_query(
            
            $conn, "SELECT * FROM usuarios
            WHERE id='$this->id'"
        
        ) or die("Erro BD");
        $num = mysqli_num_rows($sql);

        if($num > 0){

            return true;

        }else{

            return false;

        }

    }

}

?>