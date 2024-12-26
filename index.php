<?php
header('Content-Type: application/json');

require_once "config.php";

require_once "classes/Conexao.class.php";
$classeConexao = new Conexao();

require_once "classes/RetornosJson.class.php";
$classeRetornosJson = new RetornosJson();

require_once "classes/HistoricoListas.class.php";
$classeHistoricos = new HistoricoListas($classeRetornosJson, $classeConexao);

require_once "classes/Categorias.class.php";
$classeCategorias = new Categorias($classeRetornosJson, $classeConexao);

require_once "classes/Usuarios.class.php";
$classeUsuarios = new Usuarios($classeRetornosJson, $classeConexao, $classeCategorias);

require_once "classes/Listas.class.php";
$classeListas = new Listas($classeRetornosJson, $classeConexao, $classeHistoricos, $classeCategorias);

require_once "classes/UsuariosListas.class.php";
$classeUsuariosListas = new UsuariosListas($classeListas ,$classeRetornosJson, $classeConexao, $classeHistoricos, $classeCategorias);

require_once "classes/ProdutosExemplo.class.php";
$classeProdutosExemplo = new ProdutosExemplo($classeRetornosJson, $classeConexao);

require_once "classes/ProdutosUsuario.class.php";
$classeProdutosUsuario = new ProdutosUsuario($classeProdutosExemplo, $classeRetornosJson, $classeConexao);

require_once "classes/Produtos.class.php";
$classeProdutos = new Produtos($classeProdutosExemplo, $classeProdutosUsuario, $classeUsuariosListas, $classeRetornosJson, $classeConexao, $classeHistoricos, $classeCategorias);

require_once "classes/ProdutosCompras.class.php";
$classeProdutosCompras = new ProdutosCompras($classeRetornosJson, $classeConexao);

require_once "classes/Mercados.class.php";
$classeMercados = new Mercados($classeRetornosJson, $classeConexao);

require_once "classes/Compras.class.php";
$classeCompras = new Compras($classeUsuariosListas, $classeProdutosCompras, $classeMercados, $classeRetornosJson, $classeConexao, $classeHistoricos, $classeProdutos);

require_once "classes/Graficos.class.php";
$classeGraficos = new Graficos($classeConexao, $classeRetornosJson, $classeProdutosCompras);

if(isset($_GET["url"])){

    $explode = explode("/", $_GET["url"]);

    $headers = apache_request_headers();

    /* Verificando se a API está ativada, e se o token de verificação existe
    e é válido. */
    if(API_IS_ACTIVE == true){

        /* Id do usuário buscado */
        $classeUsuarios->setIdUsuarios($explode[0]);

        /* Verificando a existência do usuario informado. */
        if($classeUsuarios->verifica_usuario() == true){

            if(isset($explode[1])){

                switch($explode[1]){

                    /* *** GET *** */

                    /* Retorna todas as listas do usuário */
                    /* Exemplo de rota: API/id_usuario/listas */
                    case "listas":

                        $classeListas->setIdUsuarios($explode[0]);
        
                        echo $classeListas->retorna_listas();
        
                    break;
        
                    /* Retorna a lista de produtos */
                    /* Exemplo: API/id_usuario/produtos/id_listas/id_categoria */
                    case "produtos":                    
        
                        /* Verificando se o id da lista foi passado */
                        if(isset($explode[2])){

                            /* Verificando se o id da categoria foi passado na rota. */
                            if(isset($explode[3])){

                                $classeProdutos->setIdUsuarios($explode[0]);
                                $classeUsuariosListas->setIdUsuarios($explode[0]);

                                $classeProdutos->setIdLista($explode[2]);
                                $classeListas->setIdLista($explode[2]);

                                $classeProdutos->setCarrinho(false);

                                echo $classeProdutos->retorna_produtos($explode[3], false, $classeListas->retorna_dono_lista());

                            }else{

                                $classeProdutos->setIdUsuarios($explode[0]);
                                $classeUsuariosListas->setIdUsuarios($explode[0]);

                                $classeProdutos->setIdLista($explode[2]);
                                $classeListas->setIdLista($explode[2]);

                                $classeProdutos->setCarrinho(false);

                                echo $classeProdutos->retorna_produtos(false, true, $classeListas->retorna_dono_lista());

                            }

                        }else{

                            echo $classeRetornosJson->retornaErro("Insira um id de lista na rota");

                        }                    
        
                    break;

                    case "produtos_carrinho":

                        if(isset($explode[2])){

                            $classeProdutos->setIdUsuarios($explode[0]);
                            $classeUsuariosListas->setIdUsuarios($explode[0]);

                            $classeProdutos->setIdLista($explode[2]);
                            $classeListas->setIdLista($explode[2]);

                            $classeProdutos->setCarrinho(true);

                            echo $classeProdutos->retorna_produtos(false, false, $classeListas->retorna_dono_lista());

                        }else{

                            echo $classeRetornosJson->retornaErro("Insira um id de lista na rota");

                        }

                    break;

                    /* Retorna as categorias do usuário */
                    case "categorias":

                        $classeCategorias->setIdUsuarios($explode[0]);

                        echo $classeCategorias->retorna_categoria();

                    break;

                    case "usuarios_lista":

                        if(isset($explode[2])){

                            $classeUsuariosListas->setIdUsuarios($explode[0]);
                            $classeUsuariosListas->setIdListas($explode[2]);

                            if($classeUsuariosListas->verifica_usuario_lista() == true){

                                $classeListas->setIdUsuarios($explode[0]);
                                $classeListas->setIdLista($explode[2]);

                                echo $classeUsuariosListas->retorna_membros_lista();

                            }else{

                                echo $classeRetornosJson->retornaErro("O usuário informado, não tem acesso a essa lista");

                            }

                        }else{

                            echo $classeRetornosJson->retornaErro("Insira o id da lista, na rota");

                        }

                    break;

                    case "produtos_exemplo_usuario":

                        $classeProdutosUsuario->setId_usuarios($explode[0]);

                        echo $classeProdutosUsuario->retorna_produtos_usuario();

                    break;

                    case "mercados":

                        $classeMercados->setIdUsuarios($explode[0]);

                        echo $classeMercados->retorna_mercado();

                    break;

                    case "compras":

                        $classeCompras->setIdUsuarios($explode[0]);

                        /* explode[2] = Filtro data */
                        if(isset($explode[2])){

                            switch($explode[2]){

                                case "mes_atual":

                                    echo $classeCompras->retorna_compras("mes_atual", false, false);

                                break;

                                case "mes_passado":

                                    echo $classeCompras->retorna_compras("mes_passado", false, false);

                                break;

                                case "escolher_datas":

                                    if(isset($explode[3]) && isset($explode[4])){

                                        echo $classeCompras->retorna_compras("escolher_datas", $explode[3], $explode[4]);

                                    }else{

                                        echo $classeRetornosJson->retornaErro(

                                            "Insira a data 1 e a data 2 para comparação. Ex: API/escolher_datas/2024-10-26/1996-10-16"
                                            
                                        );

                                    }

                                break;

                            }

                        }else{

                            echo $classeCompras->retorna_compras(false);

                        }

                    break;

                    case "produtos_compra":

                        if(isset($explode[2])){

                            $classeProdutosCompras->setIdCompras($explode[2]);

                            echo $classeProdutosCompras->retorna_produtos_compra();

                        }else{

                            echo $classeRetornosJson->retornaErro("Insira um id da compra, na rota");

                        }

                    break;

                    case "graficos":

                        if(isset($explode[2])){

                            switch($explode[2]){

                                case "geral":

                                    if(isset($explode[3])){

                                        $classeGraficos->ano = $explode[3];
                                        $classeGraficos->id_usuario = $explode[0];

                                        echo $classeGraficos->formata_venda_mes();
                                        
                                    }else{

                                        echo $classeRetornosJson->retornaErro("Informe o ano que deseja exibir. API/grficos/geral/ano");

                                    }

                                break;

                                case "total_media":

                                    if(isset($explode[3])){

                                        $classeGraficos->ano = $explode[3];
                                        $classeGraficos->id_usuario = $explode[0];

                                        echo $classeGraficos->total_media();
                                        
                                    }else{

                                        echo $classeRetornosJson->retornaErro("Informe o ano que deseja exibir. API/grficos/total_media/ano");

                                    }

                                break;

                                case "detalhes_compras":

                                    if(isset($explode[3])){

                                        $classeGraficos->ano = $explode[3];
                                        $classeGraficos->id_usuario = $explode[0];

                                        echo $classeGraficos->detalhes_mes();
                                        
                                    }else{

                                        echo $classeRetornosJson->retornaErro("Informe o ano que deseja exibir. API/grficos/detalhes_compras/ano");

                                    }

                                break;

                                case "valor_mes":

                                    $classeGraficos->ano = date("Y");
                                    $classeGraficos->id_usuario = $explode[0];

                                    echo $classeGraficos->despesas_totais_mes_atual_passado();

                                break;

                                case "categorias":

                                    if(isset($explode[3])){

                                        $classeGraficos->id_usuario = $explode[0];

                                        switch($explode[3]){

                                            case "mes_atual":

                                                echo $classeGraficos->retorna_categorias_por_data("mes_atual", false, false);

                                            break;

                                            case "mes_passado":

                                                echo $classeGraficos->retorna_categorias_por_data("mes_passado", false, false);

                                            break;

                                            case "escolher_datas":

                                                if(isset($explode[4]) && isset($explode[5])){

                                                    echo $classeGraficos->retorna_categorias_por_data("escolher_datas", $explode[4], $explode[5]);

                                                }else{

                                                    echo $classeRetornosJson->retornaErro("Informe as datas para aplicar o filtro. API/grficos/categorias/escolher_datas/data_1/data_2");

                                                }

                                            break;

                                        }

                                    }else{

                                        echo $classeRetornosJson->retornaErro("Informe o tipo de fitro que deseja aplicar. API/grficos/categorias/tipo_filtro");

                                    }

                                break;

                                case "mercados":

                                    if(isset($explode[3])){

                                        $classeGraficos->id_usuario = $explode[0];

                                        switch($explode[3]){

                                            case "mes_atual":

                                                echo $classeGraficos->retorna_mercados_por_data("mes_atual", false, false);

                                            break;

                                            case "mes_passado":

                                                echo $classeGraficos->retorna_mercados_por_data("mes_passado", false, false);

                                            break;

                                            case "escolher_datas":

                                                if(isset($explode[4]) && isset($explode[5])){

                                                    echo $classeGraficos->retorna_mercados_por_data("escolher_datas", $explode[4], $explode[5]);

                                                }else{

                                                    echo $classeRetornosJson->retornaErro("Informe as datas para aplicar o filtro. API/grficos/mercados/escolher_datas/data_1/data_2");

                                                }

                                            break;

                                        }

                                    }else{

                                        echo $classeRetornosJson->retornaErro("Informe o tipo de fitro que deseja aplicar. API/grficos/mercados/tipo_filtro");

                                    }

                                break;

                                case "produtos":

                                    if(isset($explode[3])){

                                        $classeGraficos->id_usuario = $explode[0];

                                        switch($explode[3]){

                                            case "mes_atual":

                                                echo $classeGraficos->retorna_produtos_por_data("mes_atual", false, false);

                                            break;

                                            case "mes_passado":

                                                echo $classeGraficos->retorna_produtos_por_data("mes_passado", false, false);

                                            break;

                                            case "escolher_datas":

                                                if(isset($explode[4]) && isset($explode[5])){

                                                    echo $classeGraficos->retorna_produtos_por_data("escolher_datas", $explode[4], $explode[5]);

                                                }else{

                                                    echo $classeRetornosJson->retornaErro("Informe as datas para aplicar o filtro. API/grficos/produtos/escolher_datas/data_1/data_2");

                                                }

                                            break;

                                        }

                                    }else{

                                        echo $classeRetornosJson->retornaErro("Informe o tipo de fitro que deseja aplicar. API/grficos/produtos/tipo_filtro");

                                    }

                                break;

                            }

                        }else{

                            echo $classeRetornosJson->retornaErro("Informe o tipo de gráfico que deseja retornar. API/graficos/tipo");

                        }

                    break;

                    case "historico":

                        if(isset($explode[2])){

                            $classeUsuariosListas->setIdUsuarios($explode[0]);
                            $classeUsuariosListas->setIdListas($explode[2]);

                            if($classeUsuariosListas->verifica_usuario_lista() == true){

                                $classeHistoricos->setIdListas(intval($explode[2]));

                                echo $classeHistoricos->retorna_historico();

                            }else{

                                echo $classeRetornosJson->retornaErro("Acesso do usuário a lista, negado.");

                            }

                        }else{

                            echo $classeRetornosJson->retornaErro("Informe o id da lista. API/usuario/historico/ID_LISTA");

                        }

                    break;

                    case "limpar_carrinho":

                        if(isset($explode[2])){

                            $classeUsuariosListas->setIdUsuarios($explode[0]);
                            $classeUsuariosListas->setIdListas($explode[2]);

                            if($classeUsuariosListas->verifica_usuario_lista() == true){

                                $classeProdutos->setIdLista($explode[2]);
                                $classeProdutos->setIdUsuarios($explode[0]);

                                echo $classeProdutos->limpar_carrinho("limpar_carrinho");

                            }else{

                                echo $classeRetornosJson->retornaErro("Permissão negada.");

                            }

                        }else{

                            echo $classeRetornosJson->retornaErro("Informe o id da lista. API/usuario/limpar_carrinho/ID_LISTA");

                        }

                    break;

                    case "desmarcar_comprados":

                        if(isset($explode[2])){

                            $classeUsuariosListas->setIdUsuarios($explode[0]);
                            $classeUsuariosListas->setIdListas($explode[2]);

                            if($classeUsuariosListas->verifica_usuario_lista() == true){

                                $classeProdutos->setIdLista($explode[2]);
                                $classeProdutos->setIdUsuarios($explode[0]);

                                echo $classeProdutos->limpar_carrinho("desmarcar_comprados");

                            }else{

                                echo $classeRetornosJson->retornaErro("Permissão negada.");

                            }

                        }else{

                            echo $classeRetornosJson->retornaErro("Informe o id da lista. API/usuario/limpar_carrinho/ID_LISTA");

                        }

                    break;

                    case "remover_comprados":

                        if(isset($explode[2])){

                            $classeUsuariosListas->setIdUsuarios($explode[0]);
                            $classeUsuariosListas->setIdListas($explode[2]);

                            if($classeUsuariosListas->verifica_usuario_lista() == true){

                                $classeProdutos->setIdLista($explode[2]);
                                $classeProdutos->setIdUsuarios($explode[0]);

                                echo $classeProdutos->remover_comprados();

                            }else{

                                echo $classeRetornosJson->retornaErro("Permissão negada.");

                            }

                        }else{

                            echo $classeRetornosJson->retornaErro("Informe o id da lista. API/usuario/limpar_carrinho/ID_LISTA");

                        }

                    break;

                    case "meus_produtos":
                        
                        $classeProdutosUsuario->setId_usuarios($explode[0]);

                        echo $classeProdutosUsuario->retorna_todos_produtos_usuarios();

                    break;

                    /* *** POST *** */

                    /* Atualiza o nome da lista individualmente. */
                    case "atualiza_lista":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_lista"]) && isset($_POST["novo_nome"])){
    
                                $id_lista = $_POST["id_lista"];
                                $novo_nome = $_POST["novo_nome"];

                                $classeListas->setIdLista($id_lista);
                                $classeListas->setNomeLista($novo_nome);
    
                                echo $classeListas->atualiza_nome_lista($explode[0]);
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa informar o id e o novo nome da lista que quer alterar.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa informar o id e o novo nome da lista como POST.");
    
                        }
    
                    break;

                    /* Adiciona uma nova lista */
                    case "adiciona_lista":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["nome_lista"])){
    
                                $nome_lista = $_POST["nome_lista"];

                                $classeListas->setIdUsuarios($explode[0]);

                                $classeListas->setNomeLista($nome_lista);
    
                                echo $classeListas->criar_lista();
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir o nome da lista que quer inserir.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir o nome da lista como POST.");
    
                        }

                    break;

                    /* Deleta uma lista existente */
                    case "deletar_lista":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_lista"])){
    
                                $id_lista = $_POST["id_lista"];

                                $classeListas->setIdUsuarios($explode[0]);

                                $classeListas->setIdLista($id_lista);
    
                                echo $classeListas->deletar_lista();
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir o id da lista que quer deletar.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir o id da lista como POST.");
    
                        }

                    break;

                    /* Adiciona um novo produto */
                    case "adiciona_produto":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["nome_produto"]) && isset($_POST["tipo_exibicao"]) && isset($_POST["qtd"]) && isset($_POST["categoria"]) && isset($_POST["lista"]) && isset($_POST["foto"]) && isset($_POST["carrinho"])){

                                $classeProdutos->setIdUsuarios($explode[0]);
    
                                $nome_produto = $_POST["nome_produto"];
                                $tipo_exibicao = $_POST["tipo_exibicao"];
                                $qtd = $_POST["qtd"];
                                $categoria = $_POST["categoria"];
                                $lista = $_POST["lista"];
                                $foto = $_POST["foto"];
                                $carrinho = $_POST["carrinho"];
                                $valor = $_POST["valor"];
                                $obs = $_POST["obs"];

                                $classeProdutos->setNome($nome_produto);
                                $classeProdutos->setTipoExibicao($tipo_exibicao);
                                $classeProdutos->setQtd($qtd);
                                $classeProdutos->setIdCategorias($categoria);
                                $classeProdutos->setIdLista($lista);
                                $classeProdutos->setIdFotos($foto);
                                $classeProdutos->setCarrinho($carrinho);
                                $classeProdutos->setValor($valor);
                                $classeProdutos->setObs($obs);
                                
    
                                echo $classeProdutos->adicionar_produto();
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir os dados do produto.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir os dados do produto como POST.");
    
                        }

                    break;

                    /* Deleta um produto */
                    case "deleta_produto":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_produto"])){
    
                                $id_produto = $_POST["id_produto"];

                                $classeProdutos->setIdUsuarios($explode[0]);

                                $classeProdutos->setIdProduto($id_produto);
    
                                echo $classeProdutos->deletar_produto();
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir o id do produto que quer deletar.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir o id do produto como POST.");
    
                        }

                    break;

                    case "edita_produto":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_produto"]) && isset($_POST["nome_produto"])){

                                $classeProdutos->setIdUsuarios($explode[0]);
    
                                $id_produto = $_POST["id_produto"];
                                $nome_produto = $_POST["nome_produto"];
                                $tipo_exibicao = $_POST["tipo_exibicao"];
                                $qtd = $_POST["qtd"];
                                $id_categorias = $_POST["id_categorias"];
                                $id_fotos = $_POST["id_fotos"];
                                $carrinho = $_POST["carrinho"];
                                $valor = $_POST["valor"];
                                $obs = $_POST["obs"];
                                
                                $classeProdutos->setIdProduto($id_produto);
                                $classeProdutos->setNome($nome_produto );
                                $classeProdutos->setTipoExibicao($tipo_exibicao);
                                $classeProdutos->setQtd($qtd);
                                $classeProdutos->setIdCategorias($id_categorias);
                                $classeProdutos->setIdFotos($id_fotos);
                                $classeProdutos->setCarrinho($carrinho);
                                $classeProdutos->setValor($valor);
                                $classeProdutos->setObs($obs);
    
                                echo $classeProdutos->editar_produto();
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir os dados do produto que quer editar.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir os dados do produto como POST.");
    
                        }

                    break;

                    case "adicionar_produto_carrinho":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_produto"]) && isset($_POST["qtd"]) && isset($_POST["valor"])){
    
                                $id_produto = $_POST["id_produto"];
                                $qtd = $_POST["qtd"];
                                $valor = $_POST["valor"];

                                $classeProdutos->setIdUsuarios($explode[0]);

                                $classeProdutos->setIdProduto($id_produto);
                                $classeProdutos->setQtd($qtd);
                                $classeProdutos->setValor($valor);
                                $classeProdutos->setCarrinho(1);
    
                                echo $classeProdutos->add_produto_carrinho();
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir o id do produto, qtd e valor que quer inserir no carrinho.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir o id do produto, qtd e valor como POST.");
    
                        }

                    break;

                    case "remover_produto_carrinho":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_produto"])){
    
                                $id_produto = $_POST["id_produto"];

                                $classeProdutos->setIdUsuarios($explode[0]);

                                $classeProdutos->setIdProduto($id_produto);
                                $classeProdutos->setCarrinho(0);
    
                                echo $classeProdutos->remove_produto_carrinho();
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir o id do produto que quer remover do carrinho.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir o id do produto como POST.");
    
                        }

                    break;

                    case "adicionar_usuario_lista":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){

                            if(isset($_POST["id_lista"]) && isset($_POST["email_usuario"])){

                                $id_lista = $_POST["id_lista"];
                                $email_usuario = $_POST["email_usuario"];

                                $classeUsuariosListas->setIdUsuarios($explode[0]);
                                echo $classeUsuariosListas->adiciona_usuario_lista($email_usuario, $id_lista);

                            }else{

                                echo $classeRetornosJson->retornaErro("Você precisa inserir o id da lista e o email do usuário.");

                            }

                        }else{

                            echo $classeRetornosJson->retornaErro("Você precisa inserir o id da lista e o email do usuário como POST.");

                        }

                    break;

                    case "remover_usuario_lista":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){

                            if(isset($_POST["id_lista"]) && isset($_POST["id_usuario"])){

                                $id_lista = $_POST["id_lista"];
                                $id_usuario = $_POST["id_usuario"];

                                $classeUsuariosListas->setIdUsuarios($explode[0]);

                                $classeUsuariosListas->setIdUsuariosLista($id_usuario);
                                $classeUsuariosListas->setIdListas($id_lista);

                                echo $classeUsuariosListas->remover_usuario_lista();

                            }else{

                                echo $classeRetornosJson->retornaErro("Você precisa inserir o id da lista e o id do usuário.");

                            }

                        }else{

                            echo $classeRetornosJson->retornaErro("Você precisa inserir o id da lista e o id do usuário como POST.");

                        }

                    break;

                    /* Adiciona uma nova categoria */
                    case "adiciona_categoria":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["nome_categoria"])){
    
                                $nome_categoria = $_POST["nome_categoria"];

                                $classeCategorias->setIdUsuarios($explode[0]);

                                $classeCategorias->setNome($nome_categoria);
    
                                echo $classeCategorias->add_categoria();
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir o nome da categoria.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir o nome da categoria como POST.");
    
                        }

                    break;

                    /* Deleta uma lista existente */
                    case "deletar_categoria":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_categoria"])){
    
                                $id_categoria = $_POST["id_categoria"];

                                $classeCategorias->setId($id_categoria);
                                $classeCategorias->setIdUsuarios($explode[0]);
    
                                echo $classeCategorias->apagar_categoria();
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir o id da categoria que quer deletar.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir o id da categoria como POST.");
    
                        }

                    break;

                    case "editar_categoria":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_categoria"]) && isset($_POST["nome_categoria"])){
    
                                $id_categoria = $_POST["id_categoria"];
                                $nome_categoria = $_POST["nome_categoria"];

                                $classeCategorias->setIdUsuarios($explode[0]);

                                $classeCategorias->setId($id_categoria);
                                $classeCategorias->setNome($nome_categoria);
    
                                echo $classeCategorias->editar_categoria();
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa informar o id e o novo nome da categoria que quer alterar.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa informar o id e o novo nome da categoria como POST.");
    
                        }

                    break;

                    case "cadastrar_compra":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["nome_mercado"]) && isset($_POST["id_lista"])){
    
                                $nome_mercado = $_POST["nome_mercado"];
                                $id_lista = $_POST["id_lista"];

                                $classeCompras->setData(date('Y-m-d H:i:s')); // Passando a data atual
                                $classeCompras->setIdUsuarios($explode[0]);
    
                                echo $classeCompras->cadastra_compra($id_lista, $nome_mercado);
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir o nome do mercado e o id da lista.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir o nome do mercado e o id da lista, como POST.");
    
                        }

                    break;

                    case "apaga_produto_usuario":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_produto"])){

                                $classeProdutosUsuario->setId($_POST["id_produto"]);
                                $classeProdutosUsuario->setId_usuarios($explode[0]);

                                if($classeProdutosUsuario->verifica_produto_usuario() == true){

                                    echo $classeProdutosUsuario->apaga_produto_usuario();

                                }else{

                                    echo $classeRetornosJson->retornaErro("Acesso negado!");

                                }
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir o id do produto com o nome POST: id_produto.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir o id do produto, como POST.");
    
                        }

                    break;

                    case "altera_nome_meus_produtos":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_produto"]) && isset($_POST["nome_produto"])){

                                $classeProdutosUsuario->setId($_POST["id_produto"]);
                                $classeProdutosUsuario->setId_usuarios($explode[0]);

                                if($classeProdutosUsuario->verifica_produto_usuario() == true){

                                    $classeProdutosUsuario->setNome($_POST["nome_produto"]);

                                    echo $classeProdutosUsuario->altera_nome_produto_usuario();

                                }else{

                                    echo $classeRetornosJson->retornaErro("Acesso negado!");

                                }
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir o id do produto e o novo nome com o nome POST: id_produto, nome_produto respectivamente.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir o id do produto e o novo nome, como POST.");
    
                        }

                    break;

                    case "altera_nome_mercado":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_mercado"]) && isset($_POST["nome_mercado"])){

                                $classeMercados->setId($_POST["id_mercado"]);
                                $classeMercados->setIdUsuarios($explode[0]);

                                if($classeMercados->verifica_mercado_usuario() == true){

                                    $classeMercados->setNome($_POST["nome_mercado"]);

                                    echo $classeMercados->altera_nome_mercado();

                                }else{

                                    echo $classeRetornosJson->retornaErro("Acesso negado!");

                                }
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir o id do mercado e o novo nome com o nome POST: id_mercado, nome_mercado respectivamente.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir o id do mercado e o novo nome, como POST.");
    
                        }

                    break;

                    case "apaga_mercado":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_mercado"])){

                                $classeMercados->setId($_POST["id_mercado"]);
                                $classeMercados->setIdUsuarios($explode[0]);

                                if($classeMercados->verifica_mercado_usuario() == true){

                                    echo $classeMercados->apaga_mercado();

                                }else{

                                    echo $classeRetornosJson->retornaErro("Acesso negado!");

                                }
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir o id do mercado com o nome POST: id_mercado.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir o id do mercado, como POST.");
    
                        }

                    break;

                    case "apaga_compra":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_compra"])){

                                $classeCompras->setId($_POST["id_compra"]);
                                $classeCompras->setIdUsuarios($explode[0]);

                                if($classeCompras->verifica_compra_usuario() == true){

                                    echo $classeCompras->apaga_compra();

                                }else{

                                    echo $classeRetornosJson->retornaErro("Acesso negado!");

                                }
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir o id da compra com o nome POST: id_compra.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir o id da compra, como POST.");
    
                        }

                    break;

                    default:

                        echo $classeRetornosJson->retornaErro("A rota definida não existe");

                }

            }else{

                echo $classeRetornosJson->retornaErro("Defina um destino ao usuário, na rota");

            }

        }else{

            switch($explode[0]){

                case "login":

                    if($_SERVER["REQUEST_METHOD"] === "POST"){

                        if(isset($_POST["email"]) && isset($_POST["senha"])){

                            $email = $_POST["email"];
                            $senha = $_POST["senha"];

                            $classeUsuarios->setEmailUsuarios($email);
                            $classeUsuarios->setSenhaUsuarios($senha);
    
                            echo $classeUsuarios->login();

                        }else{

                            echo $classeRetornosJson->retornaErro("Você precisa informar um email e uma senha como POST");

                        }

                    }else{

                        echo $classeRetornosJson->retornaErro("Nessa rota você precisa informar o login e senha como POST");

                    }

                break;

                case "produtos_exemplo":

                    echo $classeProdutosExemplo->busca_produto();

                break;

                case "cadastrar":

                    if($_SERVER["REQUEST_METHOD"] === "POST"){

                        if(isset($_POST["nome"]) && isset($_POST["email"]) && isset($_POST["senha"]) && isset($_POST["confirma_senha"])){

                            $nome = $_POST["nome"];
                            $email = $_POST["email"];
                            $senha = $_POST["senha"];
                            $confirma_senha = $_POST["confirma_senha"];

                            $classeUsuarios->setNome($nome);
                            $classeUsuarios->setEmailUsuarios($email);
                            $classeUsuarios->setSenhaUsuarios($senha);
    
                            echo $classeUsuarios->cadastrar($confirma_senha);

                        }else{

                            echo $classeRetornosJson->retornaErro("Você precisa informar nome, email, senha, confirmar_senha como POST");

                        }

                    }else{

                        echo $classeRetornosJson->retornaErro("Nessa rota você precisa informar nome, email, senha, confirmar_senha como POST");

                    }

                break;

                default:

                    echo $classeRetornosJson->retornaErro("Usuário não existe ou rota incorreta");

            }

        }

    }else{

        echo $classeRetornosJson->retornaErro("Dados de verificação não conferem, ou existe algum erro interno");

    }

}else{

    echo $classeRetornosJson->retornaErro("API não localizada.");

}