<?php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\Unit;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Hardware', 'code' => 'HW'],
            ['name' => 'Software', 'code' => 'SW'],
            ['name' => 'Jaringan & Kabel', 'code' => 'JG'],
            ['name' => 'Komputer & Laptop', 'code' => 'KL'],
            ['name' => 'Printer & Scanner', 'code' => 'PS'],
            ['name' => 'Aksesoris', 'code' => 'AKS'],
            ['name' => 'Alat Listrik', 'code' => 'AL'],
            ['name' => 'ATK & Kantor', 'code' => 'ATK']
        ];
        foreach ($categories as $cat) {
            Category::firstOrCreate($cat);
        }

        $units = ['pcs', 'unit', 'box', 'set', 'roll', 'meter', 'lembar', 'rim', 'lusin'];
        foreach ($units as $unit) {
            Unit::firstOrCreate(['name' => $unit]);
        }

        $vendors = [
            ['name' => 'PT. Hafdzamedia Teknologi Aplikasi','contact_person' => 'Achmad Hamdan',   'phone' => '021-5551234', 'email' => 'hafdzamedia@example.com'],
            ['name' => 'CV. Mujahidden Solusindo','contact_person' => 'Nur Mujahidin','phone' => '021-5559876', 'email' => 'mujahidden@example.com'],
            ['name' => 'PT. Cabang Purnama','contact_person' => 'Panji Fiqri','phone' => '021-5554321', 'email' => 'cabangpurnama@example.com'],
        ];
        foreach ($vendors as $vendor) {
            Vendor::firstOrCreate(['name' => $vendor['name']], $vendor);
        }

        $this->command->info('Master data seeded.');
    }
}