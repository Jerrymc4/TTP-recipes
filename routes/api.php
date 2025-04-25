<?php

use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

Route::get('/recipes', [RecipeController::class, 'index']);
Route::get('recipe/new', [RecipeController::class, 'newRecipe']);
Route::post('recipe/{id}', [RecipeController::class, 'update']);
