# Fase 7 — Perfil de Usuario, Seguridad y Auditoría

## 1. Perfil tenantizado y seguro
- Ruta: `/app/{clinic}/profile`
- Permite editar: nombre, email, teléfono, idioma, zona horaria, especialidades, bio, licencia.
- Cambio de contraseña integrado.
- Validaciones multi-tenant y roles.

## 2. Historial de actividad (Activity Log)
- Sección/tab en el perfil.
- Consulta y paginación de logs de Spatie filtrados por usuario.
- Muestra fecha, evento y cambios.

## 3. Próximos pasos
- Forzar reset de contraseña a staff (solo owner/admin).
- Transferencia de ownership (solo owner, seguro y auditable).
- Tests feature para todos los flujos.

---

### Estado
- [x] Componente Livewire y vista para perfil tenantizado.
- [x] Lógica y UI para editar datos y cambiar contraseña.
- [x] Sección de Activity Log en el perfil, con paginación y tabla de eventos.
- [ ] Forzar reset de contraseña a staff.
- [ ] Transferencia de ownership.
- [ ] Tests feature.
