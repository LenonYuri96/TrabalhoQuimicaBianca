<?php
require_once __DIR__ . '/../config/Database.php';

class Usuario
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Validação estrita do domínio institucional @fiemg.com.br
     */
    public static function validarEmailFiemg(string $email): bool
    {
        $email = trim(strtolower($email));
        return (bool) preg_match('/^[a-zA-Z0-9._%+-]+@fiemg\.com\.br$/', $email);
    }

    /**
     * Verifica se o e-mail já existe no banco
     */
    public function emailExiste(string $email): bool
    {
        $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $stmt->execute([trim($email)]);
        return (bool) $stmt->fetch();
    }

    /**
     * Cadastra um novo instrutor no sistema
     */
    public function cadastrar(string $nome, string $email, string $senha, string $perfil = 'instrutor'): bool
    {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO usuarios (nome, email, senha, perfil, ativo) VALUES (?, ?, ?, ?, 1)");
        return $stmt->execute([
            trim($nome),
            trim(strtolower($email)),
            $senhaHash,
            $perfil
        ]);
    }

    /**
     * Busca usuário ativo por e-mail para autenticação
     */
    public function buscarPorEmail(string $email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE LOWER(email) = LOWER(?) AND ativo = 1 LIMIT 1");
        $stmt->execute([trim($email)]);
        return $stmt->fetch();
    }

    /**
     * Lista todos os instrutores cadastrados
     */
    public function listarTodos(): array
    {
        $stmt = $this->conn->query("SELECT id, nome, email, perfil, ativo, created_at FROM usuarios ORDER BY nome ASC");
        return $stmt->fetchAll();
    }
}