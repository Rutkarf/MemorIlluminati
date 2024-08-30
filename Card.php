<!-- MemoryGame\Card.php -->


<?php

class Card {
    private $id;
    private $image;
    private $matched = false;

    public function __construct($id, $image) {
        $this->id = $id;
        $this->image = $image;
    }

    public function getId() {
        return $this->id;
    }

    public function getImage() {
        return $this->image;
    }

    public function match() {
        $this->matched = true;
    }

    public function isMatched() {
        return $this->matched;
    }
}
?>
