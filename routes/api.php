<?php

use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

Route::get('/recipes', [RecipeController::class, 'index']);
Route::post('/recipe', [RecipeController::class, 'store']);
Route::post('recipe/{id}', [RecipeController::class, 'update']);
