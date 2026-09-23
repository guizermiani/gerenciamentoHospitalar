<?php
/**
 * API REST de Chamado.
 *
 *   GET    /api/chamados.php           -> lista todos
 *   GET    /api/chamados.php?id=1      -> busca um
 *   POST   /api/chamados.php           -> abre um novo chamado (corpo em JSON)
 *   PUT    /api/chamados.php?id=1      -> atualiza status/responsavel (corpo em JSON)
 *
 * Sem rota DELETE de propósito: no domínio do sistema, um chamado não é apagado,
 * ele é CANCELADO (mudando o status) — deletar historico de atendimento não faz
 * sentido de negócio, então essa ação não é exposta pela API.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../controller/ChamadoController.php';

$controller = new ChamadoController($conexao);

$metodo = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

try {
    switch ($metodo) {
        case 'GET':
            $resultado = $id !== null
                ? $controller->buscar($id)
                : $controller->listar();
            break;

        case 'POST':
            $dados = json_decode(file_get_contents('php://input'), true) ?? [];
            $resultado = $controller->criar($dados);
            break;

        case 'PUT':
            if ($id === null) {
                $resultado = ['sucesso' => false, 'erro' => 'Informe o id do chamado (?id=).', 'status' => 400];
                break;
            }
            $dados = json_decode(file_get_contents('php://input'), true) ?? [];
            $resultado = $controller->atualizarStatus($id, $dados);
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
