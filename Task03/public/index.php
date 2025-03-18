<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$dbPath = __DIR__ . '/../db/game.sqlite';
$pdo = new PDO("sqlite:$dbPath");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("CREATE TABLE IF NOT EXISTS games (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    player_name TEXT NOT NULL,
    date TEXT NOT NULL,
    number INTEGER NOT NULL,
    is_prime INTEGER NOT NULL,
    result TEXT NOT NULL,
    player_answer INTEGER NOT NULL
);");

function isPrime($num) {
    if ($num < 2) return false;
    for ($i = 2; $i * $i <= $num; $i++) {
        if ($num % $i == 0) return false;
    }
    return true;
}

$app->get('/', function (Request $request, Response $response) {
    $file = __DIR__ . '/index.html';
    if (file_exists($file)) {
        $response->getBody()->write(file_get_contents($file));
        return $response->withHeader('Content-Type', 'text/html');
    }
    return $response->withStatus(404)->getBody()->write('404 Not Found');
});

$app->get('/games', function (Request $request, Response $response) use ($pdo) {
    $stmt = $pdo->query("SELECT * FROM games ORDER BY date DESC");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($games));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/games', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $player_name = $data['player_name'] ?? 'Игрок';
    $number = rand(1, 100);
    $is_prime = isPrime($number) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO games (player_name, date, number, is_prime, result, player_answer)
                           VALUES (?, datetime('now'), ?, ?, '', -1)");
    $stmt->execute([$player_name, $number, $is_prime]);

    $gameId = $pdo->lastInsertId();
    $response->getBody()->write(json_encode(["game_id" => $gameId, "number" => $number]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/step/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $game_id = (int)$args['id'];
    $data = $request->getParsedBody();
    $answer = intval($data['answer'] ?? -1);

    $stmt = $pdo->prepare("SELECT * FROM games WHERE id = ?");
    $stmt->execute([$game_id]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$game) {
        return $response->withStatus(404)->getBody()->write(json_encode(["error" => "Game not found"]));
    }

    $correct = ($game['is_prime'] == $answer) ? "correct" : "incorrect";

    $stmt = $pdo->prepare("UPDATE games SET result = ?, player_answer = ? WHERE id = ?");
    $stmt->execute([$correct, $answer, $game_id]);

    $response->getBody()->write(json_encode([
        "result" => $correct,
        "correct_answer" => $game['is_prime'] ? "Простое" : "Составное",
        "message" => ($correct === "correct" ? "✅ Верно!" : "❌ Неверно.") .
                     " Число {$game['number']} - " . ($game['is_prime'] ? "простое" : "составное"),
        "game_id" => $game_id
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();