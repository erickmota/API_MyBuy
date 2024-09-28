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
    public $valor;
    public $obs;

    public function __construct($classeRetornosJson, $classeConexao){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;
        parent::__construct($classeRetornosJson, $classeConexao);

    }

    /* Setters */

    public function setIdLista($id_listas){

        $this->id_listas = $id_listas;

    }

    public function setIdProduto($id_produto){

        $this->id = $id_produto;

    }

    public function setNome($nome){

        $this->nome = $nome;

    }

    public function setTipoExibicao($tipo_exibicao){

        $this->tipo_exibicao = $tipo_exibicao;

    }

    public function setQtd($qtd){

        $this->qtd = $qtd;

    }

    public function setIdCategorias($id_categorias){

        $this->id_categorias = $id_categorias;

    }

    public function setIdFotos($id_fotos){

        if($id_fotos == 0){

            $this->id_fotos = NULL;

        }else{

            $this->id_fotos = $id_fotos;

        }

    }

    public function setCarrinho($carrinho){

        $this->carrinho = $carrinho;

    }

    public function setValor($valor){

        $this->valor = $valor;

    }

    public function setObs($obs){

        $this->obs = $obs;

    }

    /* Retornando todos os produtos, dentro de uma categorias determinada, de uma lista */
    public function retorna_produtos($categoria, $all_products){

        $conn = $this->conn;
        $id_usuario = $this->getIdUsuarios();

        if($all_products == true){

            $sql = mysqli_query(
                
                $conn, "SELECT produtos.id, produtos.nome, produtos.tipo_exibicao, produtos.carrinho, produtos.qtd, produtos.valor, produtos.obs, id_categorias, fotos.id AS id_foto, fotos.url FROM produtos
                LEFT JOIN fotos ON fotos.id=produtos.id_fotos
                INNER JOIN listas ON listas.id=produtos.id_listas
                INNER JOIN usuarios_listas ON usuarios_listas.id_listas=listas.id
                WHERE usuarios_listas.id_usuarios='$id_usuario'
                AND listas.id='$this->id_listas'
                AND produtos.carrinho=0"
            
            ) or die("Erro BD");

        }else{

            switch($this->carrinho){

                case true:
    
                    $sql = mysqli_query(
                
                        $conn, "SELECT produtos.id, produtos.nome, produtos.tipo_exibicao, produtos.carrinho, produtos.qtd, produtos.valor, produtos.obs, id_categorias, fotos.id AS id_foto, fotos.url FROM produtos
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
                
                        $conn, "SELECT produtos.id, produtos.nome, produtos.tipo_exibicao, produtos.carrinho, produtos.qtd, produtos.valor, produtos.obs, id_categorias, fotos.id AS id_foto, fotos.url FROM produtos
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

    /* Adiciona um novo produto na lista */
    public function adicionar_produto(){

        $conn = $this->conn;
        $id_usuario = $this->getIdUsuarios();

        if($this->id_fotos == NULL){

            $sql = mysqli_query(
            
                $conn, "INSERT INTO produtos (nome, tipo_exibicao, qtd, id_categorias, id_listas, id_fotos, carrinho, valor, obs, id_usuarios_dono)
                VALUES ('$this->nome', '$this->tipo_exibicao', '$this->qtd', '$this->id_categorias', '$this->id_listas', NULL, '$this->carrinho', '$this->valor', '$this->obs', $id_usuario)"
                
            ) or die("Erro conexão");

        }else{

            $sql = mysqli_query(
            
                $conn, "INSERT INTO produtos (nome, tipo_exibicao, qtd, id_categorias, id_listas, id_fotos, carrinho, valor, obs, id_usuarios_dono)
                VALUES ('$this->nome', '$this->tipo_exibicao', '$this->qtd', '$this->id_categorias', '$this->id_listas', '$this->id_fotos', '$this->carrinho', '$this->valor', '$this->obs', $id_usuario)"
                
            ) or die("Erro conexão");

        }

        return $this->retorna_json->retorna_json(false);

    }

    /* Apaga um produto da lista */
    public function deletar_produto(){

        $conn = $this->conn;

        $sql = mysqli_query(

            $conn, "DELETE FROM produtos
            WHERE id='$this->id'"

        ) or die("Erro conexão");

        return $this->retorna_json->retorna_json(false);

    }

    /* Editar dados de um produto */
    public function editar_produto(){

        $conn = $this->conn;

        if($this->id_fotos == NULL){

            $sql = mysqli_query(

                $conn, "UPDATE produtos
                SET nome='$this->nome',
                tipo_exibicao='$this->tipo_exibicao',
                qtd='$this->qtd',
                id_categorias='$this->id_categorias',
                id_fotos=NULL,
                carrinho='$this->carrinho',
                valor='$this->valor',
                obs='$this->obs'
                WHERE id='$this->id'"
    
            ) or die("Erro conexão");

        }else{

            $sql = mysqli_query(

                $conn, "UPDATE produtos
                SET nome='$this->nome',
                tipo_exibicao='$this->tipo_exibicao',
                qtd='$this->qtd',
                id_categorias='$this->id_categorias',
                id_fotos='$this->id_fotos',
                carrinho='$this->carrinho',
                valor='$this->valor',
                obs='$this->obs'
                WHERE id='$this->id'"
    
            ) or die("Erro conexão");

        }

        return $this->retorna_json->retorna_json(false);

    }

    public function add_produto_carrinho(){

        $conn = $this->conn;

        $sql = mysqli_query(

            $conn, "UPDATE produtos
            SET carrinho='$this->carrinho',
            qtd='$this->qtd',
            valor='$this->valor'
            WHERE id='$this->id'"

        ) or die("Erro conexão");

        return $this->retorna_json->retorna_json(false);

    }

    public function remove_produto_carrinho(){

        $conn = $this->conn;

        $sql = mysqli_query(

            $conn, "UPDATE produtos
            SET carrinho='$this->carrinho'
            WHERE id='$this->id'"

        ) or die("Erro conexão");

        return $this->retorna_json->retorna_json(false);

    }

}

?>