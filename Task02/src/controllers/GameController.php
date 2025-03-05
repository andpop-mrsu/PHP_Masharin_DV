<?php

namespace waitingforlove24\Prime\Controllers;

class GameController {
    private $model;

    public function __construct() {
        require_once __DIR__ . '/../Model.php';
        $this->model = new \waitingforlove24\Prime\Model();
    }

    public function handleRequest() {

        if (!isset($_SESSION['player_name'])) {
            header('Location: index.php?page=home');
            exit();
        }

        if (isset($_POST['new_game'])) {
            unset($_SESSION['number']); 
        }

        if (!isset($_SESSION['number'])) {
            $number = rand(1, 100);
            $_SESSION['number'] = $number;
        } else {
            $number = $_SESSION['number'];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_answer'])) {
            $userAnswer = $_POST['user_answer'];
            $isPrime = $this->model->isPrime($number);
            $correctAnswer = $isPrime ? 'Простое' : 'Не простое';
            $divisors = !$isPrime ? $this->model->getNonTrivialDivisors($number) : [];

            $this->model->saveGameResult($_SESSION['player_name'], $number, $userAnswer, $correctAnswer);

            $message = ($userAnswer == $correctAnswer)
                ? "Правильно! Число $number является $correctAnswer."
                : "Неправильно. Число $number является $correctAnswer.";

            if (!$isPrime) {
                $message .= " Нетривиальные делители: " . implode(", ", $divisors) . ".";
            }
        }

        require_once __DIR__ . '/../views/game.html.php';
    }
}
