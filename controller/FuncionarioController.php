<?php
/**
 * Controller de Funcionário (cadastro de usuários — somente administrador).
 */
require_once __DIR__ . '/../model/FuncionarioModel.php';
require_once __DIR__ . '/../model/ApoioModel.php';

class FuncionarioController
{
    private FuncionarioModel $model;
    private ApoioModel $apoio;

    public function __construct(PDO $conexao)
    {
        $this->model = new FuncionarioModel($conexao);
        $this->apoio = new ApoioModel($conexao);
    }

    public function listar(): array
    {
        return ['sucesso' => true, 'dados' => $this->model->listar()];
    }

    public function buscar(int $id): array
    {
        $f = $this->model->buscarPorId($id);
        if ($f === null) {
            return ['sucesso' => false, 'erro' => 'Funcionário não encontrado.', 'status' => 404];
        }
        return ['sucesso' => true, 'dados' => $f];
    }

    /** Valida e normaliza os dados. Retorna [dados, erro]. */
    private function validar(array $d, bool $senhaObrigatoria, ?int $idAtual): array
    {
        $r = [
            'nome'         => trim((string) ($d['nome'] ?? '')),
            'cpf'          => trim((string) ($d['cpf'] ?? '')),
            'matricula'    => trim((string) ($d['matricula'] ?? '')),
            'cargo'        => trim((string) ($d['cargo'] ?? '')),
            'telefone'     => trim((string) ($d['telefone'] ?? '')) ?: null,
            'id_setor'     => (int) ($d['id_setor'] ?? 0),
            'email'        => strtolower(trim((string) ($d['email'] ?? ''))),
            'tipo_usuario' => (string) ($d['tipo_usuario'] ?? ''),
            'senha'        => (string) ($d['senha'] ?? ''),
        ];

        foreach (['nome', 'cpf', 'matricula', 'cargo', 'email'] as $campo) {
            if ($r[$campo] === '') {
                return [null, "O campo {$campo} é obrigatório."];
            }
        }
        if (!filter_var($r['email'], FILTER_VALIDATE_EMAIL)) {
            return [null, 'E-mail inválido.'];
        }
        if (!in_array($r['tipo_usuario'], ['atendente', 'tecnico', 'admin'], true)) {
            return [null, 'Tipo de usuário inválido.'];
        }
        if (!$this->apoio->existe('setor', 'id_setor', $r['id_setor'])) {
            return [null, 'Setor inválido.'];
        }
        if ($senhaObrigatoria && $r['senha'] === '') {
            return [null, 'A senha é obrigatória.'];
        }
        if ($r['senha'] !== '' && strlen($r['senha']) < 6) {
            return [null, 'A senha deve ter pelo menos 6 caracteres.'];
        }
        foreach (['cpf' => 'CPF', 'matricula' => 'matrícula', 'email' => 'e-mail'] as $campo => $rotulo) {
            if ($this->model->campoDuplicado($campo, $r[$campo], $idAtual)) {
                return [null, "Já existe um funcionário com este {$rotulo}."];
            }
        }
        return [$r, null];
    }

    public function criar(array $dados): array
    {
        [$d, $erro] = $this->validar($dados, true, null);
        if ($erro !== null) {
            return ['sucesso' => false, 'erro' => $erro, 'status' => 400];
        }
        $id = $this->model->criar($d);
        return ['sucesso' => true, 'dados' => $this->model->buscarPorId($id), 'status' => 201];
    }

    public function atualizar(int $id, array $dados, int $idLogado): array
    {
        if ($this->model->buscarPorId($id) === null) {
            return ['sucesso' => false, 'erro' => 'Funcionário não encontrado.', 'status' => 404];
        }
        [$d, $erro] = $this->validar($dados, false, $id);
        if ($erro !== null) {
            return ['sucesso' => false, 'erro' => $erro, 'status' => 400];
        }
        // Evita o admin se rebaixar sem querer e ficar sem acesso ao cadastro.
        if ($id === $idLogado && $d['tipo_usuario'] !== 'admin') {
            return ['sucesso' => false, 'erro' => 'Você não pode remover seu próprio perfil de administrador.', 'status' => 422];
        }
        $this->model->atualizar($id, $d);
        return ['sucesso' => true, 'dados' => $this->model->buscarPorId($id)];
    }
}
