<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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

        $depts = ['402.2.2', '402.2.3'];

        // We seed for both July 2026 and the dynamically resolved current month/year to guarantee uptime.
        $monthsYears = [
            ['month' => 7, 'year' => 2026],
            ['month' => (int)date('n'), 'year' => (int)date('Y')]
        ];

        foreach ($depts as $dept) {
            foreach ($monthsYears as $my) {
                foreach ($targets as $t) {
                    $processName = $t['item_name'] . ' - ' . $t['size_name'];
                    DB::table('process_targets')->insertOrIgnore([
                        'department_code' => $dept,
                        'process_name' => $processName,
                        'item_name' => $t['item_name'],
                        'size_name' => $t['size_name'],
                        'unit' => $t['unit'],
                        'month' => $my['month'],
                        'year' => $my['year'],
                        'target_qty' => $t['target_qty'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('process_targets')
            ->whereIn('department_code', ['402.2.2', '402.2.3'])
            ->delete();
    }
};
