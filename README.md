# Camaleón

Motor de invitaciones digitales para bodas, quinceaños, cumpleaños y más. Los organizadores crean páginas de evento personalizadas; los invitados interactúan vía URL pública (RSVP, canciones, regalos, fotos).

**Stack:** Laravel 10 · PHP 8.1 · Tailwind CSS v4 · Vite · JWT (API) · SQLite (tests)

---

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan jwt:secret       # Genera JWT_SECRET en .env
npm install

# Base de datos (MySQL)
# Configura DB_* en .env, luego:
php artisan migrate
php artisan db:seed          # Crea usuarios y eventos de demo
php artisan storage:link     # Enlaza storage público

# Arrancar
php artisan serve
npm run dev
```

### Credenciales de demo

| Rol | Email | Contraseña |
|-----|-------|------------|
| Admin (API JWT) | admin@demo.com | password |
| Cliente (panel web) | cliente@demo.com | password |

> Ver seeders en `database/seeders/` para credenciales completas.

---

## Comandos clave

```bash
php artisan test                   # Tests (SQLite in-memory, sin MySQL)
./vendor/bin/pint                  # Estilo de código
php artisan l5-swagger:generate    # Regenerar documentación OpenAPI
npm run build                      # Build de producción
```

---

## Arquitectura

Ver [AGENTS.md](AGENTS.md) para la guía completa de arquitectura, convenciones y rutas.

### URLs principales

| Sección | URL |
|---------|-----|
| Página de evento pública | `GET /eventos/{slug}` |
| Panel del organizador | `GET /panel/eventos` |
| Login panel | `POST /panel/login` |
| API JWT login | `POST /api/auth/login` |
| Docs API (Swagger) | `GET /api/documentation` |

---

## Variables de entorno relevantes

| Variable | Descripción |
|----------|-------------|
| `APP_URL` | URL base (importante para `storage:link` y assets) |
| `JWT_SECRET` | Generado con `php artisan jwt:secret` |
| `FILESYSTEM_DISK` | `public` para subida de fotos |
| `DB_*` | Conexión MySQL para producción |
| `MAIL_*` | Configuración SMTP para notificaciones |

---

## SMTP Hostinger (producción)

Ejemplo recomendado para usar el buzón profesional:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=contactoysoporte@kaanforge.com
MAIL_PASSWORD=TU_PASSWORD_REAL
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=contactoysoporte@kaanforge.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Cola para correos (producción)

Los correos RSVP ya se encolan (queue) en la aplicación. Para que se procesen de forma asíncrona:

```bash
php artisan queue:table
php artisan migrate
php artisan queue:work --queue=default --tries=3
```

Si usas Supervisor/Systemd en servidor, deja `queue:work` como proceso permanente.
