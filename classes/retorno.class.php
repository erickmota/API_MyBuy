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

    /* Método de retorno com sucesso de json. */
    private function retorna_json($data){

        return json_encode([
    
            "status" => API_IS_ACTIVE,
            "Versao" => API_VERSION,
            "msg" => "Sucesso",
            "data" => $data
    
        ]);

    }

    /* Verifica se o id do usuário passado na URL, é válido */
    public function verifica_usuario(){

        $conn = $this->conn();

        $sql = mysqli_query(
            
            $conn, "SELECT * FROM usuarios
            WHERE id='$this->id_usuario'"
        
        ) or die("Erro BD");
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

        /* Percorrendo o array e inserindo um novo item
        com a quantidade de produtos que existem na lista */
        foreach($array as &$navegacao){

            $id_list = $navegacao["id"];

            $sql = mysqli_query($conn, "SELECT * FROM produtos
            WHERE id_listas='$id_list'") or die("Erro na consulta do array");
            $qtd_prod = mysqli_num_rows($sql);

            $navegacao["qtd_produtos"] = $qtd_prod;

        }

        if($qtd < 1){

            return $this->retornaErro("Nenhuma lista disponível para esse usuário");
        

        }else{

            return $this->retorna_json($array);

        }

    }

    /* Retornando todos os produtos, dentro de uma categorias determinada, de uma lista */
    public function retorna_produtos($categoria, $carrinho){

        $conn = $this->conn();

        switch($carrinho){

            case true:

                $sql = mysqli_query(
            
                    $conn, "SELECT produtos.id, produtos.nome, produtos.carrinho, fotos.url FROM produtos
                    LEFT JOIN fotos ON fotos.id=produtos.id_fotos
                    INNER JOIN listas ON listas.id=produtos.id_listas
                    INNER JOIN usuarios_listas ON usuarios_listas.id_listas=listas.id
                    WHERE usuarios_listas.id_usuarios='$this->id_usuario'
                    AND listas.id='$this->id_lista'
                    AND produtos.carrinho=1"
                
                ) or die("Erro BD");

            break;

            case false:

                $sql = mysqli_query(
            
                    $conn, "SELECT produtos.id, produtos.nome, produtos.carrinho, fotos.url FROM produtos
                    LEFT JOIN fotos ON fotos.id=produtos.id_fotos
                    INNER JOIN listas ON listas.id=produtos.id_listas
                    INNER JOIN usuarios_listas ON usuarios_listas.id_listas=listas.id
                    WHERE usuarios_listas.id_usuarios='$this->id_usuario'
                    AND produtos.id_categorias='$categoria'
                    AND listas.id='$this->id_lista'
                    AND produtos.carrinho=0"
                
                ) or die("Erro BD");

            break;

        }
        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        if($qtd < 1){

            return $this->retornaErro("Nenhuma produto disponivel na lista");
        

        }else{

            return $this->retorna_json($array);

        }

    }

    /* Retorna todas as categorias que um usuário tem na conta */
    public function retorna_categoria(){

        $conn = $this->conn();

        $sql = mysqli_query(
            
            $conn, "SELECT * FROM categorias
            WHERE id_usuarios='$this->id_usuario'"
        
        ) or die("Erro BD");
        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        if($qtd < 1){

            return $this->retornaErro("Nenhuma categoria disponível para essa lista");
        

        }else{

            return $this->retorna_json($array);

        }

    }

    /* Método de verificação do login de um usuário. */
    public function verificar_email_senha_usuario($email, $senha){

        $conn = $this->conn();

        $sql = mysqli_query(
            
            $conn, "SELECT id, nome, token FROM usuarios
            WHERE email='$email'
            AND senha='$senha'"
        
        ) or die("Erro ao verificar o usuário");
        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        if($qtd > 0){

            return $this->retorna_json($array);

        }else{

            return $this->retornaErro("Usuário não localizado");

        }

    }

    public function atualiza_nome_lista($id_lista, $novo_nome){

        $conn = $this->conn();

        $sql = mysqli_query(
            
            $conn, "UPDATE listas
            SET nome='$novo_nome'
            WHERE id='$id_lista'"
            
        ) or die("Erro conexão");

        return $this->retorna_json(false);

    }

}