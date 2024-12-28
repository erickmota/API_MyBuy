<?php

class ConfiguracoesUser{

    public $id;
    public $id_usuarios;
    public $id_ultima_lista;

    public function __construct($classeRetornosJson, $classeConexao){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;

    }

    public function setIdUsuarios($id_usuarios){

        $this->id_usuarios = $id_usuarios;

    }

    public function setIdUltimaLista($id_ultima_lista){

        $this->id_ultima_lista = $id_ultima_lista;

    }

    public function cria_configuracoes(){

        try {

            $conexao = $this->conn->prepare(

                "INSERT INTO configuracoes_user
                (id_usuarios, id_ultima_lista)
                VALUES (?, ?)"

            );

            if($conexao === false){
        
                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("ii", $this->id_usuarios, $this->id_ultima_lista);
        
            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }
            
        } catch (Exception $e) {

            error_log("Classe ConfiguracoesUser - Métodos: criar_configuracoes - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

}

?>