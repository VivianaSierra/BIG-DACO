# BIG-DACO

Aplicacion de gestion de inventario y ventas construida con Laravel 12, Vite y Firebase Authentication.

## Despliegue en Vercel

El proyecto incluye el runtime PHP de Vercel, un punto de entrada serverless para Laravel, el build de Vite y rutas para servir los recursos estaticos. Los datos de la aplicacion deben guardarse en una base persistente; el SQLite local no sirve para produccion serverless.

### 1. Obtener los datos de conexión de TiDB Cloud

La instancia BIG-DACO de TiDB Cloud es compatible con MySQL. En la consola de TiDB, abre la instancia y selecciona **Connect > Public > PHP** (o **General MySQL**). Usa los valores de conexión que muestra TiDB; el endpoint público requiere TLS. El puerto habitual es `4000`.

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
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | Host indicado por TiDB en **Connect** |
| `DB_PORT` | Puerto indicado por TiDB (normalmente `4000`) |
| `DB_DATABASE` | Nombre de la base de datos creada en TiDB |
| `DB_USERNAME` | Usuario completo indicado por TiDB; no omitas el prefijo de cuenta/instancia si aparece |
| `DB_PASSWORD` | Contraseña generada para la instancia TiDB |
| `SESSION_DRIVER` | `cookie` |
| `CACHE_STORE` | `array` |
| `QUEUE_CONNECTION` | `sync` |
| `LOG_CHANNEL` | `stderr` |

No configures `DB_URL` con una URL PostgreSQL. Si la agregaste siguiendo instrucciones anteriores, elimínala de Vercel para que no reemplace la conexión MySQL. Sustituye también cualquier `APP_KEY` que hayas compartido y genera una nueva. `GEMINI_API_KEY` es opcional; agrégala si quieres habilitar el chatbot. No guardes claves ni contraseñas en el repositorio.

### 4. Aplicar las migraciones

No ejecutes todavía las migraciones en una base vacía: hay más de una migración que intenta crear la tabla `ventas`. Ese conflicto debe corregirse primero. Una vez corregido, configura localmente la conexión de TiDB y ejecuta las migraciones **una sola vez** contra esa base:

```sh
php artisan migrate --force
```

No uses `migrate:fresh` sobre una base con datos.

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
