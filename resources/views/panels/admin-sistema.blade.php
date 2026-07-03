@extends('layouts.app')

@section('title', 'Admin sistema - Reporte Ciudadano')

@section('content')
    <section class="page">
        <div class="page-head">
            <div>
                <p class="kicker">Administrador del sistema</p>
                <h1>Usuarios y roles</h1>
            </div>
        </div>

        <div class="stats">
            <article><strong>{{ $totales['usuarios'] }}</strong><span>Usuarios</span></article>
            <article><strong>{{ $totales['ciudadanos'] }}</strong><span>Ciudadanos</span></article>
            <article><strong>{{ $totales['operadores'] }}</strong><span>Operadores</span></article>
            <article><strong>{{ $totales['admins'] }}</strong><span>Administradores</span></article>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->name }}</td>
                            <td>{{ $usuario->email }}</td>
                            <td colspan="2">
                                <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="row-form user-row-form">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role">
                                        @foreach (['ciudadano', 'admin_municipal', 'operador', 'admin_sistema'] as $role)
                                            <option value="{{ $role }}" @selected($usuario->role === $role)>{{ $role }}</option>
                                        @endforeach
                                    </select>
                                    <select name="status">
                                        @foreach (['activo', 'inactivo', 'suspendido', 'eliminado'] as $status)
                                            <option value="{{ $status }}" @selected($usuario->status === $status)>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button class="action-pill action-assign" type="submit">
                                        <i data-lucide="save"></i>Guardar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
