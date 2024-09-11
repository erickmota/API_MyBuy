<?php

include "Conexao.class.php";

class Usuarios{

    private $conn;
    public $id;
    public $nome;
    public $email;
    public $senha;

    public function __construct(){

        $this->conn = (new Conexao())->getConexao();

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