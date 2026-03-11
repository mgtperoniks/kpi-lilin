@extends('layouts.app')

@section('title', 'Dashboard Operator')

@section('content')

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard Operator</h1>
        <p class="text-gray-500">
            Trend KPI Operator:
            <span class="font-semibold text-gray-700">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</span>
            s/d
            <span class="font-semibold text-gray-700">{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
        </p>
    </div>

    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r shadow-sm flex items-center">
            <span class="material-icons-round text-lg mr-2">error_outline</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-100 bg-gray-50">
            <form method="GET" id="filterForm" class="flex flex-col md:flex-row md:items-end gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date', $startDate) }}"
                           class="block w-full shadow-sm text-sm border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 py-1.5">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date', $endDate) }}"
                           class="block w-full shadow-sm text-sm border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 py-1.5">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Operator</label>
                    <select name="operator_code" id="operator_code" class="select2-search block w-56 shadow-sm text-sm border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 py-1.5">
                        <option value="all">Semua Operator</option>
                        @foreach($operatorNames as $code => $name)
                            <option value="{{ $code }}" {{ request('operator_code', $selectedOperator) == $code ? 'selected' : '' }}>
                                {{ $code }} - {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex">
                    <button type="submit" class="px-5 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-bold uppercase tracking-wide rounded-md transition-colors shadow-sm h-fit inline-flex items-center gap-2">
                        <span class="material-icons-round text-sm">auto_graph</span>
                        Generate
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Chart Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h4 class="text-sm font-bold text-slate-800">Trend KPI Operator (%)</h4>
                <p class="text-[10px] text-slate-400 mt-0.5">Klik legend di bawah chart untuk show/hide operator tertentu</p>
            </div>
            <span class="material-icons-round text-slate-400">show_chart</span>
        </div>

        @if(count($chartDatasets) > 0)
            <div class="relative w-full" style="height: 480px;">
                <canvas id="operatorKpiChart"></canvas>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap gap-4 text-xs text-slate-500">
                <div class="flex items-center gap-1.5">
                    <span class="material-icons-round text-sm text-green-500">people</span>
                    <span><strong>{{ count($chartDatasets) }}</strong> operator ditampilkan</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="material-icons-round text-sm text-green-500">date_range</span>
                    <span><strong>{{ count($chartLabels) }}</strong> hari data</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span style="display:inline-block;width:18px;height:2px;border-top:2px dashed rgba(239,68,68,0.6);vertical-align:middle;"></span>
                    <span class="text-red-500 font-semibold">Target KPI 85%</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span style="display:inline-block;width:18px;height:2px;border-top:2px dashed rgba(34,197,94,0.7);vertical-align:middle;"></span>
                    <span class="text-green-600 font-semibold">Trendline (Regresi Linear)</span>
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                <span class="material-icons-round text-5xl mb-3">analytics</span>
                <p class="font-medium">Belum ada data KPI</p>
                <p class="text-xs mt-1">Silakan pilih rentang tanggal lain atau pastikan data sudah di-generate.</p>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Select2 init
            $('.select2-search').select2({ width: '100%', placeholder: 'Pilih Operator', allowClear: false });

            // Form validation
            var filterForm = document.getElementById('filterForm');
            filterForm.addEventListener('submit', function(e) {
                var start = new Date(document.getElementById('start_date').value);
                var end = new Date(document.getElementById('end_date').value);
                var diffDays = Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24));
                if (diffDays > 31) { e.preventDefault(); Swal.fire({ icon: 'warning', title: 'Batas Waktu', text: 'Rentang tanggal maksimal 31 hari.' }); return; }
                if (end < start) { e.preventDefault(); Swal.fire({ icon: 'error', title: 'Tanggal Tidak Valid', text: 'Tanggal akhir harus lebih besar dari tanggal mulai.' }); return; }
            });

            // ===== LINEAR REGRESSION FUNCTION =====
            function linearRegression(data) {
                var n = 0, sumX = 0, sumY = 0, sumXY = 0, sumXX = 0;
                for (var i = 0; i < data.length; i++) {
                    if (data[i] !== null && data[i] !== undefined) {
                        n++;
                        sumX += i;
                        sumY += data[i];
                        sumXY += i * data[i];
                        sumXX += i * i;
                    }
                }
                if (n < 2) return null;
                var slope = (n * sumXY - sumX * sumY) / (n * sumXX - sumX * sumX);
                var intercept = (sumY - slope * sumX) / n;
                return { slope: slope, intercept: intercept, n: n };
            }

            // ===== CHART =====
            var chartCanvas = document.getElementById('operatorKpiChart');
            if (!chartCanvas) return;

            var chartLabels = @json($chartLabels);
            var chartDatasets = @json($chartDatasets);
            var operatorNameMap = @json($operatorNames);
            var isSingleOperator = (chartDatasets.length === 1);

            // Map operator codes to names
            for (var i = 0; i < chartDatasets.length; i++) {
                var nm = operatorNameMap[chartDatasets[i].label];
                if (nm) { chartDatasets[i].label = chartDatasets[i].label + ' - ' + nm; }
            }

            // ===== ADD TRENDLINE (LINEAR REGRESSION) =====
            if (isSingleOperator) {
                // Single operator: trendline for that operator
                var reg = linearRegression(chartDatasets[0].data);
                if (reg) {
                    var trendData = [];
                    for (var k = 0; k < chartLabels.length; k++) {
                        trendData.push(Math.round((reg.intercept + reg.slope * k) * 10) / 10);
                    }
                    var trendDir = '';
                    if (reg.slope > 0.3) trendDir = ' ↗ Naik';
                    else if (reg.slope < -0.3) trendDir = ' ↘ Turun';
                    else trendDir = ' → Stabil';

                    chartDatasets.push({
                        label: 'Trendline' + trendDir,
                        data: trendData,
                        borderColor: 'rgba(34, 197, 94, 0.7)',
                        backgroundColor: 'transparent',
                        borderWidth: 2.5,
                        borderDash: [8, 5],
                        pointRadius: 0,
                        pointHoverRadius: 0,
                        tension: 0,
                        fill: false,
                        order: 998,
                        _isTrend: true
                    });
                }
            } else {
                // All operators: calculate average KPI per date, then one overall trendline
                var avgData = [];
                for (var d = 0; d < chartLabels.length; d++) {
                    var sum = 0, count = 0;
                    for (var o = 0; o < chartDatasets.length; o++) {
                        var val = chartDatasets[o].data[d];
                        if (val !== null && val !== undefined) {
                            sum += val;
                            count++;
                        }
                    }
                    avgData.push(count > 0 ? sum / count : null);
                }
                var regAll = linearRegression(avgData);
                if (regAll) {
                    var trendAllData = [];
                    for (var k2 = 0; k2 < chartLabels.length; k2++) {
                        trendAllData.push(Math.round((regAll.intercept + regAll.slope * k2) * 10) / 10);
                    }
                    var trendDirAll = '';
                    if (regAll.slope > 0.3) trendDirAll = ' ↗ Naik';
                    else if (regAll.slope < -0.3) trendDirAll = ' ↘ Turun';
                    else trendDirAll = ' → Stabil';

                    chartDatasets.push({
                        label: 'Trend Keseluruhan' + trendDirAll,
                        data: trendAllData,
                        borderColor: 'rgba(34, 197, 94, 0.7)',
                        backgroundColor: 'transparent',
                        borderWidth: 2.5,
                        borderDash: [8, 5],
                        pointRadius: 0,
                        pointHoverRadius: 0,
                        tension: 0,
                        fill: false,
                        order: 998,
                        _isTrend: true
                    });
                }
            }

            // Add 85% target line
            var target85 = [];
            for (var j = 0; j < chartLabels.length; j++) { target85.push(85); }
            chartDatasets.push({
                label: 'Target KPI 85%',
                data: target85,
                borderColor: 'rgba(239, 68, 68, 0.5)',
                backgroundColor: 'transparent',
                borderWidth: 2,
                borderDash: [6, 4],
                pointRadius: 0,
                pointHoverRadius: 0,
                tension: 0,
                fill: false,
                order: 999,
                _isTrend: true
            });

            var ctx = chartCanvas.getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: chartDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 6,
                                padding: 10,
                                font: { size: 10, family: 'Inter' }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            filter: function(tooltipItem) {
                                // Hide target and trendlines from tooltip
                                var ds = tooltipItem.dataset;
                                return !ds._isTrend;
                            },
                            callbacks: {
                                label: function(context) {
                                    var lbl = context.dataset.label || '';
                                    if (context.parsed.y !== null) {
                                        lbl += ': ' + context.parsed.y.toFixed(1) + '%';
                                    }
                                    return lbl;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            suggestedMax: 150,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                font: { size: 10, family: 'Inter' },
                                callback: function(value) { return value + '%'; }
                            },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 10, family: 'Inter' },
                                maxRotation: 45,
                                minRotation: 0
                            },
                            border: { display: false }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        });
    </script>
@endpush
