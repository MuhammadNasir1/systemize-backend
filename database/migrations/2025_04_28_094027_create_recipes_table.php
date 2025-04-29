<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id("inv_recipe_id");
            $table->integer('user_id');
            $table->integer('company_id');
            $table->integer('branch_id');
            $table->integer('product_id');
            $table->json('inv_recipe_ingredient');
            $table->json('inv_recipe_cost')->default(0);
            $table->integer('inv_recipe_status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recipes');
    }
};
