<?php
/**
 * Model de Chamado.
 */
class ChamadoModel
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    /**
     * Campos usados tanto em listar() quanto em buscarPorId(),
     * pra não repetir o mesmo SELECT gigante duas vezes.
     */
    private function selectBase(): string
    {
        return "SELECT
                    c.id_chamado, c.titulo, c.descricao, c.data_abertura, c.data_fechamento,
                    c.id_status, s.descricao AS status,
                    c.id_prioridade, p.descricao AS prioridade,
                    c.id_setor, st.nome_setor AS setor,
                    c.id_categoria, cat.nome_categoria AS categoria,
                    c.id_funcionario_abertura, fa.nome AS aberto_por,
                    c.id_funcionario_responsavel, fr.nome AS responsavel,
                    c.id_paciente, pac.nome AS paciente
                FROM chamado c
                JOIN status_chamado s      ON s.id_status = c.id_status
                JOIN prioridade p          ON p.id_prioridade = c.id_prioridade
                JOIN setor st              ON st.id_setor = c.id_setor
                JOIN categoria_chamado cat ON cat.id_categoria = c.id_categoria
                JOIN funcionario fa        ON fa.id_funcionario = c.id_funcionario_abertura
                LEFT JOIN funcionario fr   ON fr.id_funcionario = c.id_funcionario_responsavel
                LEFT JOIN paciente pac     ON pac.id_paciente = c.id_paciente";
    }

    public function listar(): array
    {
        $sql = $this->selectBase() . " ORDER BY c.data_abertura DESC";
        $stmt = $this->conexao->query($sql);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = $this->selectBase() . " WHERE c.id_chamado = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(['id' => $id]);
        $resultado = $stmt->fetch();x
        return $resultado ?: null;
    }

    /**
     * Cria um chamado novo. Sempre nasce com id_status = 1 (Aberto)
     * e sem funcionário responsável definido ainda.
     */
    public function criar(array $dados): int
    {
        $sql = "INSERT INTO chamado
                    (titulo, descricao, id_setor, id_categoria, id_prioridade,
                     id_funcionario_abertura, id_paciente, id_status)
                VALUES
                    (:titulo, :descricao, :id_setor, :id_categoria, :id_prioridade,
                     :id_funcionario_abertura, :id_paciente, 1)";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            'titulo'                  => $dados['titulo'],
            'descricao'               => $dados['descricao'],
            'id_setor'                => $dados['id_setor'],
            'id_categoria'            => $dados['id_categoria'],
            'id_prioridade'           => $dados['id_prioridade'],
            'id_funcionario_abertura' => $dados['id_funcionario_abertura'],
            'id_paciente'             => $dados['id_paciente'] ?? null,
        ]);
        return (int) $this->conexao->lastInsertId();
    }

    /**
     * Atualiza um chamado existente. Recebe o conjunto de valores já resolvidos
     * pelo Controller (inclusive data_fechamento, quando aplicável) —
     * o Model não decide regra nenhuma, só grava o que mandarem.
     */
    public function atualizar(int $id, array $dados): bool
    {
        $sql = "UPDATE chamado
                SET titulo = :titulo,
                    descricao = :descricao,
                    id_setor = :id_setor,
                    id_categoria = :id_categoria,
                    id_prioridade = :id_prioridade,
                    id_funcionario_responsavel = :id_funcionario_responsavel,
                    id_status = :id_status,
                    data_fechamento = :data_fechamento
                WHERE id_chamado = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            'id'                         => $id,
            'titulo'                     => $dados['titulo'],
            'descricao'                  => $dados['descricao'],
            'id_setor'                   => $dados['id_setor'],
            'id_categoria'               => $dados['id_categoria'],
            'id_prioridade'              => $dados['id_prioridade'],
            'id_funcionario_responsavel' => $dados['id_funcionario_responsavel'],
            'id_status'                  => $dados['id_status'],
            'data_fechamento'            => $dados['data_fechamento'],
        ]);
        return $stmt->rowCount() > 0;
    }

    public function excluir(int $id): bool
    {
        $sql = "DELETE FROM chamado WHERE id_chamado = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}