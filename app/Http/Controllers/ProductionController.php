<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Models\ProductionLog;

// MASTER MIRROR (READ ONLY - SSOT)
use App\Models\MdItemMirror;
use App\Models\MdMachineMirror;
use App\Models\MdOperatorMirror;

class ProductionController extends Controller
{
    /**
     * =================================
     * FORM INPUT PRODUKSI
     * =================================
     */
    public function create()
    {
        // Items and Operators are now loaded via Autocomplete API
        $machines = MdMachineMirror::where('status', 'active')
            ->orderBy('code')
            ->get(['code', 'name']);

        $activeDepartment = session('selected_department_code', auth()->user()->department_code);

        // Branch based on active department
        if ($activeDepartment === '402.2.1') {
            // Wax Injection (Cetak Lilin) - Cycle Time Engine
            return view('production.input_cycle_time', [
                'machines' => $machines,
            ]);
        }

        // Fetch process targets for the active Lilin department for the current month
        $processTargets = \App\Models\ProcessTarget::where('month', date('n'))
            ->where('year', date('Y'))
            ->where('department_code', $activeDepartment)
            ->orderBy('item_name')
            ->orderBy('size_name')
            ->get();

        // Build grouped structure: { "Fitting biasa": [ { id, size_name, target_qty, unit }, ... ], ... }
        $groupedItems = [];
        foreach ($processTargets as $pt) {
            $itemKey = $pt->item_name ?: $pt->process_name;
            if (!isset($groupedItems[$itemKey])) {
                $groupedItems[$itemKey] = [];
            }
            $groupedItems[$itemKey][] = [
                'id' => $pt->id,
                'size_name' => $pt->size_name ?: '-',
                'target_qty' => $pt->target_qty,
                'unit' => $pt->unit ?? 'PCS',
                'process_name' => $pt->process_name,
            ];
        }

        return view('production.input', [
            'machines' => $machines,
            'processTargets' => $processTargets,
            'groupedItems' => $groupedItems,
        ]);
    }


    /**
     * =================================
     * SIMPAN DATA PRODUKSI (HARD STOP)
     * =================================
     */
    public function store(Request $request)
    {
        if (auth()->user()->isReadOnly()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk menyimpan data (Read-Only).');
        }
        /**
         * ===============================
         * DEBUG PALING CEPAT (SEMENTARA)
         * ===============================
         * AKTIFKAN JIKA ADA ERROR FORM
         * Setelah ketemu masalah → HAPUS BARIS INI
         */
        //dd($request->all());

        $activeDepartment = session('selected_department_code', auth()->user()->department_code);
        $isProcessTargetEngine = in_array($activeDepartment, ['402.2.2', '402.2.3']);

        /**
         * 1. VALIDASI INPUT DASAR
         * Server = Source of Truth
         */
        if ($isProcessTargetEngine) {
            $validated = $request->validate([
                'production_date' => 'required|date',
                'shift' => 'required|string|max:10',
                'operator_code' => 'required|string',
                'machine_code' => 'required|string',
                'process_id' => 'required|integer',
                'process_name' => 'nullable|string',
                'time_start' => 'required|date_format:H:i',
                'time_end' => 'required|date_format:H:i',
                'actual_qty' => 'required|integer|min:0',
                'remark' => 'nullable|string|max:50',
                'note' => 'nullable|string|max:255',
            ]);
        } else {
            // Wax Injection Cetak Lilin (402.2.1) - Cycle Time Validation
            $validated = $request->validate([
                'production_date' => 'required|date',
                'shift' => 'required|string|max:10',
                'operator_code' => 'required|string',
                'machine_code' => 'required|string',
                'item_code' => 'required|string',
                'heat_number' => 'required|string',
                'time_start' => 'required|date_format:H:i',
                'time_end' => 'required|date_format:H:i',
                'cycle_time_minutes' => 'required|integer|min:0',
                'cycle_time_seconds' => 'required|integer|min:0|max:59',
                'actual_qty' => 'required|integer|min:0',
                'remark' => 'nullable|string|max:50',
                'note' => 'nullable|string|max:255',
            ]);
        }

        $machine = MdMachineMirror::where('code', $validated['machine_code'])
            ->where('status', 'active')
            ->firstOrFail();

        $operator = MdOperatorMirror::withoutGlobalScope(\App\Models\Scopes\DepartmentScope::class)
            ->where('code', $validated['operator_code'])
            ->where('status', 'active')
            ->firstOrFail();

        // Cross-Day Shift Handle
        $startSeconds = strtotime($validated['time_start']);
        $endSeconds = strtotime($validated['time_end']);

        if ($endSeconds < $startSeconds) {
            $endSeconds += 86400; // Add 24 hours (1 day)
        }

        $workSeconds = $endSeconds - $startSeconds;

        if ($workSeconds <= 0) {
            throw ValidationException::withMessages([
                'time_end' => 'Jam selesai harus lebih besar dari jam mulai.',
            ]);
        }

        $workHours = round($workSeconds / 3600, 2);

        $itemCode = null;
        $heatNumber = null;
        $cycleTimeSec = 0;
        $targetQty = 0;
        $actualQty = (int) $validated['actual_qty'];
        $heatNumberDetails = null;

        if ($isProcessTargetEngine) {
            // --- LILIN LOGIC (Process Based - Engine B) ---
            $processTarget = \App\Models\ProcessTarget::findOrFail($validated['process_id']);

            // Override Item Code to be the Process Name so that reports group them properly
            $itemCode = $this->normalizeCode($processTarget->process_name);

            // Target scaling logic: 
            // The processTarget->target_qty is the Full Shift Target (7 hours = 25200 seconds)
            $fullShiftSeconds = 7 * 3600; // 25200

            // Calculate proportional target based on actual workSeconds
            // e.g., 5.6 hours = 5.6/7 of the target. We use floor to round down.
            $targetQty = floor(($processTarget->target_qty / $fullShiftSeconds) * $workSeconds);
        } else {
            // --- WAX INJECTION LOGIC (Cycle Time Based - Engine A) ---
            $item = MdItemMirror::where('code', $validated['item_code'])
                ->where('status', 'active')
                ->firstOrFail();

            $itemCode = $this->normalizeCode($item->code);
            $heatNumber = $validated['heat_number'] ?? null;

            if ($heatNumber) {
                $heatNumberDetails = \App\Models\MdHeatNumberMirror::where('heat_number', $heatNumber)->first();
            }

            $cycleTimeMinutes = $validated['cycle_time_minutes'] ?? 0;
            $cycleTimeSeconds = $validated['cycle_time_seconds'] ?? 0;
            $cycleTimeSec = ($cycleTimeMinutes * 60) + $cycleTimeSeconds;

            if ($cycleTimeSec <= 0) {
                throw ValidationException::withMessages([
                    'cycle_time_seconds' => 'Total Cycle Time tidak boleh 0 detik.',
                ]);
            }

            $targetQty = intdiv($workSeconds, $cycleTimeSec);
        }

        // Calculate Achievement
        $achievementPercent = $targetQty > 0
            ? round(($actualQty / $targetQty) * 100, 2)
            : 0;

        /**
         * 7. SIMPAN KE FACT TABLE (IMMUTABLE KPI)
         * NO FK — SNAPSHOT ONLY
         */
        ProductionLog::create([
            'department_code' => $activeDepartment,
            'production_date' => $validated['production_date'],
            'shift' => $validated['shift'],

            'operator_code' => $this->normalizeCode($operator->code),
            'machine_code' => $this->normalizeCode($machine->code),
            'item_code' => $itemCode, // Process Name if Lilin Process Target, Item Code if Cetak Lilin Cycle Time
            'heat_number' => $heatNumber,
            'size' => $heatNumberDetails ? $heatNumberDetails->size : null,
            'customer' => $heatNumberDetails ? $heatNumberDetails->customer : null,
            'line' => $heatNumberDetails ? $heatNumberDetails->line : null,

            'time_start' => $validated['time_start'],
            'time_end' => $validated['time_end'],
            'work_hours' => $workHours,

            // SNAPSHOT NILAI KRITIS (MANUAL INPUT)
            'cycle_time_used_sec' => $cycleTimeSec,
            'target_qty' => $targetQty,
            'actual_qty' => $actualQty,
            'achievement_percent' => $achievementPercent,
            'remark' => $validated['remark'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        // Regenerate KPI (Daily Recap)
        \App\Services\DailyKpiService::generateOperatorDaily($validated['production_date']);
        \App\Services\DailyKpiService::generateMachineDaily($validated['production_date']);

        return redirect()
            ->back()
            ->with('success', "Data produksi a.n. {$operator->name} berhasil disimpan.");
    }

    /**
     * =================================
     * HELPER NORMALISASI KODE
     * =================================
     */
    private function normalizeCode(string $value): string
    {
        return strtolower(trim($value));
    }
}
