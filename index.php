<?php
// Ativa debug temporário para identificar qualquer falha
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se já estiver logado, redireciona para a aplicação em public
if (isset($_SESSION['usuario_id'])) {
    header('Location: public/index.php');
    exit;
}

require_once __DIR__ . '/app/config/Database.php';
require_once __DIR__ . '/app/models/Usuario.php';

// Inicializa banco de dados e cria tabelas
Database::getConnection();

$erro = '';
$sucesso = '';

if (isset($_GET['cadastrado'])) {
    $sucesso = 'Instrutor cadastrado com sucesso! Acesse com seu e-mail institucional.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (!Usuario::validarEmailFiemg($email)) {
        $erro = 'Acesso restrito! O e-mail deve pertencer ao domínio <strong>@fiemg.com.br</strong>.';
    } elseif (empty($senha)) {
        $erro = 'Por favor, informe sua senha.';
    } else {
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->buscarPorEmail($email);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id']     = $usuario['id'];
            $_SESSION['usuario_nome']   = $usuario['nome'];
            $_SESSION['usuario_email']  = $usuario['email'];
            $_SESSION['usuario_perfil'] = $usuario['perfil'];

            header('Location: public/index.php');
            exit;
        } else {
            $erro = 'Credenciais inválidas. Verifique o e-mail ou a senha informada.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 4.0 - Login do Instrutor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --fiemg-primary: #004b8d;
            --fiemg-dark: #002f5a;
        }
        body {
            background: linear-gradient(135deg, #0b1f3a 0%, #173865 50%, #004b8d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 440px;
            width: 100%;
        }
        .card-header-custom {
            background: linear-gradient(135deg, var(--fiemg-primary) 0%, var(--fiemg-dark) 100%);
            color: #fff;
            padding: 28px 20px;
            text-align: center;
        }
        .btn-fiemg {
            background-color: var(--fiemg-primary);
            border-color: var(--fiemg-primary);
            color: #fff;
            font-weight: 600;
            padding: 12px;
            border-radius: 8px;
        }
        .btn-fiemg:hover {
            background-color: var(--fiemg-dark);
            color: #fff;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="card-header-custom">
        <i class="bi bi-flask fs-1 text-info mb-2 d-block"></i>
        <h4 class="fw-bold mb-1">Lab Control 4.0</h4>
        <p class="mb-0 text-light opacity-75 small">Portal do Instrutor • FIEMG</p>
    </div>

    <div class="p-4">
        <?php if (!empty($sucesso)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= $sucesso ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $erro ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php" id="loginForm">
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold text-secondary">E-mail Institucional</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope-at"></i></span>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        class="form-control" 
                        placeholder="instrutor@fiemg.com.br" 
                        required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >
                </div>
                <div class="form-text text-muted" style="font-size: 0.78rem;">Exigido domínio <code>@fiemg.com.br</code></div>
            </div>

            <div class="mb-4">
                <label for="senha" class="form-label fw-semibold text-secondary">Senha de Acesso</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-shield-lock"></i></span>
                    <input 
                        type="password" 
                        name="senha" 
                        id="senha" 
                        class="form-control" 
                        placeholder="••••••••" 
                        required
                    >
                </div>
            </div>

            <button type="submit" class="btn btn-fiemg w-100 mb-3 shadow-sm">
                <i class="bi bi-box-arrow-in-right me-2"></i> Acessar Sistema
            </button>
        </form>
        
        <div class="text-center pt-2 border-top">
            <span class="text-muted small">Novo instrutor no laboratório?</span><br>
            <a href="cadastro.php" class="btn btn-outline-primary btn-sm rounded-pill mt-2 px-3 fw-semibold">
                <i class="bi bi-person-plus-fill me-1"></i> Criar Conta de Instrutor
            </a>
        </div>
    </div>

    <div class="text-center py-3 bg-light border-0">
        <small class="text-muted">© <?= date('Y') ?> FIEMG - Gestão Laboratorial Inteligente</small>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('loginForm').addEventListener('submit', function (e) {
        const email = document.getElementById('email').value.trim().toLowerCase();
        if (!email.endsWith('@fiemg.com.br')) {
            e.preventDefault();
            alert('Atenção: Apenas contas com e-mail institucional @fiemg.com.br são autorizadas.');
        }
    });
</script>
</body>
</html>