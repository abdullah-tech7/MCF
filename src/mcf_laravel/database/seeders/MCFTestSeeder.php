<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\MCF\Authentication\McfAuth;
use Illuminate\Database\Seeder;

class MCFTestSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        if (! Role::query()->exists()) {
            Role::create([
                'id' => 1,
                'name' => 'Administrator',
            ]);

            Role::create([
                'id' => 2,
                'name' => 'Employee',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        if (! User::query()->exists()) {
            User::create([
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => McfAuth::hashPassword('admin'),
                'role_id' => 1,
            ]);

            User::create([
                'name' => 'Employee',
                'email' => 'employee@example.com',
                'password' => McfAuth::hashPassword('@Ee12345678'),
                'role_id' => 2,
            ]);
        }
    }
}