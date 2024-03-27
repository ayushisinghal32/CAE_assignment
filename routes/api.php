<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RosterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('events', [EventController::class, 'index']);
Route::get('flights-next-week', [EventController::class, 'flightsNextWeek']);
Route::get('standby-next-week', [EventController::class, 'standbyNextWeek']);
Route::get('flights-from-location', [EventController::class, 'flightsFromLocation']);
Route::post('upload-roster', [RosterController::class, 'upload']);
