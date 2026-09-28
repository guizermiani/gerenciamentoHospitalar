<?php
/**
 * Model de Setor.
 */
class SetorModel
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function listar(): array
    {
        return $this->conexao->query(
            "SELECT id_setor, nome_setor, descricao FROM setor ORDER BY nome_setor"
        )->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conexao->prepare(
            "SELECT id_setor, nome_setor, descricao FROM setor WHERE id_setor = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function criar(string $nome, ?string $descricao): int
    {
        $stmt = $this->conexao->prepare(
            "INSERT INTO setor (nome_setor, descricao) VALUES (:nome, :descricao)"
        );
        $stmt->execute(['nome' => $nome, 'descricao' => $descricao]);
        return (int) $this->conexao->lastInsertId();
    }

    public function atualizar(int $id, string $nome, ?string $descricao): void
    {
        $stmt = $this->conexao->prepare(
            "UPDATE setor SET nome_setor = :nome, descricao = :descricao WHERE id_setor = :id"
        );
        $stmt->execute(['id' => $id, 'nome' => $nome, 'descricao' => $descricao]);
    }
}
