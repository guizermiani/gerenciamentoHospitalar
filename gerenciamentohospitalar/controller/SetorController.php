<?php
/**
 * Controller de Setor.
 */
require_once __DIR__ . '/../model/SetorModel.php';

class SetorController
{
    private SetorModel $model;

    public function __construct(PDO $conexao)
    {
        $this->model = new SetorModel($conexao);
    }

    public function listar(): array
    {
        return ['sucesso' => true, 'dados' => $this->model->listar()];
    }

    public function buscar(int $id): array
    {
        $s = $this->model->buscarPorId($id);
        if ($s === null) {
            return ['sucesso' => false, 'erro' => 'Setor não encontrado.', 'status' => 404];
        }
        return ['sucesso' => true, 'dados' => $s];
    }

    public function criar(array $dados): array
    {
        $nome      = trim((string) ($dados['nome_setor'] ?? ''));
        $descricao = isset($dados['descricao']) ? trim((string) $dados['descricao']) : null;
        if ($nome === '') {
            return ['sucesso' => false, 'erro' => 'O campo nome_setor é obrigatório.', 'status' => 400];
        }
        $id = $this->model->criar($nome, $descricao);
        return ['sucesso' => true, 'dados' => $this->model->buscarPorId($id), 'status' => 201];
    }

    public function atualizar(int $id, array $dados): array
    {
        if ($this->model->buscarPorId($id) === null) {
            return ['sucesso' => false, 'erro' => 'Setor não encontrado.', 'status' => 404];
        }
        $nome      = trim((string) ($dados['nome_setor'] ?? ''));
        $descricao = isset($dados['descricao']) ? trim((string) $dados['descricao']) : null;
        if ($nome === '') {
            return ['sucesso' => false, 'erro' => 'O campo nome_setor é obrigatório.', 'status' => 400];
        }
        $this->model->atualizar($id, $nome, $descricao);
        return ['sucesso' => true, 'dados' => $this->model->buscarPorId($id)];
    }
}
