<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        AdminUser::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'email' => 'admin@example.com',
            'password_hash' => Hash::make('admin123')
        ]);
    }
}
