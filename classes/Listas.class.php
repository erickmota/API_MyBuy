<?php

class Listas extends Usuarios{

    private $conn;
    private $retorna_json;
    private $class_historico;
    private $classe_categoria;

    public $id;
    public $nome;
    public $id_usuarios_dono;

    public function __construct($classeRetornosJson, $classeConexao, $class_historico, $classe_categoria){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;
        parent::__construct($classeRetornosJson, $classeConexao, $classe_categoria);
        $this->class_historico = $class_historico;

    }

    public function setIdLista($id_lista){

        $this->id = $id_lista;

    }

    public function setNomeLista($novo_nome){

        $this->nome = $novo_nome;

    }

    public function setIdUsuariosDono($id_usuarios_dono){

        $this->id_usuarios_dono = $id_usuarios_dono;

    }

    /* Verificar a quantidade de usuários que tem em uma lista */

    private function verificar_qtd_usuarios_lista($id_lista){

        $conn = $this->conn;

        $sql = mysqli_query(

            $conn,
            "SELECT * FROM usuarios_listas
            WHERE id_listas='$id_lista'"

        ) or die("Erro conexão");

        $qtd = mysqli_num_rows($sql);

        return $qtd;

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

            /* Percorrendo o array e inserindo um novo item
            com a quantidade de produtos que existem na lista */
            foreach($array as &$qtdUsers){

                $id_list = $qtdUsers["id"];

                $qtdUsers["qtd_usuarios"] = $this->verificar_qtd_usuarios_lista($id_list);

            }

            return $this->retorna_json->retorna_json($array);

        }

    }

    /* Atualiza o nome de uma lista */
    public function atualiza_nome_lista($id_usuarios){

        try {

            $conexao = $this->conn->prepare(

                "UPDATE listas
                SET nome=?
                WHERE id=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("si", $this->nome, $this->id);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            /* Histórico */

            $this->class_historico->setData("today");
            $this->class_historico->setTipo(2);
            $this->class_historico->setMsg("alterou o nome da lista para: ".$this->nome);
            $this->class_historico->setIdListas(intval($this->id));
            $this->class_historico->setIdCompras(false);
            $this->class_historico->setIdUsuarios(intval($id_usuarios));

            $this->class_historico->incluir_historico();

            return $this->retorna_json->retorna_json(false);
            
        } catch (Exception $e) {

            error_log("Classe Listas - Métodos: atualiza_nome_lista - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    private function incluir_usuario_lista($id_usuario, $ultimo_id){

        try {

            $conexao = $this->conn->prepare(

                "INSERT INTO usuarios_listas (id_usuarios, id_listas)
                VALUES (?, ?)"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("ii", $id_usuario, $ultimo_id);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }
            
        } catch (Exception $e) {

            error_log("Classe Listas - Métodos: incluir_usuario_lista - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    /* Cria uma nova lista */
    public function criar_lista(){

        try {

            $id_usuario = $this->getIdUsuarios();

            $conexao = $this->conn->prepare(

                "INSERT INTO listas (nome, id_usuarios_dono)
                VALUES (?, ?)"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("si", $this->nome, $id_usuario);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            $ultimo_id = mysqli_insert_id($this->conn);

            $this->incluir_usuario_lista($id_usuario, $ultimo_id);

            /* Histórico */

            $this->class_historico->setData("today");
            $this->class_historico->setTipo(1);
            $this->class_historico->setMsg("criou a lista");
            $this->class_historico->setIdListas($ultimo_id);
            $this->class_historico->setIdCompras(false);
            $this->class_historico->setIdUsuarios(intval($this->getIdUsuarios()));

            $this->class_historico->incluir_historico();

            return $this->retorna_json->retorna_json(false);
            
        } catch (Exception $e) {

            error_log("Classe Listas - Métodos: criar_lista - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

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

    /* Retorna o id do dono da lista */
    public function retorna_dono_lista(){

        $conn = $this->conn;

        $sql = mysqli_query(

            $conn,
            "SELECT usuarios.id FROM usuarios
            INNER JOIN listas ON listas.id_usuarios_dono=usuarios.id
            WHERE listas.id='$this->id'"

        ) or die("Erro conexão");

        $result = mysqli_fetch_assoc($sql);

        return $result["id"];

    }

}

?>