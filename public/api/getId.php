<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';

use Illuminate\Http\Request;

// Session storage...
session_start();

// Bootstrap...
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Request::capture();
$app->instance('request', $request);
$kernel->bootstrap();

// Get old id
$playerId = $_SESSION["gameState"]["userId"] ?? null;

header('Content-Type: application/json');
$response = [
    'success' => $playerId !== null,
    'userId' => $playerId
];

echo json_encode($response);
