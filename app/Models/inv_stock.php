<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class inv_stock extends Model
{
    use HasFactory;

    protected $table = "inv_stocks";
    protected $primaryKey = "inv_stocks_id";

    protected $fillable = [
        "user_id",
        "branch_id",
        "inv_items_id",
        "supplier_id",
        "inv_stock_qty",
        "inv_unit_purchase_price",
        "inv_unit_expiry",
        "inv_stocks_type",
    ];

    public $timestamp = true;
}
