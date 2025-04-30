<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\inv_items;
use App\Models\inv_stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryReportController extends Controller
{
    public function getReport(Request $request)
    {
        try {
            $user = Auth::user();
            $validatedData = $request->validate([
                "from_date" => "nullable|date|before_or_equal:today",
                "to_date" => "nullable|date|after_or_equal:from_date",
                "report_period" => "nullable|in:daily,weekly,monthly",
                "report_item" => "nullable|integer",
                "report_type" => "nullable|in:stock_in,stock_out,stock_waste,all",
            ]);

            $from_date = $validatedData["from_date"] ?? null;
            $to_date = $validatedData["to_date"] ?? null;

            $query = inv_stock::query();

            if ($from_date) {
                $query->whereDate('created_at', '>=', $from_date);
            }

            if ($to_date) {
                $query->whereDate('created_at', '<=', $to_date);
            }

            if (!empty($validatedData['report_item'])) {
                $query->where('inv_items_id', $validatedData['report_item']);
            }
            if (!empty($validatedData['report_type']) && $validatedData['report_type'] != 'all') {
                $query->where('inv_stocks_type', $validatedData['report_type']);
            }

            $items = $query->get();

            return response()->json(['success' => true, 'message' => "Inventory report get successfully", 'data' => $items], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
