<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_leads', function (Blueprint $table) {
            $table->id();

            $table->string('lead_number', 30)
                ->nullable()
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | CLIENTE POTENCIAL
            |--------------------------------------------------------------------------
            */

            $table->string('name', 150);

            $table->string('business_name', 180)
                ->nullable();

            $table->string('phone', 40);

            $table->string('email', 180)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | SOLICITUD
            |--------------------------------------------------------------------------
            */

            $table->string('service_interest', 180)
                ->nullable();

            $table->text('message');

            $table->string('source', 50)
                ->default('website');

            /*
            |--------------------------------------------------------------------------
            | ESTADO
            |--------------------------------------------------------------------------
            |
            | new
            | contacted
            | quoted
            | won
            | lost
            |
            */

            $table->string('status', 30)
                ->default('new')
                ->index();

            /*
            |--------------------------------------------------------------------------
            | RESPONSABLE
            |--------------------------------------------------------------------------
            */

            $table->foreignId('assigned_employee_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | SEGUIMIENTO
            |--------------------------------------------------------------------------
            */

            $table->timestamp('contacted_at')
                ->nullable();

            $table->timestamp('closed_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | ORIGEN TÉCNICO
            |--------------------------------------------------------------------------
            */

            $table->string('ip_address', 45)
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            $table->timestamps();

            $table->index([
                'status',
                'created_at',
            ]);

            $table->index([
                'assigned_employee_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'website_leads'
        );
    }
};