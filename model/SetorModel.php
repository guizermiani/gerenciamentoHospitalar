<?php
class SetorModel
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function listar(): array
    {
        $sql = "SELECT id_setor, nome_setor, descricao
                FROM setor
                ORDER BY nome_setor";
        $stmt = $this->conexao->query($sql);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT id_setor, nome_setor, descricao
                FROM setor
                WHERE id_setor = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(['id' => $id]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    public function criar(string $nome, ?string $descricao): int
    {
        $sql = "INSERT INTO setor (nome_setor, descricao)
                VALUES (:nome, :descricao)";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            'nome' => $nome,
            'descricao' => $descricao,
        ]);
        return (int) $this->conexao->lastInsertId();
    }

    public function atualizar(int $id, string $nome, ?string $descricao): bool
    {
        $sql = "UPDATE setor
                SET nome_setor = :nome, descricao = :descricao
                WHERE id_setor = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'nome' => $nome,
            'descricao' => $descricao,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function excluir(int $id): bool
    {
        $sql = "DELETE FROM setor WHERE id_setor = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
