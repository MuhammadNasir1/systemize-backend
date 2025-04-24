<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\inv_items;
use App\Models\inv_stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryStockController extends Controller
{
    public function getStock()
    {
        try {
            $user = Auth::user();
            // $stocks = inv_stock::with(['item:inv_items_id,inv_items_name,inv_items_stock,inv_items_stock', 'category'])->where('branch_id', $user->user_branch)->get();

            $stocks = inv_stock::with([
                'item:inv_items_id,inv_items_id,inv_items_name,inv_items_stock,inv_item_cats_id',
                'item.category:inv_item_cats_id,inv_item_cats_name'
            ])->where('branch_id', $user->user_branch)->where('inv_stocks_status', 1)->get();
            return response()->json(['success' => true, 'message' => "Inventory stock get successfully", 'data' => $stocks], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function createStock(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'inv_items_id' => 'required|integer',
                'inv_stock_qty' => 'required|integer',
                'inv_unit_purchase_price' => 'nullable',
                'inv_stocks_type' => 'required|string'
            ]);


            $item = inv_items::find($validated['inv_items_id']);

            if (!$item) {
                return response()->json(['success' => false, 'message' => "Inventory Item not found"], 400);
            }

            switch ($validated['inv_stocks_type']) {
                case 'stock_in':
                    $item->increment('inv_items_stock', $validated['inv_stock_qty']);
                    break;

                case 'stock_out':
                case 'stock_waste':
                    if ($item->inv_items_stock < $validated['inv_stock_qty']) {
                        return response()->json([
                            'success' => false,
                            'message' => "Stock out quantity exceeds available stock"
                        ], 400);
                    }
                    $item->decrement('inv_items_stock', $validated['inv_stock_qty']);
                    break;

                case 'stock_adjustment':
                    $item->update(['inv_items_stock' => $validated['inv_stock_qty']]);
                    break;

                case 'stock_transfer':
                default:
                    $item->decrement('inv_items_stock', $validated['inv_stock_qty']);
                    break;
            }

            $stock = inv_stock::create([
                'user_id' => $user->id,
                'branch_id' => $user->user_branch,
                'inv_items_id' => $validated['inv_items_id'],
                'supplier_id' => $item->supplier_id,
                'inv_stock_qty' => $validated['inv_stock_qty'],
                'inv_unit_purchase_price' => $validated['inv_unit_purchase_price'] ??  0,
                'inv_unit_expiry' => $request['inv_unit_expiry'],
                'inv_stocks_type' => $validated['inv_stocks_type'],
                'inv_stocks_reason' => $request['inv_stocks_reason']
            ]);
            return response()->json(['success' => true, 'message' => 'Stock added successfully', 'data' => $stock], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function  deleteStock($stock_id)
    {


        try {

            $stock = inv_stock::find($stock_id);
            if (!$stock) {
                return response()->json(['success' => false, 'message' => 'Stock not found'], 404);
            }

            $item = inv_items::find($stock->inv_items_id);

            if (!$item) {
                return response()->json(['success' => false, 'message' => "Inventory Item not found"], 400);
            }

            // Reverse the stock transaction
            switch ($stock->inv_stocks_type) {
                case 'stock_in':
                    $item->decrement('inv_items_stock', $stock->inv_stock_qty);
                    break;

                case 'stock_out':
                case 'stock_waste':
                    $item->increment('inv_items_stock', $stock->inv_stock_qty);
                    break;

                case 'stock_adjustment':
                    // Handle stock adjustment reversal if needed
                    break;

                case 'stock_transfer':
                default:
                    $item->increment('inv_items_stock', $stock->inv_stock_qty);
                    break;
            }

            $stock->inv_stocks_status = 0;
            $stock->update();
            return response()->json(['success' => true, 'message' => 'Stock deleted successfully'], 200);
        } catch (\Exception  $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
