<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class KycEmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // KYC Status Approved
        EmailTemplate::updateOrCreate(
            ['name' => 'kyc_status_approved'],
            [
                'name' => 'kyc_status_approved',
                'subject' => 'KYC Verification Approved - Full Access Granted - {{ user_full_name }}',
                'body' => '<h2>KYC Verification Approved</h2><p>Dear {{ user_full_name }},</p><p>Congratulations! Your KYC (Know Your Customer) verification has been approved.</p><div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745;"><strong>✓ KYC Verification Approved!</strong><br>You now have full access to all banking features and services.</div><div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;"><strong>You can now enjoy the following benefits:</strong><br>• Send and receive money transfers<br>• Make deposits and withdrawals<br>• Apply for loans and credit facilities<br>• Access premium banking services<br>• Higher transaction limits</div><div style="background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 15px 0;"><strong>Verification Details:</strong><br>Account Holder: {{ user_full_name }}<br>Email: {{ user_email }}<br>Account Number: {{ user_account_number }}<br>Verification Date: {{ current_date }}<br>Status: <span style="color: #28a745; font-weight: bold;">APPROVED</span></div><p style="margin-top: 30px;"><a href="{{ dashboard_url }}" style="background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">Access Your Dashboard</a></p><p>Welcome to {{ site_name }}! If you have any questions about your new account features, please contact our support team.</p><p>Best regards,<br>{{ site_name }} Compliance Team</p>',
                'category' => 'kyc',
                'status' => 'active',
                'variables' => [
                    'user_full_name' => 'User full name',
                    'user_email' => 'User email address',
                    'user_account_number' => 'User account number',
                    'current_date' => 'Current date',
                    'dashboard_url' => 'Dashboard URL',
                    'site_name' => 'Site name'
                ],
                'sender_name' => '{{ site_name }} Compliance',
                'sender_email' => 'compliance@globaltrustfinance.com'
            ]
        );

        // KYC Status Rejected
        EmailTemplate::updateOrCreate(
            ['name' => 'kyc_status_rejected'],
            [
                'name' => 'kyc_status_rejected',
                'subject' => 'KYC Verification Rejected - Action Required - {{ user_full_name }}',
                'body' => '<h2>KYC Verification Rejected</h2><p>Dear {{ user_full_name }},</p><p>We regret to inform you that your KYC verification has been rejected.</p><div style="background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #dc3545;"><strong>✗ KYC Verification Rejected</strong><br>{{ kyc_reason }}</div><div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;"><strong>What to do next:</strong><br>• Review the rejection reason above<br>• Prepare clear, high-quality documents<br>• Ensure all information matches your documents<br>• Resubmit your KYC application</div><div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;"><strong>Common reasons for rejection include:</strong><br>• Blurry or unclear document images<br>• Expired identification documents<br>• Mismatched personal information<br>• Incomplete document submission</div><div style="background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 15px 0;"><strong>Verification Details:</strong><br>Account Holder: {{ user_full_name }}<br>Email: {{ user_email }}<br>Account Number: {{ user_account_number }}<br>Review Date: {{ current_date }}<br>Status: <span style="color: #dc3545; font-weight: bold;">REJECTED</span></div><p style="margin-top: 30px;"><a href="{{ verification_url }}" style="background: #dc3545; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">Resubmit KYC Documents</a></p><p>If you need assistance with document submission or have questions about the rejection, please contact our support team.</p><p>Best regards,<br>{{ site_name }} Compliance Team</p>',
                'category' => 'kyc',
                'status' => 'active',
                'variables' => [
                    'user_full_name' => 'User full name',
                    'user_email' => 'User email address',
                    'user_account_number' => 'User account number',
                    'current_date' => 'Current date',
                    'kyc_reason' => 'KYC rejection reason',
                    'verification_url' => 'KYC verification URL',
                    'site_name' => 'Site name'
                ],
                'sender_name' => '{{ site_name }} Compliance',
                'sender_email' => 'compliance@globaltrustfinance.com'
            ]
        );

        // KYC Status Pending
        EmailTemplate::updateOrCreate(
            ['name' => 'kyc_status_pending'],
            [
                'name' => 'kyc_status_pending',
                'subject' => 'KYC Verification Under Review - {{ user_full_name }}',
                'body' => '<h2>KYC Verification Under Review</h2><p>Dear {{ user_full_name }},</p><p>Your KYC verification is currently under review by our compliance team.</p><div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;"><strong>⏳ KYC Verification Under Review</strong><br>We are reviewing your submitted documents and information.</div><div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;"><strong>What happens next:</strong><br>• Our team will review your documents within 1-3 business days<br>• You will receive an email notification once the review is complete<br>• If approved, you\'ll gain access to all banking features<br>• If rejected, we\'ll provide feedback on what needs to be corrected</div><div style="background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 15px 0;"><strong>Verification Details:</strong><br>Account Holder: {{ user_full_name }}<br>Email: {{ user_email }}<br>Account Number: {{ user_account_number }}<br>Submission Date: {{ current_date }}<br>Status: <span style="color: #ffc107; font-weight: bold;">UNDER REVIEW</span></div><p style="margin-top: 30px;"><a href="{{ verification_url }}" style="background: #ffc107; color: #333; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">Check KYC Status</a></p><p>If you have any questions about the KYC process, please don\'t hesitate to contact our support team.</p><p>Best regards,<br>{{ site_name }} Compliance Team</p>',
                'category' => 'kyc',
                'status' => 'active',
                'variables' => [
                    'user_full_name' => 'User full name',
                    'user_email' => 'User email address',
                    'user_account_number' => 'User account number',
                    'current_date' => 'Current date',
                    'verification_url' => 'KYC verification URL',
                    'site_name' => 'Site name'
                ],
                'sender_name' => '{{ site_name }} Compliance',
                'sender_email' => 'compliance@globaltrustfinance.com'
            ]
        );

        // Credit/Debit Transaction Updates
        EmailTemplate::updateOrCreate(
            ['name' => 'credit_transaction_alert'],
            [
                'name' => 'credit_transaction_alert',
                'subject' => 'Account Credited: {{ transaction_currency }}{{ transaction_amount }} - {{ user_full_name }}',
                'body' => '<h2>Account Credited</h2><p>Dear {{ user_full_name }},</p><p>Your account has been credited with <strong>{{ transaction_currency }}{{ transaction_amount }}</strong>.</p><div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745;"><strong>✓ Credit Transaction Successful!</strong><br>The funds have been added to your account.</div><div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;"><strong>Transaction Details:</strong><br>Amount: {{ transaction_currency }}{{ transaction_amount }}<br>Reference: {{ transaction_reference }}<br>Description: {{ transaction_description }}<br>Date: {{ transaction_date }}<br>Status: {{ transaction_status }}</div><div style="background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 15px 0;"><strong>Account Balance:</strong><br>Previous Balance: {{ transaction_currency }}{{ balance_before }}<br>Current Balance: {{ transaction_currency }}{{ balance_after }}<br>Available Balance: {{ transaction_currency }}{{ user_available_balance }}</div><p>Your current account balance and transaction history are available in your dashboard.</p><p style="margin-top: 30px;"><a href="{{ login_url }}" style="background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">View Transaction History</a></p><p>Thank you for banking with us.</p><p>Best regards,<br>{{ site_name }} Team</p>',
                'category' => 'transaction',
                'status' => 'active',
                'variables' => [
                    'user_full_name' => 'User full name',
                    'transaction_amount' => 'Transaction amount',
                    'transaction_currency' => 'Transaction currency',
                    'transaction_reference' => 'Transaction reference',
                    'transaction_description' => 'Transaction description',
                    'transaction_date' => 'Transaction date',
                    'transaction_status' => 'Transaction status',
                    'balance_before' => 'Balance before transaction',
                    'balance_after' => 'Balance after transaction',
                    'user_available_balance' => 'User available balance',
                    'login_url' => 'Login URL',
                    'site_name' => 'Site name'
                ],
                'sender_name' => '{{ site_name }} Alerts',
                'sender_email' => 'alerts@globaltrustfinance.com'
            ]
        );

        // Debit Transaction Alert
        EmailTemplate::updateOrCreate(
            ['name' => 'debit_transaction_alert'],
            [
                'name' => 'debit_transaction_alert',
                'subject' => 'Account Debited: {{ transaction_currency }}{{ transaction_amount }} - {{ user_full_name }}',
                'body' => '<h2>Account Debited</h2><p>Dear {{ user_full_name }},</p><p>Your account has been debited with <strong>{{ transaction_currency }}{{ transaction_amount }}</strong>.</p><div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;"><strong>! Debit Transaction Processed</strong><br>The requested amount has been debited from your account.</div><div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;"><strong>Transaction Details:</strong><br>Amount: {{ transaction_currency }}{{ transaction_amount }}<br>Fee: {{ transaction_currency }}{{ transaction_fee }}<br>Final Amount: {{ transaction_currency }}{{ transaction_final_amount }}<br>Reference: {{ transaction_reference }}<br>Description: {{ transaction_description }}<br>Date: {{ transaction_date }}<br>Status: {{ transaction_status }}</div><div style="background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 15px 0;"><strong>Account Balance:</strong><br>Previous Balance: {{ transaction_currency }}{{ balance_before }}<br>Current Balance: {{ transaction_currency }}{{ balance_after }}<br>Available Balance: {{ transaction_currency }}{{ user_available_balance }}</div><p>If you did not authorize this transaction, please contact us immediately.</p><p style="margin-top: 30px;"><a href="{{ login_url }}" style="background: #ffc107; color: #333; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">View Transaction History</a></p><p>Thank you for banking with us.</p><p>Best regards,<br>{{ site_name }} Team</p>',
                'category' => 'transaction',
                'status' => 'active',
                'variables' => [
                    'user_full_name' => 'User full name',
                    'transaction_amount' => 'Transaction amount',
                    'transaction_currency' => 'Transaction currency',
                    'transaction_fee' => 'Transaction fee',
                    'transaction_final_amount' => 'Final transaction amount',
                    'transaction_reference' => 'Transaction reference',
                    'transaction_description' => 'Transaction description',
                    'transaction_date' => 'Transaction date',
                    'transaction_status' => 'Transaction status',
                    'balance_before' => 'Balance before transaction',
                    'balance_after' => 'Balance after transaction',
                    'user_available_balance' => 'User available balance',
                    'login_url' => 'Login URL',
                    'site_name' => 'Site name'
                ],
                'sender_name' => '{{ site_name }} Alerts',
                'sender_email' => 'alerts@globaltrustfinance.com'
            ]
        );
    }
}
