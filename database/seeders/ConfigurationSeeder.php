<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Configuration;


class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Configuration::updateOrCreate(
            ['key' => 'infra_team_emails'],
            [
                'value' => 'aiman@tourism.gov.my,siti@tourism.gov.my,firdaus@tourism.gov.my',
                'provider' => 'smtp',           // 👈 required
                'updated_by' => 1               // 👈 placeholder user ID (adjust if needed)
            ]
        );

        Configuration::updateOrCreate(
            ['key' => 'dev_team_emails'],
            [
                'value' => 'nizam@tourism.gov.my,farah@tourism.gov.my',
                'provider' => 'smtp',
                'updated_by' => 1
            ]
        );
    }
}
