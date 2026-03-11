<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MdOperator;
use App\Models\MdMachine;
use App\Models\MdItem;

class MdMasterSeeder extends Seeder
{
    public function run(): void
    {
        // OPERATORS
        MdOperator::upsert([
            ['code' => 'budi', 'name' => 'Budi', 'active' => 1],
            ['code' => 'andi', 'name' => 'Andi', 'active' => 1],
            ['code' => 'dedi', 'name' => 'Dedi', 'active' => 1],
        ], ['code']);

        // MACHINES
        MdMachine::upsert([
            ['code' => 'cnc1', 'name' => 'CNC-1', 'line' => 'LILIN', 'active' => 1],
            ['code' => 'cnc2', 'name' => 'CNC-2', 'line' => 'LILIN', 'active' => 1],
        ], ['code']);

        // ITEMS
        MdItem::upsert([
            [
                'code' => 'flange_2_jis10',
                'name' => 'Flange 2” JIS 10K',
                'cycle_time_sec' => 600, // 10 menit
                'active' => 1,
            ],
        ], ['code']);
    }
}
