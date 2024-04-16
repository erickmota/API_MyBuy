<?php

include "conexao.class.php";

class Retornos extends conexao{

    public $id_usuario;
    public $id_lista;

    /* Método responsável pelos erros retornados na API */
    public function retornaErro($msg){

        return json_encode([
    
            "status" => API_IS_ACTIVE,
            "Versao" => API_VERSION,
            "msg" => $msg,
            "data" => false
    
        ]);

    }

    /* Verifica se o id do usuário passado na URL, é válido */
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

    /* Retorna todas as listas que o usuário tem disponível,
    seja criada ou compartilhada */
    public function retorna_listas(){

        $conn = $this->conn();

        $sql = mysqli_query(

            $conn, "SELECT * FROM usuarios_listas
            INNER JOIN listas
            WHERE usuarios_listas.id_usuarios='$this->id_usuario'
            AND usuarios_listas.id_listas=listas.id"
            
        ) or die("Erro BD");
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

    /* Retornando todos os produtos, com suas respectivas categorias, de uma lista */
    public function retorna_produtos($categoria){

        $conn = $this->conn();

        $sql = mysqli_query(
            
            $conn, "SELECT produtos.id, produtos.nome FROM produtos
            INNER JOIN produtos_listas ON produtos_listas.id_produtos=produtos.id
            INNER JOIN categorias ON produtos.id_categorias=categorias.id
            INNER JOIN listas ON produtos_listas.id_listas=listas.id
            INNER JOIN usuarios_listas ON usuarios_listas.id_listas=listas.id
            WHERE produtos_listas.id_listas='$this->id_lista'
            AND usuarios_listas.id_usuarios='$this->id_usuario'
            AND produtos.id_categorias='$categoria'"
        
        ) or die("Erro BD");
        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        if($qtd < 1){

            return $this->retornaErro("Nenhuma produto disponivel na lista");
        

        }else{

            return json_encode([

                "status" => API_IS_ACTIVE,
                "Versao" => API_VERSION,
                "msg" => "Sucesso",
                "data" => $array
        
            ]);

        }

    }

    /* Retorna todas as categorias que um usuário tem em determinada lista */
    public function retorna_categoria(){

        $conn = $this->conn();

        $sql = mysqli_query(
            
            $conn, "SELECT categorias.id, categorias.nome FROM categorias 
            INNER JOIN produtos ON categorias.id=produtos.id_categorias
            INNER JOIN produtos_listas ON produtos_listas.id_produtos=produtos.id
            INNER JOIN listas ON listas.id=produtos_listas.id_listas
            INNER JOIN usuarios_listas ON usuarios_listas.id_listas=listas.id
            WHERE produtos_listas.id_listas='$this->id_lista' AND usuarios_listas.id_usuarios='$this->id_usuario'"
        
        ) or die("Erro BD");
        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        if($qtd < 1){

            return $this->retornaErro("Nenhuma categoria disponível para essa lista");
        

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