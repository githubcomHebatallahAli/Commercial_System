<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('admins')->insert([
            'name' => 'Super Admin',
            'email' => 'superAdmin@mail.com',
            'password' => Hash::make('superAdmin@123'),
            'phoNum'=>'0111223344',
            'address' => 'Moharram Bey',
            'role_id' => '1',
            'status' => 'active',

        ]);
    }
}
