<?php
/**
 * Model de Chamado.
 * Lê e escreve a tabela chamado. Não decide regras de negócio (isso é do Controller).
 */
class ChamadoModel
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    /**
     * SELECT compartilhado por listar() e buscarPorId().
     * As colunas reais da tabela são id_status_chamado e id_categoria_chamado;
     * aqui elas ganham alias (id_status / id_categoria) para o restante do sistema.
     */
    private function selectBase(): string
    {
        return "SELECT
                    c.id_chamado, c.titulo, c.descricao, c.data_abertura, c.data_fechamento,
                    c.id_status_chamado AS id_status, s.descricao AS status,
                    c.id_prioridade, p.descricao AS prioridade,
                    c.id_setor, st.nome_setor AS setor,
                    c.id_categoria_chamado AS id_categoria, cat.nome_categoria AS categoria,
                    c.id_funcionario_abertura, fa.nome AS aberto_por,
                    c.id_funcionario_responsavel, fr.nome AS responsavel,
                    c.id_paciente, pac.nome AS paciente
                FROM chamado c
                JOIN status_chamado s      ON s.id_status = c.id_status_chamado
                JOIN prioridade p          ON p.id_prioridade = c.id_prioridade
                JOIN setor st              ON st.id_setor = c.id_setor
                JOIN categoria_chamado cat ON cat.id_categoria = c.id_categoria_chamado
                JOIN funcionario fa        ON fa.id_funcionario = c.id_funcionario_abertura
                LEFT JOIN funcionario fr   ON fr.id_funcionario = c.id_funcionario_responsavel
                LEFT JOIN paciente pac     ON pac.id_paciente = c.id_paciente";
    }

    /**
     * Lista chamados com filtros opcionais:
     * id_status, id_prioridade, id_setor, id_categoria, busca (título/descrição/id),
     * somente_abertos_por (id do funcionário — usado para restringir o atendente),
     * responsavel (id do funcionário responsável).
     */
    public function listar(array $filtros = []): array
    {
        $where  = [];
        $params = [];

        if (!empty($filtros['id_status'])) {
            $where[] = 'c.id_status_chamado = :id_status';
            $params['id_status'] = (int) $filtros['id_status'];
        }
        if (!empty($filtros['id_prioridade'])) {
            $where[] = 'c.id_prioridade = :id_prioridade';
            $params['id_prioridade'] = (int) $filtros['id_prioridade'];
        }
        if (!empty($filtros['id_setor'])) {
            $where[] = 'c.id_setor = :id_setor';
            $params['id_setor'] = (int) $filtros['id_setor'];
        }
        if (!empty($filtros['id_categoria'])) {
            $where[] = 'c.id_categoria_chamado = :id_categoria';
            $params['id_categoria'] = (int) $filtros['id_categoria'];
        }
        if (!empty($filtros['responsavel'])) {
            $where[] = 'c.id_funcionario_responsavel = :responsavel';
            $params['responsavel'] = (int) $filtros['responsavel'];
        }
        if (!empty($filtros['somente_abertos_por'])) {
            $where[] = 'c.id_funcionario_abertura = :aberto_por';
            $params['aberto_por'] = (int) $filtros['somente_abertos_por'];
        }
        if (!empty($filtros['busca'])) {
            $where[] = '(c.titulo LIKE :busca1 OR c.descricao LIKE :busca2 OR c.id_chamado = :busca_id)';
            $params['busca1']   = '%' . $filtros['busca'] . '%';
            $params['busca2']   = '%' . $filtros['busca'] . '%';
            $params['busca_id'] = (int) $filtros['busca'];
        }

        $sql = $this->selectBase();
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY c.data_abertura DESC, c.id_chamado DESC';

        $stmt = $this->conexao->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conexao->prepare($this->selectBase() . ' WHERE c.id_chamado = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Todo chamado nasce Aberto (status 1) e sem responsável. */
    public function criar(array $d): int
    {
        $stmt = $this->conexao->prepare(
            "INSERT INTO chamado
                (titulo, descricao, id_setor, id_categoria_chamado, id_prioridade,
                 id_funcionario_abertura, id_paciente, id_status_chamado)
             VALUES
                (:titulo, :descricao, :id_setor, :id_categoria, :id_prioridade,
                 :id_funcionario_abertura, :id_paciente, 1)"
        );
        $stmt->execute([
            'titulo'                  => $d['titulo'],
            'descricao'               => $d['descricao'],
            'id_setor'                => $d['id_setor'],
            'id_categoria'            => $d['id_categoria'],
            'id_prioridade'           => $d['id_prioridade'],
            'id_funcionario_abertura' => $d['id_funcionario_abertura'],
            'id_paciente'             => $d['id_paciente'],
        ]);
        return (int) $this->conexao->lastInsertId();
    }

    /** Grava status, responsável e data de fechamento (já decididos pelo Controller). */
    public function atualizarAndamento(int $id, int $idStatus, ?int $idResponsavel, ?string $dataFechamento): void
    {
        $stmt = $this->conexao->prepare(
            "UPDATE chamado
             SET id_status_chamado = :id_status,
                 id_funcionario_responsavel = :id_responsavel,
                 data_fechamento = :data_fechamento
             WHERE id_chamado = :id"
        );
        $stmt->execute([
            'id'              => $id,
            'id_status'       => $idStatus,
            'id_responsavel'  => $idResponsavel,
            'data_fechamento' => $dataFechamento,
        ]);
    }
}
