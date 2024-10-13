<?php

class Produtos extends Usuarios{

    private $conn;
    private $retorna_json;
    private $verifica_titularidade_lista;
    private $produtos_usuario;
    private $produtos_exemplo;
    
    public $id__prod;
    public $nome;
    public $tipo_exibicao;
    public $qtd;
    public $id_categorias;
    public $id_listas;
    public $id_fotos;
    public $carrinho;
    public $valor;
    public $obs;
    public $id_usuarios_dono;
    public $id_produtos_usuario;

    public function __construct($produtos_exemplo, $produtos_usuario, $classeUsuariosListas, $classeRetornosJson, $classeConexao){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;
        $this->verifica_titularidade_lista = $classeUsuariosListas;
        $this->produtos_usuario = $produtos_usuario;
        $this->produtos_exemplo = $produtos_exemplo;
        parent::__construct($classeRetornosJson, $classeConexao);

    }

    /* SET */

    public function setIdLista($id_listas){

        $this->id_listas = $id_listas;

    }

    public function setIdProduto($id_produto){

        $this->id__prod = $id_produto;

    }

    public function setNome($nome){

        $this->nome = ucfirst($nome);

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

    public function setId_produtos_usuario($id_produtos_usuario){

        if($id_produtos_usuario == 0){

            $this->id_produtos_usuario = NULL;

        }else{

            $this->id_produtos_usuario = $id_produtos_usuario;

        }

    }

    /* Métodos */

    /* Retornando todos os produtos, dentro de uma categorias determinada, de uma lista */

    public function retorna_produtos($categoria, $all_products, $dono_lista){

        $conn = $this->conn;
        $id_usuario = $this->getIdUsuarios();

        /* Verificando se é para mostrar todos os produtos, ou por categorias */

        if($all_products == true){

            $sql = mysqli_query(
                
                $conn, "SELECT produtos.id, produtos.nome, produtos.tipo_exibicao, produtos.carrinho,
                produtos.qtd, produtos.valor, produtos.obs, id_categorias, fotos.id AS id_foto, fotos.url,
                usuarios.id AS id_dono, usuarios.nome AS nome_dono, usuarios.foto_url AS foto_dono FROM produtos
                LEFT JOIN fotos ON fotos.id=produtos.id_fotos
                INNER JOIN listas ON listas.id=produtos.id_listas
                INNER JOIN usuarios_listas ON usuarios_listas.id_listas=listas.id
                LEFT JOIN usuarios ON produtos.id_usuarios_dono=usuarios.id
                WHERE usuarios_listas.id_usuarios='$id_usuario'
                AND listas.id='$this->id_listas'
                AND produtos.carrinho=0"
            
            ) or die("Erro BD");

        }else{

            /* Verificando se é para mostrar o carrinho ou a lista com categorias */

            switch($this->carrinho){

                case true:
    
                    $sql = mysqli_query(
                
                        $conn, "SELECT produtos.id, produtos.nome, produtos.tipo_exibicao, produtos.carrinho,
                        produtos.qtd, produtos.valor, produtos.obs, id_categorias, fotos.id AS id_foto, fotos.url,
                        usuarios.id AS id_dono, usuarios.nome AS nome_dono, usuarios.foto_url AS foto_dono FROM produtos
                        LEFT JOIN fotos ON fotos.id=produtos.id_fotos
                        INNER JOIN listas ON listas.id=produtos.id_listas
                        INNER JOIN usuarios_listas ON usuarios_listas.id_listas=listas.id
                        LEFT JOIN usuarios ON produtos.id_usuarios_dono=usuarios.id
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

            $confirmacoes = [];

            $confirmacoes = [

                /* Incluindo a informação se o usuário atual é ou
                não dono da lista */
                
                "dono_lista"=>$this->verifica_titularidade_lista->verifica_titularidade_lista($dono_lista)

            ];

            $result = [

                "Confirmacoes"=>$confirmacoes,
                "Produtos"=>$array

            ];

            return $this->retorna_json->retorna_json($result);

        }

    }

    /* Adiciona um novo produto na lista */
    public function adicionar_produto(){

        $conn = $this->conn;
        $id_usuario = $this->getIdUsuarios();

        $this->produtos_usuario->nome = ucfirst($this->nome);
        $this->produtos_usuario->id_usuarios = $id_usuario;
        $this->produtos_usuario->tipo_exibicao = $this->tipo_exibicao;
        $this->produtos_usuario->id_fotos = $this->id_fotos;

        // Essa variável tbm é responsável por armazenar o id do produto, caso haja retorno.
        // Verfica se existe o valor no banco, pelo nome.
        $existencia_bd = $this->produtos_usuario->verifica_existencia_bd();

        if($existencia_bd == false){

            $this->produtos_exemplo->setNome($this->nome);

            /* Se o produto não existir nos produtos base do usuário, vai verificar se existe
            algum produto com o mesmo nome nos produtos de exemplo.
            Caso tenha, vai ser usada a foto do produto de exemplo, no produto base e da lista. */
            $verifica_nome_exemplo = $this->produtos_exemplo->verifica_existencia_nome();

            if($verifica_nome_exemplo == false){

                $ultimo_registro = $this->produtos_usuario->criar_produtos_usuario();

            }else{

                $id_produto_exemplo = $verifica_nome_exemplo;

                $this->produtos_exemplo->setId($id_produto_exemplo);

                $id_foto_exemplo = $this->produtos_exemplo->retorna_foto_exemplo();

                $this->produtos_usuario->id_fotos = $id_foto_exemplo;

                $ultimo_registro = $this->produtos_usuario->criar_produtos_usuario();

                $this->setIdFotos($id_foto_exemplo);

            }

        }else{

            $this->produtos_usuario->id = $existencia_bd[0];
            $this->produtos_usuario->id_fotos = $existencia_bd[1];

            $this->produtos_usuario->atualiza_dados_produtos_usuario();

            $this->id_fotos = $existencia_bd[1];

            $ultimo_registro = $existencia_bd[0];

        }

        /* ***** */

        if($this->id_fotos == NULL){

            $sql = mysqli_query(
            
                $conn, "INSERT INTO produtos (nome, tipo_exibicao, qtd, id_categorias, id_listas, id_fotos, carrinho, valor, obs, id_usuarios_dono, id_produtos_usuario)
                VALUES ('$this->nome', '$this->tipo_exibicao', '$this->qtd', '$this->id_categorias', '$this->id_listas', NULL, '$this->carrinho', '$this->valor', '$this->obs', $id_usuario, $ultimo_registro)"
                
            ) or die("Erro conexão");

        }else{

            $sql = mysqli_query(
            
                $conn, "INSERT INTO produtos (nome, tipo_exibicao, qtd, id_categorias, id_listas, id_fotos, carrinho, valor, obs, id_usuarios_dono, id_produtos_usuario)
                VALUES ('$this->nome', '$this->tipo_exibicao', '$this->qtd', '$this->id_categorias', '$this->id_listas', '$this->id_fotos', '$this->carrinho', '$this->valor', '$this->obs', $id_usuario, $ultimo_registro)"
                
            ) or die("Erro conexão");

        }

        return $this->retorna_json->retorna_json(false);

    }

    /* Apaga um produto da lista */
    public function deletar_produto(){

        $conn = $this->conn;

        $sql = mysqli_query(

            $conn, "DELETE FROM produtos
            WHERE id='$this->id__prod'"

        ) or die("Erro conexão");

        return $this->retorna_json->retorna_json(false);

    }

    /* Editar dados de um produto */
    public function editar_produto(){

        $conn = $this->conn;
        $id_usuario = $this->getIdUsuarios();

        $this->produtos_usuario->nome = $this->nome;
        $this->produtos_usuario->id_usuarios = $id_usuario;
        $this->produtos_usuario->tipo_exibicao = $this->tipo_exibicao;
        $this->produtos_usuario->id_fotos = $this->id_fotos;

        // Essa variável tbm é responsável por armazenar o id do produto, caso haja retorno.
        // Verfica se existe o valor no banco, pelo nome.
        $existencia_bd = $this->produtos_usuario->verifica_existencia_bd();

        if($existencia_bd == false){

            $this->produtos_exemplo->setNome($this->nome);

            /* Se o produto não existir nos produtos base do usuário, vai verificar se existe
            algum produto com o mesmo nome nos produtos de exemplo.
            Caso tenha, vai ser usada a foto do produto de exemplo, no produto base e da lista. */
            $verifica_nome_exemplo = $this->produtos_exemplo->verifica_existencia_nome();

            if($verifica_nome_exemplo == false){

                $ultimo_registro = $this->produtos_usuario->criar_produtos_usuario();

            }else{

                $id_produto_exemplo = $verifica_nome_exemplo;

                $this->produtos_exemplo->setId($id_produto_exemplo);

                $id_foto_exemplo = $this->produtos_exemplo->retorna_foto_exemplo();

                $this->produtos_usuario->id_fotos = $id_foto_exemplo;

                $ultimo_registro = $this->produtos_usuario->criar_produtos_usuario();

                $this->setIdFotos($id_foto_exemplo);

            }

        }else{

            $this->produtos_usuario->id = $existencia_bd[0];
            $this->produtos_usuario->id_fotos = $existencia_bd[1];

            $this->produtos_usuario->atualiza_dados_produtos_usuario();

            $this->id_fotos = $existencia_bd[1];

            $ultimo_registro = $existencia_bd[0];

        }

        if($this->id_fotos == NULL){

            if($this->id_categorias == "nulo"){

                $sql = mysqli_query(

                    $conn, "UPDATE produtos
                    SET nome='$this->nome',
                    tipo_exibicao='$this->tipo_exibicao',
                    qtd='$this->qtd',
                    id_fotos=NULL,
                    carrinho='$this->carrinho',
                    valor='$this->valor',
                    obs='$this->obs',
                    id_produtos_usuario='$ultimo_registro'
                    WHERE id='$this->id__prod'"
        
                ) or die("Erro conexão");

            }else{

                $sql = mysqli_query(

                    $conn, "UPDATE produtos
                    SET nome='$this->nome',
                    tipo_exibicao='$this->tipo_exibicao',
                    qtd='$this->qtd',
                    id_categorias='$this->id_categorias',
                    id_fotos=NULL,
                    carrinho='$this->carrinho',
                    valor='$this->valor',
                    obs='$this->obs',
                    id_produtos_usuario='$ultimo_registro'
                    WHERE id='$this->id__prod'"
        
                ) or die("Erro conexão");

            }

        }else{

            if($this->id_categorias == "nulo"){

                $sql = mysqli_query(

                    $conn, "UPDATE produtos
                    SET nome='$this->nome',
                    tipo_exibicao='$this->tipo_exibicao',
                    qtd='$this->qtd',
                    id_fotos='$this->id_fotos',
                    carrinho='$this->carrinho',
                    valor='$this->valor',
                    obs='$this->obs',
                    id_produtos_usuario='$ultimo_registro'
                    WHERE id='$this->id__prod'"
        
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
                    obs='$this->obs',
                    id_produtos_usuario='$ultimo_registro'
                    WHERE id='$this->id__prod'"
        
                ) or die("Erro conexão");

            }

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
            WHERE id='$this->id__prod'"

        ) or die("Erro conexão");

        return $this->retorna_json->retorna_json(false);

    }

    public function remove_produto_carrinho(){

        $conn = $this->conn;

        $sql = mysqli_query(

            $conn, "UPDATE produtos
            SET carrinho='$this->carrinho'
            WHERE id='$this->id__prod'"

        ) or die("Erro conexão");

        return $this->retorna_json->retorna_json(false);

    }

}

?>