<?php

class Produtos extends Usuarios{

    private $conn;
    private $retorna_json;
    private $verifica_titularidade_lista;
    private $produtos_usuario;
    private $produtos_exemplo;
    private $class_historico;
    private $classe_categoria;
    
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

    public function __construct($produtos_exemplo, $produtos_usuario, $classeUsuariosListas, $classeRetornosJson, $classeConexao, $class_historico, $classe_categoria){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;
        $this->verifica_titularidade_lista = $classeUsuariosListas;
        $this->produtos_usuario = $produtos_usuario;
        $this->produtos_exemplo = $produtos_exemplo;
        parent::__construct($classeRetornosJson, $classeConexao, $classe_categoria);
        $this->class_historico = $class_historico;

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
                AND (produtos.carrinho=0 OR produtos.carrinho=2)"
            
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
                        AND (produtos.carrinho=0 OR produtos.carrinho=2)"
                    
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

            $qtd_produtos_carrinho = $this->retorna_qtd_produtos_carrinho();

            $confirmacoes = [];

            $confirmacoes = [

                /* Incluindo a informação se o usuário atual é ou
                não dono da lista */
                
                "dono_lista"=>$this->verifica_titularidade_lista->verifica_titularidade_lista($dono_lista),
                "produtos_carrinho"=>$qtd_produtos_carrinho

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

        /* Histórico */

        $this->class_historico->setData("today");
        $this->class_historico->setTipo(3);
        $this->class_historico->setMsg("adicionou o item '".$this->nome."' à lista.");
        $this->class_historico->setIdListas(intval($this->id_listas));
        $this->class_historico->setIdCompras(false);
        $this->class_historico->setIdUsuarios(intval($this->getIdUsuarios()));

        $this->class_historico->incluir_historico();

        return $this->retorna_json->retorna_json(false);

    }

    /* Retorna o nome e a lista do produto pelo id */
    private function retorna_nome_lista_produto($id_produto){

        try {

            $conexao = $this->conn->prepare(

                "SELECT nome, id_listas FROM produtos
                WHERE id=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("i", $id_produto);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();
            $result = $sql->fetch_assoc();

            $nome_produto = $result["nome"];
            $id_lista_produto = $result["id_listas"];

            return [$nome_produto, $id_lista_produto];
            
        } catch (Exception $e) {

            error_log("Classe Produtos - Métodos: retorna_nome_produto - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    /* Apaga um produto da lista */
    public function deletar_produto(){

        $conn = $this->conn;

        $inf_produto = $this->retorna_nome_lista_produto($this->id__prod);

        /* Histórico */

        $this->class_historico->setData("today");
        $this->class_historico->setTipo(4);
        $this->class_historico->setMsg("removeu o item '".$inf_produto[0]."' da lista.");
        $this->class_historico->setIdListas(intval($inf_produto[1]));
        $this->class_historico->setIdCompras(false);
        $this->class_historico->setIdUsuarios(intval($this->getIdUsuarios()));

        $this->class_historico->incluir_historico();

        $sql = mysqli_query(

            $conn, "DELETE FROM produtos
            WHERE id='$this->id__prod'"

        ) or die("Erro conexão");

        return $this->retorna_json->retorna_json(false);

    }

    private function retorna_tipo($tipo){

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
                return "Un";
            break;
            case 2:
                return "Kg";
            break;
            case 3:
                return "g";
            break;
            case 4:
                return "L";
            break;
            case 5:
                return "ml";
            break;
            case 6:
                return "dz";
            break;
            case 7:
                return "Caixa";
            break;
            case 8:
                return "Pacote";
            break;
            case 9:
                return "Garrafa";
            break;
            case 10:
                return "Lata";
            break;
            case 11:
                return "Embalagem";
            break;

        }

    }

    /* Verifica o tipo de edição do produto e inclui a alteração no histórico */
    private function verifica_edicao_produto($id_produto, $inf_produtos_att, $id_usuario){

        try {

            $conexao = $this->conn->prepare(

                "SELECT nome, tipo_exibicao, qtd, id_listas, valor, obs FROM produtos
                WHERE id=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("i", $id_produto);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();

            $inf_produtos_ant = []; // Informações antigas do produto

            $alteracoes = []; // Guarda as edições do produto

            $result = $sql->fetch_assoc();

            $inf_produtos_ant[0] = $result["nome"];
            $inf_produtos_ant[1] = $result["tipo_exibicao"];
            $inf_produtos_ant[2] = $result["qtd"];
            $inf_produtos_ant[3] = $result["valor"];
            $inf_produtos_ant[4] = $result["obs"];

            $id_lista = $result["id_listas"];
            
            if($inf_produtos_ant[0] != $inf_produtos_att[0]){

                array_push($alteracoes, "Nome: de '".$inf_produtos_ant[0]."' para '".$inf_produtos_att[0]."'");

            }

            if($inf_produtos_ant[1] != $inf_produtos_att[1]){

                array_push($alteracoes, "Tipo: de '".$this->retorna_tipo($inf_produtos_ant[1])."' para '".$this->retorna_tipo($inf_produtos_att[1])."'");

            }

            if($inf_produtos_ant[2] != $inf_produtos_att[2]){

                array_push($alteracoes, "Quantidade: de '".$inf_produtos_ant[2]."' para '".$inf_produtos_att[2]."'");

            }

            if($inf_produtos_ant[3] != $inf_produtos_att[3]){

                array_push($alteracoes, "Valor: de 'R$".number_format($inf_produtos_ant[3], 2)."' para 'R$".number_format($inf_produtos_att[3], 2)."'");

            }

            if($inf_produtos_ant[4] != $inf_produtos_att[4]){

                if($inf_produtos_ant[4] == ""){

                    $obs_ant = "(Vazio)";

                }else{

                    $obs_ant = $inf_produtos_ant[4];

                }

                if($inf_produtos_att[4] == ""){

                    $obs_att = "(Vazio)";

                }else{

                    $obs_att = $inf_produtos_att[4];

                }

                array_push($alteracoes, "Obs: de '".$obs_ant."' para '".$obs_att."'");

            }

            if(count($alteracoes) > 0){

                /* Histórico */

                $this->class_historico->setData("today");
                $this->class_historico->setTipo(5);
                $this->class_historico->setMsg("alterou o item: ".$inf_produtos_att[0]."\n\n".implode("\n", $alteracoes));
                $this->class_historico->setIdListas(intval($id_lista));
                $this->class_historico->setIdCompras(false);
                $this->class_historico->setIdUsuarios(intval($id_usuario));

                $this->class_historico->incluir_historico();

            }
            
        } catch (Exception $e) {

            error_log("Classe Produtos - Métodos: verifica_edicao_produto - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    /* Editar dados de um produto */
    public function editar_produto(){

        $conn = $this->conn;
        $id_usuario = $this->getIdUsuarios();

        $this->produtos_usuario->nome = $this->nome;
        $this->produtos_usuario->id_usuarios = $id_usuario;
        $this->produtos_usuario->tipo_exibicao = $this->tipo_exibicao;
        $this->produtos_usuario->id_fotos = $this->id_fotos;

        /* Chamando o histórico */

        $inf_produtos = [

            $this->nome,
            $this->tipo_exibicao,
            $this->qtd,
            $this->valor,
            $this->obs

        ];

        $this->verifica_edicao_produto($this->id__prod, $inf_produtos, $id_usuario);

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

    private function retorna_qtd_produtos_carrinho(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT * FROM produtos
                WHERE id_listas=?
                AND carrinho=?"

            );

            if(!$conexao){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $carrinho = 1;

            $conexao->bind_param("ii", $this->id_listas, $carrinho);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();
            $qtd = $sql->num_rows;

            return $qtd;
            
        } catch (Exception $e) {

            error_log("Classe Produtos - Métodos: retorna_qtd_produtos_carrinho - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    /* Limpa o carrinho e desmarca os produtos comprados */
    public function limpar_carrinho($tipo){

        try {

            $conexao = $this->conn->prepare(

                "UPDATE produtos
                SET carrinho=?
                WHERE id_listas=?
                AND carrinho=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }
            
            switch($tipo){

                case "limpar_carrinho":

                    $carrinho = 0;
                    $carrinho_where = 1;

                break;

                case "desmarcar_comprados":

                    $carrinho = 0;
                    $carrinho_where = 2;

                break;

            }

            $conexao->bind_param("iii", $carrinho, $this->id_listas, $carrinho_where);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            if($this->conn->affected_rows > 0){

                /* Histórico */

                switch($tipo){

                    case "limpar_carrinho":
    
                        $this->class_historico->setTipo(9);
                        $this->class_historico->setMsg("limpou o carrinho.");
    
                    break;
    
                    case "desmarcar_comprados":
    
                        $this->class_historico->setTipo(10);
                        $this->class_historico->setMsg("Removeu a etiqueta 'COMPRADO' de todos os produtos.");
    
                    break;
    
                }

                $this->class_historico->setData("today");
                $this->class_historico->setIdListas(intval($this->id_listas));
                $this->class_historico->setIdCompras(false);
                $this->class_historico->setIdUsuarios(intval($this->getIdUsuarios()));

                $this->class_historico->incluir_historico();

            }

            return $this->retorna_json->retorna_json(null);
            
        } catch (Exception $e) {
            
            error_log("Classe Produtos - Métodos: limpar_carrinho - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());

        }

    }

    public function remover_comprados(){

        try {

            $conexao = $this->conn->prepare(

                "DELETE FROM produtos
                WHERE id_listas=?
                AND carrinho=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $carrinho = 2;

            $conexao->bind_param("ii", $this->id_listas, $carrinho);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            if($this->conn->affected_rows > 0){

                /* Histórico */

                $this->class_historico->setData("today");
                $this->class_historico->setTipo(11);
                $this->class_historico->setMsg("removeu da lista, todos os produtos com a etiqueta 'COMPRADO'.");
                $this->class_historico->setIdListas(intval($this->id_listas));
                $this->class_historico->setIdCompras(false);
                $this->class_historico->setIdUsuarios(intval($this->getIdUsuarios()));

                $this->class_historico->incluir_historico();

            }

            return $this->retorna_json->retorna_json(null);
            
        } catch (Exception $e) {

            error_log("Classe Produtos - Métodos: remover_comprados - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    /* Métodos chamado após o usuário realizar uma compra.
    Ele tira todos os produto do carrinho, e insere a etiqueta "COMPRADO" */
    public function inserir_etiqueta_comprado(){

        try {

            $conexao = $this->conn->prepare(

                "UPDATE produtos
                SET carrinho=?
                WHERE id_listas=?
                AND carrinho=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $carrinho = 2;
            $carrinho_where = 1;

            $conexao->bind_param("iii", $carrinho, $this->id_listas, $carrinho_where);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }
            
        } catch (Exception $e) {

            error_log("Classe Produtos - Métodos: inserir_etiqueta_comprado - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

}

?>