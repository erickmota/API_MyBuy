<?php

class Produtos extends Usuarios{

    private $conn;
    private $retorna_json;
    public $id;
    public $nome;
    public $tipo_exibicao;
    public $qtd;
    public $id_categorias;
    public $id_listas;
    public $id_fotos;
    public $carrinho;


    public function __construct($classeRetornosJson, $classeConexao){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;
        parent::__construct($classeRetornosJson, $classeConexao);

    }

    public function setIdLista($id_listas){

        $this->id_listas = $id_listas;

    }

    public function setCarrinho($carrinho){

        $this->carrinho = $carrinho;

    }

    /* Retornando todos os produtos, dentro de uma categorias determinada, de uma lista */
    public function retorna_produtos($categoria){

        $conn = $this->conn;
        $id_usuario = $this->getIdUsuarios();

        switch($this->carrinho){

            case true:

                $sql = mysqli_query(
            
                    $conn, "SELECT produtos.id, produtos.nome, produtos.carrinho, fotos.url FROM produtos
                    LEFT JOIN fotos ON fotos.id=produtos.id_fotos
                    INNER JOIN listas ON listas.id=produtos.id_listas
                    INNER JOIN usuarios_listas ON usuarios_listas.id_listas=listas.id
                    WHERE usuarios_listas.id_usuarios='$id_usuario'
                    AND listas.id='$this->id_listas'
                    AND produtos.carrinho=1"
                
                ) or die("Erro BD");

            break;

            case false:

                $sql = mysqli_query(
            
                    $conn, "SELECT produtos.id, produtos.nome, produtos.carrinho, fotos.url FROM produtos
                    LEFT JOIN fotos ON fotos.id=produtos.id_fotos
                    INNER JOIN listas ON listas.id=produtos.id_listas
                    INNER JOIN usuarios_listas ON usuarios_listas.id_listas=listas.id
                    WHERE usuarios_listas.id_usuarios='$id_usuario'
                    AND produtos.id_categorias='$categoria'
                    AND listas.id='$this->id_listas'
                    AND produtos.carrinho=0"
                
                ) or die("Erro BD");

            break;

        }
        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        if($qtd < 1){

            return $this->retorna_json->retornaErro("Nenhuma produto disponivel na lista");
        

        }else{

            return $this->retorna_json->retorna_json($array);

        }

    }

}

?>