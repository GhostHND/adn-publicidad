<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | USUARIO
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('employee_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | ACCIÓN
            |--------------------------------------------------------------------------
            */

            $table->string('module', 100)
                ->nullable()
                ->index();

            $table->string('action', 100)
                ->nullable()
                ->index();

            $table->string('description', 255)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | PETICIÓN
            |--------------------------------------------------------------------------
            */

            $table->string('route_name', 150)
                ->nullable()
                ->index();

            $table->string('method', 10)
                ->nullable();

            $table->text('url')
                ->nullable();

            $table->unsignedSmallInteger('status_code')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | ENTIDAD
            |--------------------------------------------------------------------------
            |
            | Se intenta detectar automáticamente el ID principal utilizado
            | por la ruta.
            |
            */

            $table->string('entity_type', 150)
                ->nullable();

            $table->unsignedBigInteger('entity_id')
                ->nullable()
                ->index();

            /*
            |--------------------------------------------------------------------------
            | INFORMACIÓN ENVIADA
            |--------------------------------------------------------------------------
            */

            $table->json('request_payload')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | ORIGEN
            |--------------------------------------------------------------------------
            */

            $table->string('ip_address', 45)
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            $table->timestamps();

            $table->index([
                'created_at',
                'module',
            ]);

            $table->index([
                'user_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'audit_logs'
        );
    }
};