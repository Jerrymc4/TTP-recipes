<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laravel</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        {{-- CSRF token for secure API requests --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link rel="stylesheet" href="{{ url('app.css') }}">
        <script src="{{ url('app.js') }}"></script>
    </head>
    <body>
        <h1 class="site-title">Retro Recipes</h1>

        <div class="container" x-data="recipesApp()">
            <div class="margin-y">
                <button class="primary" x-on:click="newRecipe.show = !newRecipe.show">New Recipe</button>
                <div class="card" x-show="newRecipe.show">
                    <fieldset>
                        <label>
                            <p>Name</p>
                            <input type="text" x-model="newRecipe.name">
                        </label>
                        <label>
                            <p>Description</p>
                            <input type="text" x-model="newRecipe.description">
                        </label>
                        <label><p>Ingredients</p></label>
                        <div class="box">
                            <template x-for="(ingredient, index) in newRecipe.ingredients" :key="index">
                                <p>
                                    <input type="text" x-model="newRecipe.ingredients[index]">
                                </p>
                            </template>
                            <p>
                                <button x-on:click="newRecipe.addIngredient">Add ingredient</button>
                            </p>
                        </div>
                        <p>
                            <button class="primary" x-on:click="newRecipe.submit">Save</button>
                        </p>
                    </fieldset>
                </div>
            </div>
            <div class="margin-y">
                <div x-show="loading" class="loading-text">Loading recipes...</div>

                <div x-show="!!error" class="error-text" x-cloak>
                    <p x-text="error"></p>
                </div>

                <div x-show="!loading" x-cloak>
                    <template x-for="(recipe, index) in recipes" :key="index">
                        <div class="card">
                            <div x-show="!recipe.editing">
                                <div class="flex">
                                    <h3 class="recipe-title flex-grow" x-html="recipe.data.name"></h3>
                                    <button x-on:click="startEditingExistingRecipe(recipe)">Edit</button>
                                </div>
                                <p class="recipe-description" x-html="recipe.data.description"></p>
                                <ul>
                                    <template x-for="(ingredient, index) in recipe.data.ingredients" :key="index">
                                        <li x-html="ingredient.name"></li>
                                    </template>
                                </ul>
                            </div>
                            <fieldset x-show="recipe.editing">
                                <label>
                                    <p>Name</p>
                                    <input type="text" x-model="recipe.editingData.name">
                                </label>
                                <label>
                                    <p>Description</p>
                                    <input type="text" x-model="recipe.editingData.description">
                                </label>
                                <label><p>Ingredients</p></label>
                                <div class="box">
                                    <template x-for="(ingredient, index) in recipe.editingData.ingredients" :key="index">
                                        <p>
                                            <input type="text" x-model="recipe.editingData.ingredients[index].name">
                                        </p>
                                    </template>
                                    <p>
                                        <button x-on:click="addIngredientToExistingRecipe(recipe)">Add ingredient</button>
                                    </p>
                                </div>
                                <p class="flex">
                                    <button x-on:click="recipe.editing = false">Cancel</button>
                                    <button class="primary" x-on:click="saveExistingRecipe(recipe)">Save</button>
                                </p>
                                <div x-show="!!recipe.error" class="error-text">
                                    <p x-text="recipe.error"></p>
                                </div>
                            </fieldset>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </body>
</html>
