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
        Schema::table('services', function (Blueprint $table) {
            $table->string('title')->nullable()->after('name'); // For compatibility
            $table->foreignId('department_id')->nullable()->constrained('departments')->after('image');
            $table->json('requirements')->nullable()->after('features');
            $table->json('procedure')->nullable()->after('requirements');
            $table->string('recovery_time')->nullable()->after('procedure');
            $table->boolean('is_emergency')->default(false)->after('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'title',
                'department_id',
                'requirements',
                'procedure',
                'recovery_time',
                'is_emergency'
            ]);
        });
    }
};
