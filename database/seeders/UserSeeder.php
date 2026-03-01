<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $admin = User::firstOrCreate([
            'email' => 'admin@example.com',
        ],[
            'name' => 'Admin Gudang',
            'password' => bcrypt('password'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $admin->assignRole('admin_gudang');

        $supervisor = User::firstOrCreate([
            'email' => 'supervisor@example.com',
        ],[
            'name' => 'Supervisor',
            'password' => bcrypt('password'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $supervisor->assignRole('spv');

        $technician = User::firstOrCreate([
            'email' => 'technician@example.com',
        ],[
            'name' => 'Technician',
            'password' => bcrypt('password'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $technician->assignRole('technician');

        $this->command->info('Default users seeded. Password: password');
    }
}
