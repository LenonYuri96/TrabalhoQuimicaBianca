<?php
/**
 * =========================================================================
 * FRONT CONTROLLER & ROTEADOR PRINCIPAL (index.php)
 * =========================================================================
 */

// 1. Configurações essenciais de ambiente e fuso horário
date_default_timezone_set('America/Sao_Paulo');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Resolução do caminho da pasta /app (funciona tanto na raiz quanto na pasta /public)
$baseAppDir = is_dir(__DIR__ . '/../app') ? __DIR__ . '/../app' : __DIR__ . '/app';

// 3. Inclusão dos Controllers do Sistema
require_once $baseAppDir . '/controllers/DashboardController.php';
require_once $baseAppDir . '/controllers/QuimicosController.php';
require_once $baseAppDir . '/controllers/EquipamentosController.php';
require_once $baseAppDir . '/controllers/OcorrenciasController.php';
require_once $baseAppDir . '/controllers/PraticasController.php';
require_once $baseAppDir . '/controllers/AulasController.php';
require_once $baseAppDir . '/controllers/AiController.php';

// 4. Captura dos parâmetros de rota e ação
$route = strtolower(trim($_GET['route'] ?? 'dashboard'));
$action = trim($_GET['action'] ?? 'index');

// 5. Instanciação do Controller de acordo com a rota solicitada
switch ($route) {
    case 'dashboard':
        $ctrl = new DashboardController();
        break;

    case 'quimicos':
        $ctrl = new QuimicosController();
        break;

    case 'equipamentos':
        $ctrl = new EquipamentosController();
        break;

    case 'praticas':
        $ctrl = new PraticasController();
        break;

    case 'aulas':
        $ctrl = new AulasController();
        break;

    case 'ocorrencias':
        $ctrl = new OcorrenciasController();
        break;

    case 'ai':
        $ctrl = new AiController();
        break;

    default:
        // Rota não reconhecida -> redireciona para o Dashboard
        $ctrl = new DashboardController();
        $action = 'index';
        break;
}

// 6. Execução segura da Ação (Action)
if (method_exists($ctrl, $action)) {
    $ctrl->$action();
} else {
    http_response_code(404);

    // Resposta amigável para chamadas AJAX/JSON (como as da IA ou Práticas)
    if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'erro' => true,
            'mensagem' => "Ação '{$action}' não encontrada para o módulo '{$route}'."
        ]);
        exit;
    }

    // Resposta padrão HTML para navegador
    echo '<!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>404 - Página Não Encontrada</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center justify-content-center vh-100">
        <div class="text-center p-4 bg-white rounded shadow-sm">
            <h1 class="display-3 text-danger fw-bold">404</h1>
            <h4 class="fw-semibold">Página ou Ação não encontrada!</h4>
            <p class="text-muted">A ação solicitada <code>' . htmlspecialchars($action) . '</code> não existe para a rota <code>' . htmlspecialchars($route) . '</code>.</p>
            <a href="index.php?route=dashboard" class="btn btn-primary rounded-pill px-4">Voltar ao Painel</a>
        </div>
    </body>
    </html>';
}