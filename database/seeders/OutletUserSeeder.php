<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;

class OutletUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();

        if ($admin) {
            $admin->outlets()->syncWithoutDetaching(Outlet::pluck('id'));
        }
    }
}
