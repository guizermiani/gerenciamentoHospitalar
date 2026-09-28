<?php
/**
 * Model de Funcionário
 */
class FuncionarioModel
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->conexao->prepare(
            "SELECT id_funcionario, nome, email, senha, tipo_usuario
             FROM funcionario WHERE email = :email"
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function listar(): array
    {
        return $this->conexao->query(
            "SELECT f.id_funcionario, f.nome, f.cpf, f.matricula, f.cargo, f.telefone,
                    f.email, f.tipo_usuario, f.id_setor, s.nome_setor AS setor, f.data_cadastro
             FROM funcionario f
             JOIN setor s ON s.id_setor = f.id_setor
             ORDER BY f.nome"
        )->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conexao->prepare(
            "SELECT f.id_funcionario, f.nome, f.cpf, f.matricula, f.cargo, f.telefone,
                    f.email, f.tipo_usuario, f.id_setor, s.nome_setor AS setor, f.data_cadastro
             FROM funcionario f
             JOIN setor s ON s.id_setor = f.id_setor
             WHERE f.id_funcionario = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Lista quem pode ser responsável por chamados (técnicos e admins). */
    public function listarResponsaveis(): array
    {
        return $this->conexao->query(
            "SELECT id_funcionario, nome, tipo_usuario FROM funcionario
             WHERE tipo_usuario IN ('tecnico','admin') ORDER BY nome"
        )->fetchAll();
    }

    /** Verifica se cpf/matrícula/email já existem (ignorando o próprio id, se informado). */
    public function campoDuplicado(string $campo, string $valor, ?int $ignorarId = null): bool
    {
        if (!in_array($campo, ['cpf', 'matricula', 'email'], true)) {
            throw new InvalidArgumentException('Campo inválido.');
        }
        $sql = "SELECT COUNT(*) FROM funcionario WHERE {$campo} = :valor";
        $params = ['valor' => $valor];
        if ($ignorarId !== null) {
            $sql .= " AND id_funcionario <> :id";
            $params['id'] = $ignorarId;
        }
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function criar(array $d): int
    {
        $stmt = $this->conexao->prepare(
            "INSERT INTO funcionario (nome, cpf, matricula, cargo, telefone, id_setor, email, senha, tipo_usuario)
             VALUES (:nome, :cpf, :matricula, :cargo, :telefone, :id_setor, :email, :senha, :tipo_usuario)"
        );
        $stmt->execute([
            'nome'         => $d['nome'],
            'cpf'          => $d['cpf'],
            'matricula'    => $d['matricula'],
            'cargo'        => $d['cargo'],
            'telefone'     => $d['telefone'],
            'id_setor'     => $d['id_setor'],
            'email'        => $d['email'],
            'senha'        => password_hash($d['senha'], PASSWORD_DEFAULT),
            'tipo_usuario' => $d['tipo_usuario'],
        ]);
        return (int) $this->conexao->lastInsertId();
    }

    /** Atualiza dados; a senha só muda se $d['senha'] vier preenchida. */
    public function atualizar(int $id, array $d): void
    {
        $sql = "UPDATE funcionario SET nome = :nome, cpf = :cpf, matricula = :matricula,
                    cargo = :cargo, telefone = :telefone, id_setor = :id_setor,
                    email = :email, tipo_usuario = :tipo_usuario";
        $params = [
            'id'           => $id,
            'nome'         => $d['nome'],
            'cpf'          => $d['cpf'],
            'matricula'    => $d['matricula'],
            'cargo'        => $d['cargo'],
            'telefone'     => $d['telefone'],
            'id_setor'     => $d['id_setor'],
            'email'        => $d['email'],
            'tipo_usuario' => $d['tipo_usuario'],
        ];
        if (!empty($d['senha'])) {
            $sql .= ", senha = :senha";
            $params['senha'] = password_hash($d['senha'], PASSWORD_DEFAULT);
        }
        $sql .= " WHERE id_funcionario = :id";
        $this->conexao->prepare($sql)->execute($params);
    }
}
