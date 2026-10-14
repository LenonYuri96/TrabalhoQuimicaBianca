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

<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-lab sticky-top mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php?route=dashboard">
                <i class="bi bi-radioactive fs-3"></i>
                <span>LabManager</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
                aria-controls="navMenu" aria-expanded="false" aria-label="Navegação">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto gap-lg-2 py-3 py-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentRoute === 'dashboard' ? 'active' : '' ?>"
                            href="index.php?route=dashboard">
                            <i class="bi bi-grid-1x2-fill me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentRoute === 'quimicos' ? 'active' : '' ?>"
                            href="index.php?route=quimicos">
                            <i class="bi bi-eyedropper me-1"></i> Químicos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentRoute === 'equipamentos' ? 'active' : '' ?>"
                            href="index.php?route=equipamentos">
                            <i class="bi bi-tools me-1"></i> Equipamentos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentRoute === 'ocorrencias' ? 'active' : '' ?>"
                            href="index.php?route=ocorrencias">
                            <i class="bi bi-shield-exclamation me-1"></i> Ocorrências
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container flex-grow-1 pb-5">