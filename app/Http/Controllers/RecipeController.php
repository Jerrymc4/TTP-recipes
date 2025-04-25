<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index(): JsonResponse
    {
        //Improved this to avoid N+1 queries 
        // Could further improve performance with pagination
        $recipes = Recipe::with('ingredients')->orderBy('created_at', 'desc')
        ->get();
        $formattedRecipes = $recipes->map(function ($recipe) {
            return $this->formatRecipe($recipe);
        });
        return response()->json($formattedRecipes);
    }

    public function store(Request $request): JsonResponse
    {
        try {
        $validator = validator($request->all(), [
            'name' => ['required', 'string', 'max:255', 'regex:/^[^<>]*$/'],
            'description' => ['required', 'string', 'regex:/^[^<>]*$/'],
            'ingredients' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }
    
        $recipe = new Recipe();
        $recipe->name = $request->input('name');
        $recipe->description = $request->input('description');
        $recipe->save();
    
        foreach ($request->input('ingredients') as $ingredientName) {
            $ingredient = new Ingredient();
            $ingredient->name = $ingredientName;
            $recipe->ingredients()->save($ingredient);
        }
    
        return response()->json($this->formatRecipe($recipe));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create recipe: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $recipe = Recipe::findOrFail($id);
            
            // Validate input
            $validator = validator($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'ingredients' => 'required|array',
                'ingredients.*.name' => 'required|string|max:255',
            ]);
            
            // Return specific validation errors
            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->first()
                ], 422);
            }
            
            $recipe->name = $request->input('name');
            $recipe->description = $request->input('description');
            $recipe->save();
            
            // Update ingredients
            $ingredientData = $request->input('ingredients');
            
            // Delete existing ingredients that aren't in the new data
            $existingIds = collect($ingredientData)
                ->pluck('id')
                ->filter()
                ->toArray();
                
            $recipe->ingredients()
                ->whereNotIn('id', $existingIds)
                ->delete();
            
            // Update or create ingredients
            foreach ($ingredientData as $item) {
                if (!empty($item['id'])) {
                    // Update existing
                    $ingredient = Ingredient::find($item['id']);
                    if ($ingredient) {
                        $ingredient->name = $item['name'];
                        $ingredient->save();
                    }
                } else {
                    // Create new
                    $ingredient = new Ingredient();
                    $ingredient->name = $item['name'];
                    $recipe->ingredients()->save($ingredient);
                }
            }
            
            // Refresh and return the updated recipe
            $recipe->refresh();
            return response()->json($this->formatRecipe($recipe));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update recipe: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Format a recipe for JSON response
     */
    private function formatRecipe(Recipe $recipe): array
    {
        return [
            'id' => $recipe->id,
            'name' => $recipe->name,
            'description' => $recipe->description,
            'ingredients' => $recipe->ingredients->map(function ($ingredient) {
                return [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                ];
            }),
        ];
    }
}
