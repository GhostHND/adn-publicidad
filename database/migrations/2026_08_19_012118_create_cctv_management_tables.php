<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cctv_projects')) {
            Schema::create('cctv_projects', function (Blueprint $table) {
                $table->id();

                $table->string('project_number', 30)
                    ->unique();

                $table->foreignId('client_id')
                    ->constrained('clients')
                    ->restrictOnDelete();

                $table->foreignId('quotation_id')
                    ->nullable()
                    ->constrained('quotations')
                    ->nullOnDelete();

                $table->foreignId('sale_id')
                    ->nullable()
                    ->constrained('sales')
                    ->nullOnDelete();

                $table->foreignId('work_order_id')
                    ->nullable()
                    ->unique()
                    ->constrained('work_orders')
                    ->nullOnDelete();

                $table->foreignId('installation_id')
                    ->nullable()
                    ->unique()
                    ->constrained('installations')
                    ->nullOnDelete();

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

                $table->string('system_type', 30)
                    ->default('unspecified');

                /*
                |--------------------------------------------------------------------------
                | Estados
                |--------------------------------------------------------------------------
                |
                | planning
                | survey_pending
                | equipment_pending
                | ready_installation
                | installation_scheduled
                | installation_in_progress
                | installed
                | maintenance
                | cancelled
                |
                */

                $table->string('status', 40)
                    ->default('planning');

                $table->string('site_name', 200)
                    ->nullable();

                $table->text('address')
                    ->nullable();

                $table->string('city', 150)
                    ->nullable();

                $table->string('contact_name', 200)
                    ->nullable();

                $table->string('contact_phone', 50)
                    ->nullable();

                $table->string('internet_provider', 150)
                    ->nullable();

                $table->text('network_notes')
                    ->nullable();

                $table->dateTime('site_survey_scheduled_at')
                    ->nullable();

                $table->dateTime('site_survey_completed_at')
                    ->nullable();

                $table->text('site_survey_notes')
                    ->nullable();

                $table->text('notes')
                    ->nullable();

                $table->dateTime('installed_at')
                    ->nullable();

                $table->date('maintenance_due_at')
                    ->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index([
                    'status',
                    'maintenance_due_at',
                ]);
            });
        }

        if (!Schema::hasTable('cctv_devices')) {
            Schema::create('cctv_devices', function (Blueprint $table) {
                $table->id();

                $table->foreignId('cctv_project_id')
                    ->constrained('cctv_projects')
                    ->cascadeOnDelete();

                $table->string('device_code', 40)
                    ->unique();

                $table->string('device_type', 50);

                $table->string('brand', 100)
                    ->nullable();

                $table->string('model', 150)
                    ->nullable();

                $table->string('serial_number', 150)
                    ->nullable();

                $table->string('mac_address', 100)
                    ->nullable();

                $table->string('ip_address', 100)
                    ->nullable();

                $table->unsignedInteger('channel')
                    ->nullable();

                $table->string('location', 255)
                    ->nullable();

                $table->string('status', 30)
                    ->default('active');

                $table->dateTime('installed_at')
                    ->nullable();

                $table->text('notes')
                    ->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index([
                    'cctv_project_id',
                    'device_type',
                ]);
            });
        }

        if (!Schema::hasTable('cctv_credentials')) {
            Schema::create('cctv_credentials', function (Blueprint $table) {
                $table->id();

                $table->foreignId('cctv_project_id')
                    ->constrained('cctv_projects')
                    ->cascadeOnDelete();

                $table->foreignId('cctv_device_id')
                    ->nullable()
                    ->constrained('cctv_devices')
                    ->nullOnDelete();

                $table->string('credential_type', 50);

                $table->string('label', 200);

                $table->string('username', 200)
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | El valor se almacena CIFRADO mediante Eloquent.
                |--------------------------------------------------------------------------
                */

                $table->text('secret_value')
                    ->nullable();

                $table->string('host', 255)
                    ->nullable();

                $table->unsignedInteger('port')
                    ->nullable();

                $table->text('notes')
                    ->nullable();

                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('cctv_maintenance_records')) {
            Schema::create('cctv_maintenance_records', function (Blueprint $table) {
                $table->id();

                $table->foreignId('cctv_project_id')
                    ->constrained('cctv_projects')
                    ->cascadeOnDelete();

                $table->foreignId('cctv_device_id')
                    ->nullable()
                    ->constrained('cctv_devices')
                    ->nullOnDelete();

                $table->foreignId('performed_by_employee_id')
                    ->nullable()
                    ->constrained('employees')
                    ->nullOnDelete();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string('maintenance_type', 50);

                $table->date('maintenance_date');

                $table->text('description');

                $table->text('findings')
                    ->nullable();

                $table->text('actions_taken')
                    ->nullable();

                $table->decimal('cost', 14, 2)
                    ->default(0);

                $table->date('next_due_date')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'cctv_project_id',
                    'maintenance_date',
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'cctv_maintenance_records'
        );

        Schema::dropIfExists(
            'cctv_credentials'
        );

        Schema::dropIfExists(
            'cctv_devices'
        );

        Schema::dropIfExists(
            'cctv_projects'
        );
    }
};