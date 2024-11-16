<?php

class UsuariosListas extends Usuarios{

    private $conn;
    private $retorna_json;
    private $classeListas;
    private $class_historico;

    public $id;
    public $id_usuarios;
    public $id_listas;

    public function __construct($classeListas, $classeRetornosJson, $classeConexao, $class_historico){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;
        $this->classeListas = $classeListas;
        parent::__construct($classeRetornosJson, $classeConexao);
        $this->class_historico = $class_historico;

    }

    public function setIdListas($id_listas){

        $this->id_listas = $id_listas;

    }

    public function setIdUsuariosLista($id_usuarios){

        $this->id_usuarios = $id_usuarios;

    }

    /* Verifica se um usuário existe na lista de membros de uma
    determinada lista. */

    public function verifica_usuario_lista(){

        $conn = $this->conn;

        $this->id_usuarios = $this->getIdUsuarios();

        $sql = mysqli_query(

            $conn,
            "SELECT id FROM usuarios_listas
            WHERE id_usuarios='$this->id_usuarios'
            AND id_listas='$this->id_listas'"

        ) or die("Erro conexão");

        $qtd = mysqli_num_rows($sql);

        if($qtd > 0){

            return true;

        }else{

            return false;

        }

    }

    /* Verifica se o usuário é o dono da lista.
    Se a o id do usuário atual, for o mesmo que o id
    do dono da lista, a função retorna true */

    public function verifica_titularidade_lista($id_dono){

        $conn = $this->conn;
        $id_usuario_atual = $this->getIdUsuarios();

        if($id_dono == $id_usuario_atual){

            return true;

        }else{

            return false;

        }

    }

    /* Retorna os membros de uma determinada lista */

    public function retorna_membros_lista(){

        $conn = $this->conn;

        $id_dono = $this->classeListas->retorna_dono_lista();

        /* Verificando dados do dono */

        $sql_dono = mysqli_query(

            $conn,
            "SELECT id, nome, foto_url FROM usuarios
            WHERE id='$id_dono'"

        ) or die("Erro conexão");

        /* Verificando os dados dos membros da lista */

        $sql_membros = mysqli_query(

            $conn,
            "SELECT usuarios.id, usuarios.nome, usuarios.foto_url FROM usuarios
            INNER JOIN usuarios_listas ON usuarios_listas.id_usuarios=usuarios.id
            WHERE usuarios_listas.id_listas='$this->id_listas'
            AND usuarios_listas.id_usuarios!='$id_dono'"

        ) or die("Erro conexão");

        $array_membros = [];

        while ($row_dono = mysqli_fetch_assoc($sql_dono)){
                
            $obj_dono = $row_dono;
            
        }

        while ($row_membros = mysqli_fetch_assoc($sql_membros)){
                
            $array_membros[] = $row_membros;
            
        }

        $confirmacoes = [];

        $confirmacoes = [

            /* Incluindo a informação se o usuário atual é ou
            não dono da lista */
            
            "dono_lista"=>$this->verifica_titularidade_lista($id_dono)

        ];

        $result = [

            "Confirmacoes"=>$confirmacoes,
            "Dono"=>$obj_dono,
            "Membros"=>$array_membros

        ];

        return $this->retorna_json->retorna_json($result);

    }

    /* Retorna o ID do usuário via email. */
    private function retorna_id_usario_via_email($email_usuario){

        $conn = $this->conn;

        $sql = mysqli_query(

            $conn,
            "SELECT id, nome FROM usuarios
            WHERE email='$email_usuario'"

        ) or die("Erro conexão");

        $result = mysqli_fetch_assoc($sql);

        return [$result["id"], $result["nome"]];

    }

    /* Adiciona um novo usuário a uma lista. */

    public function adiciona_usuario_lista($email_usuario, $id_lista){

        $conn = $this->conn;

        $inf_novo_usuario = $this->retorna_id_usario_via_email($email_usuario);
        $id_novo_usuario = $inf_novo_usuario[0];

        $sql = mysqli_query(

            $conn,
            "INSERT INTO usuarios_listas (id_usuarios, id_listas)
            VALUES ('$id_novo_usuario', '$id_lista')"

        ) or die("Erro conexão");

        /* Histórico */

        $this->class_historico->setData("today");
        $this->class_historico->setTipo(6);
        $this->class_historico->setMsg("adicionou o usuario: '".$inf_novo_usuario[1]."' à lista");
        $this->class_historico->setIdListas(intval($id_lista));
        $this->class_historico->setIdCompras(false);
        $this->class_historico->setIdUsuarios(intval($this->getIdUsuarios()));

        $this->class_historico->incluir_historico();

        return $this->retorna_json->retorna_json(false);

    }

    /* Método é chamado, quando o dono remove um usuário da lista, ou
    quando um usuário sair da lista compartilhada. */

    public function remover_usuario_lista(){

        $conn = $this->conn;

        $sql = mysqli_query(

            $conn,
            "DELETE FROM usuarios_listas
            WHERE id_usuarios='$this->id_usuarios'
            AND id_listas='$this->id_listas'"

        ) or die("Erro conexão");

        /* Histórico */

        $this->class_historico->setData("today");
        $this->class_historico->setTipo(7);
        
        if($this->id_usuarios == $this->getIdUsuarios()){

            $this->class_historico->setMsg("saiu da lista");
            $this->class_historico->setIdUsuarios(intval($this->getIdUsuarios()));

        }else{

            $this->class_historico->setMsg("foi removido(a) da lista");
            $this->class_historico->setIdUsuarios(intval($this->id_usuarios));

        }

        $this->class_historico->setIdListas(intval($this->id_listas));
        $this->class_historico->setIdCompras(false);

        $this->class_historico->incluir_historico();

        return $this->retorna_json->retorna_json(false);

    }

}

?>