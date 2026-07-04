# 🚀 Despliegue en Producción — ControClinic

> Guía específica de ControClinic. Procedimiento maestro y lecciones de producción:
> `~/proyectos/migracion/runbooks/03-laravel-a-vps-coolify.md` y `migracion/AGENTS.md`.
> Decisión de infraestructura: ADR-013. Estado: **desplegado en prod** (2026-07-04).

---

## Estado actual en producción (2026-07-04)

| Item | Valor |
|------|-------|
| URL | https://controclinic.com · https://www.controclinic.com |
| Coolify app | `controclinic:main-ybwfwifzqp47cu1et7pi0vz6` |
| Repo / branch | `szystems/controclinic` · `main` |
| Compose | `/docker-compose.coolify.yml` |
| Secretos | `.env.coolify.secrets` (local, gitignored) |
| Health | `/up` → 200 · `php artisan ops:health --queue` |
| Mail | ✅ Resend SMTP · `php artisan mail:test` |
| Post-deploy | `scripts/post-deploy-health.sh` + systemd watcher |

**Pendiente manual:** Snapshot Hetzner (A11) en consola Hetzner Cloud.

---

## Correo saliente — Resend (A10)

Configurado en Coolify: `MAIL_HOST=smtp.resend.com`, puerto 587, usuario `resend`.

```bash
APP=$(sudo docker ps -q --filter name=controclinic-app | head -1)
sudo docker exec "$APP" php artisan mail:test tu@email.com
sudo docker exec "$APP" php artisan ops:health --queue --mail=tu@email.com
```

Dominio `controclinic.com` debe estar verificado en Resend (SPF/DKIM en Cloudflare).

---

## Post-deploy HTTPS (A12)

Tras deploy Coolify, Traefik puede devolver **503 no available server**. Fix:

```bash
# Instalar watcher (una vez, desde repo local)
./scripts/install-post-deploy-hook.sh

# Manual inmediato
ssh deploy@5.78.235.235 /home/deploy/bin/controclinic-post-deploy-health.sh
```

---

## Snapshot Hetzner (A11)

Hetzner Cloud Console → servidor CPX31 → **Snapshots** → Create.  
Nombre: `controclinic-pre-v1-2026-07-04`. Hacer con smoke test verde.

---

## Ops health check

`php artisan ops:health` verifica DB, Redis, storage, mail, scheduler heartbeat.  
Con `--queue` despacha job y confirma que el worker lo procesa.

---

## Infraestructura (ya operativa, propiedad de SZ Systems)

| Componente | Valor |
|------------|-------|
| VPS | Hetzner CPX31 · `5.78.235.235` · Ubuntu + Docker · SSH `deploy@5.78.235.235` (sudo sin pass) |
| Panel CI/CD | Coolify v4.1.2 · `http://5.78.235.235:8000` · GitHub App conectada |
| DNS / CDN / SSL / WAF | Cloudflare (plan Free) |
| Proxy | Traefik (gestionado por Coolify) |
| Apps vecinas en el VPS | `portal.szystems.com`, `asonataxela.com`, `clinicaselvalle.com` |

> ⚠️ El VPS es **multi-app**. Es obligatorio prefijar contenedores y enrutar por labels nativos
> para no provocar el **cross-serving** ya sufrido en producción (ver lecciones abajo).

---

## Dominio y correo (ADR-014)

- **Registrar:** `controclinic.com` en **iPage** (buzones ilimitados @controclinic.com).
- **DNS:** Cloudflare (nameservers) — A/@/www → Hetzner · MX → iPage.
- **App:** NO en iPage — solo Hetzner + Coolify.
- **SMTP Laravel:** iPage (`noreply@controclinic.com`, `support@controclinic.com`).
- **Web3Forms (contacto):** `szystemscorreos@outlook.com` (backend, no visible).

---

## Archivos que hay que crear en el repo (M4.2)

Partir de las plantillas reales en `~/proyectos/migracion/docker/` (origen: Clínicas del Valle).

1. **`Dockerfile.prod`** — multi-stage: `node-builder` (Vite build) → base PHP **8.3-fpm** → instalar deps composer `--no-dev` → imagen final app + imagen nginx.
2. **`docker/php/entrypoint.sh`**:
   ```sh
   #!/bin/sh
   set -e
   php artisan storage:link || true
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   exec "$@"
   ```
3. **`docker-compose.coolify.yml`** — servicios **prefijados `controclinic-*`**: `controclinic-app`, `controclinic-webserver`, `controclinic-mysql`, `controclinic-redis`, `controclinic-queue`, `controclinic-scheduler`. Red propia + red externa `coolify`.
4. **`docker/nginx/default.conf`** — resolver dinámico (no `fastcgi_pass` estático) y `server_name` explícito:
   ```nginx
   resolver 127.0.0.11 valid=5s ipv6=off;
   set $upstream_php controclinic-app:9000;
   # ...
   fastcgi_pass $upstream_php;
   ```

---

## Cambios de código requeridos (M4.2)

Lecciones del runbook 03 (Coolify inyecta variables vacías y `config:cache` las congela):

1. **`config/app.php`** y demás `config/*.php` → usar `?:`, no segundo argumento de `env()`:
   ```php
   'name'            => env('APP_NAME') ?: 'ControClinic',
   'env'             => env('APP_ENV') ?: 'production',
   'debug'           => (bool) (env('APP_DEBUG') ?: false),
   'url'             => env('APP_URL') ?: 'https://controclinic.com',
   'locale'          => env('APP_LOCALE') ?: 'es',
   'fallback_locale' => env('APP_FALLBACK_LOCALE') ?: 'en',
   ```
2. **`config/filesystems.php`** → `'default' => env('FILESYSTEM_DISK') ?: 'local',`
3. **`bootstrap/app.php`** → `$middleware->trustProxies(at: '*');` (evita Mixed Content con Cloudflare/Traefik; crítico para login Livewire).
4. **`docker-compose.coolify.yml`** → hardcodear `APP_NAME`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_LOCALE=es`, `FILESYSTEM_DISK`. Pasar como secretas SOLO: `APP_KEY`, `DB_*`, `PADDLE_*`, `MAIL_*`.

---

## Variables de entorno de producción

Hardcodeadas en el compose: `APP_NAME=ControClinic`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://controclinic.com`, `APP_LOCALE=es`, `APP_FALLBACK_LOCALE=en`, `FILESYSTEM_DISK`, drivers Redis (cache/session/queue), `DB_HOST=controclinic-mysql`, `REDIS_HOST=controclinic-redis`.

Secretas en Coolify UI (NO en el compose, NO en git):
`APP_KEY` · `DB_DATABASE` · `DB_USERNAME` · `DB_PASSWORD` · `MYSQL_ROOT_PASSWORD` ·
`PADDLE_SELLER_ID` · `PADDLE_CLIENT_SIDE_TOKEN` · `PADDLE_API_KEY` · `PADDLE_WEBHOOK_SECRET` ·
price IDs Paddle · `MAIL_*`.

> ⚠️ Nunca regenerar `APP_KEY` tras guardar datos cifrados (`encrypted` casts) → `The MAC is invalid`. Mantenerlo igual en `.env` y en la BD de Coolify.

---

## Pasos de deploy (resumen — detalle en runbook 03)

1. Repo en GitHub conectado a Coolify → **New Resource → Docker Compose** → compose `docker-compose.coolify.yml`, HTTP service = `controclinic-webserver` puerto 80.
2. Cargar env secretas. Primer deploy → ver logs hasta `Ready. Executing: php-fpm`.
3. Validar en **dominio temporal sslip.io** (NO tocar DNS aún):
   - [ ] `/login` responde 200 · `/app/...` redirige a `https://` (no `http://`)
   - [ ] Login, logout, subida de archivo (≥2 módulos)
   - [ ] Locale correcto (no claves `app.*`), footer "ControClinic" (no "Laravel")
   - [ ] Queue procesa jobs · Scheduler corre · sin errores en `storage/logs/laravel.log`
4. Configurar FQDN en **Coolify UI**: `https://controclinic.com,https://www.controclinic.com` → Redeploy (genera labels Traefik nativos al `container_name` específico).
5. **Cutover DNS** en Cloudflare: `A @` y `A www` → `5.78.235.235` (proxy naranja). SSL Full (strict). Esperar propagación (~5 min, TTL 300).
6. Verificar: `curl -sSI https://controclinic.com | head -3` → `200`; `curl -sSI https://controclinic.com/app | grep -i location` → `https://...` (nunca `http://`).
7. Snapshot Hetzner post-deploy exitoso.

---

## Servicios continuos en producción (M4.4)

- **Queue worker** (`php artisan queue:work redis --tries=3`) — las notificaciones email (`SendAppointmentNotification`) lo requieren.
- **Scheduler** (`php artisan schedule:run` cada 60s) — recordatorios horarios + backups.
- **Backups** — `spatie/laravel-backup` (ya integrado) → destino S3/R2; verificar restauración.
- **Mail** — ⚙️ decidir: iPage SMTP (ya usado por otras apps SZ Systems) vs transaccional dedicado (Postmark/Resend/SES). Médico/transaccional: preferible dominio verificado con SPF/DKIM.

---

## 🔴 Lecciones de producción — NO repetir (del workspace migracion)

| Problema | Causa | Fix |
|----------|-------|-----|
| **Cross-serving** (un dominio muestra otra app) | Routing a alias genérico `nginx:80` en red `coolify`; al caer un stack resuelve a otro contenedor | Prefijar contenedores `controclinic-*`; enrutar por **labels Traefik nativos** (Coolify UI); nunca crear archivos manuales en `/data/coolify/proxy/dynamic/` con alias |
| Mixed Content / redirect a `http://` | Laravel no confía en `X-Forwarded-Proto` | `trustProxies(at:'*')` |
| `validation.required` en uploads | `FILESYSTEM_DISK` vacío | `env('FILESYSTEM_DISK') ?: 'local'` |
| Footer dice "Laravel" / claves de traducción visibles | `APP_NAME`/`APP_LOCALE` vacíos por Coolify | `env() ?: default` + hardcodear en compose |
| `The MAC is invalid` | `APP_KEY` cambiado tras cifrar datos | No cambiar APP_KEY; sincronizar con BD Coolify |
| Fix de config se pierde al reiniciar | `docker restart` re-cachea con env vacías | No usar `docker restart`; `docker exec -e VAR=val ... php artisan config:cache` o redeploy |

---

## Rollback (cutover DNS)

1. En Cloudflare: revertir `A` al destino anterior (impacto < 5 min con TTL 300).
2. La app en el VPS sigue corriendo; diagnosticar en calma y reintentar.

---

## Diagnóstico rápido

```bash
APP=$(sudo docker ps --format "{{.Names}}" | grep "^controclinic-app" | head -n1)
sudo docker exec "$APP" php artisan config:show app | grep -E "name|env|debug|url|locale"
sudo docker exec "$APP" tail -n 100 /var/www/html/storage/logs/laravel.log
NGINX=$(sudo docker ps --format "{{.Names}}" | grep "^controclinic-webserver" | head -n1)
sudo docker logs "$NGINX" --tail=50
```
