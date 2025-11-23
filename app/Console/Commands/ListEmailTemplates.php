<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailTemplate;

class ListEmailTemplates extends Command
{
    protected $signature = 'email-templates:list';
    protected $description = 'List all email templates';

    public function handle()
    {
        $templates = EmailTemplate::select('name', 'status', 'category', 'description')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        if ($templates->isEmpty()) {
            $this->info('No email templates found.');
            return 0;
        }

        $this->info('Email Templates (' . $templates->count() . ' total):');
        $this->newLine();

        $data = [];
        foreach ($templates as $template) {
            $data[] = [
                'Name' => $template->name,
                'Status' => $template->status,
                'Category' => $template->category,
                'Description' => $template->description ? substr($template->description, 0, 50) . '...' : '-',
            ];
        }

        $this->table(['Name', 'Status', 'Category', 'Description'], $data);

        return 0;
    }
}

