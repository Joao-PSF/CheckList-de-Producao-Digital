<?php

include_once __DIR__ . '/../conexao.php';

/**
 * Retorna estatísticas gerais das OS
 */
function obterEstatisticasOS($conexao) {
    $sql = "
        SELECT 
            COUNT(*) as total_os,
            SUM(CASE WHEN situacao = 'PENDENTE' OR situacao = 'ANDAMENTO' THEN 1 ELSE 0 END) as abertas,
            SUM(CASE WHEN situacao = 'ENCERRADA' THEN 1 ELSE 0 END) as encerradas,
            SUM(CASE 
                WHEN (situacao = 'PENDENTE' OR situacao = 'ANDAMENTO') 
                AND data_programada IS NOT NULL 
                AND data_programada < CURDATE() 
                THEN 1 ELSE 0 
            END) as atrasadas
        FROM servicos_os
        WHERE status = 'Ativo'
    ";
    
    $stmt = $conexao->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Retorna lista de OS com suas etapas
 */
function obterOSComEtapas($conexao, $filtros = []) {
    $where = ["so.status = 'Ativo'"];
    $params = [];
    
    // Filtro por situação
    if (!empty($filtros['situacao'])) {
        $where[] = "so.situacao = :situacao";
        $params[':situacao'] = $filtros['situacao'];
    }
    
    // Filtro por tipo de serviço
    if (!empty($filtros['tipo_servico'])) {
        $where[] = "so.servico_tipo_id = :tipo_servico";
        $params[':tipo_servico'] = $filtros['tipo_servico'];
    }
    
    // Filtro por data
    if (!empty($filtros['data_inicio'])) {
        $where[] = "so.criado_em >= :data_inicio";
        $params[':data_inicio'] = $filtros['data_inicio'];
    }
    
    if (!empty($filtros['data_fim'])) {
        $where[] = "so.criado_em <= :data_fim";
        $params[':data_fim'] = $filtros['data_fim'];
    }
    
    // Ordenação
    $orderBy = "so.id DESC";
    if (!empty($filtros['ordenacao'])) {
        switch ($filtros['ordenacao']) {
            case 'mais_atrasadas':
                $orderBy = "CASE WHEN so.data_programada < CURDATE() THEN 0 ELSE 1 END, so.data_programada ASC";
                break;
            case 'mais_recentes':
                $orderBy = "so.criado_em DESC";
                break;
            case 'mais_antigas':
                $orderBy = "so.criado_em ASC";
                break;
            case 'data_programada':
                $orderBy = "so.data_programada ASC";
                break;
        }
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "
        SELECT 
            so.id,
            so.nome_cliente,
            so.numero_cliente,
            so.criado_em,
            so.data_programada,
            so.data_inicio,
            so.data_encerramento,
            so.situacao,
            st.tipo as tipo_servico,
            CASE 
                WHEN so.data_programada IS NOT NULL AND so.data_programada < CURDATE() 
                AND (so.situacao = 'PENDENTE' OR so.situacao = 'ANDAMENTO')
                THEN 1 ELSE 0 
            END as atrasada,
            (SELECT COUNT(*) FROM servico_etapas WHERE servico_os_id = so.id AND status = 'Ativo') as total_etapas,
            (SELECT COUNT(*) FROM servico_etapas WHERE servico_os_id = so.id AND status = 'Ativo' AND execucao = 1) as etapas_concluidas
        FROM servicos_os so
        LEFT JOIN servicos_tipos st ON so.servico_tipo_id = st.id
        WHERE {$whereClause}
        ORDER BY {$orderBy}
    ";
    
    $stmt = $conexao->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retorna média de tempo por tipo de serviço
 */
function obterMediaTempoPorTipo($conexao) {
    $sql = "
        SELECT 
            st.tipo as tipo_servico,
            COUNT(so.id) as total_os,
            AVG(DATEDIFF(
                COALESCE(so.data_encerramento, CURDATE()),
                so.criado_em
            )) as media_dias,
            MIN(DATEDIFF(
                COALESCE(so.data_encerramento, CURDATE()),
                so.criado_em
            )) as min_dias,
            MAX(DATEDIFF(
                COALESCE(so.data_encerramento, CURDATE()),
                so.criado_em
            )) as max_dias
        FROM servicos_os so
        INNER JOIN servicos_tipos st ON so.servico_tipo_id = st.id
        WHERE so.status = 'Ativo'
        GROUP BY st.id, st.tipo
        ORDER BY st.tipo
    ";
    
    $stmt = $conexao->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retorna tipos de serviço para filtro
 */
function obterTiposServico($conexao) {
    $sql = "SELECT id, tipo FROM servicos_tipos WHERE status = 'Ativo' ORDER BY tipo";
    $stmt = $conexao->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retorna logs de serviços com filtros
 */
function obterLogsServicos($conexao, $filtros = []) {
    $where = ["1=1"];
    $params = [];
    
    // Filtro por ação
    if (!empty($filtros['acao'])) {
        $where[] = "sl.acao = :acao";
        $params[':acao'] = $filtros['acao'];
    }
    
    // Filtro por status
    if (!empty($filtros['status'])) {
        $where[] = "sl.status = :status";
        $params[':status'] = $filtros['status'];
    }
    
    // Filtro por data
    if (!empty($filtros['data_inicio'])) {
        $where[] = "DATE(sl.criado_em) >= :data_inicio";
        $params[':data_inicio'] = $filtros['data_inicio'];
    }
    
    if (!empty($filtros['data_fim'])) {
        $where[] = "DATE(sl.criado_em) <= :data_fim";
        $params[':data_fim'] = $filtros['data_fim'];
    }
    
    // Filtro por usuário
    if (!empty($filtros['usuario_matricula'])) {
        $where[] = "sl.usuario_matricula = :usuario_matricula";
        $params[':usuario_matricula'] = $filtros['usuario_matricula'];
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "
        SELECT 
            sl.*,
            u.nome as usuario_nome
        FROM servicos_log sl
        LEFT JOIN users u ON sl.usuario_matricula = u.matricula
        WHERE {$whereClause}
        ORDER BY sl.criado_em DESC
        LIMIT 500
    ";
    
    $stmt = $conexao->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retorna logs de cadastro com filtros
 */
function obterLogsCadastro($conexao, $filtros = []) {
    $where = ["1=1"];
    $params = [];
    
    // Filtro por ação
    if (!empty($filtros['acao'])) {
        $where[] = "cl.acao = :acao";
        $params[':acao'] = $filtros['acao'];
    }
    
    // Filtro por status
    if (!empty($filtros['status'])) {
        $where[] = "cl.status = :status";
        $params[':status'] = $filtros['status'];
    }
    
    // Filtro por data
    if (!empty($filtros['data_inicio'])) {
        $where[] = "DATE(cl.criado_em) >= :data_inicio";
        $params[':data_inicio'] = $filtros['data_inicio'];
    }
    
    if (!empty($filtros['data_fim'])) {
        $where[] = "DATE(cl.criado_em) <= :data_fim";
        $params[':data_fim'] = $filtros['data_fim'];
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "
        SELECT 
            cl.*,
            u.nome as usuario_nome
        FROM cadastro_log cl
        LEFT JOIN users u ON cl.usuario_matricula = u.matricula
        WHERE {$whereClause}
        ORDER BY cl.criado_em DESC
        LIMIT 500
    ";
    
    $stmt = $conexao->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retorna logs de login com filtros
 */
function obterLogsLogin($conexao, $filtros = []) {
    $where = ["1=1"];
    $params = [];
    
    // Filtro por ação
    if (!empty($filtros['acao'])) {
        $where[] = "ll.acao = :acao";
        $params[':acao'] = $filtros['acao'];
    }
    
    // Filtro por status
    if (!empty($filtros['status'])) {
        $where[] = "ll.status = :status";
        $params[':status'] = $filtros['status'];
    }
    
    // Filtro por data
    if (!empty($filtros['data_inicio'])) {
        $where[] = "DATE(ll.criado_em) >= :data_inicio";
        $params[':data_inicio'] = $filtros['data_inicio'];
    }
    
    if (!empty($filtros['data_fim'])) {
        $where[] = "DATE(ll.criado_em) <= :data_fim";
        $params[':data_fim'] = $filtros['data_fim'];
    }
    
    // Filtro por usuário
    if (!empty($filtros['usuario_matricula'])) {
        $where[] = "ll.usuario_matricula = :usuario_matricula";
        $params[':usuario_matricula'] = $filtros['usuario_matricula'];
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "
        SELECT 
            ll.*,
            u.nome as usuario_nome
        FROM login_log ll
        LEFT JOIN users u ON ll.usuario_matricula = u.matricula
        WHERE {$whereClause}
        ORDER BY ll.criado_em DESC
        LIMIT 500
    ";
    
    $stmt = $conexao->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retorna todos os logs (serviços, cadastro e login) combinados
 */
function obterTodosLogs($conexao, $filtros = []) {
    $where = ["1=1"];
    $params = [];
    
    // Filtro por ação
    if (!empty($filtros['acao'])) {
        $where[] = "acao = :acao";
        $params[':acao'] = $filtros['acao'];
    }
    
    // Filtro por status
    if (!empty($filtros['status'])) {
        $where[] = "status = :status";
        $params[':status'] = $filtros['status'];
    }
    
    // Filtro por data
    if (!empty($filtros['data_inicio'])) {
        $where[] = "DATE(criado_em) >= :data_inicio";
        $params[':data_inicio'] = $filtros['data_inicio'];
    }
    
    if (!empty($filtros['data_fim'])) {
        $where[] = "DATE(criado_em) <= :data_fim";
        $params[':data_fim'] = $filtros['data_fim'];
    }
    
    // Filtro por usuário
    if (!empty($filtros['usuario_matricula'])) {
        $where[] = "usuario_matricula = :usuario_matricula";
        $params[':usuario_matricula'] = $filtros['usuario_matricula'];
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "
        (
            SELECT 
                sl.id,
                sl.acao,
                sl.descricao,
                sl.status,
                sl.usuario_matricula,
                sl.criado_em,
                u.nome as usuario_nome,
                'Serviços' as tipo_log
            FROM servicos_log sl
            LEFT JOIN users u ON sl.usuario_matricula = u.matricula
            WHERE {$whereClause}
        )
        UNION ALL
        (
            SELECT 
                cl.id,
                cl.acao,
                cl.descricao,
                cl.status,
                cl.usuario_matricula,
                cl.criado_em,
                u.nome as usuario_nome,
                'Cadastro' as tipo_log
            FROM cadastro_log cl
            LEFT JOIN users u ON cl.usuario_matricula = u.matricula
            WHERE {$whereClause}
        )
        UNION ALL
        (
            SELECT 
                ll.id,
                ll.acao,
                ll.descricao,
                ll.status,
                ll.usuario_matricula,
                ll.criado_em,
                u.nome as usuario_nome,
                'Login' as tipo_log
            FROM login_log ll
            LEFT JOIN users u ON ll.usuario_matricula = u.matricula
            WHERE {$whereClause}
        )
        ORDER BY criado_em DESC
        LIMIT 500
    ";
    
    $stmt = $conexao->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retorna estatísticas de tentativas de login (sucesso vs falha vs erro)
 */
function obterEstatisticasLogin($conexao) {
    $sql = "
        SELECT 
            COUNT(*) as total_tentativas,
            SUM(CASE WHEN status = 'sucesso' THEN 1 ELSE 0 END) as login_sucesso,
            SUM(CASE WHEN status = 'falha' THEN 1 ELSE 0 END) as login_falha,
            SUM(CASE WHEN status = 'erro' THEN 1 ELSE 0 END) as login_erro
        FROM login_log
        WHERE acao = 'login' OR acao = 'Tentativa_Login'
    ";
    
    $stmt = $conexao->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Retorna histórico de tentativas de login ao longo do tempo (últimos 30 dias)
 * Agrupado de 2 em 2 horas
 */
function obterHistoricoLogin($conexao) {
    $sql = "
        SELECT 
            DATE_FORMAT(criado_em, '%Y-%m-%d') as data_dia,
            FLOOR(HOUR(criado_em) / 2) * 2 as hora_agrupada,
            COUNT(*) as total_tentativas,
            SUM(CASE WHEN status = 'sucesso' THEN 1 ELSE 0 END) as login_sucesso,
            SUM(CASE WHEN status = 'falha' THEN 1 ELSE 0 END) as login_falha,
            SUM(CASE WHEN status = 'erro' THEN 1 ELSE 0 END) as login_erro
        FROM login_log
        WHERE (acao = 'login' OR acao = 'Tentativa_Login')
        AND criado_em >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE_FORMAT(criado_em, '%Y-%m-%d'), FLOOR(HOUR(criado_em) / 2)
        ORDER BY data_dia ASC, hora_agrupada ASC
    ";
    
    $stmt = $conexao->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
