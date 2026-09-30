<?php
/**
 * Guarda de autenticação e autorização.
 */
require_once __DIR__ . '/../controller/AuthController.php';

function responderJson(array $resultado): void
{
    $status = $resultado['status'] ?? (!empty($resultado['sucesso']) ? 200 : 500);
    unset($resultado['status']);
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    exit;
}

$auth   = new AuthController($conexao);
$sessao = $auth->sessaoAtual();

if (!$sessao['sucesso']) {
    responderJson(['sucesso' => false, 'erro' => 'Autenticação necessária.', 'status' => 401]);
}

$usuarioLogado = $sessao['dados']; // id_funcionario, nome, tipo_usuario

function exigirPerfil(array $perfis): void
{
    global $usuarioLogado;
    if (!in_array($usuarioLogado['tipo_usuario'], $perfis, true)) {
        responderJson(['sucesso' => false, 'erro' => 'Você não tem permissão para esta ação.', 'status' => 403]);
    }
}
