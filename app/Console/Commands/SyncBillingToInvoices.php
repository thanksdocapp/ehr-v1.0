<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Billing;

class SyncBillingToInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:sync-invoices {--force : Force sync even if invoice exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize existing billing records with patient invoices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting billing to invoice synchronization...');
        
        $billings = Billing::with(['patient', 'appointment'])->get();
        $synced = 0;
        $skipped = 0;
        
        foreach ($billings as $billing) {
            try {
                // Check if invoice already exists
                if ($billing->invoice && !$this->option('force')) {
                    $this->line("Skipping billing #{$billing->bill_number} - Invoice already exists");
                    $skipped++;
                    continue;
                }
                
                // Sync the billing with invoice
                $billing->syncWithInvoice();
                
                $this->info("✓ Synced billing #{$billing->bill_number} to invoice");
                $synced++;
                
            } catch (\Exception $e) {
                $this->error("✗ Failed to sync billing #{$billing->bill_number}: {$e->getMessage()}");
            }
        }
        
        $this->newLine();
        $this->info("Synchronization completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Synced', $synced],
                ['Skipped', $skipped],
                ['Total', $billings->count()]
            ]
        );
        
        return 0;
    }
}
