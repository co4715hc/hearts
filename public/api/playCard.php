<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';


use App\Events\GameLifecycle\StartGameEvent;
use App\Events\PassingPhase\PlayerPassInputtedEvent;
use App\Events\TrickPhase\PlayerTrickInputtedEvent;
use App\Models\CardHand;
use App\Models\GamePlayer;
use App\Models\Hand;
use App\Models\Player;
use App\Models\Round;
use App\Models\Trick;
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
$trickId = $gameState['trickId'];

$cardHandId = $_POST['card'];

$round = Round::find($roundId);
$player = GamePlayer::find($playerId);
$trick = Trick::find($trickId);
$hand = Hand::find($handId);
$cardHand = CardHand::find($cardHandId);

event(new PlayerTrickInputtedEvent($trick, $player, $cardHand));

$state = $_SESSION["state"] ?? "";
$data = $_SESSION["data"] ?? "";

header('Content-Type: application/json');
$response = [
    'state' => $state,
    'data' => $data,
];

echo json_encode($response);
