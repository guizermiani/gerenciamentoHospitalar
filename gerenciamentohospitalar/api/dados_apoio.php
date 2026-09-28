<?php
/**
 * Endpoint somente leitura com as listas usadas nos selects das telas.
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../model/ApoioModel.php';
require_once __DIR__ . '/../model/FuncionarioModel.php';

try {
    $apoio = (new ApoioModel($conexao))->tudo();
    // Só técnicos e admins podem ser responsáveis por chamados.
    $apoio['responsaveis'] = (new FuncionarioModel($conexao))->listarResponsaveis();

    responderJson(['sucesso' => true, 'dados' => $apoio, 'status' => 200]);
} catch (Throwable $e) {
    responderJson(['sucesso' => false, 'erro' => 'Erro interno: ' . $e->getMessage(), 'status' => 500]);
}
