<?php
/**
 * Controller de Categoria.
 * Aplica validações e regras de negócio antes/depois de falar com o Model.
 * Não sabe nada sobre HTTP (isso é responsabilidade da camada API).
 */
require_once __DIR__ . '/../model/CategoriaModel.php';

class CategoriaController
{
    private CategoriaModel $model;

    public function __construct(PDO $conexao)
    {
        $this->model = new CategoriaModel($conexao);
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
        $categoria = $this->model->buscarPorId($id);

        if ($categoria === null) {
            return [
                'sucesso' => false,
                'erro'    => 'Categoria não encontrada.',
                'status'  => 404,
            ];
        }

        return [
            'sucesso' => true,
            'dados'   => $categoria,
        ];
    }

    public function criar(array $dados): array
    {
        $nome      = trim($dados['nome_categoria'] ?? '');
        $descricao = isset($dados['descricao']) ? trim($dados['descricao']) : null;

        if ($nome === '') {
            return [
                'sucesso' => false,
                'erro'    => 'O campo nome_categoria é obrigatório.',
                'status'  => 400,
            ];
        }

        $id = $this->model->criar($nome, $descricao);

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
                'erro'    => 'Categoria não encontrada.',
                'status'  => 404,
            ];
        }

        $nome      = trim($dados['nome_categoria'] ?? '');
        $descricao = isset($dados['descricao']) ? trim($dados['descricao']) : null;

        if ($nome === '') {
            return [
                'sucesso' => false,
                'erro'    => 'O campo nome_categoria é obrigatório.',
                'status'  => 400,
            ];
        }

        $this->model->atualizar($id, $nome, $descricao);

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
                'erro'    => 'Categoria não encontrada.',
                'status'  => 404,
            ];
        }

        $this->model->excluir($id);

        return [
            'sucesso'   => true,
            'mensagem'  => 'Categoria excluída com sucesso.',
        ];
    }
}