---
paths:
  - '**'
---

# General

## Documentar el trabajo de cada tarea en DECISIONES_DISENO.txt
REGLA MANDATORIA: al cerrar cada tarea o cambio relevante se anota en DECISIONES_DISENO.txt (raíz del repositorio) qué se hizo y por qué, con el formato Contexto / Decisión / Motivación técnica / Alternativas descartadas. El archivo es append-only: no se editan ni borran secciones previas. A partir del ADR-0001 este archivo es la BITÁCORA DE SESIÓN; la fuente de verdad de las decisiones técnicas es docs/adr/ (ver la regla siguiente).

## Registrar decisiones de arquitectura como ADR en docs/adr/
REGLA PERSISTENTE: toda decisión técnica relevante (modelado de datos, librerías, patrones de diseño, estrategias de caché, filtros, SEO) DEBE registrarse como un nuevo archivo .md dentro de docs/adr/ con formato correlativo (0001-nombre-decision.md) y actualizarse en el índice docs/adr/README.md. Cada ADR usa la plantilla estricta: encabezado con Estado/Fecha/Autor, secciones Contexto, Decisión y Consecuencias (Positivas / Limitaciones-Riesgos). Los ADR son append-only: una decisión que cambia se marca como Reemplazado y se crea uno nuevo. DECISIONES_DISENO.txt queda como bitácora de sesión, no como fuente de verdad de decisiones.

## Reactividad del sitio con Livewire
REGLA PERSISTENTE: toda interactividad dinámica del sitio (catálogo, filtros, carrito, contador del header, mensajes, paginado de listados) se implementa con Livewire 3: componentes class-based en app/Livewire con vistas Blade en resources/views/livewire. No se introduce JavaScript ad-hoc ni frameworks SPA; Alpine.js se admite sólo para detalles de UI local. La lógica de negocio no vive en el componente: se delega a los servicios existentes (p. ej. CartService), y la sesión/caché siguen sus ADR respectivos.

## Componentes reutilizables antes de crear vistas nuevas
REGLA PERSISTENTE: antes de escribir una vista, component o partial nuevo, revisar resources/views/components, resources/views/livewire y app/View/Components. Las piezas que se repitan (cards, precios, badges, alertas, formularios) se extraen a componentes Blade con props tipadas y documentadas (o componentes Livewire reutilizables) en vez de copiar markup entre vistas: los parciales @include a mano se consideran legado que se migra al primer toque.

## No correr tests automáticamente
REGLA PERSISTENTE: los tests se escriben (o actualizan) como parte del trabajo, pero NUNCA se ejecutan automáticamente al terminar de programarlos. Al devolver el resultado de una tarea se informa explícitamente al usuario que los tests NO se corrieron y que puede ejecutarlos a mano (php artisan test). Sólo se corren tests cuando el usuario lo pide de manera explícita. Esta regla sobreescribe la instrucción genérica de AGENTS.md de "run the affected tests".
