<?php

namespace App\Http\Controllers;

use App\Models\Anticipo;
use App\Models\DevolucionReembolso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DevolucionReembolsoController extends Controller
{
    /**
     * Listar devoluciones y reembolsos según el rol del usuario
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            // Super admin: ver todas las devoluciones y reembolsos
            $devolucionesReembolsos = DevolucionReembolso::with(['anticipo.usuario', 'anticipo.area', 'banco', 'creador'])
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($user->isAreaAdmin()) {
            // Area admin: ver devoluciones y reembolsos de su empresa
            $devolucionesReembolsos = DevolucionReembolso::with(['anticipo.usuario', 'anticipo.area', 'banco', 'creador'])
                ->whereHas('anticipo', function ($query) use ($user) {
                    $query->where('area_id', $user->area_id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Operador: ver solo sus devoluciones
            $devolucionesReembolsos = DevolucionReembolso::with(['anticipo.usuario', 'anticipo.area', 'banco', 'creador'])
                ->where('tipo', 'devolucion')
                ->whereHas('anticipo', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return view('devoluciones-reembolsos.index', compact('devolucionesReembolsos'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create($anticipoId, $tipo) // tipo: 'devolucion' o 'reembolso'
    {
        $anticipo = Anticipo::with('comprobantes')->findOrFail($anticipoId);
        $user = Auth::user();
        
        // Validar permisos según tipo
        if ($tipo === 'devolucion') {
            // Solo el usuario dueño del anticipo puede crear devolución
            if ($anticipo->user_id !== $user->id) {
                abort(403, 'Solo puedes registrar devoluciones de tus propios anticipos.');
            }
            
            // Validar que hay saldo pendiente a devolver
            $totalComprobado = $anticipo->comprobantes()->where('estado', 'aprobado')->sum('monto');
            $restante = $anticipo->importe - $totalComprobado;
            if ($restante <= 0) {
                abort(403, 'No hay saldo pendiente para devolver.');
            }
        } else {
            // Solo admin puede crear reembolso
            if (!$user->isAdmin() && !$user->isAreaAdmin()) {
                abort(403, 'Solo los administradores pueden generar reembolsos.');
            }
            
            // Validar que hay saldo pendiente a reembolsar
            $totalComprobado = $anticipo->comprobantes()->where('estado', 'aprobado')->sum('monto');
            $restante = $anticipo->importe - $totalComprobado;
            if ($restante >= 0) {
                abort(403, 'No hay saldo pendiente para reembolsar.');
            }
        }
        
        $bancos = \App\Models\Banco::orderBy('descripcion')->get();
        
        return view('devoluciones-reembolsos.create', compact('anticipo', 'tipo', 'bancos'));
    }

    /**
     * Guardar devolución o reembolso
     */
    public function store(Request $request, $anticipoId)
    {
        $anticipo = Anticipo::with('comprobantes')->findOrFail($anticipoId);
        $user = Auth::user();
        $tipo = $request->tipo; // 'devolucion' o 'reembolso'
        
        // Validar permisos
        if ($tipo === 'devolucion' && $anticipo->user_id !== $user->id) {
            abort(403, 'Solo puedes registrar devoluciones de tus propios anticipos.');
        }
        
        if ($tipo === 'reembolso' && !$user->isAdmin() && !$user->isAreaAdmin()) {
            abort(403, 'Solo los administradores pueden generar reembolsos.');
        }
        
        // Validar que no exceda el saldo pendiente
        $totalComprobado = $anticipo->comprobantes()->where('estado', 'aprobado')->sum('monto');
        $restante = $anticipo->importe - $totalComprobado;
        
        // Calcular total ya registrado
        $totalRegistrado = $anticipo->devolucionesReembolsos()
            ->where('tipo', $tipo)
            ->where('estado', 'aprobado')
            ->sum('importe');
        
        $saldoDisponible = $tipo === 'devolucion' ? $restante : abs($restante);
        
        // Validación según método de pago
        $rules = [
            'tipo' => 'required|in:devolucion,reembolso',
            'metodo_pago' => 'required|in:deposito_cuenta,deposito_caja',
            'moneda' => 'required|in:soles,dolares,euros',
            'importe' => 'required|numeric|min:0.01',
        ];
        
        if ($request->metodo_pago === 'deposito_cuenta') {
            $rules['banco_id'] = 'nullable|exists:bancos,id';
            $rules['billetera_digital'] = 'nullable|in:yape,plin';
            $rules['numero_operacion'] = 'required|string|max:255';
            $rules['fecha_deposito'] = 'required|date';
            $rules['archivo'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:40960';
            
            // Validar que tenga banco o billetera
            $request->validate($rules);
            if (!$request->banco_id && !$request->billetera_digital) {
                return redirect()->back()
                    ->with('error', 'Debe seleccionar un banco o una billetera digital.')
                    ->withInput();
            }
        } else {
            $rules['fecha_devolucion'] = 'required|date';
            $rules['observaciones'] = 'nullable|string';
            $request->validate($rules);
        }
        
        // Validar que el importe no exceda el saldo disponible
        if ($request->importe > ($saldoDisponible - $totalRegistrado)) {
            return redirect()->back()
                ->with('error', 'El importe excede el saldo disponible. Saldo disponible: ' . number_format($saldoDisponible - $totalRegistrado, 2))
                ->withInput();
        }
        
        // Guardar archivo si existe
        $archivoPath = null;
        if ($request->hasFile('archivo')) {
            $archivoPath = $request->file('archivo')->store('devoluciones-reembolsos', 'public');
        }
        
        DevolucionReembolso::create([
            'anticipo_id' => $anticipo->id,
            'tipo' => $tipo,
            'metodo_pago' => $request->metodo_pago,
            'banco_id' => $request->banco_id ?? null,
            'billetera_digital' => $request->billetera_digital ?? null,
            'numero_operacion' => $request->numero_operacion ?? null,
            'fecha_deposito' => $request->fecha_deposito ?? null,
            'fecha_devolucion' => $request->fecha_devolucion ?? null,
            'archivo' => $archivoPath,
            'moneda' => $request->moneda,
            'importe' => $request->importe,
            'observaciones' => $request->observaciones ?? null,
            'creado_por' => $user->id,
            'estado' => 'pendiente',
        ]);
        
        $tipoTexto = $tipo === 'devolucion' ? 'Devolución' : 'Reembolso';
        return redirect()->route('anticipos.show', $anticipo->id)
            ->with('success', $tipoTexto . ' registrado correctamente.');
    }

    /**
     * Aprobar devolución/reembolso
     */
    public function aprobar($id)
    {
        $devolucionReembolso = DevolucionReembolso::with('anticipo')->findOrFail($id);
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isAreaAdmin()) {
            abort(403, 'Solo los administradores pueden aprobar.');
        }
        
        $devolucionReembolso->estado = 'aprobado';
        $devolucionReembolso->save();
        
        $tipoTexto = $devolucionReembolso->tipo === 'devolucion' ? 'Devolución' : 'Reembolso';
        return redirect()->back()->with('success', $tipoTexto . ' aprobado correctamente.');
    }

    /**
     * Rechazar devolución/reembolso
     */
    public function rechazar($id)
    {
        $devolucionReembolso = DevolucionReembolso::with('anticipo')->findOrFail($id);
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isAreaAdmin()) {
            abort(403, 'Solo los administradores pueden rechazar.');
        }
        
        $devolucionReembolso->estado = 'rechazado';
        $devolucionReembolso->save();
        
        $tipoTexto = $devolucionReembolso->tipo === 'devolucion' ? 'Devolución' : 'Reembolso';
        return redirect()->back()->with('success', $tipoTexto . ' rechazado correctamente.');
    }
}
