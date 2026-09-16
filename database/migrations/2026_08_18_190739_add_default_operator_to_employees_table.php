<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('is_default_operator')
                ->default(false)
                ->after('active');
        });

        /*
        |--------------------------------------------------------------------------
        | Modo operador único
        |--------------------------------------------------------------------------
        |
        | Si solamente existe un empleado activo, lo dejamos automáticamente
        | configurado como operador principal del sistema.
        |
        */

        $activeEmployees = DB::table('employees')
            ->where('active', true)
            ->whereNull('deleted_at')
            ->pluck('id');

        if ($activeEmployees->count() === 1) {
            DB::table('employees')
                ->where('id', $activeEmployees->first())
                ->update([
                    'is_default_operator' => true,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('is_default_operator');
        });
    }
};