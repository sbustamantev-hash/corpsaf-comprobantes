@extends('layouts.app')

@section('title', 'Listado de Comprobantes')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Comprobantes Registrados</h2>
    <a href="{{ route('comprobantes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nuevo Comprobante
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($comprobantes->isEmpty())
    <div class="alert alert-info text-center">
        No hay comprobantes registrados aún.
    </div>
@else
    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Monto</th>
                <th>Mensaje</th>
                <th>Comprobante</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>

            @foreach($comprobantes as $comprobante)
                <tr>
                    <td>{{ $comprobante->id }}</td>
                    <td>{{ $comprobante->nombre }}</td>
                    <td>{{ $comprobante->fecha }}</td>
                    <td>S/ {{ number_format($comprobante->monto, 2) }}</td>
                    <td>{{ $comprobante->mensaje ?? '-' }}</td>

                    <td>
                        @if($comprobante->imagen)
                            <img src="{{ asset('storage/' . $comprobante->imagen) }}" 
                                 class="thumbnail" 
                                 alt="Comprobante"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#imagenModal{{ $comprobante->id }}">
                             
                            <!-- Modal de imagen -->
                            <div class="modal fade" id="imagenModal{{ $comprobante->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Comprobante de {{ $comprobante->nombre }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="{{ asset('storage/' . $comprobante->imagen) }}" 
                                                 alt="Comprobante" 
                                                 class="img-fluid rounded">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @else
                            <span class="text-muted">Sin imagen</span>
                        @endif
                    </td>

                    <td class="d-flex gap-1">
                        <a href="{{ route('comprobantes.show', $comprobante->id) }}" 
                           class="btn btn-info btn-sm">
                           <i class="fas fa-eye"></i>
                        </a>

                        <a href="{{ route('comprobantes.edit', $comprobante->id) }}" 
                           class="btn btn-warning btn-sm">
                           <i class="fas fa-edit"></i>
                        </a>

                        <form action="{{ route('comprobantes.destroy', $comprobante->id) }}" 
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('¿Seguro que deseas eliminar este comprobante?')">
                            
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>

                </tr>
            @endforeach

        </tbody>
    </table>
@endif

@endsection
