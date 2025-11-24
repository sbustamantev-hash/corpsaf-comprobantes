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

### Con Docker (Recomendado)

- Docker
- Docker Compose
- Git (opcional, para clonar el repositorio)

### Sin Docker

- PHP 8.2 o superior
- Composer
- MySQL 8.0 o superior (o MariaDB)
- Node.js 20 o superior y npm
- Git (opcional, para clonar el repositorio)

## 🔧 Instalación

Elige el método de instalación que prefieras:

- [Instalación con Docker](#instalación-con-docker) (Recomendado)
- [Instalación con Laragon](#instalación-con-laragon) (Windows)
- [Instalación Manual](#instalación-manual) (Sin Docker)

---

## 🐳 Instalación con Docker

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

---

## 🪟 Instalación con Laragon (Windows)

Laragon es un entorno de desarrollo local para Windows que incluye PHP, MySQL, Apache/Nginx y más.

### 1. Instalar Laragon

1. Descarga Laragon desde [laragon.org](https://laragon.org/download/)
2. Instala Laragon en tu sistema
3. Asegúrate de tener las siguientes versiones:
   - PHP 8.2 o superior
   - MySQL 8.0 o superior
   - Node.js 20 o superior (instálalo por separado si no está incluido)

### 2. Clonar el repositorio

```bash
# Navega a la carpeta www de Laragon (o donde prefieras)
cd C:\laragon\www
git clone <repository-url>
cd corpsaf-comprobantes
```

### 3. Instalar dependencias de PHP

```bash
composer install
```

### 4. Configurar variables de entorno

```bash
# Copia el archivo de ejemplo
copy .env.example .env
```

Edita el archivo `.env` y configura:

```env
APP_NAME="CorpSAF Comprobantes"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://corpsaf-comprobantes.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=corpsaf
DB_USERNAME=root
DB_PASSWORD=
```

**Nota**: Laragon usa `root` sin contraseña por defecto. Ajusta según tu configuración.

### 5. Crear la base de datos

1. Abre Laragon y haz clic en "Database" o accede a phpMyAdmin
2. Crea una nueva base de datos llamada `corpsaf` (o la que configuraste en `.env`)

### 6. Generar clave de aplicación

```bash
php artisan key:generate
```

### 7. Ejecutar migraciones y seeders

```bash
php artisan migrate
php artisan db:seed
```

### 8. Crear enlace simbólico para storage

```bash
php artisan storage:link
```

### 9. Instalar dependencias de Node.js y compilar assets

```bash
npm install
npm run build
```

### 10. Configurar virtual host en Laragon

1. Abre Laragon
2. Haz clic derecho en el proyecto y selecciona "Menu" > "Apache" > "Add Site"
3. O manualmente, edita `C:\laragon\etc\apache2\sites-enabled\corpsaf-comprobantes.test.conf`:

```apache
<VirtualHost *:80>
    ServerName corpsaf-comprobantes.test
    DocumentRoot "C:/laragon/www/corpsaf-comprobantes/public"
    <Directory "C:/laragon/www/corpsaf-comprobantes/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

4. Reinicia Apache en Laragon

### 11. Acceder a la aplicación

- **URL**: http://corpsaf-comprobantes.test
- O usa: http://localhost/corpsaf-comprobantes/public

### Comandos útiles con Laragon

```bash
# Iniciar servidor de desarrollo (opcional, Laragon ya lo hace)
php artisan serve

# Compilar assets en modo desarrollo
npm run dev

# Ver logs
php artisan pail
```

---

## 💻 Instalación Manual (Sin Docker)

Esta guía es para instalar el proyecto sin Docker ni Laragon, usando instalaciones manuales de PHP, MySQL, etc.

### 1. Requisitos previos

Asegúrate de tener instalado:

- **PHP 8.2+** con extensiones: `pdo_mysql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd`
- **Composer** ([getcomposer.org](https://getcomposer.org/))
- **MySQL 8.0+** o MariaDB
- **Node.js 20+** y npm
- **Servidor web** (Apache/Nginx) o usar `php artisan serve`

### 2. Clonar el repositorio

```bash
git clone <repository-url>
cd corpsaf-comprobantes
```

### 3. Instalar dependencias de PHP

```bash
composer install
```

### 4. Configurar variables de entorno

```bash
cp .env.example .env
```

Edita el archivo `.env`:

```env
APP_NAME="CorpSAF Comprobantes"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=corpsaf
DB_USERNAME=root
DB_PASSWORD=tu_contraseña
```

### 5. Crear la base de datos

```sql
CREATE DATABASE corpsaf CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

O usa tu herramienta de gestión de MySQL preferida (phpMyAdmin, MySQL Workbench, etc.)

### 6. Generar clave de aplicación

```bash
php artisan key:generate
```

### 7. Ejecutar migraciones y seeders

```bash
php artisan migrate
php artisan db:seed
```

### 8. Crear enlace simbólico para storage

```bash
php artisan storage:link
```

### 9. Instalar dependencias de Node.js

```bash
npm install
```

### 10. Compilar assets

Para producción:
```bash
npm run build
```

Para desarrollo (con hot-reload):
```bash
npm run dev
```

### 11. Configurar servidor web

#### Opción A: Usar servidor de desarrollo de Laravel

```bash
php artisan serve
```

Accede a: http://localhost:8000

#### Opción B: Configurar Apache

Crea un virtual host en tu configuración de Apache:

```apache
<VirtualHost *:80>
    ServerName corpsaf.local
    DocumentRoot "/ruta/a/corpsaf-comprobantes/public"
    <Directory "/ruta/a/corpsaf-comprobantes/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Agrega a tu archivo `/etc/hosts` (Linux/Mac) o `C:\Windows\System32\drivers\etc\hosts` (Windows):

```
127.0.0.1    corpsaf.local
```

#### Opción C: Configurar Nginx

```nginx
server {
    listen 80;
    server_name corpsaf.local;
    root /ruta/a/corpsaf-comprobantes/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 12. Permisos (Linux/Mac)

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Comandos útiles

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar aplicación
php artisan optimize

# Ver logs
tail -f storage/logs/laravel.log
```

---

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
