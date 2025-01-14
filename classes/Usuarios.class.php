<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "./vendor/phpmailer/phpmailer/src/Exception.php";
require "./vendor/phpmailer/phpmailer/src/PHPMailer.php";
require "./vendor/phpmailer/phpmailer/src/SMTP.php";

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
    public $data_cadastro;

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

                        "INSERT INTO usuarios (nome, email, senha, token, foto_url, confirmado, data_cadastro)
                        VALUES (?, ?, ?, ?, ?, ?, ?)"
        
                    );
        
                    if($conexao === false){
        
                        throw new Exception("Erro de conexão: ".$this->conn->error);
        
                    }

                    $this->foto_url = NULL;
                    $this->confirmado = $this->gerar_codigo();
                    $this->data_cadastro = date('Y-m-d');
        
                    $conexao->bind_param("sssssis", $this->nome, $this->email, $this->senha, $this->token, $this->foto_url, $this->confirmado, $this->data_cadastro);
        
                    if(!$conexao->execute()){
        
                        throw new Exception("Erro de execução: ".$conexao->error);
        
                    }

                    $this->mandar_email("Apenas um teste para confirmar recebimento", "Teste email");

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

    public function retorna_dados_perfil(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT nome, email, foto_url, data_cadastro FROM usuarios
                WHERE id=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("i", $this->id);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();

            while($result = $sql->fetch_assoc()){

                $array[] = $result;

            }

            /* Inserindo e formatando os campos data e hora */
            foreach($array as &$navegacao){

                $data_time = $navegacao["data_cadastro"];

                $dia = substr($data_time, 8, 2);
                $mes = substr($data_time, 5, 2);
                $ano = substr($data_time, 0, 4);

                $nova_data = $dia."/".$mes."/".$ano;

                $navegacao["data_cadastro"] = $nova_data;
            }

            return $this->retorna_json->retorna_json($array);
            
        } catch (Exception $e) {

            error_log("Classe Usuarios - Métodos: retorna_dados_perfil - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    public function upload_img($diretorio, $img, $servidor) {
        // Caminho completo para o arquivo
        $uploadFile = $diretorio . basename($img["name"]);
    
        // Verifica se o diretório existe, se não, cria o diretório
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0777, true);
        }

        $img_info = getimagesize($img['tmp_name']);
        $extensao = pathinfo($img['name'], PATHINFO_EXTENSION);
        $novo_nome = $this->id."-".date("dmYHis"); // O nome da imagem é o ID do usuário

        if($img_info["mime"] == "image/jpeg" || $img_info["mime"] == "image/png"){

            if($img['size'] < 5242880){ // 5MB

                $verificacao_url_atual = $this->retorna_url_img_atual();

                if($verificacao_url_atual != false){

                    if (file_exists($verificacao_url_atual)) {
                        unlink($verificacao_url_atual); // Exclui o arquivo antigo
                    }

                }

                if($extensao == "jpeg"){

                    $extensao = "jpg";

                }

                if (move_uploaded_file($img['tmp_name'], $diretorio.$novo_nome.".".$extensao)) {

                    $imageUrl = $servidor . '/' . $diretorio . basename($img["name"]);

                    $this->atualizar_url_imagem($diretorio.$novo_nome.".".$extensao);
            
                    echo json_encode([
                        'success' => true,
                        'message' => 'Arquivo enviado com sucesso!',
                        'file' => $imageUrl
                    ]);
                } else {
                    
                    echo json_encode([
                        'success' => false,
                        'message' => 'Erro ao enviar o arquivo.'
                    ]);
                }

            }else{

                echo json_encode([
                    'success' => false,
                    'message' => 'Tamanho excedido! Por favor, selecione uma imagem com no máximo 5mb.'
                ]);

            }

        }else{

            echo json_encode([
                'success' => false,
                'message' => 'Formato inválido! Por favor insira uma imagem no formato JPG ou PNG.'
            ]);

        }
            
    }

    public function atualizar_url_imagem($url){

        try {

            $conexao = $this->conn->prepare(

                "UPDATE usuarios
                SET foto_url=?
                WHERE id=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("si", $url, $this->id);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }
            
        } catch (Exception $e) {

            error_log("Classe Usuarios - Métodos: atualizar_url_imagem - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    private function retorna_url_img_atual(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT foto_url FROM usuarios
                WHERE id=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("i", $this->id);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();

            $resultado = $sql->fetch_assoc();

            $url = $resultado["foto_url"];

            if($url == false){

                return false;

            }else{

                return $url;

            }
            
        } catch (Exception $e) {

            error_log("Classe Usuarios - Métodos: retorna_url_img_atual - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    public function remover_img(){

        try {

            $verificacao_url_atual = $this->retorna_url_img_atual();

            if (file_exists($verificacao_url_atual)) {
                unlink($verificacao_url_atual); // Exclui o arquivo antigo
            }

            $this->atualizar_url_imagem(null);

            return $this->retorna_json->retorna_json(null);
            
        } catch (Exception $e) {

            error_log("Classe Usuarios - Métodos: remover_img - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    /* Altera Nome de usuário */
    public function alterar_dados(){

        try {

            $conexao = $this->conn->prepare(

                "UPDATE usuarios
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

            return $this->retorna_json->retorna_json(null);
            
        } catch (Exception $e) {

            error_log("Classe Usuarios - Métodos: alterar_dados - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    public function mandar_email($texto, $assunto){

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'mybuy.erickmota.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'contato@mybuy.erickmota.com';
        $mail->Password = 'AqTrioFut2626';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->CharSet = 'UTF-8';

        $mail->setFrom('contato@mybuy.erickmota.com', 'My Buy');
        $mail->addAddress($this->email, 'Erick Mota');

        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $texto;

        $mail->send();

    }

    /* Gera o código de confirmação para o email do usuário */
    private function gerar_codigo(){

        $numero = rand(1, 9999);

        $numeroFormatado = str_pad($numero, 4, '0', STR_PAD_LEFT);

        return $numeroFormatado;

    }
    
}

?>