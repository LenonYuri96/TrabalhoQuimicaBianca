<?php
require_once __DIR__ . '/../models/Quimico.php';

class QuimicosController
{
    public function index()
    {
        $model = new Quimico();
        $quimicos = $model->getAll();
        require __DIR__ . '/../views/quimicos/index.php';
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new Quimico();
            $model->create($_POST);
            header('Location: index.php?route=quimicos');
            exit;
        }
        require __DIR__ . '/../views/quimicos/create.php';
    }
}