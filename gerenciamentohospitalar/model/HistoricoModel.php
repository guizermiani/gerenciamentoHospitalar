<?php
/**
 * Model de Histórico (andamentos) do chamado — tabela historico_chamado.
 */
class HistoricoModel
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function listarPorChamado(int $idChamado): array
    {
        $stmt = $this->conexao->prepare(
            "SELECT h.id_historico, h.id_chamado, h.id_funcionario, f.nome AS funcionario,
                    f.tipo_usuario, h.comentario, h.data_hora
             FROM historico_chamado h
             JOIN funcionario f ON f.id_funcionario = h.id_funcionario
             WHERE h.id_chamado = :id
             ORDER BY h.data_hora ASC, h.id_historico ASC"
        );
        $stmt->execute(['id' => $idChamado]);
        return $stmt->fetchAll();
    }

    public function criar(int $idChamado, int $idFuncionario, string $comentario): int
    {
        $stmt = $this->conexao->prepare(
            "INSERT INTO historico_chamado (id_chamado, id_funcionario, comentario)
             VALUES (:id_chamado, :id_funcionario, :comentario)"
        );
        $stmt->execute([
            'id_chamado'     => $idChamado,
            'id_funcionario' => $idFuncionario,
            'comentario'     => $comentario,
        ]);
        return (int) $this->conexao->lastInsertId();
    }
}
