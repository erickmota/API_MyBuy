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

    /* Retornando todos os produtos, dentro de uma categorias determinada, de uma lista */
    public function retorna_produtos($categoria){

        $conn = $this->conn();

        $sql = mysqli_query(
            
            $conn, "SELECT produtos.id, produtos.nome, fotos.url FROM produtos
            LEFT JOIN fotos ON fotos.id=produtos.id_fotos
            INNER JOIN listas ON listas.id=produtos.id_listas
            INNER JOIN usuarios_listas ON usuarios_listas.id_listas=listas.id
            WHERE usuarios_listas.id_usuarios='$this->id_usuario'
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

    /* Retorna todas as categorias que um usuário tem na conta */
    public function retorna_categoria(){

        $conn = $this->conn();

        $sql = mysqli_query(
            
            $conn, "SELECT DISTINCT categorias.id, categorias.nome FROM categorias
            INNER JOIN produtos ON produtos.id_categorias=categorias.id
            INNER JOIN listas ON listas.id=produtos.id_listas
            INNER JOIN usuarios_listas ON usuarios_listas.id_listas=listas.id
            WHERE usuarios_listas.id_usuarios='$this->id_usuario'
            ORDER BY categorias.id ASC"
        
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