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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['admin', 'doctor', 'nurse', 'receptionist', 'pharmacist', 'technician', 'staff'])->default('staff')->after('password');
            $table->boolean('is_active')->default(true)->after('is_admin');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null')->after('is_active');
            $table->string('avatar')->nullable()->after('department_id');
            $table->text('bio')->nullable()->after('avatar');
            $table->string('specialization')->nullable()->after('bio');
            $table->string('employee_id')->unique()->nullable()->after('specialization');
            $table->date('hire_date')->nullable()->after('employee_id');
            $table->timestamp('last_login_at')->nullable()->after('hire_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'role',
                'is_active',
                'department_id',
                'avatar',
                'bio',
                'specialization',
                'employee_id',
                'hire_date',
                'last_login_at'
            ]);
        });
    }
};
