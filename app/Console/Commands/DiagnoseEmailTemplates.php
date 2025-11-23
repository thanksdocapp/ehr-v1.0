<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailTemplate;
use App\Http\Controllers\Admin\EmailTemplatesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DiagnoseEmailTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diagnose:email-templates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose email template issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🩺 Email Template Diagnosis');
        $this->info('==========================');
        
        // Check database connection
        $this->info('\n1. Database Connection:');
        try {
            $dbConnection = config('database.default');
            $this->info("   Connection: {$dbConnection}");
            
            if ($dbConnection === 'mysql') {
                $host = config('database.connections.mysql.host');
                $database = config('database.connections.mysql.database');
                $this->info("   Host: {$host}");
                $this->info("   Database: {$database}");
            }
            
            DB::connection()->getPdo();
            $this->info('   ✅ Database connection successful');
        } catch (\Exception $e) {
            $this->error('   ❌ Database connection failed: ' . $e->getMessage());
            return 1;
        }
        
        // Check if email_templates table exists
        $this->info('\n2. Email Templates Table:');
        if (Schema::hasTable('email_templates')) {
            $this->info('   ✅ email_templates table exists');
            
            // Check table structure
            $columns = Schema::getColumnListing('email_templates');
            $this->info('   Columns: ' . implode(', ', $columns));
        } else {
            $this->error('   ❌ email_templates table does not exist');
            $this->warn('   Run: php artisan migrate');
            return 1;
        }
        
        // Check email template count
        $this->info('\n3. Email Template Data:');
        try {
            $count = EmailTemplate::count();
            $this->info("   Total templates: {$count}");
            
            if ($count === 0) {
                $this->warn('   ⚠️  No email templates found');
                $this->info('   Run: php artisan seed:email-templates');
            } else {
                $this->info('   ✅ Email templates found');
                
                // Show template summary
                $templates = EmailTemplate::select('name', 'subject', 'status', 'category')->get();
                $this->table(
                    ['Name', 'Subject', 'Status', 'Category'],
                    $templates->map(function ($template) {
                        return [
                            $template->name,
                            substr($template->subject, 0, 50) . (strlen($template->subject) > 50 ? '...' : ''),
                            $template->status,
                            $template->category
                        ];
                    })->toArray()
                );
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Error querying email templates: ' . $e->getMessage());
        }
        
        // Test EmailTemplate model
        $this->info('\n4. Model Test:');
        try {
            $template = EmailTemplate::first();
            if ($template) {
                $this->info('   ✅ EmailTemplate model working');
                $this->info('   Sample template: ' . $template->name);
            } else {
                $this->warn('   ⚠️  No templates to test model with');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ EmailTemplate model error: ' . $e->getMessage());
        }
        
        // Test controller
        $this->info('\n5. Controller Test:');
        try {
            $controller = new EmailTemplatesController();
            $result = $controller->index();
            $this->info('   ✅ EmailTemplatesController working');
            $this->info('   View: ' . $result->name());
            $data = $result->getData();
            if (isset($data['templates'])) {
                $this->info('   Templates passed to view: ' . $data['templates']->count());
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Controller error: ' . $e->getMessage());
        }
        
        // Check routes
        $this->info('\n6. Route Check:');
        try {
            $routes = \Route::getRoutes()->getByName('admin.email-templates.index');
            if ($routes) {
                $this->info('   ✅ email-templates.index route exists');
            } else {
                $this->error('   ❌ email-templates.index route not found');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Route check error: ' . $e->getMessage());
        }
        
        // Check view files
        $this->info('\n7. View Files:');
        $viewPath = resource_path('views/admin/communication/email-templates/index.blade.php');
        if (file_exists($viewPath)) {
            $this->info('   ✅ Email templates index view exists');
        } else {
            $this->error('   ❌ Email templates index view not found');
            $this->info('   Expected: ' . $viewPath);
        }
        
        // Environment check
        $this->info('\n8. Environment:');
        $this->info('   APP_ENV: ' . config('app.env'));
        $this->info('   APP_DEBUG: ' . (config('app.debug') ? 'true' : 'false'));
        $this->info('   Cache driver: ' . config('cache.default'));
        
        // Permissions check (for shared hosting)
        $this->info('\n9. File Permissions:');
        $storagePath = storage_path();
        $this->info('   Storage path: ' . $storagePath);
        $this->info('   Storage writable: ' . (is_writable($storagePath) ? 'Yes' : 'No'));
        
        $this->info('\n🎉 Diagnosis completed!');
        $this->info('If templates are not showing in admin, try:');
        $this->info('1. php artisan seed:email-templates');
        $this->info('2. php artisan config:clear');
        $this->info('3. php artisan cache:clear');
        $this->info('4. php artisan view:clear');
        
        return 0;
    }
}
