<?php
class Game {
    private $cards = [];
    private $numberOfPairs;

    public function __construct($numberOfPairs) {
        $this->numberOfPairs = $numberOfPairs;
        $this->generateCards();
    }

    private function generateCards() {
        for ($i = 1; $i <= $this->numberOfPairs; $i++) {
            $image = "images/card{$i}.jpg"; // Assurez-vous d'avoir des images de cartes correspondantes
            $this->cards[] = new Card($i, $image);
            $this->cards[] = new Card($i, $image);
        }
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
