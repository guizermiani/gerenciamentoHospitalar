<?php
/**
 * API REST de Andamentos (histórico do chamado).
 *
 *   GET  /api/andamentos.php?id_chamado=1  -> lista os andamentos do chamado
 *   POST /api/andamentos.php?id_chamado=1  -> registra um andamento (corpo: {"comentario": "..."})
 */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../controller/ChamadoController.php';

$controller = new ChamadoController($conexao, $usuarioLogado);

$metodo    = $_SERVER['REQUEST_METHOD'];
$idChamado = isset($_GET['id_chamado']) ? (int) $_GET['id_chamado'] : 0;

try {
    if ($idChamado <= 0) {
        $resultado = ['sucesso' => false, 'erro' => 'Informe o id_chamado.', 'status' => 400];
    } elseif ($metodo === 'GET') {
        $detalhe = $controller->buscar($idChamado);
        $resultado = $detalhe['sucesso']
            ? ['sucesso' => true, 'dados' => $detalhe['dados']['andamentos']]
            : $detalhe;
    } elseif ($metodo === 'POST') {
        $dados = json_decode(file_get_contents('php://input'), true) ?? [];
        $resultado = $controller->registrarAndamento($idChamado, $dados);
    } else {
        $resultado = ['sucesso' => false, 'erro' => 'Método não permitido.', 'status' => 405];
    }
} catch (Throwable $e) {
    $resultado = ['sucesso' => false, 'erro' => 'Erro interno: ' . $e->getMessage(), 'status' => 500];
}

responderJson($resultado);
