<?php
require_once __DIR__ . '/../model/StatusModel.php';

class StatusController
{
    private StatusModel $model;

    public function __construct(PDO $conexao)
    {
        $this->model = new StatusModel($conexao);
    }

    public function listar(): array
    {
        return [
            'sucesso' => true,
            'dados'   => $this->model->listar(),
        ];
    }
}
