<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Set WhatsApp contact for IM Clinic doctors only
        $departmentId = DB::table('departments')
            ->where('name', 'like', '%IM Clinic%')
            ->value('id');

        if ($departmentId) {
            DB::table('doctors')
                ->where('department_id', $departmentId)
                ->whereNull('migration_contact_whatsapp')
                ->update(['migration_contact_whatsapp' => '+447459778835']);

            // Also update doctors linked via the pivot table
            $doctorIds = DB::table('doctor_department')
                ->where('department_id', $departmentId)
                ->pluck('doctor_id');

            if ($doctorIds->isNotEmpty()) {
                DB::table('doctors')
                    ->whereIn('id', $doctorIds)
                    ->whereNull('migration_contact_whatsapp')
                    ->update(['migration_contact_whatsapp' => '+447459778835']);
            }
        }
    }

    public function down(): void
    {
        DB::table('doctors')
            ->where('migration_contact_whatsapp', '+447459778835')
            ->update(['migration_contact_whatsapp' => null]);
    }
};
