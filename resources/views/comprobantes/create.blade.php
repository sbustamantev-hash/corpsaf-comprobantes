@extends('layouts.app')

@section('title', 'Registrar Comprobante')

@section('content')
<h2 class="mb-4">Registrar nuevo comprobante</h2>

<form action="{{ route('comprobantes.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
        <label class="form-label">Tipo de comprobante</label>
        <input type="text" name="tipo" class="form-control @error('tipo') is-invalid @enderror" value="{{ old('tipo') }}" required>
        @error('tipo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Monto</label>
        <input type="number" step="0.01" name="monto" class="form-control @error('monto') is-invalid @enderror" value="{{ old('monto') }}" required>
        @error('monto')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Fecha</label>
        <input type="date" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha') }}" required>
        @error('fecha')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Detalle</label>
        <textarea name="detalle" class="form-control @error('detalle') is-invalid @enderror">{{ old('detalle') }}</textarea>
        @error('detalle')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Archivo (imagen o PDF)</label>
        <input type="file" name="archivo" class="form-control @error('archivo') is-invalid @enderror">
        @error('archivo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
</form>
@endsection
