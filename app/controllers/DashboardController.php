<?php
require_once __DIR__ . '/../models/Quimico.php';
require_once __DIR__ . '/../models/Equipamento.php';
require_once __DIR__ . '/../models/Ocorrencia.php';

class DashboardController
{
    public function index()
    {
        $quimicoModel = new Quimico();
        $equipamentoModel = new Equipamento();
        $ocorrenciaModel = new Ocorrencia();

        $stats = [
            'total_quimicos' => $quimicoModel->countTotal(),
            'quimicos_vencidos' => $quimicoModel->countVencidos(),
            'quimicos_validos' => $quimicoModel->countValidos(),
            'total_equipamentos' => $equipamentoModel->countTotal(),
            'manutencoes_atrasadas' => $equipamentoModel->countAtrasadas(),
            'total_ocorrencias' => $ocorrenciaModel->countTotal()
        ];

        $ultimasOcorrencias = $ocorrenciaModel->getRecentes(5);
        $revisoesCriticas = $equipamentoModel->getCriticos(5);

        require __DIR__ . '/../views/dashboard/index.php';
    }
}