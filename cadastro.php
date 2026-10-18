<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se já estiver logado, encaminha direto para a aplicação
if (isset($_SESSION['usuario_id'])) {
    header('Location: public/index.php');
    exit;
}

require_once __DIR__ . '/app/config/Database.php';
require_once __DIR__ . '/app/models/Usuario.php';

// Inicializa banco de dados e cria tabelas se necessário
Database::getConnection();

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome             = trim($_POST['nome'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $senha            = $_POST['senha'] ?? '';
    $confirmarSenha   = $_POST['confirmar_senha'] ?? '';

    if (empty($nome) || empty($email) || empty($senha) || empty($confirmarSenha)) {
        $erro = 'Todos os campos são de preenchimento obrigatório.';
    } elseif (!Usuario::validarEmailFiemg($email)) {
        $erro = 'Atenção: É obrigatório o uso de e-mail institucional com domínio <strong>@fiemg.com.br</strong>.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve possuir no mínimo 6 caracteres.';
    } elseif ($senha !== $confirmarSenha) {
        $erro = 'A confirmação de senha não confere.';
    } else {
        $usuarioModel = new Usuario();

        if ($usuarioModel->emailExiste($email)) {
            $erro = 'O e-mail <strong>' . htmlspecialchars($email) . '</strong> já está cadastrado no sistema.';
        } else {
            $salvou = $usuarioModel->cadastrar($nome, $email, $senha, 'instrutor');
            if ($salvou) {
                // Redireciona para o login com mensagem de confirmação
                header('Location: index.php?cadastrado=1');
                exit;
            } else {
                $erro = 'Ocorreu uma falha ao cadastrar. Tente novamente mais tarde.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 4.0 - Cadastro de Instrutor FIEMG</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --fiemg-primary: #004b8d;
            --fiemg-dark: #002f5a;
            --fiemg-accent: #00a3e0;
        }
        body {
            background: linear-gradient(135deg, #0b1f3a 0%, #173865 50%, #004b8d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }
        .register-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            max-width: 480px;
            width: 100%;
            border: none;
        }
        .card-header-custom {
            background: linear-gradient(135deg, var(--fiemg-primary) 0%, var(--fiemg-dark) 100%);
            color: #fff;
            padding: 26px 20px;
            text-align: center;
        }
        .btn-fiemg {
            background-color: var(--fiemg-primary);
            border-color: var(--fiemg-primary);
            color: #fff;
            font-weight: 600;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-fiemg:hover {
            background-color: var(--fiemg-dark);
            border-color: var(--fiemg-dark);
            color: #fff;
            transform: translateY(-1px);
        }
        .form-control:focus {
            border-color: var(--fiemg-accent);
            box-shadow: 0 0 0 0.25rem rgba(0, 163, 224, 0.25);
        }
    </style>
</head>
<body>

<div class="container p-3">
    <div class="register-card mx-auto">
        <div class="card-header-custom">
            <div class="mb-2">
                <i class="bi bi-person-plus-fill fs-1 text-info"></i>
            </div>
            <h4 class="fw-bold mb-1">Novo Instrutor</h4>
            <p class="mb-0 text-light opacity-75 small">Cadastro de Perfil • Domínio Obrigatório @fiemg.com.br</p>
        </div>

        <div class="p-4">
            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-octagon-fill flex-shrink-0 me-2 fs-5"></i>
                    <div><?= $erro ?></div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="cadastro.php" id="formCadastro" novalidate>
                <!-- Nome Completo -->
                <div class="mb-3">
                    <label for="nome" class="form-label fw-semibold text-secondary">Nome Completo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                        <input 
                            type="text" 
                            name="nome" 
                            id="nome" 
                            class="form-control" 
                            placeholder="Ex: Carlos Eduardo Silva" 
                            required 
                            value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                        >
                    </div>
                </div>

                <!-- E-mail Institucional -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold text-secondary">E-mail Institucional (@fiemg.com.br)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope-at"></i></span>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-control" 
                            placeholder="seu.nome@fiemg.com.br" 
                            required
                            pattern="^[a-zA-Z0-9._%+-]+@fiemg\.com\.br$"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        >
                    </div>
                    <div class="form-text text-muted" style="font-size: 0.78rem;">
                        <i class="bi bi-shield-lock-fill text-primary"></i> Apenas e-mails <strong>@fiemg.com.br</strong> têm autorização de acesso.
                    </div>
                </div>

                <!-- Senha -->
                <div class="mb-3">
                    <label for="senha" class="form-label fw-semibold text-secondary">Senha de Acesso</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                        <input 
                            type="password" 
                            name="senha" 
                            id="senha" 
                            class="form-control" 
                            placeholder="Mínimo de 6 caracteres" 
                            minlength="6"
                            required
                        >
                    </div>
                </div>

                <!-- Confirmação de Senha -->
                <div class="mb-4">
                    <label for="confirmar_senha" class="form-label fw-semibold text-secondary">Confirmar Senha</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-check2-circle"></i></span>
                        <input 
                            type="password" 
                            name="confirmar_senha" 
                            id="confirmar_senha" 
                            class="form-control" 
                            placeholder="Repita sua senha" 
                            minlength="6"
                            required
                        >
                    </div>
                </div>

                <!-- Botão de Registro -->
                <button type="submit" class="btn btn-fiemg w-100 mb-3 shadow-sm">
                    <i class="bi bi-check-lg me-1"></i> Concluir Cadastro de Instrutor
                </button>

                <!-- Link de retorno ao Login -->
                <div class="text-center">
                    <span class="text-muted small">Já possui credenciais de acesso?</span>
                    <a href="index.php" class="text-decoration-none fw-semibold text-primary ms-1 small">
                        Fazer Login
                    </a>
                </div>
            </form>
        </div>

        <div class="card-footer text-center py-3 bg-light border-0">
            <small class="text-muted">© <?= date('Y') ?> FIEMG - Gestão Laboratorial Inteligente</small>
        </div>
    </div>
</div>

<!-- Validação em JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('formCadastro').addEventListener('submit', function (e) {
        const emailInput = document.getElementById('email');
        const senha = document.getElementById('senha').value;
        const confSenha = document.getElementById('confirmar_senha').value;
        const emailVal = emailInput.value.trim().toLowerCase();

        if (!emailVal.endsWith('@fiemg.com.br')) {
            e.preventDefault();
            alert('Atenção: O cadastro exige obrigatoriamente um e-mail com domínio @fiemg.com.br!');
            emailInput.focus();
            return false;
        }

        if (senha.length < 6) {
            e.preventDefault();
            alert('A senha deve conter no mínimo 6 caracteres.');
            return false;
        }

        if (senha !== confSenha) {
            e.preventDefault();
            alert('A senha e a confirmação de senha estão diferentes.');
            return false;
        }
    });
</script>
</body>
</html>