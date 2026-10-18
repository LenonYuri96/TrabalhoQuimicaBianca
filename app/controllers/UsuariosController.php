<?php
require_once __DIR__ . '/../models/Usuario.php';

class UsuariosController
{
    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Listagem dos instrutores dentro do painel
     */
    public function index()
    {
        $usuarios = $this->usuarioModel->listarTodos();
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/usuarios/index.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    /**
     * Formulário de criação interno
     */
    public function create()
    {
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/usuarios/create.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    /**
     * Processamento da gravação do instrutor
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=usuarios');
            exit;
        }

        $nome  = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $conf  = $_POST['confirmar_senha'] ?? '';

        if (empty($nome) || empty($email) || empty($senha)) {
            $_SESSION['flash_error'] = 'Preencha todos os campos obrigatórios.';
            header('Location: index.php?route=usuarios&action=create');
            exit;
        }

        if (!Usuario::validarEmailFiemg($email)) {
            $_SESSION['flash_error'] = 'O e-mail deve pertencer ao domínio @fiemg.com.br.';
            header('Location: index.php?route=usuarios&action=create');
            exit;
        }

        if ($senha !== $conf) {
            $_SESSION['flash_error'] = 'As senhas informadas não coincidem.';
            header('Location: index.php?route=usuarios&action=create');
            exit;
        }

        if (strlen($senha) < 6) {
            $_SESSION['flash_error'] = 'A senha deve conter no mínimo 6 caracteres.';
            header('Location: index.php?route=usuarios&action=create');
            exit;
        }

        if ($this->usuarioModel->emailExiste($email)) {
            $_SESSION['flash_error'] = 'Este e-mail institucional já está cadastrado.';
            header('Location: index.php?route=usuarios&action=create');
            exit;
        }

        $this->usuarioModel->cadastrar($nome, $email, $senha, 'instrutor');
        $_SESSION['flash_success'] = 'Instrutor cadastrado com sucesso!';
        header('Location: index.php?route=usuarios');
        exit;
    }
}