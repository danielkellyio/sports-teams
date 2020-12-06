<?php
namespace App\Support;
use Illuminate\Support\Collection;

class FairTeamGenerator{
    private Collection $players;
    private Int $minNumberOfPlayers = 18;
    private Int $maxNumberOfPlayers = 22;
    private Int $numberOfTeamsCached = 0;

    public function __construct(){
        $this->players = collect([]);
    }

    public function addPlayers(Collection $players){
        $this->players = $this->players->concat($players->toArray())->shuffle();
        return $this;
    }

    public function setMinNumberOfPlayers(Int $number){
        $this->minNumberOfPlayers = $number;
    }

    public function setMaxNumberOfPlayers(Int $number){
        $this->maxNumberOfPlayers = $number;
    }

    public function generate() : Collection{
        $numOfTeams = $this->getNumberOfTeams();
        $players    = $this->players->sortByDesc('ranking');
        $teams      = array_map(fn($item) => collect([]), range(1, $numOfTeams));
        $currentlyAssigning = 0;

        // add in goalies up front to make sure everybody has one
        foreach($teams as $key=>$team){
            $goalie         = $players->first(fn($player) => $player['can_play_goalie']);
            $teams[$key][]  = $goalie;
            // remove taken goalie
            $players = $players->filter(fn($player)=> $player !== $goalie);
        }

        // divy out the players to each team (ordered by top rank so everybody gets top ranking first, evening out rank as closely as possible)
        foreach($players as $player){
            $teams[$currentlyAssigning]->push($player);
            $currentlyAssigning = $currentlyAssigning === $numOfTeams -1
                ? 0
                : $currentlyAssigning + 1;
        }

        $teams = collect($teams);
        $this->checkForErrors($teams);
        return $teams->map(fn($team) => new Team($team));
    }

    private function getGoalies() : Collection{
        return $this->players->filter(fn($player)=> $player['can_play_goalie'] === 1);
    }

    private function getGoaliesEven() : int{
        $goaliesCount = $this->getGoalies()->count();
        return $this->isEven($goaliesCount) ? $goaliesCount : $goaliesCount - 1;
    }

    private function getNumberOfTeams(): int {
        if($this->numberOfTeamsCached) return $this->numberOfTeamsCached;

        $withMin = $this->players->count() / $this->minNumberOfPlayers;
        $withMax = $this->players->count() / $this->maxNumberOfPlayers;

        // try to get an even team number with the min number
        if($this->isEven(floor($withMin))){
            $numOfTeams = (int) floor($withMin);

        // Try to get an even team number with the max number
        }elseif($this->isEven(round($withMax))){
            $numOfTeams = (int) round($withMax);

        // can't do it, not possible
        }else{
            throw new \Exception($this->getExceptionMessage());
        }
        $numOfGoalies = $this->getGoaliesEven();

        // if not enough goalies for all teams limit teams to number of goalies
        return $this->numberOfTeamsCached  = $numOfTeams > $numOfGoalies
            ? $numOfGoalies : $numOfTeams;
    }

    private function getExceptionMessage() : String{
        $goalieCount = $this->getGoalies()->count();
        return "Teams cannot be divided with players between $this->minNumberOfPlayers and $this->maxNumberOfPlayers with $goalieCount goalies.";
    }

    private function checkForErrors($teams) : Void{
        foreach($teams as $team){
            $playerCount = $team->count();
            if( $playerCount > $this->maxNumberOfPlayers || $playerCount < $this->minNumberOfPlayers){
                throw new\Exception($this->getExceptionMessage() );
            }
        }
    }

    private function isEven($number) : Bool{
        return $number % 2 == 0;
    }
}
