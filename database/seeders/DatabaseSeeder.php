<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Railway runs this on every deploy, so it must stay idempotent.
     * In production the admin credentials come from ADMIN_EMAIL / ADMIN_PASSWORD.
     */
    public function run(): void
    {
        $admin = config('app.admin');
        $password = $admin['password'];

        if (! $password) {
            if (app()->environment('production')) {
                $this->command?->warn('ADMIN_PASSWORD is not set, skipping admin user.');

                return;
            }
            $password = 'password';
        }

        User::updateOrCreate(
            ['email' => $admin['email']],
            [
                'name' => $admin['name'],
                'password' => $password, // hashed via the User model's "hashed" cast
                'email_verified_at' => now(),
            ],
        );
    }
}
