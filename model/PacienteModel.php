<?php
class PacienteModel
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function listar(): array
    {
        $sql = "SELECT id_paciente, nome, cpf, data_nascimento, telefone, email
                FROM paciente
                ORDER BY nome";
        $stmt = $this->conexao->query($sql);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT id_paciente, nome, cpf, data_nascimento, telefone, email
                FROM paciente
                WHERE id_paciente = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(['id' => $id]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    public function buscarPorCpf(string $cpf): ?array
    {
        $sql = "SELECT id_paciente, nome, cpf, data_nascimento, telefone, email
                FROM paciente
                WHERE cpf = :cpf";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(['cpf' => $cpf]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    public function criar(array $dados): int
    {
        $sql = "INSERT INTO paciente (nome, cpf, data_nascimento, telefone, email)
                VALUES (:nome, :cpf, :data_nascimento, :telefone, :email)";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            'nome' => $dados['nome'],
            'cpf' => !empty($dados['cpf']) ? $dados['cpf'] : null,
            'data_nascimento' => !empty($dados['data_nascimento']) ? $dados['data_nascimento'] : null,
            'telefone' => !empty($dados['telefone']) ? $dados['telefone'] : null,
            'email' => !empty($dados['email']) ? $dados['email'] : null,
        ]);
        return (int) $this->conexao->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $sql = "UPDATE paciente
                SET nome = :nome,
                    cpf = :cpf,
                    data_nascimento = :data_nascimento,
                    telefone = :telefone,
                    email = :email
                WHERE id_paciente = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'nome' => $dados['nome'],
            'cpf' => !empty($dados['cpf']) ? $dados['cpf'] : null,
            'data_nascimento' => !empty($dados['data_nascimento']) ? $dados['data_nascimento'] : null,
            'telefone' => !empty($dados['telefone']) ? $dados['telefone'] : null,
            'email' => !empty($dados['email']) ? $dados['email'] : null,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function excluir(int $id): bool
    {
        $sql = "DELETE FROM paciente WHERE id_paciente = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
