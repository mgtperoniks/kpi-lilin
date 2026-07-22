@extends('layouts.app')

@section('title', 'Input Hasil Produksi (Cycle Time)')

@section('content')
    <div x-data="productionForm()" class="w-full mx-auto pb-24 px-2">

        {{-- Header Title --}}
        <div class="mb-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Input Hasil Produksi (Cetak Lilin)</h1>
                <p class="text-xs text-slate-500 font-medium">Departemen Cetak Lilin (Wax Injection) • Compact Grid Entry</p>
            </div>
            <a href="{{ route('daily_report.operator.index') }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-white text-slate-700 font-bold border border-slate-200 rounded-xl hover:bg-slate-50 transition text-xs">
                <span class="material-icons-round text-base">history</span>
                Riwayat Harian
            </a>
        </div>

        {{-- Main Form --}}
        <form id="production-form" action="{{ route('production.store') }}" method="POST" class="space-y-4">
            @csrf

            {{-- Header Section (Single Entry) --}}
            <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-xs">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    {{-- Tanggal --}}
                    <div class="space-y-0.5">
                        <label class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Tanggal Produksi</label>
                        <input type="date" name="production_date" value="{{ date('Y-m-d', strtotime('-1 day')) }}" required
                            class="w-full bg-slate-50 border-slate-200 rounded-lg focus:ring-1 focus:ring-green-500 focus:border-green-500 text-xs font-semibold text-slate-700 p-2">
                    </div>

                    {{-- Shift --}}
                    <div class="space-y-0.5">
                        <label class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Shift</label>
                        <select name="shift" x-model="selectedShift" @change="applyShiftTimes" required
                            class="w-full bg-slate-50 border-slate-200 rounded-lg focus:ring-1 focus:ring-green-500 focus:border-green-500 text-xs font-semibold text-slate-700 p-2">
                            <option value="1">Shift 1 (07:00 - 15:00)</option>
                            <option value="2">Shift 2 (15:00 - 23:00)</option>
                            <option value="3">Shift 3 (23:00 - 07:00)</option>
                            <option value="non_shift">Non Shift</option>
                        </select>
                    </div>

                    {{-- Operator Search --}}
                    <div class="space-y-0.5 relative" @click.outside="showOperatorSuggestions = false">
                        <label class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Operator</label>
                        <div class="relative">
                            <input type="text" x-model="operatorSearch" @input.debounce.300ms="searchOperators"
                                placeholder="Cari Operator..."
                                class="w-full bg-slate-50 border-slate-200 rounded-lg focus:ring-1 focus:ring-green-500 focus:border-green-500 text-xs font-semibold text-slate-700 p-2 pl-7" autocomplete="off">
                            <span class="material-icons-round absolute left-2 top-2 text-slate-400 text-base">person</span>
                            <input type="hidden" name="operator_code" x-model="selectedOperatorCode" required>
                        </div>
                        {{-- Suggestions --}}
                        <div x-show="showOperatorSuggestions && operatorList.length > 0"
                            class="absolute z-50 w-full bg-white border border-slate-200 rounded-lg shadow-md mt-1 max-h-48 overflow-y-auto" style="display: none;">
                            <template x-for="op in operatorList" :key="op.code">
                                <div @click="selectOperator(op)"
                                    class="p-2 hover:bg-green-50 cursor-pointer border-b border-slate-50 last:border-none">
                                    <p class="text-xs font-bold text-slate-700" x-text="op.name"></p>
                                    <p class="text-[9px] text-slate-400" x-text="op.code"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Mesin Search --}}
                    <div class="space-y-0.5 relative" @click.outside="showMachineSuggestions = false">
                        <label class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Mesin</label>
                        <div class="relative">
                            <input type="text" x-model="machineSearch" @input.debounce.300ms="searchMachines"
                                placeholder="Cari Mesin..."
                                class="w-full bg-slate-50 border-slate-200 rounded-lg focus:ring-1 focus:ring-green-500 focus:border-green-500 text-xs font-semibold text-slate-700 p-2 pl-7" autocomplete="off">
                            <span class="material-icons-round absolute left-2 top-2 text-slate-400 text-base">precision_manufacturing</span>
                            <input type="hidden" name="machine_code" x-model="selectedMachineCode" required>
                        </div>
                        {{-- Suggestions --}}
                        <div x-show="showMachineSuggestions && machineList.length > 0"
                            class="absolute z-50 w-full bg-white border border-slate-200 rounded-lg shadow-md mt-1 max-h-48 overflow-y-auto" style="display: none;">
                            <template x-for="mac in machineList" :key="mac.code">
                                <div @click="selectMachine(mac)"
                                    class="p-2 hover:bg-green-50 cursor-pointer border-b border-slate-50 last:border-none">
                                    <p class="text-xs font-bold text-slate-700" x-text="mac.name"></p>
                                    <p class="text-[9px] text-slate-400" x-text="mac.code"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Table (Production Grid) --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-xs overflow-hidden">
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left border-collapse table-fixed min-w-[950px]">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200 text-[9px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="w-8 text-center py-2 px-1">No</th>
                                <th class="w-52 py-2 px-1">Heat Number</th>
                                <th class="w-24 py-2 px-1 text-center">Jam Mulai</th>
                                <th class="w-24 py-2 px-1 text-center">Jam Selesai</th>
                                <th class="w-32 py-2 px-1 text-center">Cycle Time</th>
                                <th class="w-16 py-2 px-1 text-center">Target</th>
                                <th class="w-20 py-2 px-1 text-center">Hasil OK</th>
                                <th class="w-18 py-2 px-1 text-center">KPI %</th>
                                <th class="w-28 py-2 px-1 text-center">Jenis</th>
                                <th class="w-8 text-center py-2 px-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, idx) in rows" :key="row.uid">
                                <tr class="border-b border-slate-100 hover:bg-slate-50/20 transition-colors">
                                    {{-- Row Number --}}
                                    <td class="text-center text-xs font-semibold text-slate-400 py-1 px-1" x-text="idx + 1"></td>

                                    {{-- Heat Number & Item Name (Inline helper) --}}
                                    <td class="py-1 px-1 relative">
                                        <input type="text" x-model="row.heatNumberSearch"
                                            @input.debounce.250ms="searchHeatNumbers(row, idx)"
                                            @keydown="handleKeyNavigation($event, idx, 'heat_number')"
                                            :data-row="idx" data-col="heat_number"
                                            placeholder="Cari..." autocomplete="off"
                                            class="w-full text-xs font-bold text-slate-700 bg-white border border-slate-200 focus:border-green-500 focus:ring-1 focus:ring-green-100 rounded-md p-1 uppercase">
                                        
                                        <input type="hidden" :name="'rows['+idx+'][heat_number]'" :value="row.heat_number">
                                        <input type="hidden" :name="'rows['+idx+'][item_code]'" :value="row.item_code">

                                        {{-- Inline Item Name Helper --}}
                                        <div x-show="row.item_name" class="text-[9px] text-slate-400 truncate max-w-[200px] mt-0.5" :title="row.item_name" x-text="row.item_name"></div>

                                        {{-- Suggestions Dropdown --}}
                                        <div x-show="row.showSuggestions && row.suggestions.length > 0"
                                            @click.outside="row.showSuggestions = false"
                                            class="absolute left-1 right-1 z-50 bg-white border border-slate-200 rounded-md shadow-lg mt-1 max-h-40 overflow-y-auto" style="display: none;">
                                            <template x-for="hn in row.suggestions" :key="hn.id">
                                                <div @click="selectHeatNumber(row, idx, hn)"
                                                    class="p-2 hover:bg-green-50 cursor-pointer border-b border-slate-50 last:border-none text-left">
                                                    <p class="text-xs font-bold text-slate-700" x-text="hn.heat_number"></p>
                                                    <p class="text-[9px] text-slate-400 truncate" x-text="hn.item_name"></p>
                                                </div>
                                            </template>
                                        </div>
                                    </td>

                                    {{-- Jam Mulai --}}
                                    <td class="py-1 px-1 text-center">
                                        <input type="time" :name="'rows['+idx+'][time_start]'" x-model="row.time_start"
                                            @change="calculateRowTarget(row, idx)"
                                            @keydown="handleKeyNavigation($event, idx, 'time_start')"
                                            :data-row="idx" data-col="time_start"
                                            class="w-full text-xs font-semibold text-slate-700 bg-white border border-slate-200 focus:border-green-500 focus:ring-1 focus:ring-green-100 rounded-md p-1">
                                    </td>

                                    {{-- Jam Selesai --}}
                                    <td class="py-1 px-1 text-center">
                                        <input type="time" :name="'rows['+idx+'][time_end]'" x-model="row.time_end"
                                            @change="handleTimeEndChange(row, idx)"
                                            @keydown="handleKeyNavigation($event, idx, 'time_end')"
                                            :data-row="idx" data-col="time_end"
                                            class="w-full text-xs font-semibold text-slate-700 bg-white border border-slate-200 focus:border-green-500 focus:ring-1 focus:ring-green-100 rounded-md p-1">
                                    </td>

                                    {{-- Cycle Time (Compact Inputs: [M] : [S]) --}}
                                    <td class="py-1 px-1">
                                        <div class="flex items-center gap-1 justify-center">
                                            <div class="relative w-12">
                                                <input type="number" :name="'rows['+idx+'][cycle_time_minutes]'" x-model="row.cycle_time_minutes"
                                                    @input="calculateRowTarget(row, idx)"
                                                    @keydown="handleKeyNavigation($event, idx, 'cycle_time_minutes')"
                                                    :data-row="idx" data-col="cycle_time_minutes"
                                                    min="0" placeholder="Min"
                                                    class="w-full text-xs font-semibold text-slate-700 text-center bg-white border border-slate-200 focus:border-green-500 focus:ring-1 focus:ring-green-100 rounded-md p-1 pr-3">
                                                <span class="absolute right-0.5 top-1.5 text-[7px] font-bold text-slate-400">m</span>
                                            </div>
                                            <span class="text-slate-400 font-bold text-xs">:</span>
                                            <div class="relative w-12">
                                                <input type="number" :name="'rows['+idx+'][cycle_time_seconds]'" x-model="row.cycle_time_seconds"
                                                    @input="calculateRowTarget(row, idx)"
                                                    @keydown="handleKeyNavigation($event, idx, 'cycle_time_seconds')"
                                                    :data-row="idx" data-col="cycle_time_seconds"
                                                    min="0" max="59" placeholder="Sec"
                                                    class="w-full text-xs font-semibold text-slate-700 text-center bg-white border border-slate-200 focus:border-green-500 focus:ring-1 focus:ring-green-100 rounded-md p-1 pr-3">
                                                <span class="absolute right-0.5 top-1.5 text-[7px] font-bold text-slate-400">s</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Target (Read-only) --}}
                                    <td class="py-1 px-1 text-center">
                                        <span class="text-xs font-bold text-slate-600 block py-1.5 bg-slate-50 rounded-md border border-slate-100"
                                            x-text="row.target_qty">
                                        </span>
                                    </td>

                                    {{-- Hasil OK --}}
                                    <td class="py-1 px-1 text-center">
                                        <input type="number" :name="'rows['+idx+'][actual_qty]'" x-model="row.actual_qty"
                                            @input="calculateRowAchievement(row)"
                                            @keydown="handleKeyNavigation($event, idx, 'actual_qty')"
                                            :data-row="idx" data-col="actual_qty"
                                            min="0" placeholder="0"
                                            class="w-full text-xs font-bold text-center text-slate-850 bg-white border border-slate-200 focus:border-green-500 focus:ring-1 focus:ring-green-100 rounded-md p-1">
                                    </td>

                                    {{-- KPI % (Read-only) --}}
                                    <td class="py-1 px-1 text-center">
                                        <div class="text-xs font-bold rounded-md py-1.5 border"
                                            :class="{
                                                'bg-green-50 text-green-600 border-green-100': row.achievement >= 100,
                                                'bg-amber-50 text-amber-600 border-amber-100': row.achievement >= 80 && row.achievement < 100,
                                                'bg-red-50 text-red-600 border-red-100': row.achievement < 80
                                            }"
                                            x-text="row.achievement + '%'">
                                        </div>
                                    </td>

                                    {{-- Jenis Dropdown (maps to remark backend field) --}}
                                    <td class="py-1 px-1 text-center">
                                        <select :name="'rows['+idx+'][remark]'" x-model="row.remark"
                                            @keydown="handleKeyNavigation($event, idx, 'remark')"
                                            :data-row="idx" data-col="remark"
                                            class="w-full text-xs font-bold text-slate-700 bg-white border border-slate-200 focus:border-green-500 focus:ring-1 focus:ring-green-100 rounded-md p-1">
                                            <option value="">(Pilih)</option>
                                            <option value="FL SS">FL SS</option>
                                            <option value="PF SS">PF SS</option>
                                            <option value="FL BS">FL BS</option>
                                            <option value="PF BS">PF BS</option>
                                        </select>
                                    </td>

                                    {{-- Delete Row --}}
                                    <td class="py-1 px-1 text-center">
                                        <button type="button" @click="clearOrDeleteRow(idx)"
                                            class="text-red-400 hover:text-red-600 hover:bg-red-50 rounded-full p-0.5 transition-colors"
                                            title="Hapus baris">
                                            <span class="material-icons-round text-base">close</span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Action Row Buttons --}}
                <div class="p-2 border-t border-slate-100 bg-slate-50/20 flex justify-start gap-2">
                    <button type="button" @click="addRows(1)"
                        class="flex items-center gap-1 px-3 py-1.5 bg-white text-green-600 border border-green-200 font-extrabold hover:bg-green-50 transition rounded-lg text-[10px]">
                        <span class="material-icons-round text-xs">add</span>
                        Tambah Baris
                    </button>
                    <button type="button" @click="addRows(4)"
                        class="flex items-center gap-1 px-2.5 py-1.5 bg-white text-slate-600 border border-slate-200 font-semibold hover:bg-slate-50 transition rounded-lg text-[10px]">
                        <span class="material-icons-round text-xs">playlist_add</span>
                        + 4 Baris
                    </button>
                </div>
            </div>

            {{-- Submit & Validation Section --}}
            @if(auth()->user()->isReadOnly())
                <div class="bg-amber-50 border border-amber-200 text-amber-700 p-3 rounded-xl flex items-center gap-2">
                    <span class="material-icons-round text-amber-500 text-sm">lock</span>
                    <div class="text-xs font-semibold">
                        Anda berada dalam mode **Read-Only** ({{ auth()->user()->role }}).
                        Anda dapat melihat data tetapi tidak dapat melakukan penyimpanan atau perubahan.
                    </div>
                </div>
            @else
                <button type="button" @click="confirmSubmit"
                    class="w-full bg-green-600 text-white font-extrabold py-3 rounded-xl shadow-md shadow-green-500/20 flex items-center justify-center gap-1.5 hover:bg-green-700 active:scale-98 transition text-sm">
                    <span class="material-icons-round text-base">save_alt</span>
                    Simpan Data Produksi
                </button>
            @endif

            {{-- Session Messages --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-200 text-green-700 p-3 rounded-lg flex items-center gap-2 text-xs font-semibold">
                    <span class="material-icons-round text-sm">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg text-xs font-semibold">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </form>
    </div>

    {{-- AlpineJS Controller --}}
    <script>
        function productionForm() {
            let uniqueIdCounter = 0;

            function createEmptyRow(defaultStart = '', defaultEnd = '') {
                uniqueIdCounter++;
                return {
                    uid: 'row_' + uniqueIdCounter,
                    heat_number: '',
                    item_code: '',
                    item_name: '',
                    time_start: defaultStart,
                    time_end: defaultEnd,
                    cycle_time_minutes: '',
                    cycle_time_seconds: '',
                    target_qty: 0,
                    actual_qty: '',
                    achievement: 0,
                    remark: '',
                    
                    // Suggestions fields
                    heatNumberSearch: '',
                    showSuggestions: false,
                    suggestions: []
                };
            }

            return {
                // Header fields
                selectedShift: '1',
                operatorSearch: '',
                selectedOperatorCode: '',
                selectedOperatorName: '',
                operatorList: [],
                showOperatorSuggestions: false,

                machineSearch: '',
                selectedMachineCode: '',
                machineList: [],
                showMachineSuggestions: false,

                // Grid rows
                rows: [],

                init() {
                    // Initialize with 8 rows
                    this.applyShiftTimes();
                },

                applyShiftTimes() {
                    let start = '';
                    let end = '';
                    if (this.selectedShift === '1') {
                        start = '07:00';
                        end = '15:00';
                    } else if (this.selectedShift === '2') {
                        start = '15:00';
                        end = '23:00';
                    } else if (this.selectedShift === '3') {
                        start = '23:00';
                        end = '07:00';
                    }

                    if (this.rows.length === 0) {
                        for (let i = 0; i < 8; i++) {
                            const rowStart = i === 0 ? start : '';
                            const rowEnd = i === 0 ? end : '';
                            this.rows.push(createEmptyRow(rowStart, rowEnd));
                        }
                    } else {
                        this.rows.forEach((row, i) => {
                            if (!row.time_start) {
                                row.time_start = i === 0 ? start : '';
                            }
                            if (!row.time_end) {
                                row.time_end = i === 0 ? end : '';
                            }
                            this.calculateRowTarget(row, i);
                        });
                    }
                },

                addRows(count) {
                    let start = '';
                    let end = '';
                    if (this.selectedShift === '1') { start = '07:00'; end = '15:00'; }
                    else if (this.selectedShift === '2') { start = '15:00'; end = '23:00'; }
                    else if (this.selectedShift === '3') { start = '23:00'; end = '07:00'; }

                    for (let i = 0; i < count; i++) {
                        let prefStart = '';
                        if (this.rows.length > 0) {
                            const lastRow = this.rows[this.rows.length - 1];
                            if (lastRow.time_end) {
                                prefStart = lastRow.time_end;
                            }
                        } else {
                            prefStart = start;
                        }
                        this.rows.push(createEmptyRow(prefStart, ''));
                    }
                },

                clearOrDeleteRow(idx) {
                    if (this.rows.length > 8) {
                        this.rows.splice(idx, 1);
                    } else {
                        this.rows[idx] = createEmptyRow();
                    }
                },

                // Autocomplete endpoints
                async searchOperators() {
                    if (this.operatorSearch.length < 1) return;
                    const res = await fetch(`{{ route('api.search.operators') }}?q=${encodeURIComponent(this.operatorSearch)}`);
                    this.operatorList = await res.json();
                    this.showOperatorSuggestions = true;
                },

                selectOperator(op) {
                    this.selectedOperatorCode = op.code;
                    this.selectedOperatorName = op.name;
                    this.operatorSearch = op.name;
                    this.showOperatorSuggestions = false;
                },

                async searchMachines() {
                    if (this.machineSearch.length < 1) return;
                    const res = await fetch(`{{ route('api.search.machines') }}?q=${encodeURIComponent(this.machineSearch)}`);
                    this.machineList = await res.json();
                    this.showMachineSuggestions = true;
                },

                selectMachine(mac) {
                    this.selectedMachineCode = mac.code;
                    this.machineSearch = mac.name;
                    this.showMachineSuggestions = false;
                },

                async searchHeatNumbers(row, idx) {
                    if (row.heatNumberSearch.length < 1) {
                        row.suggestions = [];
                        row.showSuggestions = false;
                        return;
                    }
                    const res = await fetch(`{{ route('api.search.heat_numbers') }}?q=${encodeURIComponent(row.heatNumberSearch)}`);
                    row.suggestions = await res.json();
                    row.showSuggestions = true;
                },

                selectHeatNumber(row, idx, hn) {
                    row.heat_number = hn.heat_number;
                    row.heatNumberSearch = hn.heat_number;
                    row.item_code = hn.item_code;
                    row.item_name = hn.item_name;
                    row.showSuggestions = false;

                    // Prefill cycle time if available
                    if (hn.item && hn.item.cycle_time_sec) {
                        row.cycle_time_minutes = Math.floor(hn.item.cycle_time_sec / 60);
                        row.cycle_time_seconds = hn.item.cycle_time_sec % 60;
                    } else {
                        row.cycle_time_minutes = 0;
                        row.cycle_time_seconds = 0;
                    }

                    // Prepopulate starting time sequentially
                    if (!row.time_start && idx > 0) {
                        const prev = this.rows[idx - 1];
                        if (prev && prev.time_end) {
                            row.time_start = prev.time_end;
                        }
                    }

                    this.calculateRowTarget(row, idx);
                },

                handleTimeEndChange(row, idx) {
                    this.calculateRowTarget(row, idx);

                    // Sequential autofill
                    if (idx < this.rows.length - 1) {
                        const nextRow = this.rows[idx + 1];
                        if (!nextRow.time_start) {
                            nextRow.time_start = row.time_end;
                            this.calculateRowTarget(nextRow, idx + 1);
                        }
                    }
                },

                calculateRowTarget(row, idx) {
                    const mins = parseInt(row.cycle_time_minutes) || 0;
                    const secs = parseInt(row.cycle_time_seconds) || 0;
                    const totalCycleTimeSec = (mins * 60) + secs;

                    if (!row.time_start || !row.time_end || totalCycleTimeSec <= 0) {
                        row.target_qty = 0;
                        return;
                    }

                    const start = this.parseTime(row.time_start);
                    const end = this.parseTime(row.time_end);

                    let diffMinutes = end - start;
                    if (diffMinutes < 0) diffMinutes += 1440; // cross-day

                    const diffSeconds = diffMinutes * 60;
                    row.target_qty = Math.floor(diffSeconds / totalCycleTimeSec);
                    this.calculateRowAchievement(row);
                },

                calculateRowAchievement(row) {
                    if (!row.target_qty || row.target_qty <= 0) {
                        row.achievement = 0;
                        return;
                    }
                    const actual = parseInt(row.actual_qty) || 0;
                    row.achievement = ((actual / row.target_qty) * 100).toFixed(1);
                },

                parseTime(t) {
                    if (!t) return 0;
                    const [h, m] = t.split(':');
                    return parseInt(h) * 60 + parseInt(m);
                },

                // Excel Keyboard Navigation
                handleKeyNavigation(event, idx, col) {
                    const key = event.key;
                    let targetRow = idx;
                    let targetCol = col;

                    const cols = ['heat_number', 'time_start', 'time_end', 'cycle_time_minutes', 'cycle_time_seconds', 'actual_qty', 'remark'];
                    const colIndex = cols.indexOf(col);

                    if (key === 'ArrowDown') {
                        event.preventDefault();
                        targetRow = Math.min(this.rows.length - 1, idx + 1);
                    } else if (key === 'ArrowUp') {
                        event.preventDefault();
                        targetRow = Math.max(0, idx - 1);
                    } else if (key === 'ArrowRight' && event.target.selectionStart === event.target.value.length) {
                        if (colIndex < cols.length - 1) {
                            targetCol = cols[colIndex + 1];
                        }
                    } else if (key === 'ArrowLeft' && event.target.selectionStart === 0) {
                        if (colIndex > 0) {
                            targetCol = cols[colIndex - 1];
                        }
                    } else if (key === 'Enter') {
                        event.preventDefault();
                        if (idx < this.rows.length - 1) {
                            targetRow = idx + 1;
                        } else {
                            this.addRows(1);
                            targetRow = idx + 1;
                        }
                    } else {
                        return;
                    }

                    this.$nextTick(() => {
                        const selector = `[data-row="${targetRow}"][data-col="${targetCol}"]`;
                        const targetEl = document.querySelector(selector);
                        if (targetEl) {
                            targetEl.focus();
                            if (targetEl.select) targetEl.select();
                        }
                    });
                },

                confirmSubmit() {
                    const activeRows = this.rows.filter(r => r.heat_number.trim() !== '');

                    if (!this.selectedOperatorCode || !this.selectedMachineCode) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Data Header Belum Lengkap',
                            text: 'Mohon isi Operator dan Mesin terlebih dahulu.',
                            confirmButtonColor: '#059669'
                        });
                        return;
                    }

                    if (activeRows.length === 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Tabel Masih Kosong',
                            text: 'Minimal isi satu baris data produksi (Heat Number).',
                            confirmButtonColor: '#059669'
                        });
                        return;
                    }

                    for (let i = 0; i < activeRows.length; i++) {
                        const r = activeRows[i];
                        const totalSec = (parseInt(r.cycle_time_minutes) * 60) + parseInt(r.cycle_time_seconds) || 0;
                        if (!r.time_start || !r.time_end || totalSec <= 0 || r.actual_qty === '') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Baris Data Tidak Valid',
                                text: `Mohon lengkapi Jam Mulai, Jam Selesai, Cycle Time, dan Hasil OK pada Heat Number ${r.heat_number}.`,
                                confirmButtonColor: '#059669'
                            });
                            return;
                        }
                    }

                    // Build summary Table
                    let rowsHtml = '';
                    activeRows.forEach(r => {
                        rowsHtml += `
                            <tr class="border-b border-slate-100 text-xs">
                                <td class="p-1.5 font-bold text-slate-800">${r.heat_number}</td>
                                <td class="p-1.5 text-slate-500 truncate max-w-[150px]" title="${r.item_name}">${r.item_name}</td>
                                <td class="p-1.5 text-center text-slate-700">${r.time_start} - ${r.time_end}</td>
                                <td class="p-1.5 text-center text-slate-700">${r.cycle_time_minutes}m ${r.cycle_time_seconds}s</td>
                                <td class="p-1.5 text-center font-bold text-green-600">${r.actual_qty} / ${r.target_qty}</td>
                                <td class="p-1.5 text-center font-bold text-slate-600">${r.achievement}%</td>
                                <td class="p-1.5 text-center font-bold text-indigo-600">${r.remark || '-'}</td>
                            </tr>
                        `;
                    });

                    const summaryHtml = `
                        <div class="text-left space-y-3">
                            <div class="grid grid-cols-3 gap-2 bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-xs">
                                <div><span class="text-slate-400">Operator:</span> <strong class="text-slate-700">${this.selectedOperatorName}</strong></div>
                                <div><span class="text-slate-400">Mesin:</span> <strong class="text-slate-700">${this.machineSearch}</strong></div>
                                <div><span class="text-slate-400">Shift:</span> <strong class="text-slate-700">${this.selectedShift}</strong></div>
                            </div>
                            <div class="max-h-52 overflow-y-auto border border-slate-200 rounded-lg">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                            <th class="p-1.5">Heat</th>
                                            <th class="p-1.5">Barang</th>
                                            <th class="p-1.5 text-center">Waktu</th>
                                            <th class="p-1.5 text-center">C/T</th>
                                            <th class="p-1.5 text-center">Hasil</th>
                                            <th class="p-1.5 text-center">KPI</th>
                                            <th class="p-1.5 text-center">Jenis</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${rowsHtml}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;

                    Swal.fire({
                        title: 'Simpan Data Produksi?',
                        html: summaryHtml,
                        icon: 'question',
                        width: '600px',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Simpan',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#059669',
                        cancelButtonColor: '#dc2626',
                        reverseButtons: true,
                        focusConfirm: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('production-form').submit();
                        }
                    });
                }
            };
        }
    </script>
@endsection
