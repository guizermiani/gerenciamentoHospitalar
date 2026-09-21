<?php
require_once __DIR__ . '/../model/ChamadoModel.php';

class ChamadoController
{
    private ChamadoModel $model;

    public function __construct(PDO $conexao)
    {
        $this->model = new ChamadoModel($conexao);
    }

    public function listar(): array
    {
        return [
            'sucesso' => true,
            'dados'   => $this->model->listar(),
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
        $titulo = trim($dados['titulo'] ?? '');
        $descricao = trim($dados['descricao'] ?? '');
        $idSetor = (int) ($dados['id_setor'] ?? 0);
        $idCategoria = (int) ($dados['id_categoria'] ?? 0);
        $idPrioridade = (int) ($dados['id_prioridade'] ?? 0);
        $idUsuarioAbertura = (int) ($dados['id_usuario_abertura'] ?? 0);

        if ($titulo === '' || $descricao === '' || $idSetor <= 0 || $idCategoria <= 0 || $idPrioridade <= 0 || $idUsuarioAbertura <= 0) {
            return [
                'sucesso' => false,
                'erro'    => 'Campos obrigatórios: título, descrição, setor, categoria, prioridade e usuário de abertura.',
                'status'  => 400,
            ];
        }

        $id = $this->model->criar([
            'titulo'               => $titulo,
            'descricao'            => $descricao,
            'id_setor'             => $idSetor,
            'id_categoria'         => $idCategoria,
            'id_prioridade'        => $idPrioridade,
            'id_usuario_abertura'  => $idUsuarioAbertura,
            'id_paciente'          => !empty($dados['id_paciente']) ? (int) $dados['id_paciente'] : null,
        ]);

        return [
            'sucesso' => true,
            'dados'   => $this->model->buscarPorId($id),
            'status'  => 201,
        ];
    }

    public function atualizar(int $id, array $dados): array
    {
        $existente = $this->model->buscarPorId($id);
        if ($existente === null) {
            return [
                'sucesso' => false,
                'erro'    => 'Chamado não encontrado.',
                'status'  => 404,
            ];
        }

        $titulo = trim($dados['titulo'] ?? $existente['titulo']);
        $descricao = trim($dados['descricao'] ?? $existente['descricao']);
        $idSetor = (int) ($dados['id_setor'] ?? $existente['id_setor']);
        $idCategoria = (int) ($dados['id_categoria'] ?? $existente['id_categoria']);
        $idPrioridade = (int) ($dados['id_prioridade'] ?? $existente['id_prioridade']);
        $idStatus = (int) ($dados['id_status'] ?? $existente['id_status']);
        $idResponsavel = isset($dados['id_usuario_responsavel']) && $dados['id_usuario_responsavel'] !== '' ? (int) $dados['id_usuario_responsavel'] : $existente['id_usuario_responsavel'];

        $dataFechamento = $existente['data_fechamento'];
        if (in_array($idStatus, [3, 4], true) && empty($dataFechamento)) {
            $dataFechamento = date('Y-m-d H:i:s');
        } elseif (!in_array($idStatus, [3, 4], true)) {
            $dataFechamento = null;
        }

        $this->model->atualizar($id, [
            'titulo'                     => $titulo,
            'descricao'                  => $descricao,
            'id_setor'                   => $idSetor,
            'id_categoria'               => $idCategoria,
            'id_prioridade'              => $idPrioridade,
            'id_status'                  => $idStatus,
            'id_usuario_responsavel'     => $idResponsavel,
            'data_fechamento'            => $dataFechamento,
        ]);

        return [
            'sucesso' => true,
            'dados'   => $this->model->buscarPorId($id),
        ];
    }

    public function excluir(int $id): array
    {
        $existente = $this->model->buscarPorId($id);
        if ($existente === null) {
            return [
                'sucesso' => false,
                'erro'    => 'Chamado não encontrado.',
                'status'  => 404,
            ];
        }

        $this->model->excluir($id);

        return [
            'sucesso'  => true,
            'mensagem' => 'Chamado excluído com sucesso.',
        ];
    }
}
