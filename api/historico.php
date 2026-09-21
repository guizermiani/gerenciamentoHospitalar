<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../controller/HistoricoController.php';

$controller = new HistoricoController($conexao);

$metodo = $_SERVER['REQUEST_METHOD'];
$idChamado = isset($_GET['id_chamado']) ? (int) $_GET['id_chamado'] : null;

try {
    switch ($metodo) {
        case 'GET':
            if ($idChamado === null) {
                $resultado = ['sucesso' => false, 'erro' => 'Informe o ID do chamado (?id_chamado=).', 'status' => 400];
                break;
            }
            $resultado = $controller->listarPorChamado($idChamado);
            break;

        case 'POST':
            $dados = json_decode(file_get_contents('php://input'), true) ?? [];
            $resultado = $controller->criar($dados);
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
