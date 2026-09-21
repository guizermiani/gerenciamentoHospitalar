<?php
require_once __DIR__ . '/../model/PrioridadeModel.php';

class PrioridadeController
{
    private PrioridadeModel $model;

    public function __construct(PDO $conexao)
    {
        $this->model = new PrioridadeModel($conexao);
    }

    public function listar(): array
    {
        return [
            'sucesso' => true,
            'dados'   => $this->model->listar(),
        ];
    }
}
