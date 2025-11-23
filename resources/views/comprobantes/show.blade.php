@extends('layouts.app')

@section('title', 'Detalles del Comprobante')

@section('content')

<h2 class="mb-4">Detalles del Comprobante</h2>

<div class="card">
    <div class="card-body">
        <p><strong>ID:</strong> {{ $comprobante->id }}</p>
        <p><strong>Nombre:</strong> {{ $comprobante->nombre }}</p>
        <p><strong>Fecha:</strong> {{ $comprobante->fecha }}</p>
        <p><strong>Monto:</strong> S/ {{ number_format($comprobante->monto, 2) }}</p>
        <p><strong>Mensaje:</strong> {{ $comprobante->mensaje ?? '-' }}</p>
        <p><strong>Imagen:</strong></p>
        @if($comprobante->imagen)
            <img src="{{ asset('storage/'.$comprobante->imagen) }}" style="width: 200px; border-radius: 8px;">
        @else
            <span class="text-muted">Sin imagen</span>
        @endif
    </div>
</div>

<a href="{{ route('comprobantes.index') }}" class="btn btn-secondary mt-3">Volver</a>

@endsection
