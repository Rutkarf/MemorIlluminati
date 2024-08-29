<?php
class Game {
    private $cards = [];
    private $numberOfPairs;

    public function __construct($numberOfPairs) {
        $this->numberOfPairs = $numberOfPairs;
        $this->generateCards();
    }

    private function generateCards() {
        $this->cards = [];
    
        // Crée une liste d'images avec le chemin correct
        for ($i = 1; $i <= $this->numberOfPairs; $i++) {
            $image = "./assets/card/Card{$i}.jpg"; // Assurez-vous que ce chemin est correct
            
            // Crée deux instances de Card pour chaque image (une paire)
            $this->cards[] = new Card($i, $image);
            $this->cards[] = new Card($i, $image);
        }
    
        // Mélange les cartes pour un placement aléatoire
        shuffle($this->cards);
    }
    

    public function getCards() {
        return $this->cards;
    }

    public function checkMatch($card1, $card2) {
        if ($card1->getId() === $card2->getId()) {
            $card1->match();
            $card2->match();
            return true;
        }
        return false;
    }

    public function isGameOver() {
        foreach ($this->cards as $card) {
            if (!$card->isMatched()) {
                return false;
            }
        }
        return true;
    }
}
?>
