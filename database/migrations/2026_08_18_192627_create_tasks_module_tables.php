<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->string('task_number', 30)
                ->unique();

            $table->foreignId('work_order_id')
                ->constrained('work_orders')
                ->cascadeOnDelete();

            $table->foreignId('responsible_employee_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('automation_key', 150)
                ->nullable();

            $table->string('title', 200);

            $table->text('description')
                ->nullable();

            $table->string('status', 30)
                ->default('pending');

            $table->string('priority', 20)
                ->default('normal');

            $table->integer('sort_order')
                ->default(0);

            $table->boolean('is_review_task')
                ->default(false);

            $table->dateTime('started_at')
                ->nullable();

            $table->dateTime('paused_at')
                ->nullable();

            $table->dateTime('completed_at')
                ->nullable();

            $table->dateTime('review_at')
                ->nullable();

            $table->dateTime('issue_reported_at')
                ->nullable();

            $table->text('issue_description')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique([
                'work_order_id',
                'automation_key',
            ]);
        });

        Schema::create('task_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_id')
                ->constrained('tasks')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('event_type', 50);

            $table->text('notes')
                ->nullable();

            $table->dateTime('occurred_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_events');
        Schema::dropIfExists('tasks');
    }
};