<?php

use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\PeopleSearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('people-search', [PeopleSearchController::class, 'index']);
