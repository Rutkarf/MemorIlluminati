<?php
class Leaderboard {
    private $players = [];

    public function addPlayer(Player $player) {
        $this->players[$player->getName()] = $player;
    }

    public function updateLeaderboard(Player $player, $score) {
        if (isset($this->players[$player->getName()])) {
            $this->players[$player->getName()]->addScore($score);
        } else {
            $this->addPlayer($player);
            $this->players[$player->getName()]->addScore($score);
        }
        $this->sortLeaderboard();
    }

    private function sortLeaderboard() {
        usort($this->players, function($a, $b) {
            return $b->getScores()[0] - $a->getScores()[0];
        });
    }

    public function getTopPlayers($limit = 10) {
        return array_slice($this->players, 0, $limit);
    }
}
?>
