<?php

class UsuariosListas extends Usuarios{

    private $conn;
    private $retorna_json;
    private $classeListas;
    public $id;
    public $id_usuarios;
    public $id_listas;

    public function __construct($classeListas, $classeRetornosJson, $classeConexao){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;
        $this->classeListas = $classeListas;
        parent::__construct($classeRetornosJson, $classeConexao);

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

    /* private function envia_email($texto, $assunto, $email_origem, $email_destino){

        $corpo = $texto;

        $header = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";
        $header .= "From: $email_origem\r\n";

        mail ($email_destino,  $assunto, $corpo, $header);

    } */

    /* Retorna o ID do usuário via email. */

    private function retorna_id_usario_via_email($email_usuario){

        $conn = $this->conn;

        $sql = mysqli_query(

            $conn,
            "SELECT id FROM usuarios
            WHERE email='$email_usuario'"

        ) or die("Erro conexão");

        $result = mysqli_fetch_assoc($sql);

        return $result["id"];

    }

    /* Adiciona um novo usuário a uma lista. */

    public function adiciona_usuario_lista($email_usuario, $id_lista){

        $conn = $this->conn;

        $id_novo_usuario = $this->retorna_id_usario_via_email($email_usuario);

        $sql = mysqli_query(

            $conn,
            "INSERT INTO usuarios_listas (id_usuarios, id_listas)
            VALUES ('$id_novo_usuario', '$id_lista')"

        ) or die("Erro conexão");

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

        return $this->retorna_json->retorna_json(false);

    }

}

?>