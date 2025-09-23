<?php

// Configurações do banco de dados
$servidor = "localhost";
$usuario  = "root";
$senha    = "";
$banco    = "metalma2";

try {
    // Conectar ao banco de dados usando PDO
    $conexao = new PDO("mysql:host=$servidor;dbname=$banco;charset=utf8", $usuario, $senha);
    
    // Configurar o modo de erro do PDO para exceções
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // echo "Conexão realizada com sucesso!";
    
} catch (PDOException $e) {
    echo "Falha na conexão: " . $e->getMessage();
}

?>
