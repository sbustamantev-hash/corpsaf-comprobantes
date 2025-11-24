# CorpSAF - Sistema de Gestión de Comprobantes

Sistema web desarrollado con Laravel para la gestión de comprobantes de pago, con roles de administrador y operador/trabajador.

## 🚀 Características

- **Autenticación de usuarios** con roles (Admin y Operador/Trabajador)
- **Gestión de comprobantes** con estados (Pendiente, Aprobado, Rechazado)
- **Sistema de observaciones** y comunicación en tiempo real
- **Carga de archivos** (imágenes y PDFs) para comprobantes y observaciones
- **Interfaz moderna** con Tailwind CSS
- **Dockerizado** para fácil despliegue

## 📋 Requisitos

- Docker
- Docker Compose
- Git (opcional, para clonar el repositorio)

## 🔧 Instalación

### 1. Clonar el repositorio

```bash
git clone <repository-url>
cd corpsaf-comprobantes
```

### 2. Configurar variables de entorno

Copia el archivo de ejemplo y ajusta las variables según necesites:

```bash
cp .env.example .env
```

Edita el archivo `.env` y configura las siguientes variables importantes:

```env
APP_NAME="CorpSAF Comprobantes"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=corpsaf
DB_USERNAME=corpsaf_user
DB_PASSWORD=corpsaf_password
```

### 3. Construir y levantar los contenedores

```bash
docker-compose up -d --build
```

Este comando:
- Construye la imagen de la aplicación
- Levanta los contenedores de la aplicación y base de datos
- Ejecuta automáticamente las migraciones
- Genera la clave de aplicación si no existe
- Crea el enlace simbólico para el almacenamiento

### 4. Ejecutar seeders (usuarios de prueba)

Para crear usuarios de prueba, ejecuta:

```bash
docker-compose exec app php artisan db:seed
```

Esto creará:
- **Usuario administrador**: 
  - Email/Usuario: `admin`
  - Contraseña: `admin`
- **Usuarios operadores**: Varios usuarios con DNI como usuario y contraseña (ej: `12345678`)

### 5. Compilar assets (producción)

Si no estás usando el perfil de desarrollo, compila los assets:

```bash
docker-compose exec app npm install
docker-compose exec app npm run build
```

## 🌐 Acceso a la aplicación

Una vez levantados los contenedores:

- **Aplicación web**: http://localhost:8000
- **Login**: Usa las credenciales creadas por el seeder

### Credenciales por defecto

- **Administrador**:
  - Usuario: `admin`
  - Contraseña: `admin`

- **Operador/Trabajador**:
  - Usuario: DNI (ej: `12345678`)
  - Contraseña: DNI (ej: `12345678`)

## 🗄️ Base de datos

### Conexión desde el host

- **Host**: `localhost`
- **Puerto**: `3306`
- **Base de datos**: `corpsaf` (o la configurada en `.env`)
- **Usuario**: `corpsaf_user` (o el configurado en `.env`)
- **Contraseña**: `corpsaf_password` (o la configurada en `.env`)

### Conexión desde contenedores

- **Host**: `db`
- **Puerto**: `3306`
- Resto de credenciales iguales

## 👥 Roles y Permisos

### Administrador

- Puede ver todos los comprobantes
- Puede aprobar o rechazar comprobantes
- Puede agregar observaciones a cualquier comprobante
- No puede crear ni editar comprobantes

### Operador/Trabajador

- Solo puede ver sus propios comprobantes
- Puede crear nuevos comprobantes
- Puede editar sus propios comprobantes (si están pendientes)
- Puede agregar observaciones a sus propios comprobantes
- Puede ver el estado de sus comprobantes

## 🛠️ Comandos útiles

### Gestión de contenedores

```bash
# Ver logs de la aplicación
docker-compose logs -f app

# Ver logs de la base de datos
docker-compose logs -f db

# Detener los contenedores
docker-compose down

# Detener y eliminar volúmenes (incluyendo la base de datos)
docker-compose down -v

# Reconstruir los contenedores
docker-compose up -d --build
```

### Comandos de Artisan

```bash
# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Ejecutar seeders
docker-compose exec app php artisan db:seed

# Limpiar caché
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Crear enlace simbólico para storage
docker-compose exec app php artisan storage:link
```

### Desarrollo con Vite

Para desarrollo con hot-reload de Vite:

```bash
# Iniciar solo el servicio de Node
docker-compose --profile dev up node

# O iniciar todos los servicios incluyendo Node
docker-compose --profile dev up
```

**Nota**: En producción, asegúrate de compilar los assets con `npm run build` y no usar el perfil de desarrollo.

### Acceder al contenedor

```bash
# Acceder al contenedor de la aplicación
docker-compose exec app bash

# Acceder al contenedor de la base de datos
docker-compose exec db bash
```

## 📁 Estructura de Docker

- **app**: Contenedor principal con PHP 8.2, Nginx y la aplicación Laravel
- **db**: Contenedor con MySQL 8.0
- **node**: Contenedor opcional con Node.js 20 para desarrollo con Vite (perfil `dev`)

## 📝 Estructura del Proyecto

```
corpsaf-comprobantes/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ComprobanteController.php
│   │   │   └── Auth/
│   │   │       └── LoginController.php
│   │   └── Middleware/
│   │       └── CheckRole.php
│   └── Models/
│       ├── Comprobante.php
│       ├── Observacion.php
│       └── User.php
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── UserSeeder.php
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── entrypoint.sh
├── resources/
│   ├── views/
│   │   ├── auth/
│   │   │   └── login.blade.php
│   │   ├── comprobantes/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   ├── edit.blade.php
│   │   │   └── show.blade.php
│   │   └── layouts/
│   │       └── app.blade.php
│   ├── css/
│   │   └── app.css
│   └── js/
│       └── app.js
├── routes/
│   └── web.php
├── Dockerfile
├── docker-compose.yml
└── README.md
```

## 🔐 Seguridad

- Los archivos subidos se almacenan en `storage/app/public`
- Los archivos se sirven a través de rutas protegidas con autenticación
- Las contraseñas se hashean con bcrypt
- Los roles se validan mediante middleware

## 🐛 Solución de problemas

### Error: "APP_KEY is missing"

```bash
docker-compose exec app php artisan key:generate
```

### Error: "Storage link not found"

```bash
docker-compose exec app php artisan storage:link
```

### Error: "Styles not showing"

1. Asegúrate de que los assets estén compilados:
   ```bash
   docker-compose exec app npm run build
   ```

2. Si estás usando el perfil de desarrollo, detén el servicio de Node:
   ```bash
   docker-compose down node
   ```

3. Limpia la caché:
   ```bash
   docker-compose exec app php artisan optimize:clear
   ```

### Error: "Database connection failed"

1. Verifica que el contenedor de la base de datos esté corriendo:
   ```bash
   docker-compose ps
   ```

2. Verifica las credenciales en el archivo `.env`

3. Espera unos segundos después de levantar los contenedores para que MySQL esté listo

## 📄 Licencia

Este proyecto es software de código abierto bajo la licencia [MIT](https://opensource.org/licenses/MIT).

## 👨‍💻 Desarrollo

### Tecnologías utilizadas

- **Backend**: Laravel 12.x
- **Frontend**: Tailwind CSS, Blade Templates
- **Base de datos**: MySQL 8.0
- **Servidor web**: Nginx
- **PHP**: 8.2
- **Node.js**: 20 (para Vite)

### Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request
