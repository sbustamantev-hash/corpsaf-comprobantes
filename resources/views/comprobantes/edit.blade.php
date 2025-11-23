@extends('layouts.app')

@section('title', 'Editar Comprobante')

@section('content')

<h2 class="mb-4">Editar comprobante</h2>

<form action="{{ route('comprobantes.update', $comprobante->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Nombre --}}
    <div class="mb-3">
        <label class="form-label">Nombre del trabajador</label>
        <input type="text" 
               name="nombre" 
               class="form-control @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $comprobante->nombre) }}"
               required>

        @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Fecha --}}
    <div class="mb-3">
        <label class="form-label">Fecha del comprobante</label>
        <input type="date" 
               name="fecha" 
               class="form-control @error('fecha') is-invalid @enderror"
               value="{{ old('fecha', $comprobante->fecha) }}"
               required>

        @error('fecha')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Monto --}}
    <div class="mb-3">
        <label class="form-label">Monto (S/.)</label>
        <input type="number" 
               name="monto" 
               step="0.01"
               class="form-control @error('monto') is-invalid @enderror"
               value="{{ old('monto', $comprobante->monto) }}"
               required>

        @error('monto')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Mensaje --}}
    <div class="mb-3">
        <label class="form-label">Mensaje (opcional)</label>
        <textarea name="mensaje" 
                  class="form-control @error('mensaje') is-invalid @enderror">{{ old('mensaje', $comprobante->mensaje) }}</textarea>

        @error('mensaje')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Imagen --}}
    <div class="mb-3">
        <label class="form-label">Imagen actual</label><br>
        @if($comprobante->imagen)
            <img src="{{ asset('storage/'.$comprobante->imagen) }}" style="width: 120px; border-radius: 8px;">
        @else
            <span class="text-muted">Sin imagen</span>
        @endif
    </div>

    <div class="mb-3">
        <label class="form-label">Cambiar imagen (opcional)</label>
        <input type="file" 
               name="imagen" 
               class="form-control @error('imagen') is-invalid @enderror">

        @error('imagen')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">Actualizar Comprobante</button>
    <a href="{{ route('comprobantes.index') }}" class="btn btn-secondary">Cancelar</a>
</form>

@endsection
