<?php

namespace Database\Seeders;

use App\Models\Domain;
use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder
{
    public function run(): void
    {
        Domain::create([
            'domain_name' => 'mailbox.local',
            'is_active' => true,
        ]);
    }
}