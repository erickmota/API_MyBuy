<?php

class ProdutosExemplo{

    private $conn;
    private $retorna_json;
    
    public $id;
    public $nome;
    public $tipo_exibicao;
    public $id_fotos;

    public function __construct($classeRetornosJson, $classeConexao){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;

    }

    public function setId($id){

        $this->id = $id;

    }

    public function setNome($nome){

        $this->nome = $nome;

    }

    public function busca_produto(){

        $conn = $this->conn;

        $sql = mysqli_query(

            $conn,
            "SELECT produtos_exemplo.id, produtos_exemplo.nome, produtos_exemplo.tipo_exibicao, fotos.id AS id_foto, fotos.url FROM produtos_exemplo
            LEFT JOIN fotos ON fotos.id=produtos_exemplo.id_fotos"

        ) or die("Erro conexão");

        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        if($qtd < 1){

            return $this->retorna_json->retornaErro("Nenhuma produto corresponde a pesquisa");
        

        }else{

            return $array;

        }

    }

    public function verifica_existencia_nome(){

        $consulta = $this->conn->prepare(
            
            "SELECT id FROM produtos_exemplo
            WHERE nome=?"
        
        );
        $consulta->bind_param("s", $this->nome);
        $consulta->execute();

        $sql = $consulta->get_result();

        $qtd = $sql->num_rows;

        if($qtd > 0){

            $result = $sql->fetch_assoc();

            return $result["id"];

        }else{

            return false;

        }


    }

    public function retorna_foto_exemplo(){

        $consulta = $this->conn->prepare(

            "SELECT fotos.id FROM fotos
            INNER JOIN produtos_exemplo ON produtos_exemplo.id_fotos=fotos.id
            WHERE produtos_exemplo.id=?"

        );
        $consulta->bind_param("i", $this->id);
        $consulta->execute();

        $sql = $consulta->get_result();

        $resultado = $sql->fetch_assoc();

        $id_foto = $resultado["id"];

        return $id_foto;

    }

}

?>