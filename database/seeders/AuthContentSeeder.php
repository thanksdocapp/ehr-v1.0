<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authContents = [
            // Login Page Content
            [
                'type' => 'login',
                'key' => 'page_title',
                'title' => 'Welcome Back',
                'subtitle' => 'Sign in to your Global Trust Finance account',
                'content' => 'Access your financial dashboard and manage your investments securely.',
                'image_url' => '/images/auth/login-banner.jpg',
                'data_info' => json_encode([
                    'form_heading' => 'Welcome Back',
                    'submit_button_text' => 'Sign In',
                    'forgot_password_text' => 'Forgot Your Password?',
                    'register_link_text' => 'Create Account',
                    'first_background_image' => 'login-bg-1.jpg',
                    'second_background_image' => 'login-bg-2.jpg',
                    'image' => 'login-main.jpg'
                ]),
                'status' => 1
            ],
            [
                'type' => 'login',
                'key' => 'security_features',
                'title' => 'Security Features',
                'subtitle' => 'Your security is our priority',
                'content' => 'Bank-level security with 256-bit SSL encryption, two-factor authentication, and biometric login options.',
                'data_info' => json_encode([
                    'features' => [
                        [
                            'icon' => 'fas fa-shield-alt',
                            'title' => 'Secure Banking',
                            'description' => '256-bit SSL encryption'
                        ],
                        [
                            'icon' => 'fas fa-globe',
                            'title' => 'Global Access',
                            'description' => 'Worldwide availability'
                        ],
                        [
                            'icon' => 'fas fa-chart-line',
                            'title' => 'Investment Growth',
                            'description' => 'Professional portfolio management'
                        ],
                        [
                            'icon' => 'fas fa-headset',
                            'title' => '24/7 Support',
                            'description' => 'Round-the-clock assistance'
                        ]
                    ]
                ]),
                'status' => 1
            ],
            
            // Register Page Content
            [
                'type' => 'register',
                'key' => 'page_title',
                'title' => 'Create Your Account',
                'subtitle' => 'Start your journey to financial freedom today',
                'content' => 'Join thousands of satisfied clients worldwide and begin building your financial future.',
                'image_url' => '/images/auth/register-banner.jpg',
                'data_info' => json_encode([
                    'form_heading' => 'Create Your Account',
                    'submit_button_text' => 'Create Account',
                    'login_link_text' => 'Sign In',
                    'first_background_image' => 'register-bg-1.jpg',
                    'second_background_image' => 'register-bg-2.jpg',
                    'image' => 'register-main.jpg'
                ]),
                'status' => 1
            ],
            [
                'type' => 'register',
                'key' => 'benefits',
                'title' => 'Why Choose Global Trust Finance',
                'subtitle' => 'Join thousands of satisfied clients worldwide',
                'content' => 'Experience the benefits of professional financial management with our comprehensive suite of services.',
                'data_info' => json_encode([
                    'benefits' => [
                        [
                            'icon' => 'fas fa-shield-alt',
                            'title' => 'Bank-Level Security',
                            'description' => 'Your funds are protected with military-grade encryption'
                        ],
                        [
                            'icon' => 'fas fa-chart-line',
                            'title' => 'Proven Returns',
                            'description' => 'Average annual returns of 8-12% for our portfolio clients'
                        ],
                        [
                            'icon' => 'fas fa-globe',
                            'title' => 'Global Reach',
                            'description' => 'Access your funds from anywhere in the world'
                        ],
                        [
                            'icon' => 'fas fa-user-tie',
                            'title' => 'Personal Manager',
                            'description' => 'Dedicated financial advisor for accounts over $10K'
                        ]
                    ]
                ]),
                'status' => 1
            ],
            [
                'type' => 'register',
                'key' => 'account_types',
                'title' => 'Account Types',
                'subtitle' => 'Choose the account that fits your needs',
                'content' => 'We offer three account types to match your investment goals and requirements.',
                'data_info' => json_encode([
                    'account_types' => [
                        [
                            'id' => 'personal',
                            'icon' => 'fas fa-user',
                            'title' => 'Personal',
                            'description' => 'Individual investors',
                            'minimum' => '$1,000',
                            'features' => [
                                'Online banking access',
                                'Mobile app',
                                'Basic investment portfolio',
                                'Email support'
                            ]
                        ],
                        [
                            'id' => 'business',
                            'icon' => 'fas fa-building',
                            'title' => 'Business',
                            'description' => 'Companies & organizations',
                            'minimum' => '$10,000',
                            'features' => [
                                'All Personal features',
                                'Corporate banking',
                                'Business credit lines',
                                'Phone support',
                                'Quarterly reviews'
                            ]
                        ],
                        [
                            'id' => 'private',
                            'icon' => 'fas fa-crown',
                            'title' => 'Private Vault',
                            'description' => 'High-net-worth individuals',
                            'minimum' => '$100,000',
                            'features' => [
                                'All Business features',
                                'Dedicated account manager',
                                'Premium investment options',
                                '24/7 phone support',
                                'Monthly strategy sessions',
                                'Exclusive events'
                            ]
                        ]
                    ]
                ]),
                'status' => 1
            ]
        ];

        foreach ($authContents as $content) {
            DB::table('auth_contents')->updateOrInsert(
                ['type' => $content['type'], 'key' => $content['key']],
                array_merge($content, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }
}
