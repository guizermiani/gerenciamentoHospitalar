<?php
/**
 * Controller de Chamado.
 * Aplica validações e a regra de negócio de fechamento automático de chamado.
 */
require_once __DIR__ . '/../model/ChamadoModel.php';

class ChamadoController
{
    private ChamadoModel $model;

    // IDs da tabela status_chamado, na ordem em que foram inseridos no seed do banco:
    // 1 = Aberto, 2 = Em andamento, 3 = Resolvido, 4 = Cancelado
    private const STATUS_RESOLVIDO = 3;
    private const STATUS_CANCELADO = 4;

    public function __construct(PDO $conexao)
    {
        $this->model = new ChamadoModel($conexao);
    }

    public function listar(): array
    {
        return [
            'sucesso' => true,
            'dados'   => $this->model->Listar(),,
        ];
    }

    public function buscar(int $id): array
    {
        $chamado = $this->model->buscarPorId($id);

        if ($chamado === null) {
            return [
                'sucesso' => false,
                'erro'    => 'Chamado não encontrado.',
                'status'  => 404,
            ];
        }

        return [
            'sucesso' => true,
            'dados'   => $chamado,
        ];
    }

    public function criar(array $dados): array
    {
        $obrigatorios = ['titulo', 'descricao', 'id_setor', 'id_categoria', 'id_prioridade', 'id_funcionario_abertura'];

        foreach ($obrigatorios as $campo) {
            if (empty($dados[$campo])) {
                return [
                    'sucesso' => false,
                    'erro'    => "O campo {$campo} é obrigatório.",
                    'status'  => 400,
                ];
            }
        }

        $id = $this->model->criar([
            'titulo'                  => trim($dados['titulo']),
            'descricao'               => trim($dados['descricao']),
            'id_setor'                => $dados['id_setor'],
            'id_categoria'            => $dados['id_categoria'],
            'id_prioridade'           => $dados['id_prioridade'],
            'id_funcionario_abertura' => $dados['id_funcionario_abertura'],
            'id_paciente'             => $dados['id_paciente'] ?? null,
        ]);

        return [
            'sucesso' => true,
            'dados'   => $this->model->buscarPorId($id),
            'status'  => 201,
        ];
    }

    /**
     * Muda o status (e opcionalmente o responsável) de um chamado.
     * Se o novo status for Resolvido/Cancelado, grava data_fechamento = agora.
     * Se o chamado for reaberto (voltar pra Aberto/Em andamento), limpa a data.
     */
    public function atualizarStatus(int $id, array $dados): array
    {
        $chamado = $this->model->buscarPorId($id);
        if ($chamado === null) {
            return [
                'sucesso' => false,
                'erro'    => 'Chamado não encontrado.',
                'status'  => 404,
            ];
        }

        if (empty($dados['id_status'])) {
            return [
                'sucesso' => false,
                'erro'    => 'O campo id_status é obrigatório.',
                'status'  => 400,
            ];
        }

        $idStatus      = (int) $dados['id_status'];
        // Se não vier um novo responsável, mantém o que já estava salvo
        $idResponsavel = $dados['id_funcionario_responsavel'] ?? $chamado['id_funcionario_responsavel'];

        $fechando       = in_array($idStatus, [self::STATUS_RESOLVIDO, self::STATUS_CANCELADO], true);
        $dataFechamento = $fechando ? date('Y-m-d H:i:s') : null;

        $this->model->atualizar($id, [
            'titulo'                     => $chamado['titulo'],
            'descricao'                  => $chamado['descricao'],
            'id_setor'                   => $chamado['id_setor'],
            'id_categoria'               => $chamado['id_categoria'],
            'id_prioridade'              => $chamado['id_prioridade'],
            'id_funcionario_responsavel' => $idResponsavel,
            'id_status'                  => $idStatus,
            'data_fechamento'            => $dataFechamento,
        ]);

        return [
            'sucesso' => true,
            'dados'   => $this->model->buscarPorId($id),
        ];
    }
}