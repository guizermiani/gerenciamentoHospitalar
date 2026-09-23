<?php
/**
 * Guarda de autenticação — inclua no topo de qualquer endpoint que exigir login.
 * Espera que config/conexao.php já tenha sido incluído antes (usa $conexao).
 */
require_once __DIR__ . '/../controller/AuthController.php';

$auth   = new AuthController($conexao);
$sessao = $auth->sessaoAtual();

if (!$sessao['sucesso']) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'erro' => 'Autenticação necessária.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$usuarioLogado = $sessao['dados']; // ['id_funcionario' => ..., 'nome' => ..., 'tipo_usuario' => ...]
