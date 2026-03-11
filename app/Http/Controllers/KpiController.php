<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\KpiLilinService;

// MASTER MIRROR (READ ONLY)
use App\Models\MdItem;
use App\Models\MdMachine;
use App\Models\MdOperator;

class KpiController extends Controller
{
    /**
     * ===============================
     * KPI PER SHIFT (LILIN)
     * ===============================
     */
    public function shift(Request $request, KpiLilinService $service)
    {
        /**
         * 1. PARAMETER AMAN
         */
        $date = $request->input('date', now()->toDateString());
        $shift = $request->input('shift', 'A');

        /**
         * 2. AMBIL DATA FACT
         */
        $productions = DB::table('production_logs')
            ->where('production_date', $date)
            ->where('shift', $shift)
            ->get();

        $downtimes = DB::table('downtime_logs')
            ->where('production_date', $date)
            ->where('shift', $shift)
            ->get();

        /**
         * 3. HARD STOP — KPI CONTAMINATION GUARD
         * SSOT DEFENSIVE LAYER
         */

        // ITEM
        $inactiveItemsUsed = MdItem::whereIn(
            'code',
            $productions->pluck('item_code')->unique()
        )
            ->where('status', '!=', 'active')
            ->exists();

        if ($inactiveItemsUsed) {
            throw ValidationException::withMessages([
                'kpi' => 'KPI dibatalkan: terdapat item inactive pada data produksi.',
            ]);
        }

        // MACHINE
        $machineCodes = $productions->pluck('machine_code')
            ->merge($downtimes->pluck('machine_code'))
            ->unique();

        $inactiveMachinesUsed = MdMachine::whereIn('code', $machineCodes)
            ->where('status', '!=', 'active')
            ->exists();

        if ($inactiveMachinesUsed) {
            throw ValidationException::withMessages([
                'kpi' => 'KPI dibatalkan: terdapat machine inactive pada data produksi/downtime.',
            ]);
        }

        // OPERATOR
        $operatorCodes = $productions->pluck('operator_code')
            ->merge($downtimes->pluck('operator_code'))
            ->unique();

        $inactiveOperatorsUsed = MdOperator::whereIn('code', $operatorCodes)
            ->where('status', '!=', 'active')
            ->exists();

        if ($inactiveOperatorsUsed) {
            throw ValidationException::withMessages([
                'kpi' => 'KPI dibatalkan: terdapat operator inactive pada data produksi/downtime.',
            ]);
        }

        /**
         * 4. BARU BOLEH HITUNG KPI
         * Server-side authority
         */
        $result = $service->calculateWithDowntime(
            $productions,
            $downtimes
        );

        return view('kpi.shift', compact(
            'date',
            'shift',
            'result'
        ));
    }
}
