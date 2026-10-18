<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentRoute  = strtolower(trim($_GET['route'] ?? 'dashboard'));
$usuarioNome   = $_SESSION['usuario_nome'] ?? 'Instrutor FIEMG';
$usuarioEmail  = $_SESSION['usuario_email'] ?? 'instrutor@fiemg.com.br';
$usuarioPerfil = ucfirst($_SESSION['usuario_perfil'] ?? 'Instrutor');
$letraInicial  = strtoupper(mb_substr($usuarioNome, 0, 1, 'UTF-8'));
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LabManager 4.0 - Gestão Laboratorial FIEMG</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- CSS Customizado -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="bg-light d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top mb-4 shadow-sm" style="background: linear-gradient(135deg, #0b1f3a 0%, #004b8d 100%) !important;">
        <div class="container-fluid px-lg-4">
            <!-- Logo / Identidade -->
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-white me-3" href="index.php?route=dashboard">
                <i class="bi bi-radioactive fs-3 text-warning"></i>
                <div class="d-flex flex-column lh-1">
                    <span class="fs-5 tracking-wide">LabManager</span>
                    <small class="text-info fw-normal" style="font-size: 0.68rem; letter-spacing: 0.5px;">PORTAL DO INSTRUTOR</small>
                </div>
            </a>

            <!-- Botão Menu Mobile -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
                aria-controls="navMenu" aria-expanded="false" aria-label="Alternar navegação">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Links de Navegação e Perfil -->
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-auto gap-lg-1 py-3 py-lg-0 align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link px-3 <?= $currentRoute === 'dashboard' ? 'active fw-bold text-warning border-bottom border-warning border-2' : 'text-light' ?>"
                            href="index.php?route=dashboard">
                            <i class="bi bi-grid-1x2-fill me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?= $currentRoute === 'aulas' ? 'active fw-bold text-warning border-bottom border-warning border-2' : 'text-light' ?>"
                            href="index.php?route=aulas">
                            <i class="bi bi-calendar-week me-1"></i> Aulas & Materiais
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?= $currentRoute === 'praticas' ? 'active fw-bold text-warning border-bottom border-warning border-2' : 'text-light' ?>"
                            href="index.php?route=praticas">
                            <i class="bi bi-journal-medical me-1"></i> Práticas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?= $currentRoute === 'quimicos' ? 'active fw-bold text-warning border-bottom border-warning border-2' : 'text-light' ?>"
                            href="index.php?route=quimicos">
                            <i class="bi bi-eyedropper me-1"></i> Químicos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?= $currentRoute === 'equipamentos' ? 'active fw-bold text-warning border-bottom border-warning border-2' : 'text-light' ?>"
                            href="index.php?route=equipamentos">
                            <i class="bi bi-tools me-1"></i> Equipamentos & Vidrarias
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?= $currentRoute === 'ocorrencias' ? 'active fw-bold text-warning border-bottom border-warning border-2' : 'text-light' ?>"
                            href="index.php?route=ocorrencias">
                            <i class="bi bi-shield-exclamation me-1"></i> Ocorrências
                        </a>
                    </li>
                </ul>

                <!-- Bloco do Perfil do Instrutor & Logout -->
                <div class="d-flex align-items-center gap-3 pt-3 pt-lg-0 border-top border-secondary border-opacity-25 border-top-lg-0">
                    <div class="dropdown">
                        <button class="btn btn-outline-light border-0 d-flex align-items-center gap-2 py-1 px-2 rounded-3 text-start dropdown-toggle" 
                                type="button" 
                                id="userProfileDropdown" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false"
                                style="background-color: rgba(255, 255, 255, 0.08);">
                            
                            <!-- Avatar com Inicial do Nome -->
                            <div class="rounded-circle bg-warning text-dark fw-bold d-flex align-items-center justify-content-center shadow-sm" 
                                 style="width: 36px; height: 36px; font-size: 0.95rem;">
                                <?= $letraInicial ?>
                            </div>

                            <!-- Nome e E-mail Institucional -->
                            <div class="d-none d-sm-flex flex-column lh-1 pe-1">
                                <span class="fw-semibold text-white small"><?= htmlspecialchars($usuarioNome) ?></span>
                                <span class="text-white-50" style="font-size: 0.72rem;"><?= htmlspecialchars($usuarioEmail) ?></span>
                            </div>
                        </button>

                        <!-- Menu de Opções do Instrutor -->
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-2" aria-labelledby="userProfileDropdown" style="min-width: 250px;">
                            <li class="px-3 py-2 bg-light rounded-2 mb-2">
                                <div class="fw-bold text-dark small mb-0"><?= htmlspecialchars($usuarioNome) ?></div>
                                <div class="text-muted text-break" style="font-size: 0.75rem;"><?= htmlspecialchars($usuarioEmail) ?></div>
                                <span class="badge bg-primary text-uppercase mt-1" style="font-size: 0.65rem;">
                                    <i class="bi bi-patch-check-fill me-1"></i><?= htmlspecialchars($usuarioPerfil) ?> FIEMG
                                </span>
                            </li>
                            <li>
                                <a class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2 text-danger fw-semibold" href="index.php?route=logout">
                                    <i class="bi bi-box-arrow-right fs-5"></i>
                                    <span>Encerrar Sessão</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Botão de Saída Rápida -->
                    <a href="index.php?route=logout" class="btn btn-outline-danger btn-sm rounded-pill px-3 d-none d-md-inline-flex align-items-center gap-1 shadow-sm" title="Sair do Sistema">
                        <i class="bi bi-power"></i>
                        <span>Sair</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Container Principal do Conteúdo (Fechado no footer.php) -->
    <div class="container-fluid px-lg-4 flex-grow-1 pb-5">