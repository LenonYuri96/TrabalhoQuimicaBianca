<?php
require_once __DIR__ . '/../models/Equipamento.php';

class EquipamentosController
{
    public function index()
    {
        $model = new Equipamento();
        $equipamentos = $model->getAll();
        require __DIR__ . '/../views/equipamentos/index.php';
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new Equipamento();
            $model->create($_POST);
            header('Location: index.php?route=equipamentos&msg=created');
            exit;
        }
        require __DIR__ . '/../views/equipamentos/create.php';
    }
}