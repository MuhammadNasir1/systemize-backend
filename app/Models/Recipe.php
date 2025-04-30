<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $primaryKey = 'inv_recipe_id';
    protected $table = 'recipes';

    protected $fillable = [
        "user_id",
        "company_id",
        "branch_id",
        "product_id",
        "inv_recipe_ingredient",
        "inv_recipe_cost",
        "inv_recipe_status"
    ];
    public $timestamps = true;

    protected $casts = [
        'inv_recipe_ingredient' => 'array',
    ];
    


    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
}
