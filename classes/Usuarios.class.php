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
    public $codigo_confirmacao;
    public $expiracao_codigo;
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

    public function setCodigoConfirmacao($codigo_confirmacao){

        if(empty($codigo_confirmacao)){

            die ($this->retorna_json->retornaErro("Código vazio"));

        }else{

            $this->codigo_confirmacao = $codigo_confirmacao;

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
            
            $conn, "SELECT id, nome, token, foto_url, confirmado, email FROM usuarios
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

                        "INSERT INTO usuarios (nome, email, senha, token, foto_url, confirmado, codigo_confirmacao, expiracao_codigo, data_cadastro)
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW() + INTERVAL 30 MINUTE, ?)"
        
                    );
        
                    if($conexao === false){
        
                        throw new Exception("Erro de conexão: ".$this->conn->error);
        
                    }

                    $this->foto_url = NULL;
                    $this->confirmado = 0;
                    $this->codigo_confirmacao = $this->gerar_codigo();
                    $this->data_cadastro = date('Y-m-d');
        
                    $conexao->bind_param("sssssiss", $this->nome, $this->email, $this->senha, $this->token, $this->foto_url, $this->confirmado, $this->codigo_confirmacao, $this->data_cadastro);
        
                    if(!$conexao->execute()){
        
                        throw new Exception("Erro de execução: ".$conexao->error);
        
                    }

                    //$this->mandar_email("Olá <b>".$this->nome."</b><br>Esse é o seu código para ativação da conta no nosso app: <b>".$this->codigo_confirmacao."</b>", "Confirmação de email");

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

        $email_origem = EMAIL_SISTEMA;
        $senha_email_origem = SENHA_EMAIL;

        $mail->isSMTP();
        $mail->Host = 'mybuy.erickmota.com';
        $mail->SMTPAuth = true;
        $mail->Username = $email_origem;
        $mail->Password = $senha_email_origem;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->CharSet = 'UTF-8';

        $mail->setFrom($email_origem, 'My Buy');
        $mail->addAddress($this->email, 'Erick Mota');

        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = "<p>
        
        ".$texto."
        
        </p>
        
        <p style='font-size: 14px; color: #AAA'>
        
        Caso você não tenha solicitado este código, por favor, desconsidere este e-mail. Se você tiver alguma dúvida ou precisar de ajuda, estamos à disposição.<br>
        
        Essa mensagem é gerada automaticamente, por isso não é necessário responder.
        
        </p>
        
        <p>
        
        Atenciosamente<br>
        
        <span style='font-size: 20px; color: #902dc4'>My Buy Lista de compras.</span>
        
        </p>
        
        <img src='https://testes.erickmota.com/fotos/outros/logo_mybuy_email.png' width='200px'/>";

        $mail->send();

    }

    /* Gera o código de confirmação para o email do usuário */
    private function gerar_codigo(){

        $numero = rand(1, 9999);

        $numeroFormatado = str_pad($numero, 4, '0', STR_PAD_LEFT);

        return $numeroFormatado;

    }

    public function confirma_codigo(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT codigo_confirmacao, expiracao_codigo FROM usuarios
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

            $result = $sql->fetch_assoc();

            $codigo_confirmacao = $result["codigo_confirmacao"];
            $expiracao_codigo = $result["expiracao_codigo"];

            if($this->codigo_confirmacao != $codigo_confirmacao){

                return $this->retorna_json->retornaErro("Código inválido!");

            }

            if($expiracao_codigo < date("Y-m-d H:i:s")){

                return $this->retorna_json->retornaErro("Código expirado!");

            }

            $conexao_2 = $this->conn->prepare(

                "UPDATE usuarios
                SET confirmado=?,
                codigo_confirmacao=?,
                expiracao_codigo=?
                WHERE email=?"

            );

            if($conexao_2 === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $novos_dados = [

                1,
                NULL

            ];

            $conexao_2->bind_param("isss", $novos_dados[0], $novos_dados[1], $novos_dados[1], $this->email);

            if(!$conexao_2->execute()){

                throw new Exception("Erro de execução: ".$conexao_2->error);

            }

            return $this->retorna_json->retorna_json(null);
            
        } catch (Exception $e) {

            error_log("Classe Usuarios - Métodos: confirma_codigo - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    /* Gera um novo código de confirmação, e reenvia o email */
    public function reenviar_email(){

        try {

            $this->codigo_confirmacao = $this->gerar_codigo();

            $conexao = $this->conn->prepare(

                "UPDATE usuarios
                SET codigo_confirmacao=?,
                expiracao_codigo=NOW() + INTERVAL 30 MINUTE
                WHERE email=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("ss", $this->codigo_confirmacao, $this->email);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            /* $this->mandar_email("Olá <b>".$this->nome."</b><br>Esse é o seu código para ativação da conta no nosso app: <b>".$this->codigo_confirmacao."</b>", "Confirmação de email"); */
            
            return $this->retorna_json->retorna_json(null);

        } catch (Exception $e) {

            error_log("Classe Usuarios - Métodos: reenviar_email - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }
    
}

?>