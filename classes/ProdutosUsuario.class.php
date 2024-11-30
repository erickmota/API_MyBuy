<?php

class ProdutosUsuario{

    private $conn;
    private $retorna_json;
    private $classeProdutosExemplo;

    public $id;
    public $nome;
    public $tipo_exibicao;
    public $id_fotos;
    public $id_usuarios;

    public function __construct($classeProdutosExemplo, $classeRetornosJson, $classeConexao){

        $this->conn = $classeConexao->getConexao();
        $this->retorna_json = $classeRetornosJson;
        $this->classeProdutosExemplo = $classeProdutosExemplo;

    }

    /* SET */

    public function setId($id){

        $this->id = $id;

    }

    public function setNome($nome){

        $this->nome = $nome;

    }

    public function setTipo_exibicao($tipo_exibicao){

        $this->tipo_exibicao = $tipo_exibicao;

    }

    public function setId_fotos($id_fotos){

        if($id_fotos == 0){

            $this->id_fotos = NULL;

        }else{

            $this->id_fotos = $id_fotos;

        }

    }

    public function setId_usuarios($id_usuarios){

        $this->id_usuarios = $id_usuarios;

    }

    /* Métodos */

    /* Cria um novo registro na tabela produtos_usuarios */

    public function criar_produtos_usuario(){

        $conn = $this->conn;

        if($this->id_fotos == NULL){

            $sql = mysqli_query(

                $conn,
                "INSERT INTO produtos_usuario (nome, tipo_exibicao, id_fotos, id_usuarios)
                VALUES ('$this->nome', '$this->tipo_exibicao', NULL, '$this->id_usuarios')"
    
            ) or die("Erro conexão");

        }else{

            $sql = mysqli_query(

                $conn,
                "INSERT INTO produtos_usuario (nome, tipo_exibicao, id_fotos, id_usuarios)
                VALUES ('$this->nome', '$this->tipo_exibicao', '$this->id_fotos', '$this->id_usuarios')"
    
            ) or die("Erro conexão");

        }

        $ultimo_registro = mysqli_insert_id($conn); //Retornando último registro adicionado na tabela produtos_usuarios

        return $ultimo_registro;

    }

    /* Verifica se existe um produto com o mesmo nome de registro, de um novo produto */

    public function verifica_existencia_bd(){

        $conn = $this->conn;

        $sql = mysqli_query(

            $conn,
            "SELECT id, id_fotos FROM produtos_usuario
            WHERE nome='$this->nome'
            AND id_usuarios='$this->id_usuarios'"

        ) or die("Erro conexão");

        $qtd = mysqli_num_rows($sql);

        if($qtd > 0){

            $result = mysqli_fetch_assoc($sql);

            $id_produto = $result["id"];
            $id_foto = $result["id_fotos"];

            return [$id_produto, $id_foto];

        }else{

            return false;

        }

    }

    /* Atualiza os dados do produtos_usuario */

    public function atualiza_dados_produtos_usuario(){

        if($this->id_fotos == NULL){

            $sql = mysqli_query(

                $this->conn,
                "UPDATE produtos_usuario
                SET tipo_exibicao='$this->tipo_exibicao',
                id_fotos=NULL
                WHERE id='$this->id'"
    
            ) or die("Erro conexão");

        }else{

            $sql = mysqli_query(

                $this->conn,
                "UPDATE produtos_usuario
                SET tipo_exibicao='$this->tipo_exibicao',
                id_fotos='$this->id_fotos'
                WHERE id='$this->id'"
    
            ) or die("Erro conexão");

        }

    }

    public function retorna_produtos_usuario(){

        $sql = mysqli_query(

            $this->conn,
            "SELECT produtos_usuario.id, produtos_usuario.nome, produtos_usuario.tipo_exibicao, produtos_usuario.id_fotos AS id_foto,
            fotos.url FROM produtos_usuario
            LEFT JOIN fotos ON fotos.id=produtos_usuario.id_fotos
            WHERE produtos_usuario.id_usuarios='$this->id_usuarios'"

        ) or die("Erro conexão");

        $qtd = mysqli_num_rows($sql);

        /* Retornando todos os produtos de exemplo como */
        $produtos_exemplo = $this->classeProdutosExemplo->busca_produto();

        /* Inserindo a confirmação no array se é produto base do usuário ou é produto de exemplo */
        foreach($produtos_exemplo as &$prod_exemplo){

            $prod_exemplo["exemplo"] = true;

        }

        if($qtd < 1){

            return $this->retorna_json->retorna_json($produtos_exemplo);

        }else{

            while ($row = mysqli_fetch_assoc($sql)){
                
                $array[] = $row;
                
            }
    
            /* Inserindo a confirmação no array se é produto base do usuário ou é produto de exemplo */        
            foreach($array as &$prod_base){
    
                $prod_base["exemplo"] = false;
    
            }

            $result = array_merge($array, $produtos_exemplo);

            /* Elimina duplicidades no nome.
            Caso o usuário digite uma busca que, tenha tanto no exemplo como no base,
            o array eliminará o exemplo. */

            $resultado = [];
            $nomes_encontrados = [];

            foreach ($result as $item){

                if(!in_array($item["nome"], $nomes_encontrados)){

                    $nomes_encontrados[] = $item["nome"];
                    $resultado[] = $item;

                }

            }

            return $this->retorna_json->retorna_json($resultado);

        }

    }

    public function retorna_todos_produtos_usuarios(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT produtos_usuario.id, produtos_usuario.nome, fotos.url AS url_foto FROM produtos_usuario
                LEFT JOIN fotos ON fotos.id=produtos_usuario.id_fotos
                WHERE id_usuarios=?
                ORDER BY produtos_usuario.nome ASC"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("i", $this->id_usuarios);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();
            $num = $sql->num_rows;

            if($num < 1){

                return $this->retorna_json->retornaErro("Nenhum produto encontrado.");

            }else{

                while($resultado = $sql->fetch_assoc()){

                    $array[] = $resultado;
    
                }

                return $this->retorna_json->retorna_json($array);

            }
            
        } catch (Exception $e) {

            error_log("Classe ProdutosUsuario - Métodos: retorna_todos_produtos_usuarios - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    public function apaga_produto_usuario(){

        try {

            $conexao = $this->conn->prepare(

                "DELETE FROM produtos_usuario
                WHERE id=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("i", $this->id);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            return $this->retorna_json->retorna_json(null);
            
        } catch (Exception $e) {

            error_log("Classe ProdutosUsuario - Métodos: apaga_produto_usuario - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

    /* Verifica se o id do produto, pertence ao usuário */
    public function verifica_produto_usuario(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT * FROM produtos_usuario
                WHERE id_usuarios=?
                AND id=?"

            );

            if($conexao === false){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("ii", $this->id_usuarios, $this->id);

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

            error_log("Classe ProdutosUsuario - Métodos: verifica_produto_usuario - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->retorna_json->retornaErro($e->getMessage());
            
        }

    }

}

?>