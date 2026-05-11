<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            try {
                $table->dropUnique(['email']);
            } catch (\Throwable $e) {
                // Index name may differ by driver / already dropped
            }
        });

        Schema::table('patients', function (Blueprint $table) {
            try {
                $table->index('email');
            } catch (\Throwable $e) {
            }
        });

        if (! Schema::hasColumn('patients', 'contact_group_id')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->unsignedBigInteger('contact_group_id')->nullable()->after('department_id');
            });
        }

        if (! Schema::hasTable('patient_contact_groups')) {
            Schema::create('patient_contact_groups', function (Blueprint $table) {
                $table->id();
                $table->string('label');
                $table->string('description')->nullable();
                $table->timestamps();
            });

            DB::table('patient_contact_groups')->insert([
                [
                    'label' => 'Household / family',
                    'description' => 'Shared email or phone for family members',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'label' => 'Management / agency',
                    'description' => 'One contact manages multiple patient records (e.g. casting)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        if (Schema::hasColumn('patients', 'contact_group_id')) {
            Schema::table('patients', function (Blueprint $table) {
                try {
                    $table->foreign('contact_group_id')->references('id')->on('patient_contact_groups')->nullOnDelete();
                } catch (\Throwable $e) {
                }
            });
        }

        if (! Schema::hasTable('patient_password_reset_tokens')) {
            Schema::create('patient_password_reset_tokens', function (Blueprint $table) {
                $table->unsignedBigInteger('patient_id')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
                $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_password_reset_tokens');

        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'contact_group_id')) {
                try {
                    $table->dropForeign(['contact_group_id']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('contact_group_id');
            }
        });

        Schema::dropIfExists('patient_contact_groups');

        Schema::table('patients', function (Blueprint $table) {
            try {
                $table->dropIndex(['email']);
            } catch (\Throwable $e) {
            }
        });

        Schema::table('patients', function (Blueprint $table) {
            try {
                $table->unique('email');
            } catch (\Throwable $e) {
            }
        });
    }
};
