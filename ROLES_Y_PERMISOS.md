# Sistema de Roles y Permisos - CorpSAF Comprobantes

Este documento describe el sistema de roles, permisos y cómo se implementan en el sistema.

---

## 📋 Roles Disponibles

El sistema define los siguientes roles mediante la clase `App\Enums\Role`:

### 1. **ADMIN** (Administrador)
- **Constante**: `Role::ADMIN`
- **Valor en BD**: `'admin'`
- **Etiqueta**: "Administrador"

### 2. **TRABAJADOR** (Trabajador/Operador)
- **Constante**: `Role::TRABAJADOR`
- **Valor en BD**: `'trabajador'`
- **Etiqueta**: "Trabajador"

### 3. **OPERADOR** (Sinónimo de Trabajador)
- **Constante**: `Role::OPERADOR`
- **Valor en BD**: `'operador'`
- **Etiqueta**: "Operador"

---

## 🔐 Permisos por Rol

### 👑 Administrador (`admin`)

#### ✅ Puede:
- Ver **todos** los comprobantes del sistema
- Aprobar comprobantes (cambiar estado a `aprobado`)
- Rechazar comprobantes (cambiar estado a `rechazado`)
- Agregar observaciones a **cualquier** comprobante
- Ver archivos adjuntos de **todos** los comprobantes
- Ver archivos adjuntos de **todas** las observaciones

#### ❌ No puede:
- Crear nuevos comprobantes
- Editar comprobantes (ni propios ni ajenos)
- Eliminar comprobantes

---

### 👷 Trabajador/Operador (`trabajador` / `operador`)

#### ✅ Puede:
- Ver **solo sus propios** comprobantes
- Crear nuevos comprobantes
- Editar **solo sus propios** comprobantes (si están en estado `pendiente`)
- Eliminar **solo sus propios** comprobantes
- Agregar observaciones a **sus propios** comprobantes
- Ver archivos adjuntos de **sus propios** comprobantes
- Ver archivos adjuntos de observaciones de **sus propios** comprobantes
- Ver el estado de sus comprobantes (pendiente, aprobado, rechazado)

#### ❌ No puede:
- Ver comprobantes de otros usuarios
- Aprobar o rechazar comprobantes
- Editar comprobantes de otros usuarios
- Agregar observaciones a comprobantes de otros usuarios

---

## 🛠️ Implementación Técnica

### Clase de Roles

Los roles están definidos en `app/Enums/Role.php`:

```php
use App\Enums\Role;

// Constantes disponibles
Role::ADMIN        // 'admin'
Role::TRABAJADOR   // 'trabajador'
Role::OPERADOR     // 'operador'

// Métodos útiles
Role::all()                    // Obtener todos los roles
Role::isValid($role)           // Verificar si un rol es válido
Role::trabajadores()           // Obtener roles de trabajador
Role::isTrabajador($role)      // Verificar si es trabajador
Role::label($role)             // Obtener etiqueta legible
```

### Modelo User

El modelo `User` incluye métodos helper:

```php
$user->isAdmin()      // bool - Verificar si es administrador
$user->isOperador()   // bool - Verificar si es trabajador/operador
$user->role_label     // string - Etiqueta legible del rol (accessor)
```

### Validación

El modelo `User` valida automáticamente que el rol sea válido al asignarlo:

```php
// ✅ Válido
$user->role = Role::ADMIN;
$user->role = Role::TRABAJADOR;

// ❌ Lanza InvalidArgumentException
$user->role = 'invalid_role';
```

### Middleware de Roles

El middleware `CheckRole` protege rutas según roles:

```php
// En routes/web.php
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Solo administradores
});

Route::middleware(['auth', 'role:admin,trabajador'])->group(function () {
    // Administradores y trabajadores
});
```

---

## 📍 Uso en el Código

### En Controladores

```php
use Illuminate\Support\Facades\Auth;
use App\Enums\Role;

$user = Auth::user();

// Verificar rol
if ($user->isAdmin()) {
    // Lógica para admin
}

if ($user->isOperador()) {
    // Lógica para trabajador/operador
}

// Comparar directamente
if ($user->role === Role::ADMIN) {
    // ...
}
```

### En Vistas (Blade)

```blade
@if(auth()->user()->isAdmin())
    <!-- Contenido solo para admin -->
@endif

@if(auth()->user()->isOperador())
    <!-- Contenido solo para trabajador -->
@endif

<span>{{ auth()->user()->role_label }}</span>
```

### En Validación de Formularios

```php
use App\Enums\Role;
use Illuminate\Validation\Rule;

$request->validate([
    'role' => ['required', 'string', Rule::in(Role::all())],
]);
```

---

## 🔄 Flujo de Permisos

### Crear Comprobante
1. ✅ **Trabajador**: Puede crear
2. ❌ **Admin**: No puede crear (no hay interfaz, pero técnicamente podría)

### Ver Comprobantes
1. ✅ **Admin**: Ve todos
2. ✅ **Trabajador**: Ve solo los suyos

### Editar Comprobante
1. ❌ **Admin**: No puede editar (403 Forbidden)
2. ✅ **Trabajador**: Puede editar solo los suyos

### Aprobar/Rechazar
1. ✅ **Admin**: Puede aprobar/rechazar con mensaje obligatorio
2. ❌ **Trabajador**: No puede (403 Forbidden)

### Agregar Observación
1. ✅ **Admin**: Puede agregar a cualquier comprobante
2. ✅ **Trabajador**: Puede agregar solo a sus comprobantes

### Ver Archivos
1. ✅ **Admin**: Puede ver archivos de todos los comprobantes
2. ✅ **Trabajador**: Puede ver archivos solo de sus comprobantes

---

## 🗄️ Base de Datos

### Tabla `users`

| Campo | Tipo | Valores Permitidos |
|-------|------|-------------------|
| `role` | VARCHAR(255) | `'admin'`, `'trabajador'`, `'operador'` |
| | | DEFAULT: `'trabajador'` |

### Migración

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('role')->default('trabajador');
});
```

**Nota**: Actualmente no hay restricción ENUM a nivel de base de datos, pero la validación se hace a nivel de aplicación mediante la clase `Role`.

---

## 🧪 Usuarios de Prueba

El seeder `UserSeeder` crea los siguientes usuarios:

### Administrador
- **Email/Usuario**: `admin`
- **Contraseña**: `admin`
- **Rol**: `admin`

### Trabajadores
- **Email/Usuario**: DNI (ej: `12345678`)
- **Contraseña**: DNI (ej: `12345678`)
- **Rol**: `trabajador`

Ejemplos:
- `12345678` / `12345678` → Juan Pérez
- `87654321` / `87654321` → María García
- `11223344` / `11223344` → Carlos López
- `44332211` / `44332211` → Ana Martínez

---

## 🔒 Seguridad

### Validaciones Implementadas

1. **Middleware de autenticación**: Todas las rutas de comprobantes requieren autenticación
2. **Middleware de roles**: Rutas específicas pueden requerir roles específicos
3. **Validación en controladores**: Cada método verifica permisos antes de ejecutar acciones
4. **Validación en modelo**: El modelo `User` valida que el rol sea válido
5. **Protección de archivos**: Los archivos solo se sirven si el usuario tiene permisos

### Mejoras Futuras Sugeridas

- [ ] Implementar restricción ENUM en la base de datos
- [ ] Agregar tabla de permisos más granular (si se necesitan más roles)
- [ ] Implementar auditoría de cambios de roles
- [ ] Agregar validación de permisos en nivel de middleware más granular

---

## 📚 Referencias

- **Clase de Roles**: `app/Enums/Role.php`
- **Modelo User**: `app/Models/User.php`
- **Middleware**: `app/Http/Middleware/CheckRole.php`
- **Seeder**: `database/seeders/UserSeeder.php`
- **Migración**: `database/migrations/2025_11_23_183436_add_role_to_users_table.php`

---

*Última actualización: Noviembre 2025*

