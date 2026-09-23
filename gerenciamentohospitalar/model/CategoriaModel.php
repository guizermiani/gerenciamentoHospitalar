<?php
/**
 * Model de Categoria.
 * Responsável por toda a leitura e escrita da tabela categoria_chamado.
 * Nenhuma outra parte do sistema deve executar SQL diretamente nessa tabela.
 */
class CategoriaModel
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    /**
     * Retorna todas as categorias cadastradas.
     */
    public function listar(): array
    {
        $sql = "SELECT id_categoria, nome_categoria, descricao
                FROM categoria_chamado
                ORDER BY nome_categoria";
        $stmt = $this->conexao->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Retorna uma única categoria pelo id, ou null se não existir.
     */
    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT id_categoria, nome_categoria, descricao
                FROM categoria_chamado
                WHERE id_categoria = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(['id' => $id]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    /**
     * Insere uma nova categoria. Retorna o id gerado.
     */
    public function criar(string $nome, ?string $descricao): int
    {
        $sql = "INSERT INTO categoria_chamado (nome_categoria, descricao)
                VALUES (:nome, :descricao)";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            'nome'      => $nome,
            'descricao' => $descricao,
        ]);
        return (int) $this->conexao->lastInsertId();
    }

    /**
     * Atualiza uma categoria existente. Retorna true se alguma linha foi alterada.
     */
    public function atualizar(int $id, string $nome, ?string $descricao): bool
    {
        $sql = "UPDATE categoria_chamado
                SET nome_categoria = :nome, descricao = :descricao
                WHERE id_categoria = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            'id'        => $id,
            'nome'      => $nome,
            'descricao' => $descricao,
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Remove uma categoria. Retorna true se alguma linha foi apagada.
     */
    public function excluir(int $id): bool
    {
        $sql = "DELETE FROM categoria_chamado WHERE id_categoria = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
