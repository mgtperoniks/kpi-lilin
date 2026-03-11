@extends('layouts.app')

@section('content')

    <h4>KPI Lilin — Shift {{ $shift }} ({{ $date }})</h4>

    <div class="row mt-3">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted">Target</div>
                    <h3>{{ $result['target'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted">Actual</div>
                    <h3>{{ $result['actual'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted">KPI</div>
                    <h3>{{ $result['kpi'] }}%</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- TRANSPARANSI PERHITUNGAN --}}
    <small class="text-muted">
        Jam efektif sudah dikurangi downtime.
        Perhitungan KPI dilakukan otomatis oleh sistem.
    </small>

@endsection