<?php

namespace App\Http\Controllers;

use App\Models\Mensaje;
use App\Models\Requerimiento;
use App\Models\ArchivoRequerimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MensajeController extends Controller
{
    public function store(Request $request, Requerimiento $requerimiento)
    {
        $user = Auth::user();

        // Validar acceso
        if (!$user->isMarketingAdmin() && $user->area_id !== $requerimiento->area_id) {
            abort(403);
        }

        $request->validate([
            'mensaje' => 'nullable|string',
            'archivo' => 'nullable|file|max:10240', // 10MB max
        ]);

        if (!$request->mensaje && !$request->hasFile('archivo')) {
            return back()->withErrors(['mensaje' => 'Debe enviar un mensaje o un archivo.']);
        }

        $tipo = 'texto';
        if ($request->hasFile('archivo')) {
            $mime = $request->file('archivo')->getMimeType();
            if (str_contains($mime, 'image'))
                $tipo = 'imagen';
            elseif (str_contains($mime, 'video'))
                $tipo = 'video';
            elseif (str_contains($mime, 'pdf'))
                $tipo = 'pdf';
            else
                $tipo = 'archivo';
        }

        $mensaje = Mensaje::create([
            'requerimiento_id' => $requerimiento->id,
            'user_id' => $user->id,
            'mensaje' => $request->mensaje,
            'tipo' => $tipo,
        ]);

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $path = $file->store('requerimientos/' . $requerimiento->id, 'public');

            ArchivoRequerimiento::create([
                'mensaje_id' => $mensaje->id,
                'requerimiento_id' => $requerimiento->id,
                'nombre_original' => $file->getClientOriginalName(),
                'ruta' => $path,
                'tipo_mime' => $file->getMimeType(),
                'tamano' => $file->getSize(),
                'uploaded_by' => $user->id,
            ]);
        }

        return back();
    }
}
