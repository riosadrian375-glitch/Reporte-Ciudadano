# Reporte Ciudadano

Proyecto integrador en Laravel para registrar, gestionar y dar seguimiento a reportes ciudadanos.

## Requisitos

- PHP 8.2 o superior
- Composer
- MySQL/MariaDB desde XAMPP

## Instalación local

1. Copiar la configuración:

```bash
copy .env.example .env
```

2. Instalar dependencias:

```bash
composer install
```

3. Generar clave de Laravel:

```bash
php artisan key:generate
```

4. Crear la base de datos `reporteciudadano` en MySQL.

5. Revisar credenciales en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reporteciudadano
DB_USERNAME=root
DB_PASSWORD=
```

6. Ejecutar migraciones y datos demo:

```bash
php artisan migrate --seed
```

7. Levantar el servidor:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Abrir: http://127.0.0.1:8000

## Cuentas demo

Todas usan la contraseña `test1234`.

- Ciudadano: `carlos@example.com`
- Admin municipal: `admin@municipal.gob.pe`
- Operador: `operador1@municipal.gob.pe`
- Admin sistema: `sysadmin@reporteciudadano.pe`

## Funciones migradas a Laravel

- Autenticación: login, registro y logout.
- Roles: ciudadano, operador, administrador municipal y administrador del sistema.
- Paneles por rol.
- Reportes: listado, filtros, creación, detalle, estados y geolocalización.
- Evidencias: imágenes al crear reportes y evidencia de cierre del operador.
- Interacciones: comentarios, likes y guardados.
- Mapa de reportes con endpoint JSON.
- Perfil de usuario.
- Mis reportes y guardados.
- Notificaciones internas.
- Contactos de emergencia.
- Asignación de reportes a operadores.
- Administración básica de usuarios y roles.
- Moderación local de texto.
- Clima con OpenWeather opcional y fallback simulado.
- Chat IA con respuesta local y configuración preparada para API externa.

## Rutas principales

- `/login`
- `/registro`
- `/dashboard`
- `/reportes`
- `/reportes/create`
- `/mis-reportes`
- `/guardados`
- `/mapa`
- `/emergencias`
- `/notificaciones`
- `/perfil`
- `/panel/ciudadano`
- `/panel/operador`
- `/panel/admin-municipal`
- `/panel/admin-sistema`
