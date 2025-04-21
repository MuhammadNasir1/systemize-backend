<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class inv_items extends Model
{
    use HasFactory;

    protected $table =  "inv_items";

    protected $primaryKey = "inv_items_id";

    protected $fillable = [
        "company_id",
        "branch_id",
        "user_id",
        "supplier_id",
        "inv_unit_id",
        "inv_items_name",
        "inv_item_cats_id",
        "inv_items_bag_qty",
        "inv_items_stock",
        "unit_purchase_price",
        "inv_stock_alert",
        "inv_auto_order",
        "inv_unit_status",
    ];

    public $timestamp  = true;

}
