<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if table already exists (from partial migration)
        if (Schema::hasTable('email_bounces')) {
            // Table exists, check if email_logs exists and add foreign key if needed
            if (Schema::hasTable('email_logs')) {
                // Check if foreign key already exists
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'email_bounces' 
                    AND CONSTRAINT_NAME = 'email_bounces_email_log_id_foreign'
                ");
                
                if (empty($foreignKeys)) {
                    // Add foreign key constraint
                    DB::statement('ALTER TABLE email_bounces ADD CONSTRAINT email_bounces_email_log_id_foreign FOREIGN KEY (email_log_id) REFERENCES email_logs(id) ON DELETE CASCADE');
                }
            }
            return; // Table already exists, skip creation
        }
        
        // Check if email_logs table exists before creating foreign key
        $emailLogsExists = Schema::hasTable('email_logs');
        
        Schema::create('email_bounces', function (Blueprint $table) use ($emailLogsExists) {
            $table->id();
            
            if ($emailLogsExists) {
                $table->foreignId('email_log_id')->constrained('email_logs')->onDelete('cascade');
            } else {
                $table->unsignedBigInteger('email_log_id')->nullable();
            }
            
            $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('cascade');
            $table->string('email_address');
            $table->enum('bounce_type', ['hard', 'soft', 'complaint'])->default('soft');
            $table->string('bounce_reason')->nullable();
            $table->text('bounce_message')->nullable();
            $table->string('smtp_response_code')->nullable();
            $table->timestamp('bounced_at');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            
            $table->index('email_log_id');
            $table->index('patient_id');
            $table->index('email_address');
            $table->index('bounce_type');
            $table->index('bounced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_bounces');
    }
};

