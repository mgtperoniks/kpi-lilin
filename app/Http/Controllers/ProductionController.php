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

        // Fetch process targets for the active Lilin department for the current month
        $processTargets = \App\Models\ProcessTarget::where('month', date('n'))
            ->where('year', date('Y'))
            ->where('department_code', $activeDepartment)
            ->orderBy('process_name')
            ->get();

        return view('production.input', [
            'machines' => $machines,
            'processTargets' => $processTargets,
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

        /**
         * 1. VALIDASI INPUT DASAR
         * Server = Source of Truth
         */
        $validated = $request->validate([
            'production_date' => 'required|date',
            'shift' => 'required|string|max:10',

            'operator_code' => 'required|string',
            'machine_code' => 'required|string',

            // Bubut (Heat Number) fields
            'item_code' => 'nullable|string',
            'heat_number' => 'nullable|string',

            // Lilin (Process) fields
            'process_id' => 'nullable|integer',
            'process_name' => 'nullable|string',

            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i',

            'cycle_time_minutes' => 'nullable|integer|min:0',
            'cycle_time_seconds' => 'nullable|integer|min:0|max:59',
            'target_qty' => 'nullable|integer|min:0', // Passed from frontend for Lilin

            'actual_qty' => 'required|integer|min:0',
            'remark' => 'nullable|string|max:50',
            'note' => 'nullable|string|max:255',
        ]);

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

        $isLilinDepartment = session('selected_department_code', auth()->user()->department_code) &&
            str_starts_with(session('selected_department_code', auth()->user()->department_code), '402.');

        $itemCode = null;
        $heatNumber = null;
        $cycleTimeSec = 0;
        $targetQty = 0;
        $actualQty = (int) $validated['actual_qty'];
        $heatNumberDetails = null;

        if ($isLilinDepartment && $validated['process_id']) {
            // --- LILIN LOGIC (Process Based) ---
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
            // --- BUBUT LOGIC (Heat Number & Item Based) ---
            if (!$validated['item_code']) {
                throw ValidationException::withMessages([
                    'item_code' => 'Item Code is required for non-Lilin departments.',
                ]);
            }

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
            'production_date' => $validated['production_date'],
            'shift' => $validated['shift'],

            'operator_code' => $this->normalizeCode($operator->code),
            'machine_code' => $this->normalizeCode($machine->code),
            'item_code' => $itemCode, // Process Name if Lilin, Item Code if Bubut
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
