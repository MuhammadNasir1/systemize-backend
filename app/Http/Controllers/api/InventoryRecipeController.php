<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\inv_items;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryRecipeController extends Controller
{

    public function getRecipes()
    {

        try {
            $user = Auth::user();

            $recipes = Recipe::with(['product'])->where("company_id", $user->company_id)->where('branch_id', $user->user_branch)->where("inv_recipe_status", 1)->get();


            return response()->json(['success' => true, 'message' => "Recipes get successfully", "recipes" => $recipes], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' =>  $e->getMessage()], 500);
        }
    }

    public function createRecipe(Request $request)
    {

        try {

            $user = Auth::user();

            $validatedData = $request->validate([
                'product_id' => 'required|integer',
                'ingredients' => 'required|array',
            ]);

            // Ingredients is already an array from validation
            $ingredients = $validatedData['ingredients'];

            $totalCost = 0;

            // Loop through each ingredient to update stock and calculate cost
            foreach ($ingredients as $ingredient) {
                if (isset($ingredient['recipe_item_id']) && isset($ingredient['recipe_qty'])) {
                    $item = inv_items::where('inv_items_id', $ingredient['recipe_item_id'])->first();
                    if ($item) {
                        // Calculate cost for this ingredient
                        $itemCost = $item->unit_purchase_price * $ingredient['recipe_qty'];
                        $totalCost += $itemCost;
                    }
                }
            }

            // Create a new recipe with calculated cost
            $recipe = Recipe::create([
                'user_id' => $user->id,
                'branch_id' => $user->user_branch,
                'company_id' => $user->company_id,
                'product_id' => $validatedData['product_id'],
                'inv_recipe_ingredient' => $validatedData['ingredients'], // Convert array to JSON string
                'inv_recipe_cost' => $totalCost,
            ]);

            return response()->json(['message' => 'Recipe created successfully', 'data' => $recipe], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteRecipe($recipe_id)
    {

        try {
            $recipe = Recipe::find($recipe_id);
            if (!$recipe) {
                return response()->json(['success' => false, 'message' => 'Recipe not found'], 404);
            }
            $recipe->inv_recipe_status = 0;
            $recipe->update();
            return response()->json(['success' => true, 'message' => "Recipe delete successfully"], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateRecipe(Request $request, $recipe_id)
    {

        try {
            $user = Auth::user();
            $validatedData = $request->validate([
                'product_id' => 'required|integer',
                'ingredients' => 'required|array',
            ]);

            $recipe = Recipe::find($recipe_id);
            if (!$recipe) {
                return response()->json(['success' => false, 'message' => 'Recipe not found'], 404);
            }
            // Recalculate total cost
            $totalCost = 0;
            foreach ($validatedData['ingredients'] as $ingredient) {
                if (isset($ingredient['recipe_item_id']) && isset($ingredient['recipe_qty'])) {
                    $item = inv_items::where('inv_items_id', $ingredient['recipe_item_id'])->first();
                    if ($item) {
                        $itemCost = $item->unit_purchase_price * $ingredient['recipe_qty'];
                        $totalCost += $itemCost;
                    }
                }
            }
            $recipe->update([
                'product_id' => $validatedData['product_id'],
                'inv_recipe_ingredient' => $validatedData['ingredients'],
                'inv_recipe_cost' => $totalCost,
            ]);

            return response()->json(['success' => true, 'message' => 'Recipe updated successfully', 'data' => $recipe], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' =>  $e->getMessage()], 500);
        }
    }
}
