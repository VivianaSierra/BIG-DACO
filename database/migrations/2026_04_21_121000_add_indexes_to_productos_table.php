<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->index('nombre');
            $table->index('categoria');
            $table->index('stock');
            $table->index(['categoria', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex(['categoria', 'nombre']);
            $table->dropIndex(['stock']);
            $table->dropIndex(['categoria']);
            $table->dropIndex(['nombre']);
        });
    }
};
