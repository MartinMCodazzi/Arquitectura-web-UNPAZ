---
paths:
  - '**'
---

# General

## Documentar el trabajo de cada tarea en DECISIONES_DISENO.txt
REGLA MANDATORIA: al cerrar cada tarea o cambio relevante se anota en DECISIONES_DISENO.txt (raíz del repositorio) qué se hizo y por qué, con el formato Contexto / Decisión / Motivación técnica / Alternativas descartadas. El archivo es append-only: no se editan ni borran secciones previas. A partir del ADR-0001 este archivo es la BITÁCORA DE SESIÓN; la fuente de verdad de las decisiones técnicas es docs/adr/ (ver la regla siguiente).

## Registrar decisiones de arquitectura como ADR en docs/adr/
REGLA PERSISTENTE: toda decisión técnica relevante (modelado de datos, librerías, patrones de diseño, estrategias de caché, filtros, SEO) DEBE registrarse como un nuevo archivo .md dentro de docs/adr/ con formato correlativo (0001-nombre-decision.md) y actualizarse en el índice docs/adr/README.md. Cada ADR usa la plantilla estricta: encabezado con Estado/Fecha/Autor, secciones Contexto, Decisión y Consecuencias (Positivas / Limitaciones-Riesgos). Los ADR son append-only: una decisión que cambia se marca como Reemplazado y se crea uno nuevo. DECISIONES_DISENO.txt queda como bitácora de sesión, no como fuente de verdad de decisiones.
