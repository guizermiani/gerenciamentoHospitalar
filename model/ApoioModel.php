<?php
/**
 * Model de dados de apoio (listas usadas em selects) e pacientes.
 */
class ApoioModel
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function tudo(): array
    {
        return [
            'setores'      => $this->conexao->query("SELECT id_setor, nome_setor FROM setor ORDER BY nome_setor")->fetchAll(),
            'categorias'   => $this->conexao->query("SELECT id_categoria, nome_categoria FROM categoria_chamado ORDER BY nome_categoria")->fetchAll(),
            'prioridades'  => $this->conexao->query("SELECT id_prioridade, descricao FROM prioridade ORDER BY id_prioridade")->fetchAll(),
            'status'       => $this->conexao->query("SELECT id_status, descricao FROM status_chamado ORDER BY id_status")->fetchAll(),
            'pacientes'    => $this->conexao->query("SELECT id_paciente, nome FROM paciente ORDER BY nome")->fetchAll(),
        ];
    }

    public function pacienteExiste(int $id): bool
    {
        $stmt = $this->conexao->prepare("SELECT COUNT(*) FROM paciente WHERE id_paciente = :id");
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function existe(string $tabela, string $coluna, int $id): bool
    {
        $permitidos = [
            'setor'              => 'id_setor',
            'categoria_chamado'  => 'id_categoria',
            'prioridade'         => 'id_prioridade',
        ];
        if (($permitidos[$tabela] ?? null) !== $coluna) {
            throw new InvalidArgumentException('Tabela/coluna inválida.');
        }
        $stmt = $this->conexao->prepare("SELECT COUNT(*) FROM {$tabela} WHERE {$coluna} = :id");
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
