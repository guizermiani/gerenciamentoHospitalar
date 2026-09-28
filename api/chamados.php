<?php
/**
 * API REST de Chamado.
 *
 *   GET  /api/chamados.php                 -> lista (filtros: id_status, id_prioridade, id_setor,
 *                                             id_categoria, busca, meus=1). Atendente só vê os seus.
 *   GET  /api/chamados.php?id=1            -> detalhe + andamentos + ações permitidas
 *   POST /api/chamados.php                 -> abre um novo chamado (JSON)
 *   PUT  /api/chamados.php?id=1            -> muda status/responsável e registra no histórico (JSON)
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../controller/ChamadoController.php';

$controller = new ChamadoController($conexao, $usuarioLogado);

$metodo = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

try {
    switch ($metodo) {
        case 'GET':
            $resultado = $id !== null
                ? $controller->buscar($id)
                : $controller->listar($_GET);
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
            $resultado = $controller->atualizar($id, $dados);
            break;

        default:
            $resultado = ['sucesso' => false, 'erro' => 'Método não permitido.', 'status' => 405];
            break;
    }
} catch (Throwable $e) {
    $resultado = ['sucesso' => false, 'erro' => 'Erro interno: ' . $e->getMessage(), 'status' => 500];
}

responderJson($resultado);
