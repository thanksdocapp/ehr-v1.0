<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\Transaction;
use Carbon\Carbon;

class EmailTemplateService
{
    /**
     * Process email template with user and transaction data
     *
     * @param string $templateName
     * @param User $user
     * @param Transaction|null $transaction
     * @param array $additionalData
     * @return array
     */
    public function processTemplate(string $templateName, User $user, Transaction $transaction = null, array $additionalData = []): array
    {
        $template = EmailTemplate::where('name', $templateName)->where('status', 'active')->first();
        
        if (!$template) {
            throw new \Exception("Email template '{$templateName}' not found or inactive.");
        }

        // Build data array from user and transaction
        $data = $this->buildTemplateData($user, $transaction, $additionalData);
        
        // Process subject and body
        $subject = $this->replaceVariables($template->subject, $data);
        $body = $this->replaceVariables($template->body, $data);
        
        // Mark template as used
        $template->markAsUsed();
        
        return [
            'template' => $template,
            'subject' => $subject,
            'body' => $body,
            'sender_name' => $this->replaceVariables($template->sender_name, $data),
            'sender_email' => $template->sender_email,
            'data' => $data
        ];
    }

    /**
     * Build template data from user and transaction models
     *
     * @param User $user
     * @param Transaction|null $transaction
     * @param array $additionalData
     * @return array
     */
    private function buildTemplateData(User $user, Transaction $transaction = null, array $additionalData = []): array
    {
        $data = [
            // User data
            'user_full_name' => $user->full_name,
            'user_first_name' => $user->first_name,
            'user_last_name' => $user->last_name,
            'user_email' => $user->email,
            'user_phone' => $user->phone,
            'user_account_number' => $user->account_number,
            'user_account_type' => $user->account_type,
            'user_account_balance' => number_format($user->account_balance, 2),
            'user_available_balance' => number_format($user->available_balance, 2),
            'user_account_status' => $user->account_status,
            'user_kyc_status' => $user->kyc_status,
            'user_is_verified' => $user->is_verified ? 'Yes' : 'No',
            'user_registration_date' => $user->created_at->format('M d, Y'),
            'user_address' => $user->address,
            'user_city' => $user->city,
            'user_state' => $user->state,
            'user_country' => $user->country,
            
            // Site data
            'site_name' => config('app.name', 'Global Trust Finance'),
            'site_url' => config('app.url'),
            'login_url' => route('login'),
            'support_email' => 'support@globaltrustfinance.com',
            'support_phone' => '+1-800-SUPPORT',
            
            // Date/Time
            'current_date' => now()->format('M d, Y'),
            'current_time' => now()->format('h:i A'),
            'current_year' => now()->year,
        ];

        // Add transaction data if provided
        if ($transaction) {
            $data = array_merge($data, [
                'transaction_id' => $transaction->transaction_id,
                'transaction_amount' => number_format($transaction->amount, 2),
                'transaction_currency' => $transaction->currency ?? '$',
                'transaction_fee' => number_format($transaction->fee, 2),
                'transaction_final_amount' => number_format($transaction->final_amount, 2),
                'transaction_reference' => $transaction->reference,
                'transaction_description' => $transaction->description,
                'transaction_status' => ucfirst($transaction->status),
                'transaction_date' => $transaction->created_at->format('M d, Y h:i A'),
                'transaction_type' => ucfirst($transaction->type),
                'transaction_category' => ucfirst($transaction->category),
                'balance_before' => number_format($transaction->balance_before, 2),
                'balance_after' => number_format($transaction->balance_after, 2),
                'payment_method' => $transaction->payment_method,
                
                // Recipient data (if available)
                'recipient_name' => $transaction->recipientUser ? $transaction->recipientUser->full_name : 'N/A',
                'recipient_account' => $transaction->recipient_account ?? 'N/A',
                'recipient_user_id' => $transaction->recipient_user_id,
            ]);
        }

        // Merge additional data
        return array_merge($data, $additionalData);
    }

    /**
     * Replace template variables with actual data
     *
     * @param string $content
     * @param array $data
     * @return string
     */
    private function replaceVariables(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            // Handle both {{ key }} and {{key}} formats
            $content = str_replace(['{{'.$key.'}}', '{{ '.$key.' }}'], $value, $content);
        }
        
        return $content;
    }

    /**
     * Send welcome email to new user
     *
     * @param User $user
     * @return array
     */
    public function sendWelcomeEmail(User $user): array
    {
        return $this->processTemplate('welcome_email', $user);
    }

    /**
     * Send transaction alert email
     *
     * @param User $user
     * @param Transaction $transaction
     * @return array
     */
    public function sendTransactionAlert(User $user, Transaction $transaction): array
    {
        $templateName = $transaction->type === 'debit' ? 'transaction_debit_alert' : 'transaction_credit_alert';
        return $this->processTemplate($templateName, $user, $transaction);
    }

    /**
     * Send OTP code for transfer
     *
     * @param User $user
     * @param Transaction $transaction
     * @param string $otpCode
     * @return array
     */
    public function sendOtpTransferCode(User $user, Transaction $transaction, string $otpCode): array
    {
        return $this->processTemplate('otp_transfer_code', $user, $transaction, [
            'otp_code' => $otpCode
        ]);
    }

    /**
     * Send IMF code requirement for international transfer
     *
     * @param User $user
     * @param Transaction $transaction
     * @param array $transferDetails
     * @return array
     */
    public function sendImfCodeRequest(User $user, Transaction $transaction, array $transferDetails): array
    {
        return $this->processTemplate('imf_code_international_transfer', $user, $transaction, [
            'recipient_bank' => $transferDetails['recipient_bank'] ?? 'N/A',
            'recipient_country' => $transferDetails['recipient_country'] ?? 'N/A',
            'swift_code' => $transferDetails['swift_code'] ?? 'N/A',
        ]);
    }

    /**
     * Send COT code requirement for transfer commission
     *
     * @param User $user
     * @param Transaction $transaction
     * @param array $commissionDetails
     * @return array
     */
    public function sendCotCodeRequest(User $user, Transaction $transaction, array $commissionDetails): array
    {
        return $this->processTemplate('cot_code_transfer_commission', $user, $transaction, [
            'commission_fee' => number_format($commissionDetails['commission_fee'] ?? 0, 2),
            'commission_rate' => $commissionDetails['commission_rate'] ?? '2.5',
            'total_deduction' => number_format($commissionDetails['total_deduction'] ?? 0, 2),
            'transfer_type' => $commissionDetails['transfer_type'] ?? 'International',
        ]);
    }

    /**
     * Send low balance alert
     *
     * @param User $user
     * @param float $threshold
     * @return array
     */
    public function sendLowBalanceAlert(User $user, float $threshold): array
    {
        return $this->processTemplate('low_balance_alert', $user, null, [
            'low_balance_threshold' => number_format($threshold, 2)
        ]);
    }

    /**
     * Send failed transaction alert
     *
     * @param User $user
     * @param Transaction $transaction
     * @param string $failureReason
     * @return array
     */
    public function sendFailedTransactionAlert(User $user, Transaction $transaction, string $failureReason): array
    {
        return $this->processTemplate('failed_transaction_alert', $user, $transaction, [
            'failure_reason' => $failureReason
        ]);
    }

    /**
     * Send account verification reminder
     *
     * @param User $user
     * @return array
     */
    public function sendAccountVerificationReminder(User $user): array
    {
        return $this->processTemplate('account_verification', $user, null, [
            'verification_level' => $user->kyc_status === 'approved' ? 'Complete' : 'Pending',
            'verification_url' => route('user.kyc.index')
        ]);
    }

    /**
     * Send monthly account statement
     *
     * @param User $user
     * @param array $statementData
     * @return array
     */
    public function sendAccountStatement(User $user, array $statementData): array
    {
        return $this->processTemplate('account_statement', $user, null, [
            'statement_month' => $statementData['month'] ?? now()->format('F'),
            'statement_year' => $statementData['year'] ?? now()->year,
            'statement_period' => $statementData['period'] ?? now()->format('F Y'),
            'opening_balance' => number_format($statementData['opening_balance'] ?? 0, 2),
            'closing_balance' => number_format($statementData['closing_balance'] ?? $user->account_balance, 2),
            'total_credits' => number_format($statementData['total_credits'] ?? 0, 2),
            'total_debits' => number_format($statementData['total_debits'] ?? 0, 2),
            'transaction_count' => $statementData['transaction_count'] ?? 0,
            'avg_daily_balance' => number_format($statementData['avg_daily_balance'] ?? $user->account_balance, 2),
        ]);
    }

    /**
     * Send password reset email
     *
     * @param User $user
     * @param string $resetUrl
     * @return array
     */
    public function sendPasswordReset(User $user, string $resetUrl): array
    {
        return $this->processTemplate('password_reset', $user, null, [
            'reset_url' => $resetUrl,
            'request_time' => now()->format('M d, Y h:i A')
        ]);
    }

    /**
     * Get all available email templates
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTemplates()
    {
        return EmailTemplate::where('status', 'active')->orderBy('category')->orderBy('name')->get();
    }

    /**
     * Get templates by category
     *
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTemplatesByCategory(string $category)
    {
        return EmailTemplate::where('status', 'active')
            ->where('category', $category)
            ->orderBy('name')
            ->get();
    }
}
