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
    if(API_IS_ACTIVE == true && isset($headers["API_TOKEN"]) && $headers['API_TOKEN'] == API_TOKEN){

        switch($explode[0]){

            case "produtos";

                echo $classeRetorno->retornarAll("produtos");

            break;

        }

    }else{

        echo json_encode([
    
            "status" => API_IS_ACTIVE,
            "Versao" => API_VERSION,
            "msg" => "Dados de verificação não conferem, ou existe algum erro interno",
            "data" => false
    
        ]);

    }

}else{

    echo json_encode([
    
        "status" => API_IS_ACTIVE,
        "Versao" => API_VERSION,
        "msg" => "URL da API não existente",
        "data" => false

    ]);

}