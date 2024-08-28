<?php
// Charger toutes les classes nécessaires avant tout accès à la session
require_once 'Card.php';
require_once 'Game.php';
require_once 'Player.php';
require_once 'Leaderboard.php';

// Démarrer la session
session_start();

// Récupérer ou initialiser le leaderboard
$leaderboard = $_SESSION['leaderboard'] ?? new Leaderboard();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $playerName = $_POST['name'] ?? 'Anonyme';
    $numberOfPairs = (int)$_POST['pairs'];

    $player = new Player($playerName);
    $game = new Game($numberOfPairs);

    $_SESSION['player'] = $player;
    $_SESSION['game'] = $game;
    $_SESSION['startTime'] = microtime(true); // Enregistre le temps de début
    $_SESSION['attempts'] = 0; // Initialise le nombre de tentatives
    $_SESSION['flippedCards'] = []; // Pour garder une trace des cartes retournées
}

// Accéder aux objets de la session après s'assurer que les classes sont chargées
$game = $_SESSION['game'] ?? null;
$player = $_SESSION['player'] ?? null;

if ($game && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['flip'])) {
    $index = (int)$_GET['flip'];
    $cards = $game->getCards();
    $_SESSION['flippedCards'][] = $index;
    $_SESSION['attempts']++;

    if (count($_SESSION['flippedCards']) === 2) {
        $firstCard = $cards[$_SESSION['flippedCards'][0]];
        $secondCard = $cards[$_SESSION['flippedCards'][1]];

        if ($game->checkMatch($firstCard, $secondCard)) {
            echo "<p>Bonne paire trouvée !</p>";
        } else {
            echo "<p>Dommage, pas de correspondance.</p>";
        }

        $_SESSION['flippedCards'] = []; // Réinitialise les cartes retournées
    }

    if ($game->isGameOver()) {
        $endTime = microtime(true);
        $timeElapsed = $endTime - $_SESSION['startTime'];
        $score = max(1000 - ($timeElapsed * 10) - ($_SESSION['attempts'] * 5), 0);

        $leaderboard->updateLeaderboard($player, $score);
        $_SESSION['leaderboard'] = $leaderboard;

        echo "<p>Félicitations, {$player->getName()} ! Vous avez terminé le jeu.</p>";
        echo "<p>Votre score : $score</p>";
        echo "<a href='index.php'>Rejouer</a>";

        session_destroy();
        exit;
    }
}

$cards = $game ? $game->getCards() : [];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeu de Memory</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <h1>Jeu de Memory</h1>

    <?php if (!$game): ?>
        <form method="post" action="index.php">
            <label for="name">Nom du joueur :</label>
            <input type="text" id="name" name="name" required>
            <label for="pairs">Nombre de paires :</label>
            <input type="number" id="pairs" name="pairs" min="3" max="12" required>
            <button type="submit">Démarrer le jeu</button>
        </form>
    <?php else: ?>
        <div class="board">
            <?php foreach ($cards as $index => $card): ?>
                <?php if ($card->isMatched()): ?>
                    <div class="card matched">
                        <img src="<?= $card->getImage() ?>" alt="Card">
                    </div>
                <?php elseif (in_array($index, $_SESSION['flippedCards'])): ?>
                    <div class="card">
                        <img src="<?= $card->getImage() ?>" alt="Card">
                    </div>
                <?php else: ?>
                    <div class="card">
                        <a href="index.php?flip=<?= $index ?>">
                            <img src="assets/card/BackCard.jpg" alt="Hidden Card">
                        </a>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <p>Tentatives : <?= $_SESSION['attempts'] ?></p>
    <?php endif; ?>

    <h2>Classement</h2>
    <ul>
        <?php
        $topPlayers = $leaderboard->getTopPlayers();
        foreach ($topPlayers as $topPlayer) {
            echo "<li>{$topPlayer->getName()} - Meilleur score : " . $topPlayer->getScores()[0] . "</li>";
        }
        ?>
    </ul>



    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>




    
</body>

</html>