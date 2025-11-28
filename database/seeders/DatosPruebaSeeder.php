<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;
use App\Models\User;
use App\Models\Anticipo;
use App\Models\Comprobante;
use App\Models\Observacion;
use App\Models\Banco;
use App\Models\TipoRendicion;
use App\Models\TipoComprobante;
use App\Models\Concepto;
use Carbon\Carbon;

class DatosPruebaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener datos necesarios
        $areaVentas = Area::where('codigo', '20123456789')->first();
        $areaProduccion = Area::where('codigo', '20123456790')->first();
        $areaLogistica = Area::where('codigo', '20123456791')->first();

        $banco = Banco::first();
        $tipoRendicion = TipoRendicion::first();
        $tipoComprobante = TipoComprobante::first();
        $concepto = Concepto::first();

        if (!$banco || !$tipoRendicion || !$tipoComprobante || !$concepto) {
            $this->command->warn('Faltan datos básicos. Ejecuta primero los seeders de Bancos, TipoRendicion, TipoComprobante y Concepto.');
            return;
        }

        // Obtener usuarios operadores
        $operadores = User::where('role', 'OPERADOR')->get();
        $admins = User::where('role', 'AREA_ADMIN')->get();

        if ($operadores->isEmpty() || $admins->isEmpty()) {
            $this->command->warn('No hay usuarios operadores o administradores. Ejecuta primero el UserSeeder.');
            return;
        }

        $adminVentas = $admins->where('area_id', $areaVentas?->id)->first();
        $adminProduccion = $admins->where('area_id', $areaProduccion?->id)->first();

        // Crear anticipos y reembolsos para diferentes usuarios
        foreach ($operadores as $index => $operador) {
            $area = $operador->area;
            $admin = $admins->where('area_id', $area->id)->first() ?? $admins->first();

            if (!$area || !$admin) {
                continue;
            }

            // ANTICIPO 1: Pendiente con comprobantes pendientes
            $anticipo1 = Anticipo::create([
                'area_id' => $area->id,
                'user_id' => $operador->id,
                'creado_por' => $admin->id,
                'tipo' => 'anticipo',
                'fecha' => Carbon::now()->subDays(10),
                'banco_id' => $banco->id,
                'tipo_rendicion_id' => $tipoRendicion->id,
                'importe' => 500.00,
                'descripcion' => 'Anticipo para gastos de viaje y viáticos',
                'estado' => 'pendiente',
            ]);

            // Comprobantes para anticipo1
            $comprobante1_1 = Comprobante::create([
                'user_id' => $operador->id,
                'anticipo_id' => $anticipo1->id,
                'tipo' => $tipoComprobante->codigo,
                'concepto_id' => $concepto->id,
                'serie' => 'F001',
                'numero' => '0000000123',
                'monto' => 200.00,
                'fecha' => Carbon::now()->subDays(8),
                'detalle' => 'Combustible para viaje a provincia',
                'archivo' => null,
            ]);

            $comprobante1_2 = Comprobante::create([
                'user_id' => $operador->id,
                'anticipo_id' => $anticipo1->id,
                'tipo' => $tipoComprobante->codigo,
                'concepto_id' => $concepto->id,
                'serie' => 'F001',
                'numero' => '0000000124',
                'monto' => 150.00,
                'fecha' => Carbon::now()->subDays(7),
                'detalle' => 'Almuerzo durante viaje',
                'archivo' => null,
            ]);

            // ANTICIPO 2: Con comprobantes aprobados y pendientes
            $anticipo2 = Anticipo::create([
                'area_id' => $area->id,
                'user_id' => $operador->id,
                'creado_por' => $admin->id,
                'tipo' => 'anticipo',
                'fecha' => Carbon::now()->subDays(15),
                'banco_id' => $banco->id,
                'tipo_rendicion_id' => $tipoRendicion->id,
                'importe' => 1000.00,
                'descripcion' => 'Anticipo para compra de materiales',
                'estado' => 'pendiente',
            ]);

            // Comprobantes para anticipo2 (algunos aprobados, algunos pendientes)
            $comprobante2_1 = Comprobante::create([
                'user_id' => $operador->id,
                'anticipo_id' => $anticipo2->id,
                'tipo' => $tipoComprobante->codigo,
                'concepto_id' => $concepto->id,
                'serie' => 'B001',
                'numero' => '0000000456',
                'monto' => 500.00,
                'fecha' => Carbon::now()->subDays(12),
                'detalle' => 'Materiales de oficina',
                'archivo' => null,
            ]);

            // Crear observación de aprobación
            Observacion::create([
                'comprobante_id' => $comprobante2_1->id,
                'user_id' => $admin->id,
                'mensaje' => 'Comprobante aprobado correctamente',
                'tipo' => 'aprobacion',
            ]);

            $comprobante2_2 = Comprobante::create([
                'user_id' => $operador->id,
                'anticipo_id' => $anticipo2->id,
                'tipo' => $tipoComprobante->codigo,
                'concepto_id' => $concepto->id,
                'serie' => 'B001',
                'numero' => '0000000457',
                'monto' => 300.00,
                'fecha' => Carbon::now()->subDays(10),
                'detalle' => 'Herramientas y equipos',
                'archivo' => null,
            ]);

            // ANTICIPO 3: Con comprobante en observación
            $anticipo3 = Anticipo::create([
                'area_id' => $area->id,
                'user_id' => $operador->id,
                'creado_por' => $admin->id,
                'tipo' => 'anticipo',
                'fecha' => Carbon::now()->subDays(20),
                'banco_id' => $banco->id,
                'tipo_rendicion_id' => $tipoRendicion->id,
                'importe' => 800.00,
                'descripcion' => 'Anticipo para servicios profesionales',
                'estado' => 'en_observacion',
            ]);

            $comprobante3_1 = Comprobante::create([
                'user_id' => $operador->id,
                'anticipo_id' => $anticipo3->id,
                'tipo' => $tipoComprobante->codigo,
                'concepto_id' => $concepto->id,
                'serie' => 'F002',
                'numero' => '0000000789',
                'monto' => 600.00,
                'fecha' => Carbon::now()->subDays(18),
                'detalle' => 'Servicios de consultoría',
                'archivo' => null,
            ]);

            // Crear observación
            Observacion::create([
                'comprobante_id' => $comprobante3_1->id,
                'user_id' => $admin->id,
                'mensaje' => 'Favor revisar el detalle del servicio, falta información adicional',
                'tipo' => 'observacion',
            ]);

            // ANTICIPO 4: Todos los comprobantes aprobados
            $anticipo4 = Anticipo::create([
                'area_id' => $area->id,
                'user_id' => $operador->id,
                'creado_por' => $admin->id,
                'tipo' => 'anticipo',
                'fecha' => Carbon::now()->subDays(25),
                'banco_id' => $banco->id,
                'tipo_rendicion_id' => $tipoRendicion->id,
                'importe' => 600.00,
                'descripcion' => 'Anticipo para gastos varios',
                'estado' => 'aprobado',
            ]);

            $comprobante4_1 = Comprobante::create([
                'user_id' => $operador->id,
                'anticipo_id' => $anticipo4->id,
                'tipo' => $tipoComprobante->codigo,
                'concepto_id' => $concepto->id,
                'serie' => 'F003',
                'numero' => '0000000100',
                'monto' => 300.00,
                'fecha' => Carbon::now()->subDays(23),
                'detalle' => 'Gastos de transporte',
                'archivo' => null,
            ]);

            Observacion::create([
                'comprobante_id' => $comprobante4_1->id,
                'user_id' => $admin->id,
                'mensaje' => 'Aprobado',
                'tipo' => 'aprobacion',
            ]);

            $comprobante4_2 = Comprobante::create([
                'user_id' => $operador->id,
                'anticipo_id' => $anticipo4->id,
                'tipo' => $tipoComprobante->codigo,
                'concepto_id' => $concepto->id,
                'serie' => 'F003',
                'numero' => '0000000101',
                'monto' => 300.00,
                'fecha' => Carbon::now()->subDays(22),
                'detalle' => 'Gastos de alimentación',
                'archivo' => null,
            ]);

            Observacion::create([
                'comprobante_id' => $comprobante4_2->id,
                'user_id' => $admin->id,
                'mensaje' => 'Aprobado',
                'tipo' => 'aprobacion',
            ]);

            // REEMBOLSO 1: Sin importe (se calcula de los comprobantes)
            $reembolso1 = Anticipo::create([
                'area_id' => $area->id,
                'user_id' => $operador->id,
                'creado_por' => $admin->id,
                'tipo' => 'reembolso',
                'fecha' => Carbon::now()->subDays(5),
                'banco_id' => null,
                'tipo_rendicion_id' => null,
                'importe' => null,
                'descripcion' => 'Reembolso de gastos personales',
                'estado' => 'pendiente',
            ]);

            $comprobanteR1_1 = Comprobante::create([
                'user_id' => $operador->id,
                'anticipo_id' => $reembolso1->id,
                'tipo' => $tipoComprobante->codigo,
                'concepto_id' => $concepto->id,
                'serie' => 'B002',
                'numero' => '0000000200',
                'monto' => 450.00,
                'fecha' => Carbon::now()->subDays(3),
                'detalle' => 'Gastos médicos',
                'archivo' => null,
            ]);

            $comprobanteR1_2 = Comprobante::create([
                'user_id' => $operador->id,
                'anticipo_id' => $reembolso1->id,
                'tipo' => $tipoComprobante->codigo,
                'concepto_id' => $concepto->id,
                'serie' => 'B002',
                'numero' => '0000000201',
                'monto' => 250.00,
                'fecha' => Carbon::now()->subDays(2),
                'detalle' => 'Gastos de estacionamiento',
                'archivo' => null,
            ]);

            // REEMBOLSO 2: Rechazado
            $reembolso2 = Anticipo::create([
                'area_id' => $area->id,
                'user_id' => $operador->id,
                'creado_por' => $admin->id,
                'tipo' => 'reembolso',
                'fecha' => Carbon::now()->subDays(30),
                'banco_id' => null,
                'tipo_rendicion_id' => null,
                'importe' => null,
                'descripcion' => 'Reembolso de gastos de representación',
                'estado' => 'rechazado',
            ]);

            $comprobanteR2_1 = Comprobante::create([
                'user_id' => $operador->id,
                'anticipo_id' => $reembolso2->id,
                'tipo' => $tipoComprobante->codigo,
                'concepto_id' => $concepto->id,
                'serie' => 'F004',
                'numero' => '0000000300',
                'monto' => 1200.00,
                'fecha' => Carbon::now()->subDays(28),
                'detalle' => 'Cena de negocios',
                'archivo' => null,
            ]);

            Observacion::create([
                'comprobante_id' => $comprobanteR2_1->id,
                'user_id' => $admin->id,
                'mensaje' => 'El monto excede el límite permitido para este tipo de gasto',
                'tipo' => 'rechazo',
            ]);

            // Limitar a 2 usuarios para no generar demasiados datos
            if ($index >= 1) {
                break;
            }
        }

        $this->command->info('Datos de prueba creados exitosamente!');
        $this->command->info('- Anticipos con diferentes estados (pendiente, aprobado, rechazado, en_observacion)');
        $this->command->info('- Reembolsos con comprobantes');
        $this->command->info('- Comprobantes asociados a anticipos');
        $this->command->info('- Observaciones asociadas');
    }
}

