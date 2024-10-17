<?php

class Compras{

    private $conn;
    private $retorna_json;
    private $class_mercado;
    private $class_produtos_compra;
    private $class_usuarios_listas;

    public $id;
    public $data;
    public $id_mercados;
    public $id_usuarios;

    public function __construct($class_usuarios_listas, $class_produtos_compra, $class_mercado, $classeRetornosJson, $classeConexao){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;
        $this->class_mercado = $class_mercado;
        $this->class_produtos_compra = $class_produtos_compra;
        $this->class_usuarios_listas = $class_usuarios_listas;
    }

    public function setData($data){

        $this->data = $data;

    }

    public function setIdMercados($id_mercados){

        $this->id_mercados = $id_mercados;

    }

    public function setIdUsuarios($id_usuarios){

        $this->id_usuarios = $id_usuarios;

    }

    /* Cadastra a compra */
    function cadastra_compra($id_lista, $nome_mercado){

        try {

            $this->class_usuarios_listas->setIdListas($id_lista);
            $this->class_usuarios_listas->setIdUsuarios($this->id_usuarios);
            $verifica_usuarios_listas = $this->class_usuarios_listas->verifica_usuario_lista();

            if($verifica_usuarios_listas == false){

                throw new Exception("Usuário não tem acesso a lista definida");

            }

            $this->class_mercado->setNome($nome_mercado);
            $this->class_mercado->setIdUsuarios($this->id_usuarios);

            $verifica_mercado = $this->class_mercado->verifica_nome_mercado();

            /* Verificando se o nome do mercado, passado pelo usuário, já existe, ou se
            será preciso criar um novo registro. */
            if($verifica_mercado == false){

                $this->id_mercados = $this->class_mercado->cadastra_mercado();

            }else{

                $this->id_mercados = $verifica_mercado;

            }

            $conexao = $this->conn->prepare(

                "INSERT INTO compras (data, id_usuarios, id_mercados)
                VALUES (?, ?, ?)"

            );

            if($conexao === false){

                throw new Exception("Erro na conexão: ".$this->conn->error);

            }

            $conexao->bind_param("sii", $this->data, $this->id_usuarios, $this->id_mercados);

            if(!$conexao->execute()){

                throw new Exception("Erro na execução: ".$conexao->error);

            }

            $id_compra = $this->conn->insert_id;

            $this->class_produtos_compra->setIdCompras($id_compra);

            $this->class_produtos_compra->cadastrar_produtos($id_lista);

            return $this->retorna_json->retorna_json(null);
            
        } catch (Exception $e) {

            error_log("Classe Compras - Métodos: cadastra_compra - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

}

?>