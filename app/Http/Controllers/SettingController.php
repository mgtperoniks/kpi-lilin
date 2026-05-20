<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ProcessTarget;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $selectedMonth = $request->get('month', date('n'));
        $selectedYear = $request->get('year', date('Y'));

        $currentMonth = date('n');
        $currentYear = date('Y');

        // Check if the selected month is in the past
        $isLocked = false;
        if ($selectedYear < $currentYear || ($selectedYear == $currentYear && $selectedMonth < $currentMonth)) {
            $isLocked = true;
        }

        $activeDepartment = session('selected_department_code', auth()->user()->department_code);

        // View process targets for the active department matching the selected month and year
        $targets = ProcessTarget::where('month', $selectedMonth)
            ->where('year', $selectedYear)
            ->where('department_code', $activeDepartment)
            ->orderBy('process_name')
            ->get();

        $activeDepartment = session('selected_department_code', auth()->user()->department_code);

        // Auto-copy targets from previous month if empty for the selected month
        if ($targets->isEmpty() && str_starts_with($activeDepartment, '402.')) {
            $previousMonth = $selectedMonth == 1 ? 12 : $selectedMonth - 1;
            $previousYear = $selectedMonth == 1 ? $selectedYear - 1 : $selectedYear;

            // 1. Check the immediately previous month for the active department
            $previousTargets = ProcessTarget::where('month', $previousMonth)
                ->where('year', $previousYear)
                ->where('department_code', $activeDepartment)
                ->get();

            // 2. Self-Healing: If the immediate previous month is empty, search for the most recent month with targets for this department
            if ($previousTargets->isEmpty()) {
                $latestTarget = ProcessTarget::where('department_code', $activeDepartment)
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc')
                    ->first();
                if ($latestTarget) {
                    $previousTargets = ProcessTarget::where('month', $latestTarget->month)
                        ->where('year', $latestTarget->year)
                        ->where('department_code', $activeDepartment)
                        ->get();
                }
            }

            if ($previousTargets->isNotEmpty()) {
                foreach ($previousTargets as $pt) {
                    ProcessTarget::create([
                        'department_code' => $pt->department_code,
                        'process_name' => $pt->process_name,
                        'month' => $selectedMonth,
                        'year' => $selectedYear,
                        'target_qty' => $pt->target_qty,
                        'item_name' => $pt->item_name,
                        'size_name' => $pt->size_name,
                        'unit' => $pt->unit
                    ]);
                }

                // Re-fetch after copy
                $targets = ProcessTarget::where('month', $selectedMonth)
                    ->where('year', $selectedYear)
                    ->where('department_code', $activeDepartment)
                    ->orderBy('process_name')
                    ->get();
            }
        }

        return view('settings.index', compact('targets', 'selectedMonth', 'selectedYear', 'isLocked'));
    }

    public function updateTargets(Request $request)
    {
        if (auth()->user()->isReadOnly()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses (Read-Only).');
        }

        $selectedMonth = $request->input('month');
        $selectedYear = $request->input('year');
        $currentMonth = date('n');
        $currentYear = date('Y');

        if ($selectedYear < $currentYear || ($selectedYear == $currentYear && $selectedMonth < $currentMonth)) {
            return redirect()->back()->with('error', 'Tidak dapat mengubah target untuk bulan yang sudah berlalu (Terkunci).');
        }

        $request->validate([
            'targets' => 'required|array',
            'targets.*' => 'required|integer|min:0',
        ]);

        foreach ($request->targets as $id => $qty) {
            $target = ProcessTarget::find($id);
            if ($target && $target->department_code == session('selected_department_code', auth()->user()->department_code)) {
                $target->update(['target_qty' => $qty]);
            }
        }

        return redirect()->back()->with('success', 'Target produksi berhasil diperbarui.');
    }
}
