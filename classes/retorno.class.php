<?php

class Retornos{

    public function retornarAll($tabela){

        include 'conexao.class.php';

        $sql = mysqli_query($conn, "SELECT * FROM $tabela") or die("Erro mostrar");
        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        if($qtd < 1){

            return json_encode([

                "status" => API_IS_ACTIVE,
                "Versao" => API_VERSION,
                "msg" => "Sucesso",
                "data" => false
        
            ]);
        

        }else{

            return json_encode([

                "status" => API_IS_ACTIVE,
                "Versao" => API_VERSION,
                "msg" => "Sucesso",
                "data" => $array
        
            ]);

        }

    }

    public function retornaDado($tabela, $coluna, $dado){

        include 'conexao.class.php';

        $sql = mysqli_query($conn, "SELECT * FROM $tabela WHERE $coluna='$dado'") or die ("Erro BD");
        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        if($qtd < 1){

            return false;
        

        }else{

            return json_encode([

                "status" => API_IS_ACTIVE,
                "Versao" => API_VERSION,
                "msg" => "Sucesso",
                "data" => $array
        
            ]);

        }

    }

}