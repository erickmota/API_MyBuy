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

            /* Exemplo de rota: API/id_usuario/listas */
            switch($explode[1]){

                /* Retorna todas as listas do usuário */
                case "listas":
    
                    echo $classeRetorno->retorna_listas();
    
                break;
    
                case "produtos":
    
                    /* Em desenvolvimento */
                    echo $classeRetorno->retornaErro("Rota em desenvolvimento.");
    
                break;

                default:

                    echo $classeRetorno->retornaErro("A rota definida não existe");

            }

        }else{

            echo $classeRetorno->retornaErro("Usuário não existe");

        }

    }else{

        echo $classeRetorno->retornaErro("Dados de verificação não conferem, ou existe algum erro interno");

    }

}else{

    echo $classeRetorno->retornaErro("API não localizada.");

}