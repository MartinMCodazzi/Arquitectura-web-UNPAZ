# ADR-0019: Catálogo exclusivo gamer y corrección en la entrega de imágenes

* **Estado:** Aceptado
* **Fecha:** 2026-11-04
* **Autor:** Martin Munoz Codazzi

## Contexto

Tras el rediseño estético gamer (ADR-0018), el catálogo base de datos aún conservaba las categorías genéricas de una tienda departamental (`Hogar y Cocina`, `Moda`, `Deportes`, `Libros`, `Juguetes`, `Belleza y Cuidado Personal`) y productos ajenos a la tecnología (perfumes, indumentaria, vajilla).

Asimismo, las imágenes de producto presentaban dos inconvenientes críticos:
1. En Windows, `BinaryFileResponse` / Symfony MimeTypes no infiere de forma nativa los tipos MIME `image/webp` ni `image/avif` desde el registro del sistema, provocando que `ImageController` devolviera respuestas sin cabecera `Content-Type`, lo que ocasionaba que los navegadores rechazaran o mostrasen rotas las imágenes.
2. Los datos de demostración originales generaban cuadros abstractos monocromáticos con texto plano en lugar de fotografías reales de hardware y periféricos.

## Decisión

1. **Especialización 100% Gamer y Tecnología:**
   - Se reestructuraron las categorías en `CategorySeeder` a 6 categorías representativas: `Componentes de PC` (`componentes-pc`), `Periféricos Gamer` (`perifericos-gamer`), `Monitores Gamer` (`monitores-gamer`), `Notebooks & PCs Gamer` (`notebooks-y-pcs`), `Consolas y Videojuegos` (`consolas-y-gaming`), y `Sillas y Setup Gamer` (`sillas-y-setup`).
   - Se renovó `ProductSeeder` con 24 productos reales de gaming y tecnología (placas RTX 4070, procesadores Ryzen 7 7800X3D, periféricos mecánicos e inalámbricos, consolas PS5/Xbox/Switch, monitores 144Hz/240Hz, notebooks y escritorios gamer) con precios íntegros en céntimos de ARS y existencias reales.
   - Se purgaron de la base de datos todas las categorías y productos antiguos no tecnológicos.

2. **Entrega y formato de imágenes de producto:**
   - En `ImageController`, se agregó la determinación explícita de la cabecera `Content-Type` (`image/webp`, `image/avif`, `image/jpeg`, `image/png`) según la extensión del archivo servido, garantizando compatibilidad multiplataforma tanto en Windows como en entornos Linux de producción.
   - En `ProductSeeder` y `GenerateProductImages`, se incorporó la obtención de fotografías reales de alta resolución para cada producto del catálogo, procesadas por `ImageService` para generar automáticamente sus miniaturas optimizadas en 200px, 400px y 800px (WebP y AVIF).
   - En `resources/views/products/show.blade.php`, se adaptó el contenedor de imagen a la paleta dark (`bg-gray-950 border border-gray-800`) y `object-contain` para que los productos no sufran recortes visuales.

## Consecuencias

### Positivas
- Catálogo 100% coherente con la identidad gamer de la tienda.
- Las imágenes cargan correctamente en todos los navegadores con su MIME type exacto.
- Las imágenes de productos ahora son fotografías reales y nítidas de hardware y periféricos.
- Se mantiene total compatibilidad con los tests y las reglas de diseño preexistentes.
