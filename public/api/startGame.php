<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';


use App\Events\GameLifecycle\StartGameEvent;
use App\Models\GamePlayer;
use App\Models\Hand;
use App\Models\Player;
use Illuminate\Http\Request;

// Session storage...
session_start();

// Bootstrap...
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Request::capture();
$app->instance('request', $request);
$kernel->bootstrap();

// Get post data
$playerId = $_POST['playerId'];

// Run game until first player input is required
event(new StartGameEvent($playerId));

// Get the cards for the player
//$gamePlayer= GamePlayer::where('player_id', $playerId)->latest('game_id')->first();
//$game = $gamePlayer->game;
//$round = $game->rounds()->latest()->first();
//$hand = $gamePlayer->hands()->where('round_id', $round->id)->first();
//$cardHands = $hand->cardHands()->whereNull('from_hand_id')->with('card')->get();

$state = $_SESSION["state"] ?? "";
$data = $_SESSION["data"] ?? "";

header('Content-Type: application/json');
$response = [
    'state' => $state,
    'data' => $data,
];

echo json_encode($response);
