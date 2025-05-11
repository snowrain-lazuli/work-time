<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Time;
use App\Models\BreakTime;
use App\Models\Application;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory(10)->create();

        Time::factory(100)->create();

        BreakTime::factory(150)->create();

        Application::factory(50)->create();
        
        User::firstOrCreate(
            ['email' => 'admin@jp.com'], // このemailがなければ作成
            [
                'name' => 'admin',
                'password' => Hash::make('Pswd1234'),
                'role' => 2,
                'email_verified_at' => now(),
            ]
        );
    }
}