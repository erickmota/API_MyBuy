<?php
header('Content-Type: application/json');

require_once "config.php";

require_once "classes/Conexao.class.php";
$classeConexao = new Conexao();

require_once "classes/RetornosJson.class.php";
$classeRetornosJson = new RetornosJson();

require_once "classes/Usuarios.class.php";
$classeUsuarios = new Usuarios($classeRetornosJson, $classeConexao);

require_once "classes/Listas.class.php";
$classeListas = new Listas($classeRetornosJson, $classeConexao);

require_once "classes/UsuariosListas.class.php";
$classeUsuariosListas = new UsuariosListas($classeListas ,$classeRetornosJson, $classeConexao);

require_once "classes/ProdutosExemplo.class.php";
$classeProdutosExemplo = new ProdutosExemplo($classeRetornosJson, $classeConexao);

require_once "classes/ProdutosUsuario.class.php";
$classeProdutosUsuario = new ProdutosUsuario($classeProdutosExemplo, $classeRetornosJson, $classeConexao);

require_once "classes/Produtos.class.php";
$classeProdutos = new Produtos($classeProdutosExemplo, $classeProdutosUsuario, $classeUsuariosListas, $classeRetornosJson, $classeConexao);

require_once "classes/Categorias.class.php";
$classeCategorias = new Categorias($classeRetornosJson, $classeConexao);

require_once "classes/ProdutosCompras.class.php";
$classeProdutosCompras = new ProdutosCompras($classeRetornosJson, $classeConexao);

require_once "classes/Mercados.class.php";
$classeMercados = new Mercados($classeRetornosJson, $classeConexao);

require_once "classes/Compras.class.php";
$classeCompras = new Compras($classeUsuariosListas, $classeProdutosCompras, $classeMercados, $classeRetornosJson, $classeConexao);

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

                    /* *** POST *** */

                    /* Atualiza o nome da lista individualmente. */
                    case "atualiza_lista":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_lista"]) && isset($_POST["novo_nome"])){
    
                                $id_lista = $_POST["id_lista"];
                                $novo_nome = $_POST["novo_nome"];

                                $classeCategorias->setIdUsuarios($explode[0]);

                                $classeListas->setIdLista($id_lista);
                                $classeListas->setNomeLista($novo_nome);
    
                                echo $classeListas->atualiza_nome_lista($id_lista, $novo_nome);
    
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

                                $classeCompras->setData(date('Y-m-d')); // Passando a data atual
                                $classeCompras->setIdUsuarios($explode[0]);
    
                                echo $classeCompras->cadastra_compra($id_lista, $nome_mercado);
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir o nome do mercado e o id da lista.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir o nome do mercado e o id da lista, como POST.");
    
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