<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TestController;

Route::post('/password-tool', [TestController::class, 'handlePasswordTool']);
Route::get('/test-db-connection', [TestController::class, 'testDynamicConnection']);
Route::post('/update-password', [TestController::class, 'updateTenantPasswordDynamic']);