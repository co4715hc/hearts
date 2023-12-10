<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';


use App\Events\GameLifecycle\StartGameEvent;
use App\Events\PassingPhase\PlayerPassInputtedEvent;
use App\Models\CardHand;
use App\Models\GamePlayer;
use App\Models\Hand;
use App\Models\Player;
use App\Models\Round;
use Illuminate\Http\Request;
// Session storage...
session_start();

// Bootstrap...
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Request::capture();
$app->instance('request', $request);
$kernel->bootstrap();

// Get post data
$gameState = $_SESSION["gameState"];
$playerId = $gameState['playerId'];
$roundId = $gameState['roundId'];
$handId = $gameState['handId'];
$cardHandIds = $_POST['cards'];

$round = Round::find($roundId);
$player = GamePlayer::find($playerId);
$hand = Hand::find($handId);
$cardHands = CardHand::whereIn('id', $cardHandIds)->get();

event(new PlayerPassInputtedEvent($round, $player, $cardHands));

$state = $_SESSION["state"] ?? "";
$data = $_SESSION["data"] ?? "";

header('Content-Type: application/json');
$response = [
    'state' => $state,
    'data' => $data,
];

echo json_encode($response);
