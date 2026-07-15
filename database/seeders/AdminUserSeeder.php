<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the super admin account.
     *
     * ponytail: belum ada kolom role/RBAC — user ini "super admin" secara
     * konvensi saja. Tambah role saat modul RBAC (docs/06-security/rbac-matrix.md)
     * diimplementasikan.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
    }
}
