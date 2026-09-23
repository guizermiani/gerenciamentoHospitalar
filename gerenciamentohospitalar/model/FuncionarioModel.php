<?php
/**
 * Model de Funcionário — por enquanto só o necessário pro login.
 * Um CRUD completo de funcionário fica fora do escopo do MVP.
 */
class FuncionarioModel
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function buscarPorEmail(string $email): ?array
    {
        $sql = "SELECT id_funcionario, nome, email, senha, tipo_usuario
                FROM funcionario
                WHERE email = :email";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(['email' => $email]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }
}
