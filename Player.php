<?php
class Player {
    private $name;
    private $scores = [];

    public function __construct($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function addScore($score) {
        $this->scores[] = $score;
        // Tri les scores du meilleur au moins bon
        rsort($this->scores);
        // Garde seulement les 10 meilleurs scores
        if (count($this->scores) > 10) {
            array_pop($this->scores);
        }
    }

    public function getScores() {
        return $this->scores;
    }
}
?>
