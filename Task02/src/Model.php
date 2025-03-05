<?php

namespace waitingforlove24\Prime;

use SQLite3;

class Model {
    private $db;

    public function __construct() {
        $this->db = new SQLite3(__DIR__ . '/../db/database.sqlite');
        $this->initializeDatabase();
    }

    private function initializeDatabase() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS games (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            player_name TEXT,
            number INTEGER,
            user_answer TEXT,
            correct_answer TEXT,
            result TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public function isPrime($number) {
        if ($number <= 1) return false;
        for ($i = 2; $i <= sqrt($number); $i++) {
            if ($number % $i == 0) return false;
        }
        return true;
    }

    public function getNonTrivialDivisors($number) {
        $divisors = [];
        for ($i = 2; $i <= $number / 2; $i++) {
            if ($number % $i == 0) {
                $divisors[] = $i;
            }
        }
        return $divisors;
    }

    public function saveGameResult($playerName, $number, $userAnswer, $correctAnswer) {
        $result = ($userAnswer == $correctAnswer) ? 'Win' : 'Lose';
        $stmt = $this->db->prepare("INSERT INTO games (player_name, number, user_answer, correct_answer, result, created_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");

        $stmt->bindValue(1, $playerName);
        $stmt->bindValue(2, $number);
        $stmt->bindValue(3, $userAnswer);
        $stmt->bindValue(4, $correctAnswer);
        $stmt->bindValue(5, $result, SQLITE3_TEXT);

        $stmt->execute();
    }

    public function getGameResults() {
        $results = [];
        $query = $this->db->query("SELECT * FROM games");
        while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    }
}


