<?php

namespace waitingforlove24\Prime\Controllers;

use waitingforlove24\Prime\Model;

class ResultsController {
    private $model;

    public function __construct() {
        $this->model = new Model();
    }

    public function handleRequest() {
        $results = $this->model->getGameResults();
        require_once __DIR__ . '/../views/results.html.php';
    }
}
