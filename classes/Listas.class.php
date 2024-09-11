<?php

require_once "Conexao.class.php";
require_once "Usuarios.class.php";

class Listas extends Usuarios{

    private $conn;
    private $retorno_json;
    public $id;
    public $nome;

    public function __construct(){

        $this->conn = (new Conexao())->getConexao();
        /* $this->retorno_json = (new RetornosJson())->retorna_json(); */

    }

    /* Retorna todas as listas que o usuário tem disponível,
    seja criada ou compartilhada */
    public function retorna_listas(){

        $conn = $this->conn;
        $id_usuario = $this->getId();

        $sql = mysqli_query(

            $conn, "SELECT * FROM usuarios_listas
            INNER JOIN listas
            WHERE usuarios_listas.id_usuarios='$id_usuario'
            AND usuarios_listas.id_listas=listas.id"
            
        ) or die("Erro BD");
        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        /* Percorrendo o array e inserindo um novo item
        com a quantidade de produtos que existem na lista */
        foreach($array as &$navegacao){

            $id_list = $navegacao["id"];

            $sql = mysqli_query($conn, "SELECT * FROM produtos
            WHERE id_listas='$id_list'") or die("Erro na consulta do array");
            $qtd_prod = mysqli_num_rows($sql);

            $navegacao["qtd_produtos"] = $qtd_prod;

        }

        if($qtd < 1){

            /* return $this->retornaErro("Nenhuma lista disponível para esse usuário"); */

            echo "Não existe lista";
        

        }else{

            /* return $this->retorna_json($array); */

            echo "Existe lista";

        }

    }

}

?>