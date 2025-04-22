<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\inv_items;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryItemsController extends Controller
{


    public function createItem(Request $request)
    {
        try {

            $user = Auth::user();
            $validatedData = $request->validate([
                "supplier_id" => "required",
                "inv_unit_id" => "required",
                "inv_items_name" => "required",
                "inv_item_cats_id" => "required",
                "inv_items_bag_qty" => "required",
                "inv_items_stock" => "required",
                "unit_purchase_price" => "required",
                "inv_stock_alert" => "required",
                "inv_auto_order" => "nullable"
            ]);

            $inventory_item = inv_items::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'branch_id' => $user->user_branch,
                'supplier_id' => $validatedData["supplier_id"],
                "inv_unit_id" => $validatedData["inv_unit_id"],
                "inv_items_name" => $validatedData["inv_items_name"],
                "inv_item_cats_id" => $validatedData["inv_item_cats_id"],
                "inv_items_bag_qty" => $validatedData["inv_items_bag_qty"],
                "inv_items_stock" => $validatedData["inv_items_stock"],
                "unit_purchase_price" => $validatedData["unit_purchase_price"],
                "inv_stock_alert" => $validatedData["inv_stock_alert"],
                "inv_auto_order" => $validatedData["inv_auto_order"],
            ]);

            return response()->json(['success' => true, 'message' => "Inventory item add successfully", 'data' => $inventory_item], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getItems()
    {
        try {
            $user = Auth::user();
            // $items = inv_items::where('company_id', $user->company_id)->where('branch_id', $user->user_branch)->get();
            $items = inv_items::with(['category:inv_item_cats_id,inv_item_cats_name', 'unit:inv_unit_id,inv_unit_name'])->where('company_id', $user->company_id)->where('branch_id', $user->user_branch)->where('inv_unit_status', 1)->get();
            return response()->json(['success' => true, 'message' => "Inventory item get successfully", 'data' => $items], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }


    public function deleteItem($item_id)
    {

        try {
            $item  = inv_items::find($item_id);
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Item not found'], 404);
            }

            $item->inv_unit_status = 0;
            $item->update();
            return response()->json(['success' => true, 'message' => "Inventory item deleted successfully"], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    public function updataItem(Request $request, $item_id)
    {
        try {

            $validatedData = $request->validate([
                "supplier_id" => "required",
                "inv_unit_id" => "required",
                "inv_items_name" => "required",
                "inv_item_cats_id" => "required",
                "inv_items_bag_qty" => "required",
                "inv_items_stock" => "required",
                "unit_purchase_price" => "required",
                "inv_stock_alert" => "required",
                "inv_auto_order" => "nullable"
            ]);
            $item  = inv_items::find($item_id);
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Item not found'], 404);
            }
            // $item-> = 0;
            $item->supplier_id = $validatedData["supplier_id"];
            $item->inv_unit_id = $validatedData["inv_unit_id"];
            $item->inv_items_name = $validatedData["inv_items_name"];
            $item->inv_item_cats_id = $validatedData["inv_item_cats_id"];
            $item->inv_items_bag_qty = $validatedData["inv_items_bag_qty"];
            $item->inv_items_stock = $validatedData["inv_items_stock"];
            $item->unit_purchase_price = $validatedData["unit_purchase_price"];
            $item->inv_stock_alert = $validatedData["inv_stock_alert"];
            $item->inv_auto_order = $validatedData["inv_auto_order"];
            $item->update();
            return response()->json(['success' => true, 'message' => "Inventory update successfully", 'data' => $item], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
