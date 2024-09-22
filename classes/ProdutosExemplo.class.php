<?php

class ProdutosExemplo{

    private $conn;
    private $retorna_json;
    public $id;
    public $nome;
    public $tipo_exibicao;
    public $id_fotos;

    public function __construct($classeRetornosJson, $classeConexao){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;

    }

    public function setNome($nome){

        $this->nome = $nome;

    }

    public function busca_produto(){

        $conn = $this->conn;

        $sql = mysqli_query(

            $conn,
            "SELECT produtos.nome, produtos.tipo_exibicao, fotos.url FROM produtos
            LEFT JOIN fotos ON fotos.id=produtos.id_fotos
            WHERE produtos.nome LIKE '%$this->nome%'"

        ) or die("Erro conexão");

        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        if($qtd < 1){

            return $this->retorna_json->retornaErro("Nenhuma produto corresponde a pesquisa");
        

        }else{

            return $this->retorna_json->retorna_json($array);

        }

    }

}

?>