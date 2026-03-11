<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcessTargetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds process targets for dept 402.2.1 (Cetak Lilin)
     * using two-level Item → Size structure.
     */
    public function run(): void
    {
        $dept = '402.2.1';
        $month = date('n');
        $year = date('Y');

        $targets = [
            // Fitting biasa
            ['item_name' => 'Fitting biasa', 'size_name' => '1/8"-1/2"', 'target_qty' => 1300, 'unit' => 'PCS'],
            ['item_name' => 'Fitting biasa', 'size_name' => '3/4"-1"', 'target_qty' => 900, 'unit' => 'PCS'],
            ['item_name' => 'Fitting biasa', 'size_name' => '1-1/4"-2"', 'target_qty' => 450, 'unit' => 'PCS'],
            ['item_name' => 'Fitting biasa', 'size_name' => '2-1/2"-4"', 'target_qty' => 250, 'unit' => 'PCS'],
            ['item_name' => 'Fitting biasa', 'size_name' => '5"-6"', 'target_qty' => 70, 'unit' => 'PCS'],
            ['item_name' => 'Fitting biasa', 'size_name' => '8"-10"', 'target_qty' => 35, 'unit' => 'PCS'],

            // Fitting Lem
            ['item_name' => 'Fitting Lem', 'size_name' => '1/2"-1"', 'target_qty' => 400, 'unit' => 'set'],
            ['item_name' => 'Fitting Lem', 'size_name' => '1-1/4"-2"', 'target_qty' => 300, 'unit' => 'set'],
            ['item_name' => 'Fitting Lem', 'size_name' => '2-1/2"-4"', 'target_qty' => 125, 'unit' => 'set'],

            // Butt Weld
            ['item_name' => 'Butt Weld', 'size_name' => '1/2"-1"', 'target_qty' => 600, 'unit' => 'PCS'],
            ['item_name' => 'Butt Weld', 'size_name' => '1-1/4"-2"', 'target_qty' => 400, 'unit' => 'PCS'],
            ['item_name' => 'Butt Weld', 'size_name' => '2-1/2"-4"', 'target_qty' => 125, 'unit' => 'PCS'],
            ['item_name' => 'Butt Weld', 'size_name' => '5"-6"', 'target_qty' => 35, 'unit' => 'PCS'],

            // Flange
            ['item_name' => 'Flange', 'size_name' => '1/2"-1"', 'target_qty' => 450, 'unit' => 'PCS'],
            ['item_name' => 'Flange', 'size_name' => '1-1/4"-2"', 'target_qty' => 350, 'unit' => 'PCS'],
            ['item_name' => 'Flange', 'size_name' => '2-1/2"-4"', 'target_qty' => 250, 'unit' => 'PCS'],
            ['item_name' => 'Flange', 'size_name' => '5"-6"', 'target_qty' => 175, 'unit' => 'PCS'],
        ];

        foreach ($targets as $t) {
            $processName = $t['item_name'] . ' - ' . $t['size_name'];
            DB::table('process_targets')->insertOrIgnore([
                'department_code' => $dept,
                'process_name' => $processName,
                'item_name' => $t['item_name'],
                'size_name' => $t['size_name'],
                'unit' => $t['unit'],
                'month' => $month,
                'year' => $year,
                'target_qty' => $t['target_qty'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
