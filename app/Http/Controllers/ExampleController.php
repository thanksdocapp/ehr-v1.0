<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ExampleController extends Controller
{
    /**
     * Example method demonstrating how to create a user with a valid account type
     */
    public function createUserExample()
    {
        // Create a new user with all required fields
        $user = new User();
        $user->first_name = 'John';
        $user->last_name = 'Doe';
        $user->name = 'John Doe'; // Full name
        $user->username = 'johndoe123';
        $user->email = 'john.doe@example.com';
        $user->password = Hash::make('securePassword123');
        $user->country_code = '+1'; // US country code
        $user->phone = '2025551234';
        $user->pin_code = '1234'; // 4-digit PIN
        $user->status = 'active';
        $user->is_verified = true;
        
        // Generate a unique account number
        $user->account_number = 'GTF' . rand(1000000, 9999999);
        
        // IMPORTANT: Use one of the valid account types
        // Valid options are: 'savings', 'checking', 'investment', 'business'
        $user->account_type = 'savings'; // Using a valid account type from the allowed list
        
        // Save the user
        $user->save();
        
        // Create a user account for this user
        $userAccount = new UserAccount();
        $userAccount->user_id = $user->id;
        $userAccount->account_name = $user->first_name . ' ' . $user->last_name;
        $userAccount->account_number = $user->account_number;
        $userAccount->available_balance = 0.00;
        $userAccount->pending_balance = 0.00;
        $userAccount->currency = 'USD';
        $userAccount->daily_limit = 5000.00;
        $userAccount->monthly_limit = 50000.00;
        $userAccount->interest_rate = 0.75; // 0.75% interest rate
        $userAccount->opened_at = now();
        
        // Save the user account
        $userAccount->save();
        
        return "User created successfully with account type: " . $user->account_type;
    }
    
    /**
     * Example for bulk creating users with different valid account types
     */
    public function bulkCreateUsers()
    {
        // Array of valid account types - use only these values
        $validAccountTypes = ['savings', 'checking', 'investment', 'business'];
        
        // Example data for multiple users
        $usersData = [
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'account_type' => 'checking' // valid account type
            ],
            [
                'first_name' => 'Robert',
                'last_name' => 'Johnson',
                'email' => 'robert.johnson@example.com',
                'account_type' => 'investment' // valid account type
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Brown',
                'email' => 'emily.brown@example.com',
                'account_type' => 'business' // valid account type
            ]
        ];
        
        $createdUsers = [];
        
        foreach ($usersData as $userData) {
            // Create user with valid account type
            $user = new User();
            $user->first_name = $userData['first_name'];
            $user->last_name = $userData['last_name'];
            $user->name = $userData['first_name'] . ' ' . $userData['last_name'];
            $user->username = strtolower($userData['first_name'] . $userData['last_name'] . rand(100, 999));
            $user->email = $userData['email'];
            $user->password = Hash::make('Password' . rand(1000, 9999));
            $user->country_code = '+1';
            $user->phone = '202' . rand(1000000, 9999999);
            $user->pin_code = rand(1000, 9999);
            $user->status = 'active';
            $user->is_verified = true;
            $user->account_number = 'GTF' . rand(1000000, 9999999);
            
            // Set the valid account type
            $user->account_type = $userData['account_type'];
            
            $createdUsers[] = $user;
            // Note: In a real implementation, you would save each user and create their account
        }
        
        return "Created user examples with valid account types: " . implode(', ', $validAccountTypes);
    }
}
