<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Schema::disableForeignKeyConstraints();

        // Hapus data lama (sekarang aman)
        User::truncate();

        // Suruh MySQL "jaga" lagi
        Schema::enableForeignKeyConstraints();

        // Buat User Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => true,
        ]);

        // Buat User Manager
        User::create([
            'name' => 'Manager User',
            'email' => 'manager@test.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'status' => true,
        ]);

        User::create([
            'name' => 'Staff User Active',
            'email' => 'staff@test.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'status' => true,
        ]);

        // Buat User Staff Inaktif
        User::create([
            'name' => 'Staff User Inactive',
            'email' => 'staff.inactive@test.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'status' => false, // Statusnya false
        ]);
    }
}
