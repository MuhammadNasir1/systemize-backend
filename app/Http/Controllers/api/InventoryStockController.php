<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\inv_items;
use App\Models\inv_stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryStockController extends Controller
{

    public function createStock(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'inv_items_id' => 'required|integer',
                'supplier_id' => 'required|integer',
                'inv_stock_qty' => 'required|integer',
                'inv_unit_purchase_price' => 'required|numeric',
                'inv_unit_expiry' => 'required|date',
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
                'supplier_id' => $validated['supplier_id'],
                'inv_stock_qty' => $validated['inv_stock_qty'],
                'inv_unit_purchase_price' => $validated['inv_unit_purchase_price'],
                'inv_unit_expiry' => $validated['inv_unit_expiry'],
                'inv_stocks_type' => $validated['inv_stocks_type']
            ]);
            return response()->json(['success' => true, 'message' => 'Stock added successfully', 'data' => $stock], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
