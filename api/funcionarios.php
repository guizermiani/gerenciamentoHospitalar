<?php
/**
 * API REST de Funcionário — cadastro de usuários (somente administrador).
 *
 *   GET  /api/funcionarios.php        -> lista
 *   GET  /api/funcionarios.php?id=1   -> busca um
 *   POST /api/funcionarios.php        -> cadastra (JSON)
 *   PUT  /api/funcionarios.php?id=1   -> atualiza (JSON; senha em branco = mantém a atual)
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../controller/FuncionarioController.php';

exigirPerfil(['admin']);

$controller = new FuncionarioController($conexao);

$metodo = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

try {
    switch ($metodo) {
        case 'GET':
            $resultado = $id !== null ? $controller->buscar($id) : $controller->listar();
            break;

        case 'POST':
            $dados = json_decode(file_get_contents('php://input'), true) ?? [];
            $resultado = $controller->criar($dados);
            break;

        case 'PUT':
            if ($id === null) {
                $resultado = ['sucesso' => false, 'erro' => 'Informe o id do funcionário (?id=).', 'status' => 400];
                break;
            }
            $dados = json_decode(file_get_contents('php://input'), true) ?? [];
            $resultado = $controller->atualizar($id, $dados, (int) $usuarioLogado['id_funcionario']);
            break;

        default:
            $resultado = ['sucesso' => false, 'erro' => 'Método não permitido.', 'status' => 405];
            break;
    }
} catch (Throwable $e) {
    $resultado = ['sucesso' => false, 'erro' => 'Erro interno: ' . $e->getMessage(), 'status' => 500];
}

responderJson($resultado);
