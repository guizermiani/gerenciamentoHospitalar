<?php
/**
 * API REST de Setor. Leitura para qualquer usuário logado; escrita somente administrador.
 *
 *   GET  /api/setores.php        -> lista
 *   GET  /api/setores.php?id=1   -> busca um
 *   POST /api/setores.php        -> cria (JSON)
 *   PUT  /api/setores.php?id=1   -> atualiza (JSON)
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../controller/SetorController.php';

$controller = new SetorController($conexao);

$metodo = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

try {
    switch ($metodo) {
        case 'GET':
            $resultado = $id !== null ? $controller->buscar($id) : $controller->listar();
            break;

        case 'POST':
            exigirPerfil(['admin']);
            $dados = json_decode(file_get_contents('php://input'), true) ?? [];
            $resultado = $controller->criar($dados);
            break;

        case 'PUT':
            exigirPerfil(['admin']);
            if ($id === null) {
                $resultado = ['sucesso' => false, 'erro' => 'Informe o id do setor (?id=).', 'status' => 400];
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
