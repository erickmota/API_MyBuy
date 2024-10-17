<?php

class ProdutosCompras{

    private $conn;
    private $retorna_json;

    public $id;
    public $preco_produto;
    public $nome_produto;
    public $qtd;
    public $validade;
    public $id_compras;
    public $tipo_exibicao;
    public $id_produtos_usuario;
    public $id_categorias;

    public function __construct($classeRetornosJson, $classeConexao){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;
    }

    public function setIdCompras($id_compras){

        $this->id_compras = $id_compras;

    }

    /* Cadastra os produtos da tabela produtos, que estão no carrinho, para a tabela produtos_compras */
    public function cadastrar_produtos($id_lista){

        try {

            $conexao = $this->conn->prepare(

                "INSERT INTO produtos_compras (preco_produto, nome_produto, qtd, validade, id_compras, tipo_exibicao,
                id_produtos_usuario, id_categorias)
                SELECT valor, nome, qtd, NULL, ?, tipo_exibicao, id_produtos_usuario, id_categorias
                FROM produtos
                WHERE produtos.id_listas=?
                AND produtos.carrinho=?"

            );

            if($conexao === false){

                throw new Exception("Erro na conexão: ".$this->conn->error);

            }

            $carrinho = 1;

            $conexao->bind_param("iii", $this->id_compras, $id_lista, $carrinho); // O 1 aqui, significa produtos que estão no carrinho.

            if(!$conexao->execute()){

                throw new Exception("Erro na execução: ".$conexao->error);

            }

            return $this->retorna_json->retorna_json(null);
            
        } catch (Exception $e) {

            error_log("Classe ProdutosCompras - Métodos: cadastrar_produtos - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

}

?>