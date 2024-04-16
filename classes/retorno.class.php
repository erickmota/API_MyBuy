<?php

include "conexao.class.php";

class Retornos extends conexao{

    public $id_usuario;

    public function retornaErro($msg){

        return json_encode([
    
            "status" => API_IS_ACTIVE,
            "Versao" => API_VERSION,
            "msg" => $msg,
            "data" => false
    
        ]);

    }

    public function verifica_usuario(){

        $conn = $this->conn();

        $sql = mysqli_query($conn, "SELECT * FROM usuarios WHERE id='$this->id_usuario'") or die("Erro BD");
        $num = mysqli_num_rows($sql);

        if($num > 0){

            return true;

        }else{

            return false;

        }

    }

    public function retorna_listas(){

        $conn = $this->conn();

        $sql = mysqli_query($conn, "SELECT * FROM usuarios_listas INNER JOIN listas WHERE usuarios_listas.id_usuarios='$this->id_usuario' AND usuarios_listas.id_listas=listas.id") or die("Erro BD");
        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        if($qtd < 1){

            return $this->retornaErro("Nenhuma lista disponível para esse usuário");
        

        }else{

            return json_encode([

                "status" => API_IS_ACTIVE,
                "Versao" => API_VERSION,
                "msg" => "Sucesso",
                "data" => $array
        
            ]);

        }

    }

}