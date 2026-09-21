<?php
class UsuarioModel
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function listar(): array
    {
        $sql = "SELECT id_usuario, nome, email, tipo_usuario, data_cadastro
                FROM usuario
                ORDER BY nome";
        $stmt = $this->conexao->query($sql);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT id_usuario, nome, email, tipo_usuario, data_cadastro
                FROM usuario
                WHERE id_usuario = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(['id' => $id]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    public function buscarPorEmail(string $email): ?array
    {
        $sql = "SELECT id_usuario, nome, email, senha, tipo_usuario, data_cadastro
                FROM usuario
                WHERE email = :email";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(['email' => $email]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    public function criar(array $dados): int
    {
        $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuario (nome, email, senha, tipo_usuario)
                VALUES (:nome, :email, :senha, :tipo_usuario)";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'senha' => $senhaHash,
            'tipo_usuario' => $dados['tipo_usuario'] ?? 'atendente',
        ]);
        return (int) $this->conexao->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        if (!empty($dados['senha'])) {
            $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
            $sql = "UPDATE usuario
                    SET nome = :nome, email = :email, senha = :senha, tipo_usuario = :tipo_usuario
                    WHERE id_usuario = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha' => $senhaHash,
                'tipo_usuario' => $dados['tipo_usuario'],
            ]);
        } else {
            $sql = "UPDATE usuario
                    SET nome = :nome, email = :email, tipo_usuario = :tipo_usuario
                    WHERE id_usuario = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'tipo_usuario' => $dados['tipo_usuario'],
            ]);
        }
        return $stmt->rowCount() > 0;
    }

    public function excluir(int $id): bool
    {
        $sql = "DELETE FROM usuario WHERE id_usuario = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
