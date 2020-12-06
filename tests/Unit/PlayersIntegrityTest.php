<?php

namespace Tests\Unit;

use App\Support\FairTeamGenerator;
use Tests\TestCase;
use App\Models\User;

class PlayersIntegrityTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGoaliePlayersExist ()
    {
/*
		Check there are players that have can_play_goalie set as 1
*/
		$result = User::where('user_type', 'player')->where('can_play_goalie', 1)->count();
		$this->assertTrue($result > 1);

    }

    public function testAtLeastOneGoaliePlayerPerTeam(){
        $players = User::where('user_type', 'player')->get();
        $teamGenerator = (new FairTeamGenerator())->addPlayers($players);
        $teams = $teamGenerator->generate();
        foreach($teams as $key => $team){
            $hasGoalie = !empty($team->players->first(fn($player)=> $player['can_play_goalie'] === 1));
            self::assertTrue($hasGoalie);
        }
    }

    public function dataProviderNumberOfPlayersPerTeamAreWithin18To22(){
        parent::setUp();
        return [
            ['players' => User::where('user_type', 'player')->get()], // 85 players
            ['players' => User::where('user_type', 'player')->limit(79)->get()], // 79 players
            ['players' => User::where('user_type', 'player')->limit(72)->get()], // 68 players
        ];
    }

    /**
     * @dataProvider dataProviderNumberOfPlayersPerTeamAreWithin18To22
     */
    public function testNumberOfPlayersPerTeamAreWithin18To22 ($players)
    {
        $teamGenerator = (new FairTeamGenerator())->addPlayers($players);
        $teams = $teamGenerator->generate();
        foreach($teams as $key=>$team){
            self::assertLessThan(23, $team->players->count());
            self::assertGreaterThan(17, $team->players->count());
        }
    }

    /**
     * Player number scendarios where it's impossible to break up into even 18 - 22 player teams
     */
    public function dataProviderExceptionIsThrownWithImpossiblePlayers(){
        parent::setUp();
        return [
            ['players'=> User::limit(45)->get()],  // 45 players
            ['players' => User::where('user_type', 'player')->limit(60)->get()], // 79 players
            ['players'=> User::limit(90)->get()], // 90 players
            ['players'=> User::limit(90)->get()->concat(User::limit(10)->get()) ],  // 100 players
        ];
    }

    /**
     * @dataProvider dataProviderExceptionIsThrownWithImpossiblePlayers
     */
    public function testExceptionIsThrownWithImpossiblePlayers($players){
        // 90 players cannot possibly be divided into an even number of teams between 18 - 23 so it should throw an exception
        $teamGenerator = (new FairTeamGenerator())->addPlayers($players);
        try{
            $teams = $teamGenerator->generate();
        }catch(\Exception $e){
            self::assertStringStartsWith('Teams cannot be divided', $e->getMessage());
        }
    }

    public function testCanAdjustTeamNumberConstraintsToCompensateForTotalPlayers()
    {
        // 90 players cannot possibly be divided into an even number of teams between 18 - 23 so let's change the player range
        $players       = User::limit(90)->get();
        $teamGenerator = (new FairTeamGenerator())->addPlayers($players);
        $teamGenerator->setMaxNumberOfPlayers(23);
        $teams = $teamGenerator->generate();
        self::assertEquals(4, $teams->count());
    }
}
