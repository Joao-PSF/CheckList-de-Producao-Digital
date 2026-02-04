<?php
/**
 * Script para importar o banco de dados metalma2.sql
 * Acesse via: http://localhost/metalma/import_database.php
 */

try {
    // Conectar ao banco
    $conexao = new PDO('mysql:host=127.0.0.1', 'root', '');
    
    // Ler arquivo SQL
    $sql = file_get_contents(__DIR__ . '/utils/metalma2.sql');
    
    // Executar múltiplas queries
    $queries = array_filter(
        array_map(
            'trim',
            explode(';', $sql)
        ),
        fn($q) => !empty($q) && !str_starts_with($q, '--') && !str_starts_with($q, '/*')
    );
    
    foreach ($queries as $query) {
        if (!empty(trim($query))) {
            try {
                $conexao->exec($query);
            } catch (PDOException $e) {
                // Ignorar erros de tabelas que já existem
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "Erro em query: " . substr($query, 0, 50) . "...<br>";
                    echo "Erro: " . $e->getMessage() . "<br><br>";
                }
            }
        }
    }
    
    echo "<div style='padding:20px; background:#d4edda; color:#155724; border-radius:5px;'>";
    echo "<h3>✓ Banco de dados importado com sucesso!</h3>";
    echo "<p>Todas as tabelas foram criadas/atualizadas.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='padding:20px; background:#f8d7da; color:#721c24; border-radius:5px;'>";
    echo "<h3>✗ Erro ao importar banco de dados</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
