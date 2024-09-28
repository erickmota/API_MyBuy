<?php

class Categorias{

    private $conn;
    private $retorna_json;
    public $id;
    public $nome;
    public $id_usuarios;

    public function __construct($classeRetornosJson, $classeConexao){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;
    }

    public function setIdUsuarios($id_usuarios){

        $this->id_usuarios = $id_usuarios;

    }

    /* Retorna todas as categorias que um usuário tem na conta */
    public function retorna_categoria(){

        $conn = $this->conn;

        $sql = mysqli_query(
            
            $conn, "SELECT * FROM categorias
            WHERE id_usuarios='$this->id_usuarios'"
        
        ) or die("Erro BD");
        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        /* array_unshift($array, [

            "id"=>null,
            "nome"=>"Sem categoria",
            "id_usuarios"=>null

        ]); */

        if($qtd < 1){

            return $this->retorna_json->retornaErro("Nenhuma categoria disponível para essa lista");
        

        }else{

            return $this->retorna_json->retorna_json($array);

        }

    }

}

?>