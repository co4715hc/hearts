<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';

use App\Models\Player;
use Illuminate\Http\Request;

// Session storage...
session_start();

// Bootstrap...
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Request::capture();
$app->instance('request', $request);
$kernel->bootstrap();


// Fail if something weird happened.
if (!isset($_POST["username"])) {
    header('Content-Type: application/json');
    $response = [
        'success' => false,
        'userId' => null
    ];
    echo json_encode($response);
    return;
}

// Get old id
$username = $_POST["username"];
$player = Player::firstOrCreate(["name" => $username]);
$userId = $player->id;

header('Content-Type: application/json');
$response = [
    'success' => isset($_POST["username"]),
    'userId' => $userId
];

echo json_encode($response);
