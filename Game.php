<?php

class Game
{
    private $cards = [];        // Tableau pour stocker toutes les cartes du jeu
    private $numberOfPairs;     // Nombre de paires de cartes dans le jeu
    private $path = "./assets/card/"; // Chemin vers le dossier contenant les images des cartes

    // Constructeur de la classe Game
    public function __construct($numberOfPairs)
    {
        $this->numberOfPairs = $numberOfPairs; // Initialiser le nombre de paires
        $this->generateCards();                // Générer les cartes pour le jeu
    }

    // Méthode pour générer les cartes du jeu
    private function generateCards()
    {
        $this->cards = []; // Réinitialise le tableau des cartes

        // Créer une liste d'images avec le chemin correct pour chaque paire
        for ($i = 1; $i <= $this->numberOfPairs; $i++) {
            $image = $this->path . "Card{$i}.jpg"; // Construit le chemin de l'image pour chaque carte

            // Crée deux instances de Card pour chaque image (une paire)
            $this->cards[] = new Card($i, $image);
            $this->cards[] = new Card($i, $image);
        }

        // Mélange les cartes pour un placement aléatoire sur le plateau
        shuffle($this->cards);
    }

    // Méthode pour obtenir toutes les cartes du jeu
    public function getCards()
    {
        return $this->cards;
    }

    // Vérifie si deux cartes correspondent en comparant leurs IDs
    public function checkMatch(Card $card1, Card $card2)
    {
        // Vérifie si les IDs des deux cartes correspondent
        if ($card1->getId() === $card2->getId()) {
            $card1->match(); // Marque la première carte comme trouvée
            $card2->match(); // Marque la seconde carte comme trouvée
            return true;     // Retourne vrai si les cartes correspondent
        }
        return false; // Retourne faux si les cartes ne correspondent pas
    }

    // Vérifie si le jeu est terminé en vérifiant si toutes les cartes sont trouvées
    public function isCompleted()
    {
        // Parcourt toutes les cartes pour vérifier si elles sont toutes retournées
        foreach ($this->cards as $card) {
            if (!$card->isMatched()) { // Si une carte n'est pas encore trouvée
                return false;          // Le jeu n'est pas terminé
            }
        }
        return true; // Retourne vrai si toutes les cartes sont trouvées
    }
}
