<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->insert([
            [
                'key' => 'app_name',
                'value' => 'Test App',
            ],
            [
                'key' => 'date_format',
                'value' => 'MM/DD/YYYY',
            ],
            [
                'key' => 'pagination_limit',
                'value' => 10,
            ]
        ]);
    }
}
