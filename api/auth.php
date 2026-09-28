<?php
/**
 * API REST de autenticação.
 *   GET    /api/auth.php  -> quem está logado agora (401 se ninguém)
 *   POST   /api/auth.php  -> login (corpo: {"email":..., "senha":...})
 *   DELETE /api/auth.php  -> logout
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../controller/AuthController.php';

$controller = new AuthController($conexao);
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    switch ($metodo) {
        case 'GET':
            $resultado = $controller->sessaoAtual();
            break;

        case 'POST':
            $dados = json_decode(file_get_contents('php://input'), true) ?? [];
            $resultado = $controller->login($dados);
            break;

        case 'DELETE':
            $resultado = $controller->logout();
            break;

        default:
            $resultado = ['sucesso' => false, 'erro' => 'Método não permitido.', 'status' => 405];
            break;
    }
} catch (Exception $e) {
    $resultado = ['sucesso' => false, 'erro' => 'Erro interno: ' . $e->getMessage(), 'status' => 500];
}

$status = $resultado['status'] ?? ($resultado['sucesso'] ? 200 : 500);
unset($resultado['status']);

http_response_code($status);
echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
