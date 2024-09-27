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

        $result = [

            "Dono"=>$obj_dono,
            "Membros"=>$array_membros

        ];

        return $this->retorna_json->retorna_json($result);

    }

}

?>