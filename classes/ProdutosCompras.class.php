<?php

include "Conexao.class.php";

class ProdutosCompras extends Conexao{

    public $id;
    public $preco_produto;
    public $nome_produto;
    public $qtd;
    public $validade;
    public $id_compras;
    public $tipo_exibicao;
    public $id_produtos_usuario;
    public $id_categorias;

}

?>