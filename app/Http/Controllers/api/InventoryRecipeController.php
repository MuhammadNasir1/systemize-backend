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
            $recipes = Recipe::where("inv_recipe_status", 1)->get();

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
                'ingredients' => 'required|json',
            ]);

            $ingredients = json_decode($validatedData['ingredients'], true);

            foreach ($ingredients as $ingredient) {
                if (isset($ingredient['recipe_item_id']) && isset($ingredient['recipe_qty'])) {
                    $item = inv_items::where('inv_items_id', $ingredient['recipe_item_id'])->first();
                    if ($item) {
                        $item->inv_items_stock -= $ingredient['recipe_qty'];
                        $item->update();
                    }
                }
            }

            // Create a new recipe
            $recipe = Recipe::create([
                'user_id' => $user->id,
                'branch_id' => $user->user_branch,
                'company_id' => $user->company_id,
                'product_id' => $validatedData['product_id'],
                'inv_recipe_ingredient' => $validatedData['ingredients'], // Use validated JSON directly
                'inv_recipe_cost' => 0,
            ]);


            return response()->json(['message' => 'Recipe created successfully', 'data' => $recipe], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
