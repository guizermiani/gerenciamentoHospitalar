<?php
/**
 * Controller de autenticação.
 * Trata a "sessão" como um recurso: criar (login), apagar (logout), consultar (quem está logado).
 */
require_once __DIR__ . '/../model/FuncionarioModel.php';

class AuthController
{
    private FuncionarioModel $model;

    public function __construct(PDO $conexao)
    {
        $this->model = new FuncionarioModel($conexao);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login(array $dados): array
    {
        $email = trim($dados['email'] ?? '');
        $senha = $dados['senha'] ?? '';

        if ($email === '' || $senha === '') {
            return ['sucesso' => false, 'erro' => 'Informe email e senha.', 'status' => 400];
        }

        $funcionario = $this->model->buscarPorEmail($email);

        if (!$funcionario || !password_verify($senha, $funcionario['senha'])) {
            return ['sucesso' => false, 'erro' => 'Email ou senha incorretos.', 'status' => 401];
        }

        $_SESSION['id_funcionario'] = $funcionario['id_funcionario'];
        $_SESSION['nome']           = $funcionario['nome'];
        $_SESSION['tipo_usuario']   = $funcionario['tipo_usuario'];

        return [
            'sucesso' => true,
            'dados'   => [
                'id_funcionario' => $funcionario['id_funcionario'],
                'nome'           => $funcionario['nome'],
                'tipo_usuario'   => $funcionario['tipo_usuario'],
            ],
        ];
    }

    public function logout(): array
    {
        $_SESSION = [];
        session_destroy();
        return ['sucesso' => true, 'mensagem' => 'Sessão encerrada.'];
    }

    public function sessaoAtual(): array
    {
        if (!isset($_SESSION['id_funcionario'])) {
            return ['sucesso' => false, 'erro' => 'Nenhuma sessão ativa.', 'status' => 401];
        }

        return [
            'sucesso' => true,
            'dados'   => [
                'id_funcionario' => $_SESSION['id_funcionario'],
                'nome'           => $_SESSION['nome'],
                'tipo_usuario'   => $_SESSION['tipo_usuario'],
            ],
        ];
    }
}