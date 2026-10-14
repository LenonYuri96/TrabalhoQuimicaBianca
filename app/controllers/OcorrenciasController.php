<?php
require_once __DIR__ . '/../models/Ocorrencia.php';

class OcorrenciasController
{
    public function index()
    {
        $model = new Ocorrencia();
        $ocorrencias = $model->getAll();
        require __DIR__ . '/../views/ocorrencias/index.php';
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new Ocorrencia();
            $model->create($_POST);
            header('Location: index.php?route=ocorrencias');
            exit;
        }
        require __DIR__ . '/../views/ocorrencias/create.php';
    }
}