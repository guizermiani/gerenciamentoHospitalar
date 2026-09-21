<?php
require_once __DIR__ . '/../model/HistoricoModel.php';

class HistoricoController
{
    private HistoricoModel $model;

    public function __construct(PDO $conexao)
    {
        $this->model = new HistoricoModel($conexao);
    }

    public function listarPorChamado(int $idChamado): array
    {
        return [
            'sucesso' => true,
            'dados'   => $this->model->listarPorChamado($idChamado),
        ];
    }

    public function criar(array $dados): array
    {
        $idChamado = isset($dados['id_chamado']) ? (int) $dados['id_chamado'] : 0;
        $idUsuario = isset($dados['id_usuario']) ? (int) $dados['id_usuario'] : 0;
        $comentario = trim($dados['comentario'] ?? '');

        if ($idChamado <= 0 || $idUsuario <= 0 || $comentario === '') {
            return [
                'sucesso' => false,
                'erro'    => 'Chamado, Usuário e Comentário são obrigatórios.',
                'status'  => 400,
            ];
        }

        $id = $this->model->criar($idChamado, $idUsuario, $comentario);

        return [
            'sucesso' => true,
            'dados'   => [
                'id_historico' => $id,
                'id_chamado'   => $idChamado,
                'id_usuario'   => $idUsuario,
                'comentario'   => $comentario,
                'data_hora'    => date('Y-m-d H:i:s'),
            ],
            'status'  => 201,
        ];
    }
}
