<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->string('employee_code', 20)->unique();

            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();

            $table->string('last_name', 100);
            $table->string('second_last_name', 100)->nullable();

            $table->string('identity_number', 30)
                ->nullable()
                ->unique();

            $table->string('gender', 20)->nullable();

            $table->date('birth_date')->nullable();

            $table->string('email', 150)
                ->nullable()
                ->unique();

            $table->string('phone', 30);

            $table->string('alternate_phone', 30)->nullable();

            $table->text('address')->nullable();

            $table->string('position', 100)->nullable();

            $table->date('hire_date')->nullable();

            $table->text('notes')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};