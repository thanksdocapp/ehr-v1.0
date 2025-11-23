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
        Schema::table('email_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('email_templates', 'target_roles')) {
                $table->json('target_roles')->nullable()->after('category');
                $table->index('target_roles');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            if (Schema::hasColumn('email_templates', 'target_roles')) {
                $table->dropIndex(['target_roles']);
                $table->dropColumn('target_roles');
            }
        });
    }
};
