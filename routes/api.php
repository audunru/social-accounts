<?php

use audunru\SocialAccounts\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

$path = config('social-accounts.api_path', 'social-accounts');
$middleware = config('social-accounts.api_middleware', ['api']);

Route::middleware($middleware)
    ->apiResource($path, ApiController::class)
    ->only('index', 'show', 'destroy');
