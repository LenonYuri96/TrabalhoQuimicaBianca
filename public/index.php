<?php
/**
 * =========================================================================
 * FRONT CONTROLLER & ROTEADOR PRINCIPAL (public/index.php)
 * Sistema de Gestão Laboratorial Inteligente (Lab Control 4.0)
 * =========================================================================
 */

// 1. Ativa exibição de erros para diagnóstico e define fuso horário
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Sao_Paulo');

// 2. Inicialização da sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Resolução dinâmica de caminhos (compatível com servidor embutido e Apache)
$baseAppDir = is_dir(__DIR__ . '/../app') ? __DIR__ . '/../app' : __DIR__ . '/app';
$loginUrl   = is_dir(__DIR__ . '/../app') ? '../index.php' : 'index.php';

// 4. Conexão segura e auto-criação de tabelas no banco de dados
require_once $baseAppDir . '/config/Database.php';
try {
    Database::getConnection();
} catch (Throwable $e) {
    die("<div style='font-family:sans-serif;padding:30px;background:#fff2f2;border:1px solid #ffb3b3;margin:50px auto;max-width:600px;border-radius:10px;'>
        <h3 style='color:#c00;margin-top:0;'>⚠️ Erro ao Inicializar Banco de Dados</h3>
        <p>Certifique-se de que o serviço <strong>MySQL</strong> está em execução no seu ambiente local (XAMPP / Laragon).</p>
        <p><small style='color:#666;'>" . htmlspecialchars($e->getMessage()) . "</small></p>
    </div>");
}

// 5. Captura dos parâmetros de rota e ação
$route  = strtolower(trim($_GET['route'] ?? 'dashboard'));
$action = trim($_GET['action'] ?? 'index');

// 6. Rota pública de Logout (encerra sessão e limpa cookies)
if ($route === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
    header("Location: {$loginUrl}");
    exit;
}

// 7. GUARDA DE AUTENTICAÇÃO (Bloqueia acessos sem login do Instrutor)
if (!isset($_SESSION['usuario_id'])) {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
              (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($isAjax) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'erro'         => true,
            'mensagem'     => 'Sessão expirada. Faça login novamente.',
            'redirecionar' => $loginUrl
        ]);
        exit;
    }

    header("Location: {$loginUrl}");
    exit;
}

// 8. Inclusão segura e automática de todos os Controllers existentes
$controllersMap = [
    'DashboardController',
    'QuimicosController',
    'EquipamentosController',
    'OcorrenciasController',
    'PraticasController',
    'AulasController',
    'AiController',
    'UsuariosController'
];

foreach ($controllersMap as $controllerClass) {
    $cPath = $baseAppDir . "/controllers/{$controllerClass}.php";
    if (file_exists($cPath)) {
        require_once $cPath;
    }
}

// 9. Instanciação do Controller conforme a rota
$ctrl = null;

switch ($route) {
    case 'quimicos':
        $ctrl = class_exists('QuimicosController') ? new QuimicosController() : null;
        break;

    case 'equipamentos':
        $ctrl = class_exists('EquipamentosController') ? new EquipamentosController() : null;
        break;

    case 'praticas':
        $ctrl = class_exists('PraticasController') ? new PraticasController() : null;
        break;

    case 'aulas':
        $ctrl = class_exists('AulasController') ? new AulasController() : null;
        break;

    case 'ocorrencias':
        $ctrl = class_exists('OcorrenciasController') ? new OcorrenciasController() : null;
        break;

    case 'ai':
        $ctrl = class_exists('AiController') ? new AiController() : null;
        break;

    case 'usuarios':
        $ctrl = class_exists('UsuariosController') ? new UsuariosController() : null;
        break;

    case 'dashboard':
    default:
        $ctrl = class_exists('DashboardController') ? new DashboardController() : null;
        break;
}

// 10. Execução segura da Ação (Action)
if ($ctrl && method_exists($ctrl, $action)) {
    $ctrl->$action();
} else {
    http_response_code(404);

    // Resposta em JSON se a requisição esperar dados (IA, AJAX, etc.)
    if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'erro'     => true,
            'mensagem' => "Ação '{$action}' ou módulo '{$route}' não encontrado."
        ]);
        exit;
    }

    // Tela de 404 estilizada em Bootstrap 5 para o navegador
    echo '<!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Página Não Encontrada | Lab Control</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center justify-content-center vh-100 font-sans-serif">
        <div class="text-center p-5 bg-white rounded-4 shadow-sm border" style="max-width: 520px;">
            <div class="mb-3 text-danger">
                <i class="bi bi-exclamation-octagon display-1"></i>
            </div>
            <h1 class="display-5 fw-bold text-dark">404</h1>
            <h5 class="fw-semibold text-secondary mb-3">Módulo ou Ação não encontrada!</h5>
            <p class="text-muted small mb-4">
                A rota <code>' . htmlspecialchars($route, ENT_QUOTES, 'UTF-8') . '</code> 
                ou a ação <code>' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '</code> não foi localizada no sistema.
            </p>
            <a href="index.php?route=dashboard" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                <i class="bi bi-house-door me-1"></i> Voltar ao Painel
            </a>
        </div>
    </body>
    </html>';
    exit;
}