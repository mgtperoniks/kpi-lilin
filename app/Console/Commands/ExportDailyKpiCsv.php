<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyKpiOperator;
use App\Models\DailyKpiMachine;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ExportDailyKpiCsv extends Command
{
    protected $signature = 'kpi:export-csv {date?}';
    protected $description = 'Export KPI harian Lilin ke CSV (KPI Contract v1)';

    public function handle()
    {
        $date = $this->argument('date')
            ?? Carbon::yesterday()->toDateString();

        $filename = "kpi_lilin_{$date}.csv";
        $path = "exports/{$filename}";

        $rows = [];

        // Header
        $rows[] = [
            'date',
            'department',
            'entity_type',
            'entity_code',
            'kpi_percent',
            'total_target',
            'total_actual',
            'total_work_hours'
        ];

        // Operator KPI
        $operators = DailyKpiOperator::where('kpi_date', $date)->get();
        foreach ($operators as $op) {
            $rows[] = [
                $date,
                'LILIN',
                'OPERATOR',
                $op->operator_code,
                $op->kpi_percent,
                $op->total_target_qty,
                $op->total_actual_qty,
                $op->total_work_hours,
            ];
        }

        // Machine KPI
        $machines = DailyKpiMachine::where('kpi_date', $date)->get();
        foreach ($machines as $mc) {
            $rows[] = [
                $date,
                'LILIN',
                'MACHINE',
                $mc->machine_code,
                $mc->kpi_percent,
                $mc->total_target_qty,
                $mc->total_actual_qty,
                $mc->total_work_hours,
            ];
        }

        // Convert to CSV string
        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', $row) . "\n";
        }

        Storage::disk('local')->put($path, $csv);

        $this->info("CSV exported: storage/app/{$path}");
    }
}