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
        Schema::table('faqs', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('faqs', 'order')) {
                $table->integer('order')->default(0)->after('is_active');
            }
            if (!Schema::hasColumn('faqs', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('order');
            }
            if (!Schema::hasColumn('faqs', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropIndex(['category', 'is_active']);
            $table->dropIndex(['order', 'created_at']);
            $table->dropColumn(['order', 'created_by', 'updated_by']);
        });
    }
};
