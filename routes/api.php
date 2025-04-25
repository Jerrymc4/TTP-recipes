<?php

use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

Route::get('/recipes', [RecipeController::class, 'getRecipes']);
Route::get('recipe/new', [RecipeController::class, 'newRecipe']);
Route::post('recipe/{id}', [RecipeController::class, 'update']);
