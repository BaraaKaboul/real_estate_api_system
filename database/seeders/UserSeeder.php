<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->delete();


        DB::table('users')->insert([
            'name' => 'LoRdQuSaY',
            'email' => 'qoqohr@gmail.com',
            'password' => Hash::make('Aa123456789'),
            'role'=>'admin',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
