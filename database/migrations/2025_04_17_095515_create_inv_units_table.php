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
        Schema::create('inv_units', function (Blueprint $table) {
            $table->id("inv_unit_id");
            $table->integer("company_id");
            $table->integer("user_id");
            $table->string("inv_unit_name");
            $table->string("inv_unit_symbol");
            $table->string("inv_unit_symbol");
            $table->integer("inv_unit_status")->default(1);
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
        Schema::dropIfExists('inv_units');
    }
};
