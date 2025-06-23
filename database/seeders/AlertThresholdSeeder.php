<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AlertThreshold;

class AlertThresholdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AlertThreshold::create([
        'server_id' => 1,
        'metric_type' => 'cpu', 
        'threshold_value' => 80,
        'notification_channel' => 'email',
        'created_by' => 1, 
        ]);
    }
}
