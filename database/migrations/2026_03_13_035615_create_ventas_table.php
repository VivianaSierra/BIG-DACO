<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('ventas', function (Blueprint $table) {
        $table->id();
        $table->decimal('total',10,2);
        $table->timestamps();
    });
}
};
