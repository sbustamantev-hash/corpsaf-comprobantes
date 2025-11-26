# EstTipoRendiciontura de Base de Datos - CorpSAF Comprobantes

Este documento describe la estTipoRendiciontura completa de las tablas de la base de datos del sistema.

---

## 📊 Tablas Principales

### 1. `users` - Usuarios del Sistema

| Campo | Tipo | Descripción | Restricciones |
|-------|------|-------------|---------------|
| `id` | BIGINT UNSIGNED | Identificador único | PRIMARY KEY, AUTO_INCREMENT |
| `name` | VARCHAR(255) | Nombre completo del usuario | NOT NULL |
| `email` | VARCHAR(255) | Correo electrónico | UNIQUE, NOT NULL |
| `email_verified_at` | TIMESTAMP | Fecha de verificación de email | NULLABLE |
| `password` | VARCHAR(255) | Contraseña encriptada | NOT NULL |
| `role` | VARCHAR(255) | Rol del usuario | DEFAULT: 'trabajador' |
| `remember_token` | VARCHAR(100) | Token para "Recordarme" | NULLABLE |
| `created_at` | TIMESTAMP | Fecha de creación | NULLABLE |
| `updated_at` | TIMESTAMP | Fecha de actualización | NULLABLE |

**Valores posibles para `role`:**
- `admin` - Administrador del sistema
- `trabajador` - Trabajador/Operador
- `operador` - Operador (sinónimo de trabajador)

**Relaciones:**
- `hasMany` → `comprobantes` (un usuario puede tener muchos comprobantes)

---

### 2. `comprobantes` - Comprobantes de Pago

| Campo | Tipo | Descripción | Restricciones |
|-------|------|-------------|---------------|
| `id` | BIGINT UNSIGNED | Identificador único | PRIMARY KEY, AUTO_INCREMENT |
| `user_id` | BIGINT UNSIGNED | ID del usuario que creó el comprobante | FOREIGN KEY → users.id, ON DELETE CASCADE |
| `tipo` | VARCHAR(255) | Tipo de comprobante | NOT NULL |
| `monto` | DECIMAL(10,2) | Monto del comprobante | NOT NULL |
| `fecha` | DATE | Fecha del comprobante | NOT NULL |
| `detalle` | TEXT | Descripción adicional | NULLABLE |
| `archivo` | VARCHAR(255) | Ruta del archivo (imagen o PDF) | NULLABLE |
| `estado` | VARCHAR(255) | Estado del comprobante | DEFAULT: 'pendiente' |
| `created_at` | TIMESTAMP | Fecha de creación | NULLABLE |
| `updated_at` | TIMESTAMP | Fecha de actualización | NULLABLE |

**Valores posibles para `estado`:**
- `pendiente` - Esperando revisión del administrador
- `aprobado` - Aprobado por el administrador
- `rechazado` - Rechazado por el administrador

**Valores comunes para `tipo`:**
- Boleta
- Recibo
- Vale
- Factura
- Otros tipos personalizados

**Relaciones:**
- `belongsTo` → `users` (cada comprobante pertenece a un usuario)
- `hasMany` → `observaciones` (un comprobante puede tener muchas observaciones)

**Almacenamiento de archivos:**
- Los archivos se guardan en: `storage/app/public/comprobantes/`
- La ruta se almacena en el campo `archivo` (ej: `comprobantes/abc123.jpg`)

---

### 3. `observaciones` - Observaciones y Conversaciones

| Campo | Tipo | Descripción | Restricciones |
|-------|------|-------------|---------------|
| `id` | BIGINT UNSIGNED | Identificador único | PRIMARY KEY, AUTO_INCREMENT |
| `comprobante_id` | BIGINT UNSIGNED | ID del comprobante relacionado | FOREIGN KEY → comprobantes.id, ON DELETE CASCADE |
| `user_id` | BIGINT UNSIGNED | ID del usuario que creó la observación | FOREIGN KEY → users.id, ON DELETE CASCADE |
| `mensaje` | TEXT | Contenido del mensaje/observación | NOT NULL |
| `tipo` | VARCHAR(255) | Tipo de observación | DEFAULT: 'observacion' |
| `archivo` | VARCHAR(255) | Ruta del archivo adjunto (imagen o PDF) | NULLABLE |
| `created_at` | TIMESTAMP | Fecha de creación | NULLABLE |
| `updated_at` | TIMESTAMP | Fecha de actualización | NULLABLE |

**Valores posibles para `tipo`:**
- `observacion` - Observación o comentario general
- `aprobacion` - Mensaje de aprobación del administrador
- `rechazo` - Mensaje de rechazo del administrador

**Relaciones:**
- `belongsTo` → `comprobantes` (cada observación pertenece a un comprobante)
- `belongsTo` → `users` (cada observación fue creada por un usuario)

**Almacenamiento de archivos:**
- Los archivos se guardan en: `storage/app/public/observaciones/`
- La ruta se almacena en el campo `archivo` (ej: `observaciones/xyz789.pdf`)

---

## 🔗 Relaciones entre Tablas

```
users (1) ──────< (N) comprobantes
                    │
                    │ (1)
                    │
                    └───< (N) observaciones
                            │
                            │ (N)
                            │
users (1) ──────────────────┘
```

**Diagrama de relaciones:**
- Un **Usuario** puede tener muchos **Comprobantes**
- Un **Comprobante** pertenece a un **Usuario**
- Un **Comprobante** puede tener muchas **Observaciones**
- Una **Observación** pertenece a un **Comprobante**
- Una **Observación** pertenece a un **Usuario** (quien la creó)

---

## 📋 Tablas del Sistema (Laravel)

### 4. `password_reset_tokens` - Tokens de Restablecimiento de Contraseña

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `email` | VARCHAR(255) | Email del usuario (PRIMARY KEY) |
| `token` | VARCHAR(255) | Token de restablecimiento |
| `created_at` | TIMESTAMP | Fecha de creación |

---

### 5. `sessions` - Sesiones de Usuario

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | VARCHAR(255) | ID de la sesión (PRIMARY KEY) |
| `user_id` | BIGINT UNSIGNED | ID del usuario (NULLABLE, INDEX) |
| `ip_address` | VARCHAR(45) | Dirección IP |
| `user_agent` | TEXT | User Agent del navegador |
| `payload` | LONGTEXT | Datos de la sesión |
| `last_activity` | INTEGER | Timestamp de última actividad (INDEX) |

---

### 6. `cache` - Caché del Sistema

Tabla estándar de Laravel para almacenar datos en caché.

---

### 7. `cache_locks` - Bloqueos de Caché

Tabla estándar de Laravel para gestionar bloqueos de caché.

---

### 8. `jobs` - Cola de Trabajos

Tabla estándar de Laravel para gestionar trabajos en cola.

---

### 9. `job_batches` - Lotes de Trabajos

Tabla estándar de Laravel para gestionar lotes de trabajos.

---

### 10. `failed_jobs` - Trabajos Fallidos

Tabla estándar de Laravel para registrar trabajos que fallaron.

---

## 🔍 Índices y Claves Foráneas

### Claves Foráneas:

1. **`comprobantes.user_id`** → `users.id`
   - ON DELETE: CASCADE (si se elimina un usuario, se eliminan sus comprobantes)

2. **`observaciones.comprobante_id`** → `comprobantes.id`
   - ON DELETE: CASCADE (si se elimina un comprobante, se eliminan sus observaciones)

3. **`observaciones.user_id`** → `users.id`
   - ON DELETE: CASCADE (si se elimina un usuario, se eliminan sus observaciones)

### Índices:

- `users.email` - UNIQUE (búsqueda rápida por email)
- `sessions.user_id` - INDEX (búsqueda de sesiones por usuario)
- `sessions.last_activity` - INDEX (limpieza de sesiones expiradas)

---

## 📝 Notas Importantes

1. **Eliminación en Cascada:**
   - Si se elimina un usuario, se eliminan automáticamente todos sus comprobantes y observaciones.
   - Si se elimina un comprobante, se eliminan automáticamente todas sus observaciones.

2. **Almacenamiento de Archivos:**
   - Los archivos se almacenan en el sistema de archivos, no en la base de datos.
   - Las rutas se guardan en los campos `archivo` de las tablas `comprobantes` y `observaciones`.
   - Los archivos se sirven a través de enlaces simbólicos (`public/storage` → `storage/app/public`).

3. **Estados de Comprobantes:**
   - Los comprobantes inician en estado `pendiente`.
   - Solo los administradores pueden cambiar el estado a `aprobado` o `rechazado`.
   - Cada cambio de estado genera una observación automática.

4. **Roles de Usuario:**
   - Los administradores pueden ver y gestionar todos los comprobantes.
   - Los trabajadores/operadores solo pueden ver y gestionar sus propios comprobantes.

---

## 🗄️ Ejemplo de Consultas Útiles

### Obtener todos los comprobantes con sus usuarios:
```sql
SELECT c.*, u.name, u.email 
FROM comprobantes c 
INNER JOIN users u ON c.user_id = u.id;
```

### Obtener comprobantes pendientes:
```sql
SELECT * FROM comprobantes WHERE estado = 'pendiente';
```

### Obtener observaciones de un comprobante con información del usuario:
```sql
SELECT o.*, u.name as usuario_nombre 
FROM observaciones o 
INNER JOIN users u ON o.user_id = u.id 
WHERE o.comprobante_id = ?;
```

### Contar comprobantes por estado:
```sql
SELECT estado, COUNT(*) as total 
FROM comprobantes 
GROUP BY estado;
```

---

## 📦 Resumen de Tablas

| Tabla | Registros Típicos | Propósito |
|-------|------------------|-----------|
| `users` | 10-1000+ | Usuarios del sistema |
| `comprobantes` | 100-10000+ | Comprobantes de pago |
| `observaciones` | 200-20000+ | Mensajes y conversaciones |
| `sessions` | Variable | Sesiones activas |
| `password_reset_tokens` | Temporal | Recuperación de contraseñas |
| `cache` | Variable | Datos en caché |
| `jobs` | Variable | Cola de trabajos |

---

*Última actualización: Noviembre 2025*

