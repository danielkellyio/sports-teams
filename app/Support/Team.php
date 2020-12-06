<?php
namespace App\Support;
use Illuminate\Support\Collection;

class Team{
    public Collection $players;
    public Int $avgRanking;
    public Int $playerCount;
    public String $name = '';

    public function __construct(Collection $players=null){
        $this->players = $players ?? collect([]);
        $this->avgRanking = $this->getAverageRanking();
        $this->playerCount = $this->players->count();
        $this->name = $this->getName();
    }
    public function getName(){
        if(!empty($this->name)){ return $this->name; }
        $faker = \Faker\Factory::create();
        return $this->name = $faker->company;
    }
    public function getAverageRanking(){
        $total = $this->players->map(fn($player)=> $player['ranking'] )->reduce(fn($a, $b)=> $a + $b );
        if(!$this->players->count()) return 0;
        return round($total / $this->players->count());
    }
}
