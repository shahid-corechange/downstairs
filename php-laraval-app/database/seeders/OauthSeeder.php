<?php

namespace Database\Seeders;

use App\Models\OauthRemoteToken;
use Illuminate\Database\Seeder;

class OauthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->getOAuths() as $oauth) {
            OauthRemoteToken::create([
                'app_name' => $oauth['app_name'],
                'token_type' => $oauth['token_type'],
                'scope' => $oauth['scope'],
                'access_token' => $oauth['access_token'],
                'refresh_token' => $oauth['refresh_token'],
                'access_expires_at' => $oauth['access_expires_at'],
                'refresh_expires_at' => $oauth['refresh_expires_at'],
            ]);
        }
    }

    private function getOAuths()
    {
        return [
            [
                'app_name' => 'fortnox-customer',
                'token_type' => 'Bearer',
                'scope' => config('services.fortnox.customer_scope'),
                'access_token' => '',
                'refresh_token' => '',
                'access_expires_at' => now()->addMinutes(60),
                'refresh_expires_at' => now()->addDays(30),
            ],
            [
                'app_name' => 'fortnox-employee',
                'token_type' => 'Bearer',
                'scope' => config('services.fortnox.employee_scope'),
                'access_token' => '',
                'refresh_token' => '',
                'access_expires_at' => now()->addMinutes(60),
                'refresh_expires_at' => now()->addDays(30),
            ],
        ];
    }
}
