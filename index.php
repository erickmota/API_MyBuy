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

require_once "classes/Produtos.class.php";
$classeProdutos = new Produtos($classeRetornosJson, $classeConexao);

require_once "classes/Categorias.class.php";
$classeCategorias = new Categorias($classeRetornosJson, $classeConexao);

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
        
                        if(isset($explode[2])){

                            /* Verificando se o id da categoria foi passado na rota. */
                            if(isset($explode[3])){

                                $classeProdutos->setIdUsuarios($explode[0]);

                                $classeProdutos->setIdLista($explode[2]);

                                $classeProdutos->setCarrinho(false);

                                echo $classeProdutos->retorna_produtos($explode[3]);

                            }else{

                                echo $classeRetornosJson->retornaErro("Insira um id de categoria na rota");

                            }

                        }else{

                            echo $classeRetornosJson->retornaErro("Insira um id de lista na rota");

                        }                    
        
                    break;

                    case "produtos_carrinho":

                        if(isset($explode[2])){

                            $classeProdutos->setIdUsuarios($explode[0]);

                            $classeProdutos->setIdLista($explode[2]);

                            $classeProdutos->setCarrinho(true);

                            echo $classeProdutos->retorna_produtos(false);

                        }else{

                            echo $classeRetornosJson->retornaErro("Insira um id de lista na rota");

                        }

                    break;

                    /* Retorna as categorias do usuário */
                    case "categorias":

                        $classeCategorias->setIdUsuarios($explode[0]);

                        echo $classeCategorias->retorna_categoria();

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
    
                            if(isset($_POST["id_produto"]) && isset($_POST["nome_produto"]) && isset($_POST["qtd_produto"])){
    
                                $id_produto = $_POST["id_produto"];
                                $nome_produto = $_POST["nome_produto"];
                                $qtd_produto = $_POST["qtd_produto"];

                                $classeProdutos->setIdUsuarios($explode[0]);

                                $classeProdutos->setIdProduto($id_produto);
                                $classeProdutos->setQtd($qtd_produto);
                                $classeProdutos->setNome($nome_produto);
    
                                echo $classeProdutos->editar_produto();
    
                            }else{
    
                                echo $classeRetornosJson->retornaErro("Você precisa inserir os dados do produto que quer editar.");
    
                            }
    
                        }else{
    
                            echo $classeRetornosJson->retornaErro("Você precisa inserir os dados do produto como POST.");
    
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