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

    /* Retorna a quantidade de itens de uma compra */
    public function retorna_qtd_produtos(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT * FROM produtos_compras
                WHERE id_compras=?"

            );

            if($conexao === false){

                throw new Exception("Erro na conexão: ".$this->conn->error);

            }

            $conexao->bind_param("i", $this->id_compras);

            if(!$conexao->execute()){

                throw new Exception("Erro na execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();

            return $sql->num_rows;
            
        } catch (Exception $e) {

            error_log("Classe ProdutosCompras - Métodos: retorna_qtd_produtos - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    /* Soma o valor de todos os produtos registrados em uma compra */
    private function somar_valor_produtos($array){

        $i = 0;

        $valor_total_compra = [];

        /* Realizando a soma dos produtos.
        O tipo 3 e 5 é o tipo de exibição, eles são os tipo, ml e g, e não somam
        o valor de acordo com o quantidade. */
        foreach($array as $navegacao){

            $valor = $navegacao["preco_produto"];
            $qtd = $navegacao["qtd"];
            $tipo = $navegacao["tipo_exibicao"];

            /* Se o tipo for ml ou g, não considera a quantidade */
            if($tipo == 3 || $tipo == 5){

                $valor_total_produto = $valor;

            }else{

                $valor_total_produto = $valor * $qtd;

            }

            $valor_total_compra[$i++] = $valor_total_produto;

        }

        return number_format(array_sum($valor_total_compra), 2, ".", "");

    }

    /* Retorna o valor da compra */
    public function retorna_valor_compra(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT preco_produto, qtd, tipo_exibicao FROM produtos_compras
                WHERE id_compras=?"

            );

            if($conexao === false){

                throw new Exception("Erro na conexão: ".$this->conn->error);

            }

            $conexao->bind_param("i", $this->id_compras);

            if(!$conexao->execute()){

                throw new Exception("Erro na execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();

            while($result = $sql->fetch_assoc()){

                $array[] = $result;

            }

            return $this->somar_valor_produtos($array);
            
        } catch (Exception $e) {

            error_log("Classe ProdutosCompras - Métodos: retorna_qtd_produtos - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    /* Retorna o valor real pago no produto, individualmente */
    private function somar_qtd_valor($qtd, $tipo, $valor){

        /* Se o tipo for ml ou g, não considera a quantidade */
        if($tipo == 3 || $tipo == 5){

            $valor_real = $valor;

        }else{

            $valor_real = $valor * $qtd;

        }

        return $valor_real;

    }

    private function retorna_tipo_formatado($tipo){

        /* 1 = Un
        2 = Kg
        3 = g
        4 = L
        5 = ml
        6 = dz
        7 = Caixa
        8 = Pacote
        9 = Garrafa
        10 = Lata
        11 = Embalagem */

        switch($tipo){

            case 1:

                $formato = "Un";

            break;

            case 2:

                $formato = "Kg";

            break;

            case 3:

                $formato = "g";

            break;

            case 4:

                $formato = "L";

            break;

            case 5:

                $formato = "ml";

            break;

            case 6:

                $formato = "dz";

            break;

            case 7:

                $formato = "Caixa";

            break;

            case 8:

                $formato = "Pacote";

            break;

            case 9:

                $formato = "Garrafa";

            break;

            case 10:

                $formato = "Lata";

            break;

            case 11:

                $formato = "Embalagem";

            break;

        }

        return $formato;

    }

    /* Retorna todos os produtos e dados de uma determinada compra */
    public function retorna_produtos_compra(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT produtos_compras.id, produtos_compras.nome_produto, produtos_compras.qtd, produtos_compras.tipo_exibicao,
                produtos_compras.preco_produto FROM produtos_compras
                INNER JOIN compras ON compras.id=produtos_compras.id_compras
                WHERE produtos_compras.id_compras=?"

            );

            if($conexao === false){

                throw new Exception("Erro na conexão: ".$this->conn->error);

            }

            $conexao->bind_param("i", $this->id_compras);

            if(!$conexao->execute()){

                throw new Exception("Erro na execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();

            while($result = $sql->fetch_assoc()){

                $array[] = $result;

            }

            /* Manipulando dados do array */
            foreach($array as &$resultadoArray){

                /* Retorna o valor total do produto */
                $valor_total = $this->somar_qtd_valor($resultadoArray["qtd"], $resultadoArray["tipo_exibicao"], $resultadoArray["preco_produto"]);

                /* Retorna o tipo formatado */
                $tipo_correto = $this->retorna_tipo_formatado($resultadoArray["tipo_exibicao"]);

                $resultadoArray["preco_produto"] = number_format($valor_total, 2, ".", "");
                $resultadoArray["tipo_exibicao"] = $tipo_correto;

            }

            return $this->retorna_json->retorna_json($array);
            
        } catch (Exception $e) {

            error_log("Classe ProdutosCompras - Métodos: retorna_produtos_compra - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

}

?>