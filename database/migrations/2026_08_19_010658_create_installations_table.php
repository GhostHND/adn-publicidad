<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('installations')) {
            Schema::create('installations', function (Blueprint $table) {
                $table->id();

                $table->string('installation_number', 30)
                    ->unique();

                $table->foreignId('work_order_id')
                    ->unique()
                    ->constrained('work_orders')
                    ->cascadeOnDelete();

                $table->foreignId('client_id')
                    ->constrained('clients')
                    ->restrictOnDelete();

                $table->foreignId('responsible_employee_id')
                    ->nullable()
                    ->constrained('employees')
                    ->nullOnDelete();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string('source', 30)
                    ->default('automatic');

                /*
                |--------------------------------------------------------------------------
                | Estados
                |--------------------------------------------------------------------------
                |
                | pending_schedule = pendiente de programar
                | scheduled        = programada
                | on_route         = en camino
                | in_progress      = instalando
                | completed        = instalada
                | cancelled        = cancelada
                |
                */

                $table->string('status', 30)
                    ->default('pending_schedule');

                $table->dateTime('scheduled_at')
                    ->nullable();

                $table->dateTime('departed_at')
                    ->nullable();

                $table->dateTime('started_at')
                    ->nullable();

                $table->dateTime('completed_at')
                    ->nullable();

                $table->string('contact_name', 200)
                    ->nullable();

                $table->string('contact_phone', 50)
                    ->nullable();

                $table->text('address')
                    ->nullable();

                $table->string('city', 150)
                    ->nullable();

                $table->string('reference', 255)
                    ->nullable();

                $table->unsignedInteger('estimated_duration_minutes')
                    ->nullable();

                $table->text('notes')
                    ->nullable();

                $table->text('completion_notes')
                    ->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index([
                    'status',
                    'scheduled_at',
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('installations');
    }
};