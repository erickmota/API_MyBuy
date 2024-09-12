<?php

class Listas extends Usuarios{

    private $conn;
    private $retorna_json;
    public $id;
    public $nome;

    public function __construct($classeRetornosJson, $classeConexao){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;
        parent::__construct($classeConexao);

    }

    public function setIdLista($id_lista){

        $this->id = $id_lista;

    }

    public function setNomeLista($novo_nome){

        $this->nome = $novo_nome;

    }

    /* Retorna todas as listas que o usuário tem disponível,
    seja criada ou compartilhada */
    public function retorna_listas(){

        $conn = $this->conn;
        $id_usuario = $this->getIdUsuarios();

        $sql = mysqli_query(

            $conn, "SELECT * FROM usuarios_listas
            INNER JOIN listas
            WHERE usuarios_listas.id_usuarios='$id_usuario'
            AND usuarios_listas.id_listas=listas.id"
            
        ) or die("Erro BD");
        $qtd = mysqli_num_rows($sql);

        $array = [];

        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        if($qtd < 1){

            return $this->retorna_json->retornaErro("Nenhuma lista disponível para esse usuário");

        }else{

            /* Percorrendo o array e inserindo um novo item
            com a quantidade de produtos que existem na lista */
            foreach($array as &$navegacao){

                $id_list = $navegacao["id"];

                $sql = mysqli_query($conn, "SELECT * FROM produtos
                WHERE id_listas='$id_list'") or die("Erro na consulta do array");
                $qtd_prod = mysqli_num_rows($sql);

                $navegacao["qtd_produtos"] = $qtd_prod;

            }

            return $this->retorna_json->retorna_json($array);

        }

    }

    /* Atualiza o nome de uma lista */
    public function atualiza_nome_lista(){

        $conn = $this->conn;

        $sql = mysqli_query(
            
            $conn, "UPDATE listas
            SET nome='$this->nome'
            WHERE id='$this->id'"
            
        ) or die("Erro conexão");

        return $this->retorna_json->retorna_json(false);

    }

    /* Cria uma nova lista */
    public function criar_lista(){

        $conn = $this->conn;
        $id_usuario = $this->getIdUsuarios();

        $sql = mysqli_query(

            $conn, "INSERT INTO listas (nome)
            VALUES ('$this->nome')"

        ) or die("Erro conexão");

        $ultimo_id = mysqli_insert_id($conn);

        $sql2 = mysqli_query(

            $conn, "INSERT INTO usuarios_listas (id_usuarios, id_listas)
            VALUES ('$id_usuario', '$ultimo_id')"

        ) or die("Erro conexão");

        return $this->retorna_json->retorna_json(false);

    }

    /* Deleta uma lista */
    public function deletar_lista(){

        /* Como os produtos e a tabela usuarios_produtos foram criados com o
        ON DELETE CASCADE, produtos e dados na tabela acima, serão exluídos
        com esse msm método. */

        $conn = $this->conn;

        $sql = mysqli_query(

            $conn, "DELETE FROM listas WHERE id='$this->id'"

        ) or die("Erro conexão");

        return $this->retorna_json->retorna_json(false);

    }

}

?>