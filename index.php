<?php
header('Content-Type: application/json');

include "config.php";

/* include "classes/retorno.class.php";
$classeRetorno = new Retornos(); */

include "classes/Usuarios.class.php";
$classeUsuarios = new Usuarios();

if(isset($_GET["url"])){

    $explode = explode("/", $_GET["url"]);

    $headers = apache_request_headers();

    /* Verificando se a API está ativada, e se o token de verificação existe
    e é válido. */
    if(API_IS_ACTIVE == true){

        /* Id do usuário buscado */
        $classeUsuarios->id = $explode[0];

        /* Verificando a existência do usuario informado. */
        if($classeUsuarios->verifica_usuario() == true){

            echo "existe";

            if(isset($explode[1])){

                switch($explode[1]){

                    /* *** GET *** */

                    /* Retorna todas as listas do usuário */
                    /* Exemplo de rota: API/id_usuario/listas */
                    case "listas":
        
                       /*  echo $classeRetorno->retorna_listas(); */
        
                    break;
        
                    /* Retorna a lista de produtos */
                    /* Exemplo: API/id_usuario/produtos/id_listas/id_categoria */
                    case "produtos":                    
        
                        if(isset($explode[2])){

                            /* Verificando se o id da categoria foi passado na rota. */
                            if(isset($explode[3])){

                                /* $classeRetorno->id_lista = $explode[2]; */

                                /* echo $classeRetorno->retorna_produtos($explode[3], false); */

                            }else{

                                /* echo $classeRetorno->retornaErro("Insira um id de categoria na rota"); */

                            }

                        }else{

                            /* echo $classeRetorno->retornaErro("Insira um id de lista na rota"); */

                        }                    
        
                    break;

                    case "produtos_carrinho":

                        if(isset($explode[2])){

                            /* $classeRetorno->id_lista = $explode[2]; */

                            /* echo $classeRetorno->retorna_produtos(false, true); */

                        }else{

                            /* echo $classeRetorno->retornaErro("Insira um id de lista na rota"); */

                        }

                    break;

                    /* Retorna as categorias do usuário */
                    case "categorias":

                        /* echo $classeRetorno->retorna_categoria(); */

                    break;

                    /* *** POST *** */

                    /* Atualiza o nome da lista individualmente. */
                    case "atualiza_lista":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_lista"]) && isset($_POST["novo_nome"])){
    
                                $id_lista = $_POST["id_lista"];
                                $novo_nome = $_POST["novo_nome"];
    
                                /* echo $classeRetorno->atualiza_nome_lista($id_lista, $novo_nome); */
    
                            }else{
    
                                /* echo $classeRetorno->retornaErro("Você precisa informar o id e o novo nome da lista que quer alterar."); */
    
                            }
    
                        }else{
    
                            /* echo $classeRetorno->retornaErro("Você precisa informar o id e o novo nome da lista como POST."); */
    
                        }
    
                    break;

                    /* Adiciona uma nova lista */
                    case "adiciona_lista":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["nome_lista"])){
    
                                $nome_lista = $_POST["nome_lista"];
    
                                /* echo $classeRetorno->criar_lista($nome_lista); */
    
                            }else{
    
                                /* echo $classeRetorno->retornaErro("Você precisa inserir o nome da lista que quer inserir."); */
    
                            }
    
                        }else{
    
                            /* echo $classeRetorno->retornaErro("Você precisa inserir o nome da lista como POST."); */
    
                        }

                    break;

                    /* Deleta uma lista existente */
                    case "deletar_lista":

                        if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                            if(isset($_POST["id_lista"])){
    
                                $id_lista = $_POST["id_lista"];
    
                                /* echo $classeRetorno->deletar_lista($id_lista); */
    
                            }else{
    
                                /* echo $classeRetorno->retornaErro("Você precisa inserir o id da lista que quer deletar."); */
    
                            }
    
                        }else{
    
                           /*  echo $classeRetorno->retornaErro("Você precisa inserir o id da lista como POST."); */
    
                        }

                    break;

                    default:

                        /* echo $classeRetorno->retornaErro("A rota definida não existe"); */

                }

            }else{

               /*  echo $classeRetorno->retornaErro("Defina um destino ao usuário, na rota"); */

            }

        }else{

            echo "não existe";

            switch($explode[0]){

                case "login":

                    if($_SERVER["REQUEST_METHOD"] === "POST"){

                        if(isset($_POST["email"]) && isset($_POST["senha"])){

                            $email = $_POST["email"];
                            $senha = $_POST["senha"];
    
                            /* echo $classeRetorno->verificar_email_senha_usuario($email, $senha); */

                        }else{

                           /*  echo $classeRetorno->retornaErro("Você precisa informar um email e uma senha como POST"); */

                        }

                    }else{

                        /* echo $classeRetorno->retornaErro("Nessa rota você precisa informar o login e senha como POST"); */

                    }

                break;

                default:

                    /* echo $classeRetorno->retornaErro("Usuário não existe ou rota incorreta"); */

            }

        }

    }else{

        /* echo $classeRetorno->retornaErro("Dados de verificação não conferem, ou existe algum erro interno"); */

    }

}else{

    /* echo $classeRetorno->retornaErro("API não localizada."); */

}