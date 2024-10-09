<?php

class Usuarios{

    private $conn;
    private $retorna_json;
    public $id;
    public $nome;
    public $email;
    public $senha;

    public function __construct($classeRetornosJson, $classeConexao){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;

    }

    protected function getIdUsuarios(){

        return $this->id;

    }

    public function setIdUsuarios($id){

        $this->id = $id;

    }

    protected function getEmailUsuarios(){

        return $this->email;

    }

    public function setEmailUsuarios($email){

        $this->email = $email;

    }

    protected function getSenhaUsuarios(){

        return $this->senha;

    }

    public function setSenhaUsuarios($senha){

        $this->senha = $senha;

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

    /* Método de verificação do login de um usuário. */
    public function login(){

        $conn = $this->conn;

        $sql = mysqli_query(
            
            $conn, "SELECT id, nome, token, foto_url FROM usuarios
            WHERE email='$this->email'
            AND senha='$this->senha'"
        
        ) or die("Erro ao verificar o usuário");
        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        if($qtd > 0){

            return $this->retorna_json->retorna_json($array);

        }else{

            return $this->retorna_json->retornaErro("Usuário não localizado");

        }

    }

}

?>