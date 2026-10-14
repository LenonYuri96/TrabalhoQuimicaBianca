<?php
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/QuimicosController.php';
require_once __DIR__ . '/../app/controllers/EquipamentosController.php';
require_once __DIR__ . '/../app/controllers/OcorrenciasController.php';
require_once __DIR__ . '/../app/controllers/AiController.php'; // <-- Adicionado

$route = $_GET['route'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

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
    case 'ocorrencias':
        $ctrl = new OcorrenciasController();
        break;
    case 'ai': // <-- Nova Rota para a Inteligência Artificial
        $ctrl = new AiController();
        break;
    default:
        $ctrl = new DashboardController();
}

if (method_exists($ctrl, $action)) {
    $ctrl->$action();
} else {
    http_response_code(404);
    echo "Página não encontrada!";
}