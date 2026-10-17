<?php
require_once __DIR__ . '/../models/AulaAgenda.php';
require_once __DIR__ . '/../models/Pratica.php';

class AulasController
{
    public function index()
    {
        $model = new AulaAgenda();
        $praticaModel = new Pratica();

        $dataFiltro = $_GET['data'] ?? date('Y-m-d');
        $materiaFiltro = $_GET['materia'] ?? null;

        $aulas = $model->getPorDataOuFiltro($dataFiltro, $materiaFiltro);

        // Agrupa os materiais necessários das práticas vinculadas às aulas do dia
        $materiaisDoDia = [
            'quimicos' => [],
            'vidrarias' => [],
            'equipamentos' => []
        ];

        foreach ($aulas as $aula) {
            if (!empty($aula['pratica_id'])) {
                $itens = $praticaModel->getItens($aula['pratica_id']);
                foreach ($itens as $it) {
                    $materiaisDoDia[$it['tipo_item']][] = [
                        'item_nome' => $it['item_nome'],
                        'qtd' => $it['quantidade_necessaria'],
                        'capacidade' => $it['capacidade_volume'],
                        'materia' => $aula['materia'],
                        'pratica' => $aula['pratica_titulo']
                    ];
                }
            }
        }

        $praticas = $praticaModel->getAll();
        require __DIR__ . '/../views/aulas/index.php';
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new AulaAgenda();
            $model->create($_POST);
            header("Location: index.php?route=aulas&data=" . $_POST['data_aula']);
            exit;
        }
    }
}