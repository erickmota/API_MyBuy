<?php
header('Content-Type: application/json');

include "config.php";

include "classes/retorno.class.php";
$classeRetorno = new Retornos();

if(isset($_GET["url"])){

    $explode = explode("/", $_GET["url"]);

    $headers = apache_request_headers();

    /* Verificando se a API está ativada, e se o token de verificação existe
    e é válido. */
    if(API_IS_ACTIVE == true){

        /* Id do usuário buscado */
        $classeRetorno->id_usuario = $explode[0];

        /* Verificando a existência do usuario informado. */
        if($classeRetorno->verifica_usuario() == true){

            if(isset($explode[1])){

                switch($explode[1]){

                    /* Retorna todas as listas do usuário */
                    /* Exemplo de rota: API/id_usuario/listas */
                    case "listas":
        
                        echo $classeRetorno->retorna_listas();
        
                    break;
        
                    /* Retorna a lista de produtos */
                    /* Exemplo: API/id_usuario/produtos/id_listas/id_categoria */
                    case "produtos":                    
        
                        if(isset($explode[2])){

                            /* Verificando se o id da categoria foi passado na rota. */
                            if(isset($explode[3])){

                                $classeRetorno->id_lista = $explode[2];

                                echo $classeRetorno->retorna_produtos($explode[3], false);

                            }else{

                                echo $classeRetorno->retornaErro("Insira um id de categoria na rota");

                            }

                        }else{

                            echo $classeRetorno->retornaErro("Insira um id de lista na rota");

                        }                    
        
                    break;

                    case "produtos_carrinho":

                        if(isset($explode[2])){

                            $classeRetorno->id_lista = $explode[2];

                            echo $classeRetorno->retorna_produtos(false, true);

                        }else{

                            echo $classeRetorno->retornaErro("Insira um id de lista na rota");

                        }

                    break;

                    /* Retorna as categorias do usuário */
                    case "categorias":

                        echo $classeRetorno->retorna_categoria();

                    break;

                    default:

                        echo $classeRetorno->retornaErro("A rota definida não existe");

                }

            }else{

                echo $classeRetorno->retornaErro("Defina um destino ao usuário, na rota");

            }

        }else{

            switch($explode[0]){

                case "login":

                    if($_SERVER["REQUEST_METHOD"] === "POST"){

                        if(isset($_POST["email"]) && isset($_POST["senha"])){

                            $email = $_POST["email"];
                            $senha = $_POST["senha"];
    
                            echo $classeRetorno->verificar_email_senha_usuario($email, $senha);

                        }else{

                            echo $classeRetorno->retornaErro("Você precisa informar um email e uma senha como POST");

                        }

                    }else{

                        echo $classeRetorno->retornaErro("Nessa rota você precisa informar o login e senha como POST");

                    }

                break;

                default:

                    echo $classeRetorno->retornaErro("Usuário não existe ou rota incorreta");

            }

        }

    }else{

        echo $classeRetorno->retornaErro("Dados de verificação não conferem, ou existe algum erro interno");

    }

}else{

    echo $classeRetorno->retornaErro("API não localizada.");

}