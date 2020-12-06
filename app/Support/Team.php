<?php
namespace App\Support;
class Team{
    public function getName(){
        $faker = \Faker\Factory::create();
        return $faker->name;
    }
    public function averageRanking(){

    }

    public function totalPlayers(){

    }
}
