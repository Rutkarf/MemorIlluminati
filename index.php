<!-- MemoryGame\index.php -->
<?php
require_once 'Card.php';
require_once 'Game.php';
require_once 'Player.php';
require_once 'Leaderboard.php';

session_start();

// Vérifier si le bouton "reset" a été cliqué pour réinitialiser la partie
if (isset($_POST['reset'])) {
    unset($_SESSION['game']);
    unset($_SESSION['player']);
    unset($_SESSION['startTime']);
    unset($_SESSION['attempts']);
    unset($_SESSION['flippedCards']);
    unset($_SESSION['toHide']);
    header("Location: index.php");
    exit;
}

// Récupérer ou initialiser le leaderboard depuis la session
$leaderboard = $_SESSION['leaderboard'] ?? new Leaderboard();

// Initialisation du jeu si le formulaire a été soumis (sauf si c'est un reset)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['reset'])) {
    $playerName = $_POST['name'] ?? 'Anonyme';
    $numberOfPairs = isset($_POST['pairs']) ? (int)$_POST['pairs'] : 3;

    $player = new Player($playerName);
    $game = new Game($numberOfPairs);

    $_SESSION['player'] = $player;
    $_SESSION['game'] = $game;
    $_SESSION['startTime'] = microtime(true);
    $_SESSION['attempts'] = 0;
    $_SESSION['flippedCards'] = [];
    $_SESSION['toHide'] = [];
}

// Récupérer les objets jeu et joueur depuis la session
$game = $_SESSION['game'] ?? null;
$player = $_SESSION['player'] ?? null;

// Vérifiez et initialisez 'flippedCards' comme tableau s'il n'existe pas encore
if (!isset($_SESSION['flippedCards']) || !is_array($_SESSION['flippedCards'])) {
    $_SESSION['flippedCards'] = [];
}

// Vérifiez et initialisez 'toHide' comme tableau s'il n'existe pas encore
if (!isset($_SESSION['toHide']) || !is_array($_SESSION['toHide'])) {
    $_SESSION['toHide'] = [];
}

// Vérifier pour retourner une carte si le jeu est en cours et si une carte est sélectionnée
if ($game && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['flip'])) {
    $index = (int)$_GET['flip'];
    $cards = $game->getCards();

    if (in_array($index, $_SESSION['flippedCards']) || in_array($index, $_SESSION['toHide'])) {
        header("Location: index.php");
        exit;
    }

    $_SESSION['flippedCards'][] = $index;

    $_SESSION['attempts']++;

    if (count($_SESSION['flippedCards']) === 2) {
        $firstCard = $cards[$_SESSION['flippedCards'][0]];
        $secondCard = $cards[$_SESSION['flippedCards'][1]];

        if ($game->checkMatch($firstCard, $secondCard)) {
            $_SESSION['toHide'] = $_SESSION['flippedCards'];
        } else {
            // Si les cartes ne correspondent pas, les retourner après un délai
            $_SESSION['toHide'] = [];
        }
        $_SESSION['flippedCards'] = [];
    }

    // Cacher les cartes après un court délai si elles ne correspondent pas
    if (count($_SESSION['toHide']) === 2) {
        // Définir un délai pour cacher les cartes
        sleep(1); // Utiliser sleep() peut ne pas être la meilleure approche pour les applications web en production
        $_SESSION['toHide'] = [];
    }

    // Vérifier si le jeu est terminé
    if ($game->isCompleted()) {
        // Ajouter le score au leaderboard
        $elapsedTime = microtime(true) - $_SESSION['startTime'];
        $score = max(0, 1000 - (int)($_SESSION['attempts'] * 10 + $elapsedTime / 60));
        $leaderboard->addEntry($player->getName(), $score);
        $_SESSION['leaderboard'] = $leaderboard;

        // Réinitialiser la session du jeu
        unset($_SESSION['game']);
        unset($_SESSION['player']);
        unset($_SESSION['startTime']);
        unset($_SESSION['attempts']);
        unset($_SESSION['flippedCards']);
        unset($_SESSION['toHide']);
    }

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeu de Mémoire</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand" href="#">Jeu de Mémoire</a>
        </nav>

        <?php if (!$game): ?>
            <form method="post" class="mt-4">
                <div class="form-group">
                    <label for="name">Nom :</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="pairs">Nombre de paires :</label>
                    <input type="number" id="pairs" name="pairs" class="form-control" min="2" max="10" value="3" required>
                </div>
                <button type="submit" class="btn btn-primary">Démarrer le jeu</button>
            </form>
        <?php else: ?>
            <div class="board mt-4">
                <?php foreach ($game->getCards() as $index => $card): ?>
                    <?php
                    $isFlipped = in_array($index, $_SESSION['flippedCards']) || in_array($index, $_SESSION['toHide']);
                    ?>
                    <div class="card-container">
                        <div class="card <?php echo $isFlipped ? 'flipped' : ''; ?>" onclick="flipCard(<?php echo $index; ?>)">
                            <div class="front">
                                <!-- Face avant de la carte avec l'image du dos -->
                            </div>
                            <div class="back">
                                <img src="<?php echo htmlspecialchars($card->getImage()); ?>" alt="Carte">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="mt-4">
            <button type="submit" name="reset" class="btn btn-danger">Réinitialiser</button>
        </form>

        <div id="leaderboard" class="mt-5">
            <h2>Leaderboard</h2>
            <ul>
                <?php foreach ($leaderboard->getEntries() as $entry): ?>
                    <li><?php echo htmlspecialchars($entry['name']); ?> : <?php echo htmlspecialchars($entry['score']); ?> points</li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <script>
        function flipCard(index) {
            window.location.href = `index.php?flip=${index}`;
        }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>