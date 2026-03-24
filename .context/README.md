# 📚 Carpeta de Contexto (.context)

Esta carpeta contiene documentación estructurada para mantener el contexto del proyecto **ControClinic**. Es especialmente útil para:

1. **AI Assistants** (GitHub Copilot, Claude, ChatGPT) - Para entender el proyecto
2. **Nuevos desarrolladores** - Para onboarding rápido
3. **Referencia rápida** - Para consultar decisiones y convenciones

---

## 📁 Estructura de Archivos

| Archivo | Propósito | Actualizar |
|---------|-----------|------------|
| `PROJECT.md` | Información general del proyecto | Cuando cambie info básica |
| `ARCHITECTURE.md` | Estructura técnica y patrones | Cuando cambie arquitectura |
| `STATUS.md` | Estado actual y progreso | **Frecuentemente** |
| `CONVENTIONS.md` | Estándares y convenciones de código | Cuando se definan nuevas |
| `MODELS.md` | Documentación detallada de modelos | Cuando cambien modelos |
| `ROADMAP.md` | Plan de desarrollo por fases | Mensualmente |
| `TASKS.md` | Tareas pendientes y prioridades | **Frecuentemente** |
| `DECISIONS.md` | Registro de decisiones (ADRs) | Cuando se tome una decisión |
| `AI_INSTRUCTIONS.md` | Instrucciones para AI assistants | Cuando cambien convenciones |

---

## 🔄 Flujo de Trabajo

### Al iniciar trabajo en el proyecto:
1. Lee `STATUS.md` para ver el estado actual
2. Revisa `TASKS.md` para ver qué está pendiente
3. Consulta `CONVENTIONS.md` si tienes dudas de estilo

### Al completar una feature:
1. Actualiza `STATUS.md` moviendo items a "Completado"
2. Actualiza `TASKS.md` si aplica
3. Documenta decisiones importantes en `DECISIONS.md`

### Al tomar una decisión de arquitectura:
1. Agrega una entrada en `DECISIONS.md` usando el template ADR
2. Actualiza `ARCHITECTURE.md` si cambia la estructura

---

## 🤖 Uso con AI Assistants

Para mejores resultados con AI, incluye referencias a estos archivos:

```
"Revisa .context/CONVENTIONS.md antes de escribir código"
"El estado actual está en .context/STATUS.md"
"Las decisiones de arquitectura están en .context/DECISIONS.md"
```

El archivo `AI_INSTRUCTIONS.md` contiene instrucciones específicas para AI assistants.

---

## ✏️ Mantenimiento

Esta documentación es un **documento vivo**. Debe actualizarse regularmente para mantener su valor.

**Responsable:** Desarrollador principal  \n**Frecuencia mínima de revisión:** Semanal  \n**Última revisión:** 2026-03-23", "oldString": "**Responsable:** Desarrollador principal  \n**Frecuencia mínima de revisión:** Semanal  \n**Última revisión:** 2026-01-28
