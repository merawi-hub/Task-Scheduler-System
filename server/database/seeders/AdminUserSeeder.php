<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::firstOrCreate(
            ['email' => 'admin@taskscheduler.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
            ]
        );

        $this->command->info('Admin user created:');
        $this->command->info('Email: admin@taskscheduler.com');
        $this->command->info('Password: admin123');
        $this->command->warn('Please change the password after first login!');

        // Create a test regular user
        User::firstOrCreate(
            ['email' => 'user@taskscheduler.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('user123'),
                'is_admin' => false,
            ]
        );

        $this->command->info('Test user created:');
        $this->command->info('Email: user@taskscheduler.com');
        $this->command->info('Password: user123');
    }
}
