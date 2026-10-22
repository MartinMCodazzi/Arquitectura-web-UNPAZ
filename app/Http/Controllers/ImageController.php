<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Sirve las miniaturas de producto con cabeceras de cacheo largas.
 *
 * Las miniaturas se publican en el disco `public` pero se sirven por esta
 * ruta, en lugar de como archivos estáticos, para poder enviar un
 * `Cache-Control` explícito: el nombre del archivo incluye la versión de la
 * imagen original, así que la URL cambia cuando la imagen cambia y la
 * miniatura puede cachearse durante un año sin riesgo de servir una versión
 * vieja (ver ADR-0007).
 */
class ImageController extends Controller
{
    /**
     * Devuelve la miniatura solicitada.
     */
    public function show(string $path): BinaryFileResponse
    {
        $path = $this->normalize($path);

        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Cache-Control' => 'public, max-age='.(int) config('store.image_cache_seconds').', immutable',
        ]);
    }

    /**
     * Normaliza la ruta solicitada y evita el path traversal.
     */
    private function normalize(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        abort_if($path === '' || str_contains($path, '..'), 404);

        return $path;
    }
}
