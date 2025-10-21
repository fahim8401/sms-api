<?php

namespace Database\Seeders;

use App\Models\Gateway;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@hplink.com.bd',
            'password' => Hash::make('admin123'),
            'api_key' => User::generateApiKey(),
            'balance' => 10000,
            'rate' => 0.30,
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create a test reseller
        User::create([
            'name' => 'Test Reseller',
            'email' => 'reseller@hplink.com.bd',
            'password' => Hash::make('reseller123'),
            'api_key' => User::generateApiKey(),
            'balance' => 1000,
            'rate' => 0.30,
            'role' => 'reseller',
            'status' => 'active',
        ]);

        // Create a test user
        User::create([
            'name' => 'Test User',
            'email' => 'user@hplink.com.bd',
            'password' => Hash::make('user123'),
            'api_key' => User::generateApiKey(),
            'balance' => 100,
            'rate' => 0.30,
            'role' => 'user',
            'status' => 'active',
        ]);

        // Create default gateway
        Gateway::create([
            'name' => 'DigitalSquare',
            'api_url' => config('services.digitalsquare.baseurl', 'http://isms.digitalsquare.ltd:5683'),
            'api_key' => config('services.digitalsquare.apikey', 'your_api_key_here'),
            'secret_key' => config('services.digitalsquare.secret', 'your_secret_here'),
            'status' => 'active',
        ]);
    }
}
