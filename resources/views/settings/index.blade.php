@extends('layouts.app')

@section('title', 'Pengaturan Target Proses')

@section('content')

@php
    $isReadOnly = auth()->user()->isReadOnly();
@endphp

<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Pengaturan Target Produksi</h1>
        <p class="text-gray-500 text-sm mt-1">Atur target harian berdasarkan masing-masing proses untuk departemen yang aktif.</p>
    </div>

    {{-- Filter Bulan & Tahun --}}
    <form action="{{ route('settings.index') }}" method="GET" class="flex items-center gap-2 bg-white p-2 rounded-xl border border-gray-200 shadow-sm">
        <select name="month" class="bg-gray-50 border-gray-200 rounded-lg text-sm focus:ring-green-500 focus:border-green-500">
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                </option>
            @endfor
        </select>
        <select name="year" class="bg-gray-50 border-gray-200 rounded-lg text-sm focus:ring-green-500 focus:border-green-500">
            @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                    {{ $y }}
                </option>
            @endfor
        </select>
        <button type="submit" class="p-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            <span class="material-icons-round text-sm">search</span>
        </button>
    </form>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r shadow-sm flex items-center">
        <span class="material-icons-round mr-2">check_circle</span>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r shadow-sm flex items-center">
        <span class="material-icons-round mr-2">error</span>
        {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6">
        @if($targets->isEmpty())
            <div class="text-center py-8">
                <span class="material-icons-round text-gray-300 text-4xl mb-3">inbox</span>
                <p class="text-gray-500 font-medium">Belum ada proses yang terdaftar untuk departemen ini.</p>
            </div>
        @else
            @if($isLocked)
                <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-700 p-4 rounded-xl flex items-center gap-2 text-sm">
                    <span class="material-icons-round">lock</span>
                    Target untuk bulan ini sudah berlalu dan tidak dapat diubah lagi. (Read-Only)
                </div>
            @endif

            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($targets as $target)
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                {{ $target->process_name }}
                                <span class="text-xs font-normal text-gray-500 block">Bagian: {{ $target->department_code }}</span>
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <input type="number" 
                                    name="targets[{{ $target->id }}]" 
                                    value="{{ old('targets.'.$target->id, $target->target_qty) }}"
                                    min="0"
                                    {{ ($isReadOnly || $isLocked) ? 'readonly' : '' }}
                                    class="focus:ring-green-500 focus:border-green-500 block w-full pl-3 pr-12 sm:text-sm border-gray-300 rounded-md py-2 {{ ($isReadOnly || $isLocked) ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '' }}">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">PCS</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if(!$isReadOnly && !$isLocked)
                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <span class="material-icons-round text-sm mr-2">save</span>
                            Simpan Perubahan
                        </button>
                    </div>
                @endif
            </form>
        @endif
    </div>
</div>

@endsection
