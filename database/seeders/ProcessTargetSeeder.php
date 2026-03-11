<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class ProcessTargetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cetak Lilin (402.2.1)
        $flangeProcesses = [
            'HAND DRILL FL',
            'BLASTING FL',
            'POTONG FL',
            'GERINDA KASAR FL',
            'LAS ARGON FL',
        ];

        // Rangkai Lilin (402.2.2)
        $fittingProcesses = [
            'GERINDA HALUS PF',
            'GERINDA KASAR PF',
            'POTONG PF',
            'GERINDA FINISH PF',
            'GERINDA FLANGE PF',
            'HAND DRILL PF',
            'BOR FITTING PF',
            'BLASTING PF',
            'GERINDA FLAP PF',
            'POTONG RESIBON PF',
        ];

        // Insert unique processes for Cetak Lilin (402.2.1)
        foreach ($flangeProcesses as $process) {
            DB::table('process_targets')->insertOrIgnore([
                'department_code' => '402.2.1',
                'process_name' => $process,
                'month' => date('n'),
                'year' => date('Y'),
                'target_qty' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Insert unique processes for Rangkai Lilin (402.2.2)
        foreach ($fittingProcesses as $process) {
            DB::table('process_targets')->insertOrIgnore([
                'department_code' => '402.2.2',
                'process_name' => $process,
                'month' => date('n'),
                'year' => date('Y'),
                'target_qty' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
