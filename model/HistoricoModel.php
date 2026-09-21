<?php
class HistoricoModel
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function listarPorChamado(int $idChamado): array
    {
        $sql = "SELECT h.id_historico, h.id_chamado, h.id_usuario, u.nome AS usuario, h.comentario, h.data_hora
                FROM historico_chamado h
                JOIN usuario u ON u.id_usuario = h.id_usuario
                WHERE h.id_chamado = :id_chamado
                ORDER BY h.data_hora ASC";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(['id_chamado' => $idChamado]);
        return $stmt->fetchAll();
    }

    public function criar(int $idChamado, int $idUsuario, string $comentario): int
    {
        $sql = "INSERT INTO historico_chamado (id_chamado, id_usuario, comentario)
                VALUES (:id_chamado, :id_usuario, :comentario)";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            'id_chamado' => $idChamado,
            'id_usuario' => $idUsuario,
            'comentario' => $comentario,
        ]);
        return (int) $this->conexao->lastInsertId();
    }
}
