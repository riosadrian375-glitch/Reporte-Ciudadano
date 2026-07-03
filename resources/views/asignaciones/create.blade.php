@extends('layouts.app')

@section('title', 'Asignar reporte - Reporte Ciudadano')

@section('content')
    <section class="page narrow">
        <p class="kicker">{{ $reporte->categoria->nombre }} · {{ $reporte->distrito->nombre }}</p>
        <h1>Asignar reporte</h1>
        <p class="lead small-lead">{{ $reporte->titulo }}</p>

        <form method="POST" action="{{ route('reportes.asignar.store', $reporte) }}" class="form report-form">
            @csrf
            <label>
                Operador municipal
                <select name="operador_id" required>
                    <option value="">Selecciona operador</option>
                    @foreach ($operadores as $operador)
                        <option value="{{ $operador->id }}">{{ $operador->name }} - {{ $operador->email }}</option>
                    @endforeach
                </select>
            </label>
            <button class="button primary submit-wide" type="submit">
                <i data-lucide="user-plus"></i>Asignar operador
            </button>
        </form>
    </section>
@endsection
