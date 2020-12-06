<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Support\Team;

class TeamsTest extends TestCase{
    function testNameIsRandomString(){
        $team = new Team();
        self::assertIsString($team->getName());
    }

    function testNameStaysTheSameEvenEachTimeGetNameIsCalledInSingleRequest(){
        $team = new Team();
        self::assertEquals($team->getName(), $team->getName());
    }

    function testAverageRanking(){
        $team = new Team(collect([
            ['ranking'=> 4],
            ['ranking'=> 3],
            ['ranking'=> 5],
        ]));
        self::assertEquals(4, $team->getAverageRanking());
    }
}
