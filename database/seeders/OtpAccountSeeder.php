<?php

namespace Database\Seeders;

use App\Models\OtpAccount;
use Illuminate\Database\Seeder;

class OtpAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'phone_number' => '0895637875901',
                'gmail_address' => 'hayrika.am@gmail.com',
                'account_name' => 'Disney Account 1',
                'is_active' => true,
            ],
            [
                'phone_number' => '085813608728',
                'gmail_address' => 'hallo.unimo@gmail.com',
                'account_name' => 'Disney Account 2',
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $account) {
            OtpAccount::updateOrCreate(
                ['phone_number' => $account['phone_number']],
                $account
            );
        }
    }
}