<?php

include "Conexao.class.php";

class Produtos extends Conexao{

    public $id;
    public $nome;
    public $tipo_exibicao;
    public $qtd;
    public $id_categorias;
    public $id_listas;
    public $id_fotos;
    public $carrinho;

}

?>