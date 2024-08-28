<?php
class Card {
    private $id;
    private $image;
    private $isMatched;

    public function __construct($id, $image) {
        $this->id = $id;
        $this->image = $image;
        $this->isMatched = false;
    }

    public function getId() {
        return $this->id;
    }

    public function getImage() {
        return $this->image;
    }

    public function isMatched() {
        return $this->isMatched;
    }

    public function match() {
        $this->isMatched = true;
    }

    public function unmatch() {
        $this->isMatched = false;
    }
}
?>
