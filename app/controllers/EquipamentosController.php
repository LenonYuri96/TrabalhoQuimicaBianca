<?php
require_once __DIR__ . '/../models/Equipamento.php';

class EquipamentosController
{
    /**
     * Listagem principal com alternância de abas (Equipamentos / Vidrarias)
     */
    public function index()
    {
        $model = new Equipamento();
        $aba = $_GET['aba'] ?? 'equipamentos'; // 'equipamentos' ou 'vidrarias'

        // Carrega as listas separadas por categoria
        $equipamentos = $model->getAll('equipamento');
        $vidrarias = $model->getAll('vidraria');

        // Carrega o balanço de perdas e estoque para os cards do topo
        $statsVidrarias = $model->getVidrariasResumo();

        require __DIR__ . '/../views/equipamentos/index.php';
    }

    /**
     * Cadastro de novos Equipamentos ou Vidrarias
     */
    public function create()
    {
        $model = new Equipamento();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipo = $_POST['tipo'] ?? 'equipamento';
            $model->create($_POST);

            // Redireciona o laboratorista de volta à aba do tipo de item cadastrado
            $abaDestino = ($tipo === 'vidraria') ? 'vidrarias' : 'equipamentos';
            header("Location: index.php?route=equipamentos&aba={$abaDestino}&msg=created");
            exit;
        }

        require __DIR__ . '/../views/equipamentos/create.php';
    }

    /**
     * Registra quebra total ou avaria física (trinca, fissura) em vidrarias
     */
    public function registrarAvaria()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new Equipamento();

            $id = (int) ($_POST['id'] ?? 0);
            $tipoAvaria = $_POST['tipo_avaria'] ?? 'quebra'; // 'quebra' ou 'defeito'
            $quantidade = max(1, (int) ($_POST['quantidade'] ?? 1));

            if ($id > 0) {
                $model->registrarAvariaVidraria($id, $tipoAvaria, $quantidade);
            }

            header("Location: index.php?route=equipamentos&aba=vidrarias&msg=avaria_registrada");
            exit;
        }

        header("Location: index.php?route=equipamentos&aba=vidrarias");
        exit;
    }

    /**
     * Reposição / Entrada de novas peças de vidraria adquiridas
     */
    public function reporEstoque()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new Equipamento();

            $id = (int) ($_POST['id'] ?? 0);
            $quantidade = max(1, (int) ($_POST['quantidade'] ?? 1));

            if ($id > 0) {
                $model->reporEstoqueVidraria($id, $quantidade);
            }

            header("Location: index.php?route=equipamentos&aba=vidrarias&msg=estoque_reposto");
            exit;
        }

        header("Location: index.php?route=equipamentos&aba=vidrarias");
        exit;
    }

    /**
     * Exclui um equipamento ou vidraria do acervo
     */
    public function delete()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $aba = $_GET['aba'] ?? 'equipamentos';

        if ($id > 0) {
            $model = new Equipamento();
            $model->delete($id);
        }

        header("Location: index.php?route=equipamentos&aba={$aba}&msg=deleted");
        exit;
    }
}