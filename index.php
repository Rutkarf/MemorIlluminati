<!-- MemoryGame\index.php -->


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

// Initialisation du jeu
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
    $_SESSION['lastFlippedCards'] = []; // Pour stocker temporairement les cartes retournées
}

// Accéder aux objets de la session après s'assurer que les classes sont chargées
$game = $_SESSION['game'] ?? null;
$player = $_SESSION['player'] ?? null;

if ($game && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['flip'])) {
    $index = (int)$_GET['flip'];
    $cards = $game->getCards();

    // Si la carte a déjà été retournée, ignorez l'action
    if (in_array($index, $_SESSION['flippedCards'])) {
        header("Location: index.php");
        exit;
    }

    // Ajoute l'index de la carte retournée
    $_SESSION['flippedCards'][] = $index;
    $_SESSION['attempts']++;

    if (count($_SESSION['flippedCards']) === 2) {
        $firstCard = $cards[$_SESSION['flippedCards'][0]];
        $secondCard = $cards[$_SESSION['flippedCards'][1]];

        if ($game->checkMatch($firstCard, $secondCard)) {
            // Si c'est une correspondance, vide la liste des cartes retournées
            $_SESSION['flippedCards'] = [];
        } else {
            // Si ce n'est pas une correspondance, affichez les cartes pendant 1 seconde
            $_SESSION['lastFlippedCards'] = $_SESSION['flippedCards'];
            $_SESSION['flippedCards'] = [];

            header("Refresh: 1; url=index.php");
            exit;
        }
    }

    // Vérifier si le jeu est terminé
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

// Réinitialiser l'état des dernières cartes retournées
if ($game && isset($_SESSION['lastFlippedCards'])) {
    $_SESSION['lastFlippedCards'] = [];
}

// Récupère les cartes à afficher
$cards = $game ? $game->getCards() : [];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memor'Illuminati</title>
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <nav class="navbar">
        <div class="container-fluid">
            <!-- Nom du jeu à gauche -->
            <a class="navbar-brand" href="#">Memor'Illuminati</a>

            <!-- Bouton pour mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Contenu de la navbar -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <!-- Réseaux sociaux au centre -->
                    <li class="nav-item">
                        <a class="nav-link" href="https://www.facebook.com" target="_blank">
                            <i class="fab fa-facebook"></i> Facebook
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://www.twitter.com" target="_blank">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://www.instagram.com" target="_blank">
                            <i class="fab fa-instagram"></i> Instagram
                        </a>
                    </li>
                </ul>

                <!-- Leaderboard à droite -->
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#leaderboard">Leaderboard</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="mt-5">Jeu de Memory</h1>

        <?php if (!$game): ?>
            <form method="post" action="index.php" class="mt-4">
                <div class="form-group">
                    <label for="name">Nom du joueur :</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="pairs">Nombre de paires :</label>
                    <input type="number" id="pairs" name="pairs" class="form-control" min="3" max="12" required>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Démarrer le jeu</button>
            </form>
        <?php else: ?>
            <div class="board mt-4 d-flex flex-wrap">
                <?php foreach ($cards as $index => $card): ?>
                    <?php
                    $flipped = in_array($index, $_SESSION['flippedCards']) || in_array($index, $_SESSION['lastFlippedCards']);
                    ?>
                    <div class="card m-2" style="width: 100px; height: 150px;">
                        <?php if ($card->isMatched() || $flipped): ?>
                            <img src="<?= $card->getImage() ?>" alt="Card" class="img-fluid">
                        <?php else: ?>
                            <a href="index.php?flip=<?= $index ?>">
                                <img src="assets/card/BackCard.jpg" alt="Hidden Card" class="img-fluid">
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="mt-3">Tentatives : <?= $_SESSION['attempts'] ?></p>
        <?php endif; ?>

        <h2 class="mt-5">Classement</h2>
        <ul>
            <?php
            $topPlayers = $leaderboard->getTopPlayers();
            foreach ($topPlayers as $topPlayer) {
                echo "<li>{$topPlayer->getName()} - Meilleur score : " . $topPlayer->getScores()[0] . "</li>";
            }
            ?>
        </ul>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.3/js/bootstrap.min.js"></script>

</body>

</html>