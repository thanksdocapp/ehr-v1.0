@php
    $migrationNewUrl = \App\Models\Setting::get('migration_mode_new_url', 'https://my.thanksdoc.co.uk');
@endphp

<div class="modal fade" id="migrationModeModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="migrationModeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border: 3px solid #f59e0b; border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none; padding: 24px 30px;">
                <h4 class="modal-title text-white" id="migrationModeModalLabel" style="font-weight: 700;">
                    <i class="fas fa-exchange-alt me-2"></i>We've Moved to a New System
                </h4>
            </div>
            <div class="modal-body" style="padding: 30px;">
                <div class="text-center mb-4">
                    <div style="width: 80px; height: 80px; background: #fef3c7; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                        <i class="fas fa-arrow-right" style="font-size: 32px; color: #d97706;"></i>
                    </div>
                    <h5 style="font-weight: 600; color: #1e293b;">Your new EHR is ready!</h5>
                </div>

                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <p style="font-size: 15px; color: #92400e; margin-bottom: 12px;">
                        <i class="fas fa-info-circle me-2"></i>
                        This system is being retired. Your patients, records, and services have been migrated to the new EHR.
                    </p>
                    <p style="font-size: 14px; color: #78350f; margin-bottom: 0;">
                        Please use the new system for all consultations, bookings, and patient management going forward.
                        This system is now in <strong>read-only mode</strong> — you can still view your services, patients, appointments, and settlement requests.
                    </p>
                </div>

                <div class="text-center">
                    <a href="{{ $migrationNewUrl }}" target="_blank" class="btn btn-lg" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; font-weight: 600; padding: 12px 40px; border-radius: 10px; border: none; font-size: 16px;">
                        <i class="fas fa-external-link-alt me-2"></i>Go to New EHR
                    </a>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 16px 30px; justify-content: space-between;">
                <small class="text-muted"><i class="fas fa-lock me-1"></i>This system is in read-only mode</small>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    Continue in Read-Only Mode
                </button>
            </div>
        </div>
    </div>
</div>

<style>
#migrationModeModal .modal-content {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var migrationModal = new bootstrap.Modal(document.getElementById('migrationModeModal'));
    var dismissedKey = 'migration_modal_dismissed_' + new Date().toISOString().slice(0, 10);
    if (!sessionStorage.getItem(dismissedKey)) {
        migrationModal.show();
    }
    document.getElementById('migrationModeModal').addEventListener('hidden.bs.modal', function() {
        sessionStorage.setItem(dismissedKey, '1');
    });
});
</script>
