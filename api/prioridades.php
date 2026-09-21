<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../controller/PrioridadeController.php';

$controller = new PrioridadeController($conexao);

try {
    $resultado = $controller->listar();
} catch (Exception $e) {
    $resultado = ['sucesso' => false, 'erro' => 'Erro interno: ' . $e->getMessage(), 'status' => 500];
}

$status = $resultado['status'] ?? ($resultado['sucesso'] ? 200 : 500);
unset($resultado['status']);

http_response_code($status);
echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
