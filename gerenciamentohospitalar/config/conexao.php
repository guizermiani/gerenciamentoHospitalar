<?php
/**
 * Conexão com o banco de dados (MariaDB) usando PDO.
 */

$host    = 'localhost';
$dbname  = 'sistema_chamados_hospital';
$usuario = 'root';   
$senha   = '';       

try {
    $conexao = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $usuario,
        $senha
    );
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexao->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erro na conexão com o banco de dados: ' . $e->getMessage());
}
