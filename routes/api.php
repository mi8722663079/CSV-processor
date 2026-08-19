<?php

use App\Http\Controllers\ImportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/import.store', [ImportController::class, 'store']);
Route::get('/import.show/{import}',[ImportController::class, "show"]);