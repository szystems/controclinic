# Política de Retención de Datos — ControClinic

> Última actualización: 2026-05-04
> Referencia normativa: regulaciones LATAM estándar (México NOM-004, Colombia Res. 1995/1999)

## Resumen

| Tipo de dato              | Retención mínima | Retención en sistema | Acción al vencer |
|---------------------------|-----------------|---------------------|------------------|
| Historiales médicos       | 5 años           | 5 años desde última consulta | Soft-delete → exportar → purgar |
| Expedientes de pacientes  | 5 años           | 5 años desde última consulta | Soft-delete → exportar → purgar |
| Facturas / cobros         | 7 años           | 7 años desde emisión | Solo soft-delete (audit trail) |
| Logs de actividad (spatie)| 2 años           | 2 años              | Purgar con `activity:clean` |
| Backups de BD             | 30 días          | 30 días             | `backup:clean` (automático) |
| Archivos adjuntos (S3)    | 5 años           | 5 años              | S3 Lifecycle policy |
| Logs del servidor         | 90 días          | `LOG_DAILY_DAYS=30` | Rotación automática Laravel |

---

## Backups de Base de Datos

Gestionado por `spatie/laravel-backup`. Estrategia `DefaultStrategy`:

- **Diarios completos**: mantener todos por 7 días
- **Un backup por día**: retener 16 días (semanas 1-2)
- **Un backup por semana**: retener 8 semanas
- **Un backup por mes**: retener 4 meses

Schedule (UTC, ajustar a timezone de producción):
- `01:00` — `backup:clean` (aplica política de retención)
- `02:00` — `backup:run --only-db` (solo BD, el código está en Git)

Destino por defecto: `local` (storage/app/controclinic/). Configurar `BACKUP_DISK=s3` en producción.

---

## Historiales Médicos (`medical_records`)

- Retención mínima legal: **5 años** desde la última consulta del paciente
- Implementación actual: soft-delete (`deleted_at`) en modelos `Patient` y `MedicalRecord`
- **TODO**: Comando artisan `patients:purge-expired` para purgar pacientes sin actividad en 5+ años
  - Debe exportar el expediente a PDF antes de purgar
  - Solo ejecutable por `owner` de la clínica
  - Requiere confirmación explícita

---

## Registros Fiscales (`appointments` con `is_billable=true`)

- Retención mínima legal: **7 años** desde la fecha de emisión
- Nunca eliminar físicamente — solo soft-delete
- Los registros fiscales deben ser exportables (PDF/CSV) antes de vencer

---

## Logs de Actividad (spatie/laravel-activitylog)

- Retención: **2 años**
- Comando de limpieza: `php artisan activitylog:clean --days=730`
- Agregar al schedule cuando el volumen lo justifique

---

## Datos de Usuarios y Clínicas

- Al cancelar suscripción: la clínica pasa a estado `cancelled`
- Período de gracia: **30 días** para exportar datos antes de soft-delete
- Después de 30 días: soft-delete de todos los registros de la clínica
- Hard-delete: **no implementado** — los datos médicos requieren retención legal

---

## Archivos Adjuntos (Storage / S3)

- Mismo ciclo de vida que el historial médico (5 años)
- En S3: configurar **S3 Lifecycle Policy** para mover a Glacier a los 2 años y eliminar a los 5
- En local: no hay política automática — gestionar manualmente

---

## Notas de Implementación

1. Las políticas de retención son **por clínica** (multi-tenant) — en el futuro, el `owner` debería poder configurar períodos más largos.
2. Los comandos de purga deben registrar en `activity_log` quién los ejecutó.
3. GDPR/LATAM: si un paciente solicita "derecho al olvido", solo aplica para datos no médicos. Los historiales clínicos tienen retención obligatoria.
