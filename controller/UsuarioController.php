<?php
require_once __DIR__ . '/../model/UsuarioModel.php';

class UsuarioController
{
    private UsuarioModel $model;

    public function __construct(PDO $conexao)
    {
        $this->model = new UsuarioModel($conexao);
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
        $usuario = $this->model->buscarPorId($id);
        if ($usuario === null) {
            return [
                'sucesso' => false,
                'erro'    => 'Usuário não encontrado.',
                'status'  => 404,
            ];
        }
        return [
            'sucesso' => true,
            'dados'   => $usuario,
        ];
    }

    public function criar(array $dados): array
    {
        $nome = trim($dados['nome'] ?? '');
        $email = trim($dados['email'] ?? '');
        $senha = trim($dados['senha'] ?? '');
        $tipo = trim($dados['tipo_usuario'] ?? 'atendente');

        if ($nome === '' || $email === '' || $senha === '') {
            return [
                'sucesso' => false,
                'erro'    => 'Nome, e-mail e senha são obrigatórios.',
                'status'  => 400,
            ];
        }

        if (!in_array($tipo, ['atendente', 'tecnico', 'admin'], true)) {
            return [
                'sucesso' => false,
                'erro'    => 'Tipo de usuário inválido.',
                'status'  => 400,
            ];
        }

        $existente = $this->model->buscarPorEmail($email);
        if ($existente !== null) {
            return [
                'sucesso' => false,
                'erro'    => 'Este e-mail já está cadastrado.',
                'status'  => 400,
            ];
        }

        $id = $this->model->criar([
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'tipo_usuario' => $tipo,
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
                'erro'    => 'Usuário não encontrado.',
                'status'  => 404,
            ];
        }

        $nome = trim($dados['nome'] ?? '');
        $email = trim($dados['email'] ?? '');
        $tipo = trim($dados['tipo_usuario'] ?? $existente['tipo_usuario']);
        $senha = trim($dados['senha'] ?? '');

        if ($nome === '' || $email === '') {
            return [
                'sucesso' => false,
                'erro'    => 'Nome e e-mail são obrigatórios.',
                'status'  => 400,
            ];
        }

        $usuarioComMesmoEmail = $this->model->buscarPorEmail($email);
        if ($usuarioComMesmoEmail !== null && (int)$usuarioComMesmoEmail['id_usuario'] !== $id) {
            return [
                'sucesso' => false,
                'erro'    => 'Este e-mail já está em uso por outro usuário.',
                'status'  => 400,
            ];
        }

        $this->model->atualizar($id, [
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'tipo_usuario' => $tipo,
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
                'erro'    => 'Usuário não encontrado.',
                'status'  => 404,
            ];
        }

        $this->model->excluir($id);

        return [
            'sucesso'  => true,
            'mensagem' => 'Usuário excluído com sucesso.',
        ];
    }
}
