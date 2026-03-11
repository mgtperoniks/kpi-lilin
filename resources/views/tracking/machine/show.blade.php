@extends('layouts.app')

@section('title', 'Detail KPI Mesin')

@section('content')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Detail KPI Mesin</h1>
            <p class="text-gray-500">Laporan detail kinerja mesin harian</p>
        </div>
        <a href="{{ route('tracking.mesin.index', ['date' => $summary->kpi_date]) }}"
            class="flex items-center text-green-600 hover:text-green-800 font-medium transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                    clip-rule="evenodd" />
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <!-- Ringkasan Card Compact -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 md:gap-8">

            <!-- Left: Machine -->
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div
                    class="bg-amber-100 text-amber-600 rounded-lg h-10 w-10 flex items-center justify-center font-bold text-lg shrink-0">
                    M
                </div>
                <div class="leading-tight">
                    <div class="font-bold text-gray-800 text-lg">{{ $summary->machine->name ?? $summary->machine_code }}
                    </div>
                    <div class="text-xs text-gray-400 font-mono">{{ $summary->machine_code }}</div>
                </div>
            </div>

            <!-- Center: Date & Hours -->
            <div
                class="flex items-center gap-6 text-sm text-gray-600 border-t md:border-t-0 md:border-l border-b md:border-b-0 md:border-r border-gray-100 py-2 md:py-0 w-full md:w-auto justify-center md:justify-start px-0 md:px-8">
                <div class="text-center md:text-left">
                    <span class="block text-xs text-gray-400 uppercase tracking-wider font-semibold">Tanggal</span>
                    <span
                        class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($summary->kpi_date)->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                </div>
                <div class="h-8 w-px bg-gray-200"></div>
                <div class="text-center md:text-left">
                    <span class="block text-xs text-gray-400 uppercase tracking-wider font-semibold">Jam Prod</span>
                    <span class="font-medium text-gray-800">{{ number_format($summary->total_work_hours, 2) }} Jam</span>
                </div>
            </div>

            <!-- Right: KPI -->
            <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                <div class="text-right">
                    <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Total KPI</div>
                    <div
                        class="text-3xl font-black {{ $summary->kpi_percent >= 100 ? 'text-green-600' : ($summary->kpi_percent >= 85 ? 'text-orange-500' : 'text-red-600') }}">
                        {{ number_format($summary->kpi_percent, 2) }}%
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-800">Detail Aktivitas Mesin</h3>
            <span class="text-xs font-medium bg-gray-200 text-gray-600 px-2 py-1 rounded-full">{{ $activities->count() }}
                Item</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 font-medium uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-3 border-b">Jam</th>
                        <th class="px-6 py-3 border-b">Operator</th>
                        <th class="px-6 py-3 border-b">Item Produk</th>
                        <th class="px-6 py-3 border-b">Heat No</th>
                        <th class="px-6 py-3 border-b text-right">Target</th>
                        <th class="px-6 py-3 border-b text-right">Aktual</th>
                        <th class="px-6 py-3 border-b text-center">KPI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($activities as $act)
                        <tr class="hover:bg-green-50 transition-colors duration-150 group">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-600">
                                {{ \Carbon\Carbon::parse($act->time_start)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($act->time_end)->format('H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-800">{{ $act->operator->name ?? $act->operator_code }}</div>
                                <div class="text-xs text-gray-400 font-mono">{{ $act->operator_code }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800">{{ $act->item->name ?? $act->item_code }}</div>
                                @if($act->size)
                                <div class="text-xs text-gray-500">{{ $act->size }}</div> @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-gray-600">
                                {{ $act->heat_number ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-right text-gray-600">
                                {{ $act->target_qty }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-800">
                                {{ $act->actual_qty }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $colorClass = 'bg-red-100 text-red-700';
                                    if ($act->achievement_percent >= 100) {
                                        $colorClass = 'bg-green-100 text-green-700';
                                    } elseif ($act->achievement_percent >= 85) {
                                        $colorClass = 'bg-orange-100 text-orange-700';
                                    }
                                @endphp
                                <span
                                    class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $colorClass }}">
                                    {{ $act->achievement_percent }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400 italic">
                                Tidak ada aktivitas tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection