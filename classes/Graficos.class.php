<?php

class Graficos{

    private $conn;
    private $class_produtosCompras;
    private $class_json;

    public $id_usuario;
    public $ano;

    public function __construct($classeConexao, $class_json, $class_produtosCompras){

        $this->conn = $classeConexao->getConexao();
        $this->class_json = $class_json;
        $this->class_produtosCompras = $class_produtosCompras;

    }

    /* Retorna o valor total das vendas de cada mês, no ano informado */
    public function retorna_vendas_por_mes(){

        try {

            $conexao = $this->conn->prepare(

                "SELECT id, DATE_FORMAT(data, '%m') as mes_compra FROM compras
                WHERE DATE_FORMAT(data, '%Y')=?
                AND id_usuarios=?"

            );

            if(!$conexao){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("si", $this->ano, $this->id_usuario);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao);

            }

            $sql = $conexao->get_result();

            if($sql->num_rows > 0){

                while($resultado = $sql->fetch_assoc()){

                    $array[] = $resultado;
    
                }
    
                /* Retornando o valor total de cada compra */
                foreach($array as &$result){
    
                    $id_compra = $result["id"];
    
                    $this->class_produtosCompras->id_compras = $id_compra;
                    $result["valor_compra"] = $this->class_produtosCompras->retorna_valor_compra();
    
                }
    
                $total_mes = [
    
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0
    
                ];
    
                /* Cada posição do array acima, representa um mês de 1 a 12.
                Abaixo o código soma o valor das compras e reotorna para a
                posição específica do mês */
                foreach($array as $result2){
    
                    $mes_compra = $result2["mes_compra"];
                    $valor_compra = $result2["valor_compra"];
    
                    $i_mes = 1;
    
                    while($i_mes <= 12){
    
                        if($mes_compra == $i_mes){
    
                            $total_mes[$i_mes - 1] += $valor_compra;
    
                        }
    
                        $i_mes++;
    
                    }
    
                }
    
                return $total_mes;

            }else{

                return null;

            }
            
        } catch (Exception $e) {
            
            error_log("Classe Gráficos - Métodos: retorna_vendas_por_mes - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->class_json->retornaErro($e->getMessage());

        }

    }

    /* Retorna os dados extra da categoria, no gráfico geral */
    private function dados_extras_categoria($data){

        try {

            $conexao = $this->conn->prepare(

                "SELECT categorias.nome, COUNT(DISTINCT compras.id) as quantidade FROM categorias
                INNER JOIN produtos_compras ON produtos_compras.id_categorias=categorias.id
                INNER JOIN compras ON compras.id=produtos_compras.id_compras
                WHERE DATE_FORMAT(compras.data, '%Y-%m')=?
                AND categorias.id_usuarios=?
                GROUP BY categorias.id
                ORDER BY quantidade DESC
                LIMIT 1"

            );

            if(!$conexao){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("si", $data, $this->id_usuario);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();

            if($sql->num_rows > 0){

                $nome_categoria = $sql->fetch_assoc();

                return $nome_categoria["nome"];

            }else{

                return null;

            }
            
        } catch (Exception $e) {

            error_log("Classe Gráficos - Métodos: dados_extras_categoria - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->class_json->retornaErro($e->getMessage());
            
        }

    }

    /* Retorna os dados extra do mercado, no gráfico geral */
    private function dados_extras_mercado($data){

        try {

            $conexao = $this->conn->prepare(

                "SELECT mercados.nome, compras.id_mercados, COUNT(DISTINCT compras.id) as quantidade FROM mercados
                INNER JOIN compras ON compras.id_mercados=mercados.id
                WHERE DATE_FORMAT(compras.data, '%Y-%m')=?
                AND mercados.id_usuarios=?
                GROUP BY mercados.id
                ORDER BY quantidade DESC
                LIMIT 1"

            );

            if(!$conexao){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("si", $data, $this->id_usuario);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();

            if($sql->num_rows > 0){

                $nome_mercado = $sql->fetch_assoc();

                return $nome_mercado["nome"];

            }else{

                return null;

            }
            
        } catch (Exception $e) {

            error_log("Classe Gráficos - Métodos: dados_extras_categoria - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->class_json->retornaErro($e->getMessage());
            
        }

    }

    /* Retorna os dados extra do produto, no gráfico geral */
    private function dados_extras_produto($data){

        try {

            $conexao = $this->conn->prepare(

                "SELECT produtos_compras.nome_produto, COUNT(*) AS quantidade FROM produtos_compras
                INNER JOIN compras ON compras.id=produtos_compras.id_compras
                WHERE DATE_FORMAT(compras.data, '%Y-%m')=?
                AND compras.id_usuarios=?
                GROUP BY produtos_compras.id
                ORDER BY quantidade DESC
                LIMIT 1"

            );

            if(!$conexao){

                throw new Exception("Erro de conexão: ".$this->conn->error);

            }

            $conexao->bind_param("si", $data, $this->id_usuario);

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();

            if($sql->num_rows > 0){

                $nome_produto = $sql->fetch_assoc();

                return $nome_produto["nome_produto"];

            }else{

                return null;

            }
            
        } catch (Exception $e) {

            error_log("Classe Gráficos - Métodos: dados_extras_categoria - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->class_json->retornaErro($e->getMessage());
            
        }

    }

    /* Retorna detalhes de cada mês */
    public function detalhes_mes(){

        try {

            $valores_mes = $this->retorna_vendas_por_mes();

            $meses_nomes = [

                "janeiro",
                "fevereiro",
                "marco",
                "abril",
                "maio",
                "junho",
                "julho",
                "agosto",
                "setembro",
                "outubro",
                "novembro",
                "dezembro"

            ];

            $meses = [];

            $mes = 1;

            $valores_por_mes = $this->retorna_vendas_por_mes();

            while($mes <= 12){

                $conexao = $this->conn->prepare(

                    "SELECT * FROM compras
                    WHERE DATE_FORMAT(data, '%Y-%m')=?
                    AND id_usuarios=?"

                );

                if(!$conexao){

                    throw new Exception("Erro de conexão: ".$this->conn->error);
    
                }

                $ano_mes = $this->ano."-".str_pad($mes, 2, '0', STR_PAD_LEFT);

                $conexao->bind_param("si", $ano_mes, $this->id_usuario);

                if(!$conexao->execute()){

                    throw new Exception("Erro de execução: ".$conexao->error);
    
                }

                $sql = $conexao->get_result();

                $qtd = $sql->num_rows;

                $meses[$mes - 1] = [

                    "nome"=>$meses_nomes[$mes - 1],
                    "qtd_compras"=>$qtd ?? 0,
                    "valor_total"=>$valores_por_mes[$mes - 1] ?? 0,
                    "categoria_principal"=>$this->dados_extras_categoria($ano_mes) ?? null,
                    "mercado_principal"=>$this->dados_extras_mercado($ano_mes) ?? null,
                    "produto_principal"=>$this->dados_extras_produto($ano_mes) ?? null

                ];

                $mes++;

            }

            return $this->class_json->retorna_json(array_reverse($meses));
            
        } catch (Exception $e) {

            error_log("Classe Gráficos - Métodos: detalhes_mes - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->class_json->retornaErro($e->getMessage());
            
        }

    }

    /* Retorna as vendas em json */
    public function formata_venda_mes(){

        return $this->class_json->retorna_json($this->retorna_vendas_por_mes());

    }

    /* Retorna o total e a média do ano */
    public function total_media(){

        $resultados = [];

        $vendas = $this->retorna_vendas_por_mes();

        if(is_array($vendas)){

            $i_mes = 0;

            foreach($vendas as $total_mes){

                if($total_mes > 0){

                    $i_mes++;

                }

            }

        }

        if($vendas != null){

            $total_ano = array_sum($vendas);

            $media_ano = $total_ano / $i_mes;
    
            $resultados = [
    
                "total"=>$total_ano,
                "media"=>$media_ano
    
            ];
    
            return $this->class_json->retorna_json($resultados);

        }else{

            return $this->class_json->retornaErro("Nenhuma compra encontrada no ano");

        }

    }

    /* Retorna o valor total do mês atual e do mês anterior */
    public function despesas_totais_mes_atual_passado(){

        /* Esse método é responsável por retornar o valor das vendas de cada mês do ano
        de referência. */
        $vendas = $this->retorna_vendas_por_mes();

        $mes_atual = date("m");

        /* Aqui estou pegando os valores do mês atual e do mês passado
        com base na posição do array */
        $retorno_mes_atual = $vendas[$mes_atual - 1];
        $retorno_mes_anterior = $vendas[$mes_atual - 2];

        $resultado = [

            "mes_atual"=>$retorno_mes_atual,
            "mes_anterior"=>$retorno_mes_anterior

        ];

        return $this->class_json->retorna_json($resultado);

    }

    /* Retorna as informações visuais dos graficos, como cores e tamanhos de legendas. */
    private function informacoes_visuais_graficos(){

        $cores = [

            "#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF",
            "#FF9F40", "#FFCD56", "#4DD0E1", "#FF4081", "#FF6E40",
            "#9575CD", "#81C784", "#F06292", "#7986CB", "#FFD54F"

        ]; // Cores do gráfico

        $cor_legenda = "#7F7F7F"; // Cor da legenda dos gráficos pizza

        $tamanho_legenda = 13; // Tamanho da leganda dos gráficos pizza

        $compilado = [

            "cores"=>$cores,
            "cor_legenda"=>$cor_legenda,
            "tamanho_legenda"=>$tamanho_legenda

        ];

        return $compilado;

    }

    /* Retorna os dados para o gráfico categorias */
    public function retorna_categorias_por_data($filtro_data, $data_1, $data_2){

        try {

            switch($filtro_data){

                case "mes_atual":

                    $conexao = $this->conn->prepare(

                        "SELECT categorias.id, categorias.nome AS name, COUNT(DISTINCT compras.id) AS quantidade,
                        SUM(CASE

                        WHEN produtos_compras.tipo_exibicao = 3 OR produtos_compras.tipo_exibicao = 5
                        THEN produtos_compras.preco_produto
                        ELSE produtos_compras.preco_produto * produtos_compras.qtd
                        
                        END) AS valor_total FROM categorias
                        INNER JOIN produtos_compras ON produtos_compras.id_categorias=categorias.id
                        INNER JOIN compras ON compras.id=produtos_compras.id_compras
                        WHERE categorias.id_usuarios=?
                        AND DATE_FORMAT(compras.data, '%Y-%m')=DATE_FORMAT(CURRENT_DATE, '%Y-%m')
                        GROUP BY categorias.id
                        ORDER BY valor_total DESC"

                    );

                    if(!$conexao){

                        throw new Exception("Erro de conexão: ".$this->conn->error);

                    }

                    $conexao->bind_param("i", $this->id_usuario);

                break;

                case "mes_passado":

                    $conexao = $this->conn->prepare(

                        "SELECT categorias.id, categorias.nome AS name, COUNT(DISTINCT compras.id) AS quantidade,
                        SUM(CASE

                        WHEN produtos_compras.tipo_exibicao = 3 OR produtos_compras.tipo_exibicao = 5
                        THEN produtos_compras.preco_produto
                        ELSE produtos_compras.preco_produto * produtos_compras.qtd
                        
                        END) AS valor_total FROM categorias
                        INNER JOIN produtos_compras ON produtos_compras.id_categorias=categorias.id
                        INNER JOIN compras ON compras.id=produtos_compras.id_compras
                        WHERE categorias.id_usuarios=?
                        AND DATE_FORMAT(compras.data, '%Y-%m')=DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH), '%Y-%m')
                        GROUP BY categorias.id
                        ORDER BY valor_total DESC"

                    );

                    if(!$conexao){

                        throw new Exception("Erro de conexão: ".$this->conn->error);

                    }

                    $conexao->bind_param("i", $this->id_usuario);

                break;

                case "escolher_datas":

                    $conexao = $this->conn->prepare(

                        "SELECT categorias.id, categorias.nome AS name, COUNT(DISTINCT compras.id) AS quantidade,
                        SUM(CASE

                        WHEN produtos_compras.tipo_exibicao = 3 OR produtos_compras.tipo_exibicao = 5
                        THEN produtos_compras.preco_produto
                        ELSE produtos_compras.preco_produto * produtos_compras.qtd
                        
                        END) AS valor_total FROM categorias
                        INNER JOIN produtos_compras ON produtos_compras.id_categorias=categorias.id
                        INNER JOIN compras ON compras.id=produtos_compras.id_compras
                        WHERE categorias.id_usuarios=?
                        AND DATE(compras.data) BETWEEN ? AND ?
                        GROUP BY categorias.id
                        ORDER BY valor_total DESC"

                    );

                    if(!$conexao){

                        throw new Exception("Erro de conexão: ".$this->conn->error);

                    }

                    $conexao->bind_param("iss", $this->id_usuario, $data_1, $data_2);

                break;

            }

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();

            if($sql->num_rows > 0){

                while($resultado = $sql->fetch_assoc()){

                    $array[] = $resultado;
    
                }

                $inf_graficos = $this->informacoes_visuais_graficos();

                $i = 0;

                foreach($array as &$resultados){

                    if($i > 14){

                        $resultados["color"] = "#DDD";

                    }else{

                        $resultados["color"] = $inf_graficos["cores"][$i];

                    }

                    $resultados["legendFontColor"] = $inf_graficos["cor_legenda"];
                    $resultados["legendFontSize"] = $inf_graficos["tamanho_legenda"];

                    $i++;

                }
    
                return $this->class_json->retorna_json($array);

            }else{

                return $this->class_json->retornaErro("Sem dados para exibição");

            }
            
        } catch (Exception $e) {

            error_log("Classe Gráficos - Métodos: retorna_categorias_por_data - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->class_json->retornaErro($e->getMessage());
            
        }

    }

    /* Retorna os dados para o gráfico mercados */
    public function retorna_mercados_por_data($filtro_data, $data_1, $data_2){

        try {

            switch($filtro_data){

                case "mes_atual":

                    $conexao = $this->conn->prepare(

                        "SELECT mercados.id, mercados.nome AS name, COUNT(DISTINCT compras.id) AS quantidade,
                        SUM(CASE

                        WHEN produtos_compras.tipo_exibicao = 3 OR produtos_compras.tipo_exibicao = 5
                        THEN produtos_compras.preco_produto
                        ELSE produtos_compras.preco_produto * produtos_compras.qtd
                        
                        END) AS valor_total FROM mercados
                        INNER JOIN compras ON compras.id_mercados=mercados.id
                        INNER JOIN produtos_compras ON produtos_compras.id_compras=compras.id
                        WHERE compras.id_usuarios=?
                        AND DATE_FORMAT(compras.data, '%Y-%m')=DATE_FORMAT(CURRENT_DATE, '%Y-%m')
                        GROUP BY mercados.id
                        ORDER BY valor_total DESC"

                        /* "SELECT categorias.id, categorias.nome AS name, COUNT(DISTINCT compras.id) AS quantidade,
                        SUM(CASE

                        WHEN produtos_compras.tipo_exibicao = 3 OR produtos_compras.tipo_exibicao = 5
                        THEN produtos_compras.preco_produto
                        ELSE produtos_compras.preco_produto * produtos_compras.qtd
                        
                        END) AS valor_total FROM categorias
                        INNER JOIN produtos_compras ON produtos_compras.id_categorias=categorias.id
                        INNER JOIN compras ON compras.id=produtos_compras.id_compras
                        WHERE categorias.id_usuarios=?
                        AND DATE_FORMAT(compras.data, '%Y-%m')=DATE_FORMAT(CURRENT_DATE, '%Y-%m')
                        GROUP BY categorias.id
                        ORDER BY valor_total DESC" */

                    );

                    if(!$conexao){

                        throw new Exception("Erro de conexão: ".$this->conn->error);

                    }

                    $conexao->bind_param("i", $this->id_usuario);

                break;

                case "mes_passado":

                    $conexao = $this->conn->prepare(

                        "SELECT mercados.id, mercados.nome AS name, COUNT(DISTINCT compras.id) AS quantidade,
                        SUM(CASE

                        WHEN produtos_compras.tipo_exibicao = 3 OR produtos_compras.tipo_exibicao = 5
                        THEN produtos_compras.preco_produto
                        ELSE produtos_compras.preco_produto * produtos_compras.qtd
                        
                        END) AS valor_total FROM mercados
                        INNER JOIN compras ON compras.id_mercados=mercados.id
                        INNER JOIN produtos_compras ON produtos_compras.id_compras=compras.id
                        WHERE compras.id_usuarios=?
                        AND DATE_FORMAT(compras.data, '%Y-%m')=DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH), '%Y-%m')
                        GROUP BY mercados.id
                        ORDER BY valor_total DESC"

                    );

                    if(!$conexao){

                        throw new Exception("Erro de conexão: ".$this->conn->error);

                    }

                    $conexao->bind_param("i", $this->id_usuario);

                break;

                case "escolher_datas":

                    $conexao = $this->conn->prepare(

                        "SELECT mercados.id, mercados.nome AS name, COUNT(DISTINCT compras.id) AS quantidade,
                        SUM(CASE

                        WHEN produtos_compras.tipo_exibicao = 3 OR produtos_compras.tipo_exibicao = 5
                        THEN produtos_compras.preco_produto
                        ELSE produtos_compras.preco_produto * produtos_compras.qtd
                        
                        END) AS valor_total FROM mercados
                        INNER JOIN compras ON compras.id_mercados=mercados.id
                        INNER JOIN produtos_compras ON produtos_compras.id_compras=compras.id
                        WHERE compras.id_usuarios=?
                        AND DATE(compras.data) BETWEEN ? AND ?
                        GROUP BY mercados.id
                        ORDER BY valor_total DESC"

                    );

                    if(!$conexao){

                        throw new Exception("Erro de conexão: ".$this->conn->error);

                    }

                    $conexao->bind_param("iss", $this->id_usuario, $data_1, $data_2);

                break;

            }

            if(!$conexao->execute()){

                throw new Exception("Erro de execução: ".$conexao->error);

            }

            $sql = $conexao->get_result();

            if($sql->num_rows > 0){

                while($resultado = $sql->fetch_assoc()){

                    $array[] = $resultado;
    
                }

                $inf_graficos = $this->informacoes_visuais_graficos();

                $i = 0;

                foreach($array as &$resultados){

                    if($i > 14){

                        $resultados["color"] = "#DDD";

                    }else{

                        $resultados["color"] = $inf_graficos["cores"][$i];

                    }

                    $resultados["legendFontColor"] = $inf_graficos["cor_legenda"];
                    $resultados["legendFontSize"] = $inf_graficos["tamanho_legenda"];

                    $i++;

                }
    
                return $this->class_json->retorna_json($array);

            }else{

                return $this->class_json->retornaErro("Sem dados para exibição");

            }
            
        } catch (Exception $e) {

            error_log("Classe Gráficos - Métodos: retorna_categorias_por_data - ".$e->getMessage()."\n", 3, 'erros.log');

            return $this->class_json->retornaErro($e->getMessage());
            
        }

    }

}

?>