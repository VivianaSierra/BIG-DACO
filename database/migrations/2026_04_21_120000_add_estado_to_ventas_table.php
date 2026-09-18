<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (! Schema::hasColumn('ventas', 'estado')) {
                $table->string('estado', 20)->default('completada')->after('total');
            }

            if (! Schema::hasColumn('ventas', 'anulada_at')) {
                $table->timestamp('anulada_at')->nullable()->after('estado');
            }
        });

        DB::table('ventas')
            ->whereNull('estado')
            ->update(['estado' => 'completada']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (Schema::hasColumn('ventas', 'anulada_at')) {
                $table->dropColumn('anulada_at');
            }

            if (Schema::hasColumn('ventas', 'estado')) {
                $table->dropColumn('estado');
            }
        });
    }
};
