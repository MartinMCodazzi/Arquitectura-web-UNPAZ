# Instrucciones del proyecto — Tienda Online

> Este archivo existe para que cualquier agente (OpenCode, Claude Code, Cursor…)
> que arranque en este repositorio conozca las reglas permanentes del proyecto.
> Las reglas canónicas viven en `AGENTS.md` y en `.ai/rules/general.md`.

## REGLA PERSISTENTE 1 — Decisiones de arquitectura como ADR

Toda decisión técnica relevante (modelado de datos, librerías, patrones de diseño, estrategias de caché, filtros, SEO) DEBE registrarse como un nuevo archivo `.md` dentro de `docs/adr/` con formato correlativo (`0001-nombre-decision.md`) y actualizarse en el índice `docs/adr/README.md`.

Plantilla obligatoria de cada ADR:

```markdown
# ADR-XXXX: [Título corto de la decisión]

* **Estado:** [Propuesto | Aceptado | Reemplazado]
* **Fecha:** YYYY-MM-DD
* **Autor:** [Nombre / OpenCode Agent]

## Contexto

## Decisión

## Consecuencias

### Positivas

### Limitaciones / Riesgos
```

- Los ADR son append-only en su numeración: si una decisión cambia, el anterior se marca como `Reemplazado` y se crea uno nuevo que lo referencia.
- `docs/adr/README.md` se actualiza en el mismo commit en que se agrega un ADR.

## REGLA PERSISTENTE 2 — Bitácora de sesión

`DECISIONES_DISENO.txt` (raíz del repositorio) registra **qué se hizo en cada tarea** (Contexto / Decisión / Motivación técnica / Alternativas descartadas). Es append-only. No es la fuente de verdad de las decisiones técnicas: eso es `docs/adr/`.

## Convenciones del repositorio

- Precios: entero en **céntimos ARS** (`config/store.php` → `store.currency`). Nunca float.
- Locale `es_AR`, moneda ARS, rutas y textos en español.
- El carrito vive en la sesión (`CartService`); no se agregan tablas de carrito sin un ADR nuevo.
- Todo listado público filtra por `is_active = true` y pagina.
- El **eager loading es explícito**, incluso en consultas de un solo modelo (ver ADR-0009).
- Ninguna consulta a base de datos dentro de vistas Blade.
- Formato de código: `vendor/bin/pint --dirty --format agent`.
- Tests: Pest. No se borran tests existentes sin aprobación del usuario.
- No se agregan dependencias de Composer/npm sin aprobación explícita del usuario.
