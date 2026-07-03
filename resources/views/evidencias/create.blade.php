@extends('layouts.app')

@section('title', 'Registrar evidencia - Reporte Ciudadano')

@section('content')
    <section class="page narrow">
        <p class="kicker">Cierre operativo</p>
        <h1>Registrar evidencia</h1>
        <p>{{ $reporte->titulo }}</p>

        <form method="POST" action="{{ route('reportes.evidencias.store', $reporte) }}" class="form" enctype="multipart/form-data">
            @csrf
            <label>
                Comentario de resolucion
                <textarea name="comentario_resolucion" rows="5" required>{{ old('comentario_resolucion') }}</textarea>
            </label>
            <label>
                Archivo
                <input type="file" name="archivo" accept="image/jpeg,image/png,image/webp,application/pdf" required>
            </label>
            <button class="button primary" type="submit">Registrar y resolver</button>
        </form>
    </section>
@endsection
