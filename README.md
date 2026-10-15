# BIG-DACO

Aplicacion de gestion de inventario y ventas construida con Laravel 12, Vite y Firebase Authentication.

## Despliegue en Vercel

El proyecto incluye el runtime PHP de Vercel, un punto de entrada serverless para Laravel, el build de Vite y rutas para servir los recursos estaticos. Los datos de la aplicacion deben guardarse en una base persistente; el SQLite local no sirve para produccion serverless.

### 1. Crear la base de datos Neon

En Neon, crea una base PostgreSQL y copia la cadena de conexion pooled. Conserva `sslmode=require` en la URL.

### 2. Importar el repositorio en Vercel

Importa este repositorio desde Vercel y deja la configuracion de build detectada por `vercel.json`. El build ejecuta `npm run build`; no ejecutes migraciones durante el build.

### 3. Configurar las variables de entorno

Agrega estas variables en **Project Settings > Environment Variables** para Production y, si aplica, Preview:

| Variable | Valor |
| --- | --- |
| `APP_NAME` | `BIG-DACO` |
| `APP_ENV` | `production` |
| `APP_KEY` | Clave generada con `php artisan key:generate --show` |
| `APP_DEBUG` | `false` |
| `APP_URL` | URL HTTPS asignada a tu proyecto Vercel |
| `ASSET_URL` | La misma URL HTTPS del proyecto Vercel |
| `DB_CONNECTION` | `pgsql` |
| `DB_URL` | Cadena pooled de Neon con `sslmode=require` |
| `SESSION_DRIVER` | `cookie` |
| `CACHE_STORE` | `array` |
| `QUEUE_CONNECTION` | `sync` |
| `LOG_CHANNEL` | `stderr` |

`GEMINI_API_KEY` es opcional; agregala si quieres habilitar el chatbot. No guardes claves ni URLs con credenciales en el repositorio.

### 4. Aplicar las migraciones

Una vez creada la base de Neon y configuradas las variables, ejecuta las migraciones **una sola vez** contra esa base:

```sh
php artisan migrate --force
```

Puedes hacerlo desde tu equipo con las variables de Neon configuradas localmente, o desde un entorno seguro con acceso a ellas. No uses `migrate:fresh` sobre una base con datos.

### 5. Desplegar

Haz push de los cambios e inicia el despliegue desde Vercel. Las funciones escriben archivos temporales bajo `/tmp`; por eso las sesiones usan cookies y el cache en memoria, mientras PostgreSQL conserva los datos entre invocaciones.

## Desarrollo local

Requisitos: PHP 8.2+, Composer y Node.js.

```sh
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

Para desarrollar el frontend en vivo, ejecuta `npm run dev` en otra terminal.

## Pruebas

```sh
php artisan test
```
