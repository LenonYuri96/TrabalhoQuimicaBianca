<?php
$currentRoute = $_GET['route'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LabManager - Sistema de Laboratório</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- CSS Customizado -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="bg-light d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-lab sticky-top mb-4 shadow-sm" data-bs-theme="dark">
        <div class="container">
            <!-- Logo / Identidade -->
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="index.php?route=dashboard">
                <i class="bi bi-radioactive fs-3 text-warning"></i>
                <span>LabManager</span>
            </a>

            <!-- Botão Menu Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
                aria-controls="navMenu" aria-expanded="false" aria-label="Alternar navegação">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menu de Navegação Limpo -->
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto gap-lg-1 py-3 py-lg-0 align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentRoute === 'dashboard' ? 'active fw-bold' : '' ?>"
                            href="index.php?route=dashboard">
                            <i class="bi bi-grid-1x2-fill me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentRoute === 'aulas' ? 'active fw-bold' : '' ?>"
                            href="index.php?route=aulas">
                            <i class="bi bi-calendar-week me-1"></i> Aulas & Materiais
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentRoute === 'praticas' ? 'active fw-bold' : '' ?>"
                            href="index.php?route=praticas">
                            <i class="bi bi-journal-medical me-1"></i> Práticas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentRoute === 'quimicos' ? 'active fw-bold' : '' ?>"
                            href="index.php?route=quimicos">
                            <i class="bi bi-eyedropper me-1"></i> Químicos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentRoute === 'equipamentos' ? 'active fw-bold' : '' ?>"
                            href="index.php?route=equipamentos">
                            <i class="bi bi-tools me-1"></i> Equipamentos & Vidrarias
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentRoute === 'ocorrencias' ? 'active fw-bold' : '' ?>"
                            href="index.php?route=ocorrencias">
                            <i class="bi bi-shield-exclamation me-1"></i> Ocorrências
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Container Principal do Conteúdo (Fechado no footer.php) -->
    <div class="container flex-grow-1 pb-5">