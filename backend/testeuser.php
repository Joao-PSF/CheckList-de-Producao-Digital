<?php

//Incluir arquivo de conexão
include ('backend/conexao.php');
echo "teste";
//criar um usuario de teste
$senha = password_hash("12345", PASSWORD_DEFAULT);
$sql = "INSERT INTO users (matricula, nome, email, senha, nivel, datadecadastro) VALUES 
        (12345, 'Usuario Teste', 'teste@gmail.com', '$senha', 1, NOW())";
if ($conexao->query($sql) === TRUE) {
    echo "Usuário de teste criado com sucesso.";
}