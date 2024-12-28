<?php

class Usuarios{

    private $conn;
    private $retorna_json;
    private $classe_categoria;
    private $classe_configuracoes_user;

    public $id;
    public $nome;
    public $email;
    public $senha;
    public $token;
    public $foto_url;
    public $confirmado;

    public function __construct($classeRetornosJson, $classeConexao, $classe_categoria, $classe_configuracoes_user){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;
        $this->classe_categoria = $classe_categoria;
        $this->classe_configuracoes_user = $classe_configuracoes_user;

    }

    protected function getIdUsuarios(){

        return $this->id;

    }

    public function setIdUsuarios($id){

        $this->id = $id;

    }

    protected function getEmailUsuarios(){

        return $this->email;

    }

    public function setNome($nome){

        if(empty($nome)){

            die ($this->retorna_json->retornaErro("Nome vazio"));

        }else{

            $this->nome = $nome;

        }

    }

    public function setEmailUsuarios($email){

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $this->email = $email;

        } else {

            die ($this->retorna_json->retornaErro("Formato de e-mail inválido!"));

        }

    }

    protected function getSenhaUsuarios(){

        return $this->senha;

    }

    public function setSenhaUsuarios($senha){

        if(empty($senha)){

            die ($this->retorna_json->retornaErro("Senha Vazia"));

        }else{

            $this->senha = $senha;

        }

    }

    /* Verifica se o id do usuário passado na URL, é válido */
    public function verifica_usuario(){

        $conn = $this->conn;

        $sql = mysqli_query(
            
            $conn, "SELECT * FROM usuarios
            WHERE id='$this->id'"
        
        ) or die("Erro BD");
        $num = mysqli_num_rows($sql);

        if($num > 0){

            return true;

        }else{

            return false;

        }

    }

    /* Método de verificação do login de um usuário. */
    public function login(){

        $conn = $this->conn;

        $sql = mysqli_query(
            
            $conn, "SELECT id, nome, token, foto_url FROM usuarios
            WHERE email='$this->email'
            AND senha='$this->senha'"
        
        ) or die("Erro ao verificar o usuário");
        $qtd = mysqli_num_rows($sql);
        while ($row = mysqli_fetch_assoc($sql)){
                
            $array[] = $row;
            
        }

        if($qtd > 0){

            return $this->retorna_json->retorna_json($array);

        }else{

            return $this->retorna_json->retornaErro("Usuário não localizado");

        }

    }

    /* Verifica se o email de cadastro já existe no banco */
    public function verifica_email(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT * FROM usuarios
                WHERE email=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("s", $this->email);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();

            $qtd = $sql->num_rows;

            if($qtd > 0){

                return true;

            }else{

                return false;

            }
            
        } catch (Exception $e) {

            error_log("Classe Usuarios - Métodos: verifica_email - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    public function cadastrar($confirma_senha){

        try {

            if($this->senha != $confirma_senha){

                return $this->retorna_json->retornaErro("Senha e confirmacão de senha, nao conferem!");

            }else{

                if($this->verifica_email() == true){

                    return $this->retorna_json->retornaErro("Já existe um cadastro com esse e-mail!");

                }else{

                    $conexao = $this->conn->prepare(

                        "INSERT INTO usuarios (nome, email, senha, token, foto_url, confirmado)
                        VALUES (?, ?, ?, ?, ?, ?)"
        
                    );
        
                    if($conexao === false){
        
                        throw new Exception("Erro de conexão: ".$this->conn->error);
        
                    }

                    $this->foto_url = NULL;
                    $this->confirmado = 0;
        
                    $conexao->bind_param("sssssi", $this->nome, $this->email, $this->senha, $this->token, $this->foto_url, $this->confirmado);
        
                    if(!$conexao->execute()){
        
                        throw new Exception("Erro de execução: ".$conexao->error);
        
                    }

                    $ultimo_id = $conexao->insert_id;

                    /* Categoria */

                    $this->classe_categoria->setNome("Padrão");

                    $this->classe_categoria->setIdUsuarios($ultimo_id); // ***

                    $this->classe_categoria->add_categoria();

                    /* Configurações */

                    $this->classe_configuracoes_user->setIdUsuarios($ultimo_id);
                    $this->classe_configuracoes_user->setIdUltimaLista(NULL);

                    $this->classe_configuracoes_user->cria_configuracoes();
        
                    return $this->retorna_json->retorna_json(null);

                }

            }
            
        } catch (Exception $e) {

            error_log("Classe Usuarios - Métodos: cadastrar - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

}

?>