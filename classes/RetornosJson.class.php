<?php

class RetornosJson{

    /* Método responsável pelos erros retornados na API */
    public function retornaErro($msg){

        return json_encode([

            "status" => API_IS_ACTIVE,
            "Versao" => API_VERSION,
            "msg" => $msg,
            "data" => false

        ]);

    }

    /* Método de retorno com sucesso de json. */
    private function retorna_json($data){

        return json_encode([

            "status" => API_IS_ACTIVE,
            "Versao" => API_VERSION,
            "msg" => "Sucesso",
            "data" => $data

        ]);

    }

}

?>