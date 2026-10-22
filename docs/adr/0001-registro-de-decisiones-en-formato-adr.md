# ADR-0001: Registro de decisiones de arquitectura en formato ADR

* **Estado:** Aceptado
* **Fecha:** 2026-10-22
* **Autor:** OpenCode Agent

## Contexto

El proyecto es un trabajo práctico universitario sobre arquitectura web en Laravel. Durante su desarrollo se toman decisiones que no quedan solamente en el código: por qué el precio es un entero, por qué el carrito vive en la sesión, por qué no se usan "cache tags".

La primera versión del registro fue un archivo de texto plano (`DECISIONES_DISENO.txt`) con un historial append-only. Funcionaba, pero tenía las siguientes limitaciones:

- No es un formato estándar: ningún visor ni reviewer espera un ADR en `.txt`.
- Se mezclaban dos cosas distintas en un mismo archivo: la bitácora de sesión (qué se hizo) y el registro de decisiones (por qué se eligió algo).
- No había forma de marcar una decisión como *reemplazada*: sólo quedaba un párrafo nuevo que desmentía al anterior.
- El índice era lineal y crecía sin estructura de consulta.

Alternativas evaluadas: Wiki externa (fuera del repositorio, se pierde al versionar), issues de GitHub (no versionan junto al código), comentarios en el código (se pierden en un refactor).

## Decisión

Adoptar el formato **ADR (Architecture Decision Records)** en Markdown:

- Carpeta `docs/adr/` en la raíz del repositorio.
- Un archivo por decisión, con nombre correlativo `NNNN-nombre-decision.md` (4 dígitos, kebab-case en español).
- Plantilla estricta y obligatoria:

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

- `docs/adr/README.md` es el índice: una tabla con número, título, estado, fecha y enlace a cada ADR.
- El índice se actualiza en el mismo commit en que se agrega un ADR.
- `DECISIONES_DISENO.txt` **se conserva** como bitácora de sesión (qué se hizo en cada tarea) y apunta al ADR correspondiente. Deja de ser la fuente de verdad de las decisiones técnicas.

Regla permanente para los agentes: toda decisión técnica relevante (modelado de datos, librerías, patrones de diseño, estrategias de caché, filtros, SEO) se registra como un ADR nuevo en `docs/adr/` y se agrega al índice.

## Consecuencias

### Positivas

- Formato estándar y reconocible: cualquier docente o revisor puede leer, comparar y citar las decisiones sin contexto adicional.
- Cada decisión queda aislada en un archivo con su contexto y sus consecuencias; se puede marcar como *Reemplazado* sin reescribir la historia.
- El índice permite responder "¿por qué hicimos esto?" en segundos, sin leer el código.
- Versionado junto al código: `git blame` sobre `docs/adr/` muestra quién cambió de opinión y cuándo.

### Limitaciones / Riesgos

- Dos fuentes de información (ADR + bitácora) que pueden divergir si se actualiza una y no la otra. Mitigación: el ADR es la fuente de verdad de *por qué*; la bitácora sólo registra *qué se hizo*.
- La plantilla es rígida: decisiones muy chicas no merecen un ADR y se anotan directamente en la bitácora.
- El índice es un archivo que se desactualiza con facilidad. Mitigación: la regla exige actualizarlo junto con cada ADR nuevo.
