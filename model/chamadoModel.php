<?php
class ChamadoModel
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    private function selectBase(): string
    {
        return "SELECT
                    c.id_chamado, c.titulo, c.descricao, c.data_abertura, c.data_fechamento,
                    c.id_status, s.descricao AS status,
                    c.id_prioridade, p.descricao AS prioridade,
                    c.id_setor, st.nome_setor AS setor,
                    c.id_categoria, cat.nome_categoria AS categoria,
                    c.id_usuario_abertura, ua.nome AS aberto_por,
                    c.id_usuario_responsavel, ur.nome AS responsavel,
                    c.id_paciente, pac.nome AS paciente
                FROM chamado c
                JOIN status_chamado s ON s.id_status = c.id_status
                JOIN prioridade p ON p.id_prioridade = c.id_prioridade
                JOIN setor st ON st.id_setor = c.id_setor
                JOIN categoria_chamado cat ON cat.id_categoria = c.id_categoria
                JOIN usuario ua ON ua.id_usuario = c.id_usuario_abertura
                LEFT JOIN usuario ur ON ur.id_usuario = c.id_usuario_responsavel
                LEFT JOIN paciente pac ON pac.id_paciente = c.id_paciente";
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
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    public function criar(array $dados): int
    {
        $sql = "INSERT INTO chamado
                    (titulo, descricao, id_setor, id_categoria, id_prioridade,
                     id_usuario_abertura, id_paciente, id_status)
                VALUES
                    (:titulo, :descricao, :id_setor, :id_categoria, :id_prioridade,
                     :id_usuario_abertura, :id_paciente, 1)";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            'titulo' => $dados['titulo'],
            'descricao' => $dados['descricao'],
            'id_setor' => $dados['id_setor'],
            'id_categoria' => $dados['id_categoria'],
            'id_prioridade' => $dados['id_prioridade'],
            'id_usuario_abertura' => $dados['id_usuario_abertura'],
            'id_paciente' => !empty($dados['id_paciente']) ? $dados['id_paciente'] : null,
        ]);
        return (int) $this->conexao->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $sql = "UPDATE chamado
                SET titulo = :titulo,
                    descricao = :descricao,
                    id_setor = :id_setor,
                    id_categoria = :id_categoria,
                    id_prioridade = :id_prioridade,
                    id_usuario_responsavel = :id_usuario_responsavel,
                    id_status = :id_status,
                    data_fechamento = :data_fechamento
                WHERE id_chamado = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'titulo' => $dados['titulo'],
            'descricao' => $dados['descricao'],
            'id_setor' => $dados['id_setor'],
            'id_categoria' => $dados['id_categoria'],
            'id_prioridade' => $dados['id_prioridade'],
            'id_usuario_responsavel' => !empty($dados['id_usuario_responsavel']) ? $dados['id_usuario_responsavel'] : null,
            'id_status' => $dados['id_status'],
            'data_fechamento' => !empty($dados['data_fechamento']) ? $dados['data_fechamento'] : null,
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