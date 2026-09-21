<?php
require_once __DIR__ . '/../model/PacienteModel.php';

class PacienteController
{
    private PacienteModel $model;

    public function __construct(PDO $conexao)
    {
        $this->model = new PacienteModel($conexao);
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
        $paciente = $this->model->buscarPorId($id);
        if ($paciente === null) {
            return [
                'sucesso' => false,
                'erro'    => 'Paciente não encontrado.',
                'status'  => 404,
            ];
        }
        return [
            'sucesso' => true,
            'dados'   => $paciente,
        ];
    }

    public function criar(array $dados): array
    {
        $nome = trim($dados['nome'] ?? '');
        $cpf = trim($dados['cpf'] ?? '');

        if ($nome === '') {
            return [
                'sucesso' => false,
                'erro'    => 'O nome do paciente é obrigatório.',
                'status'  => 400,
            ];
        }

        if ($cpf !== '') {
            $existente = $this->model->buscarPorCpf($cpf);
            if ($existente !== null) {
                return [
                    'sucesso' => false,
                    'erro'    => 'Este CPF já está cadastrado.',
                    'status'  => 400,
                ];
            }
        }

        $id = $this->model->criar($dados);

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
                'erro'    => 'Paciente não encontrado.',
                'status'  => 404,
            ];
        }

        $nome = trim($dados['nome'] ?? '');
        $cpf = trim($dados['cpf'] ?? '');

        if ($nome === '') {
            return [
                'sucesso' => false,
                'erro'    => 'O nome do paciente é obrigatório.',
                'status'  => 400,
            ];
        }

        if ($cpf !== '') {
            $pacienteComMesmoCpf = $this->model->buscarPorCpf($cpf);
            if ($pacienteComMesmoCpf !== null && (int)$pacienteComMesmoCpf['id_paciente'] !== $id) {
                return [
                    'sucesso' => false,
                    'erro'    => 'Este CPF já pertence a outro paciente.',
                    'status'  => 400,
                ];
            }
        }

        $this->model->atualizar($id, $dados);

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
                'erro'    => 'Paciente não encontrado.',
                'status'  => 404,
            ];
        }

        $this->model->excluir($id);

        return [
            'sucesso'  => true,
            'mensagem' => 'Paciente excluído com sucesso.',
        ];
    }
}
