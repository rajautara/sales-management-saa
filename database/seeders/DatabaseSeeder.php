<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $company = Company::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo Company Sdn Bhd',
                'registration_no' => 'DEMO123456',
                'address' => '123 Jalan Demo, 50480 Kuala Lumpur',
                'phone' => '03-12345678',
                'currency' => 'MYR',
                'is_active' => true,
            ]
        );

        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
            ]
        );

        $user->assignRole('admin');
    }
}
