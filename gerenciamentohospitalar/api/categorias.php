<?php
/**
 * API REST de Categoria.
 * Endpoint único que roteia pelo verbo HTTP (GET, POST, PUT, DELETE).
 *
 *   GET    /api/categorias.php           -> lista todas
 *   GET    /api/categorias.php?id=1      -> busca uma
 *   POST   /api/categorias.php           -> cria (corpo em JSON)
 *   PUT    /api/categorias.php?id=1      -> atualiza (corpo em JSON)
 *   DELETE /api/categorias.php?id=1      -> exclui
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../controller/CategoriaController.php';

$controller = new CategoriaController($conexao);

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
                $resultado = ['sucesso' => false, 'erro' => 'Informe o id da categoria (?id=).', 'status' => 400];
                break;
            }
            $dados = json_decode(file_get_contents('php://input'), true) ?? [];
            $resultado = $controller->atualizar($id, $dados);
            break;

        case 'DELETE':
            if ($id === null) {
                $resultado = ['sucesso' => false, 'erro' => 'Informe o id da categoria (?id=).', 'status' => 400];
                break;
            }
            $resultado = $controller->excluir($id);
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
