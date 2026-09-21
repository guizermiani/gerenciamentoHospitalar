<?php
/**
 * Endpoint de leitura only, sem Model/Controller de propósito
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/conexao.php';

$setores      = $conexao->query("SELECT id_setor, nome_setor FROM setor ORDER BY nome_setor")->fetchAll();
$categorias   = $conexao->query("SELECT id_categoria, nome_categoria FROM categoria_chamado ORDER BY nome_categoria")->fetchAll();
$prioridades  = $conexao->query("SELECT id_prioridade, descricao FROM prioridade ORDER BY id_prioridade")->fetchAll();
$funcionarios = $conexao->query("SELECT id_funcionario, nome FROM funcionario ORDER BY nome")->fetchAll();
$status       = $conexao->query("SELECT id_status, descricao FROM status_chamado ORDER BY id_status")->fetchAll();

echo json_encode([
    'sucesso' => true,
    'dados'   => [
        'setores'      => $setores,
        'categorias'   => $categorias,
        'prioridades'  => $prioridades,
        'funcionarios' => $funcionarios,
        'status'       => $status,
    ],
], JSON_UNESCAPED_UNICODE);