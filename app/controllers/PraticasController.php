<?php
require_once __DIR__ . '/../models/Pratica.php';
require_once __DIR__ . '/../models/Quimico.php';
require_once __DIR__ . '/../models/Equipamento.php';

class PraticasController
{
    public function index()
    {
        $model = new Pratica();
        $praticas = $model->getAll();
        require __DIR__ . '/../views/praticas/index.php';
    }

    public function view()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $model = new Pratica();
        $pratica = $model->findById($id);
        if (!$pratica) {
            header('Location: index.php?route=praticas');
            exit;
        }
        $itens = $model->getItens($id);
        require __DIR__ . '/../views/praticas/view.php';
    }

    public function create()
    {
        $praticaModel = new Pratica();
        $quimicoModel = new Quimico();
        $equipamentoModel = new Equipamento();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'titulo' => $_POST['titulo'],
                'materia_curso' => $_POST['materia_curso'],
                'objetivo' => $_POST['objetivo'],
                'procedimento' => $_POST['procedimento']
            ];

            $itens = [];
            if (!empty($_POST['itens'])) {
                foreach ($_POST['itens'] as $item) {
                    if (!empty($item['item_id'])) {
                        $itens[] = $item;
                    }
                }
            }

            $id = $praticaModel->create($dados, $itens);
            header("Location: index.php?route=praticas&action=view&id=$id");
            exit;
        }

        $quimicos = $quimicoModel->getAll();
        $equipamentos = $equipamentoModel->getAll('equipamento');
        $vidrarias = $equipamentoModel->getAll('vidraria');

        require __DIR__ . '/../views/praticas/create.php';
    }

    public function produtosVinculados()
    {
        header('Content-Type: application/json; charset=utf-8');
        $tipo = $_GET['tipo'] ?? 'quimico';
        $id = (int) ($_GET['id'] ?? 0);
        $model = new Pratica();
        $praticas = $model->getPraticasPorProduto($tipo, $id);
        echo json_encode($praticas);
    }
}