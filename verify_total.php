<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Anticipo;
use App\Models\Comprobante;
use App\Models\User;
use App\Models\Area;
use App\Models\TipoRendicion;
use App\Models\Banco;
use Illuminate\Support\Facades\DB;

// Create dummy data
$area = Area::first() ?? Area::create(['nombre' => 'Test Area', 'activo' => true]);
$user = User::first() ?? User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    'dni' => '12345678',
    'area_id' => $area->id,
    'role' => 'operador'
]);
$banco = Banco::first() ?? Banco::create(['descripcion' => 'Test Banco', 'activo' => true]);
$tipoRendicion = TipoRendicion::first() ?? TipoRendicion::create(['descripcion' => 'Viaticos', 'activo' => true]);

// Create Anticipo
$anticipo = Anticipo::create([
    'area_id' => $area->id,
    'user_id' => $user->id,
    'creado_por' => $user->id,
    'tipo' => 'anticipo',
    'fecha' => now(),
    'banco_id' => $banco->id,
    'tipo_rendicion_id' => $tipoRendicion->id,
    'importe' => 1000,
    'descripcion' => 'Test Anticipo',
    'estado' => 'pendiente'
]);

echo "Anticipo created with ID: " . $anticipo->id . "\n";

// Create Comprobante (Pendiente)
$comprobante = Comprobante::create([
    'user_id' => $user->id,
    'anticipo_id' => $anticipo->id,
    'tipo' => '01', // Assuming 01 exists
    'concepto_id' => 1, // Assuming 1 exists
    'serie' => 'F001',
    'numero' => '00000001',
    'ruc_empresa' => '20123456789',
    'monto' => 100,
    'fecha' => now(),
    'detalle' => 'Test Comprobante',
    'archivo' => 'test.pdf',
    'estado' => 'pendiente'
]);

echo "Comprobante created with ID: " . $comprobante->id . " and status: " . $comprobante->estado . "\n";

// Check Total (Should be 0)
$total = $anticipo->comprobantes()->where('estado', 'aprobado')->sum('monto');
echo "Total (Pendiente): " . $total . " (Expected: 0)\n";

// Approve Comprobante
$comprobante->estado = 'aprobado';
$comprobante->save();
echo "Comprobante status changed to: " . $comprobante->estado . "\n";

// Check Total (Should be 100)
$total = $anticipo->comprobantes()->where('estado', 'aprobado')->sum('monto');
echo "Total (Aprobado): " . $total . " (Expected: 100)\n";

// Reject Comprobante
$comprobante->estado = 'rechazado';
$comprobante->save();
echo "Comprobante status changed to: " . $comprobante->estado . "\n";

// Check Total (Should be 0)
$total = $anticipo->comprobantes()->where('estado', 'aprobado')->sum('monto');
echo "Total (Rechazado): " . $total . " (Expected: 0)\n";

// Clean up
$comprobante->delete();
$anticipo->delete();
