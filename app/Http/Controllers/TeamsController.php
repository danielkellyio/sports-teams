<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\FairTeamGenerator;

class TeamsController extends Controller
{
    public function index(){
        $teamGenerator = new FairTeamGenerator();
        $teamGenerator->addPlayers(User::player()->get());

        return view('teams', [
            'teams' => $teamGenerator->generate()
        ]);
    }
}
