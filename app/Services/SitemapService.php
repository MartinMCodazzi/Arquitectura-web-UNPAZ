<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Support\CatalogCache;
use Illuminate\Support\Facades\Cache;

/**
 * Genera el sitemap XML del catálogo.
 *
 * El sitemap se arma con los datos del catálogo y se cachea con la versión
 * vigente: cuando un producto o una categoría cambian, el observador
 * invalida la caché del catálogo y el sitemap se regenera solo.
 */
class SitemapService
{
    /**
     * Tiempo de vida del sitemap (segundos).
     */
    public const TTL = 3600;

    /**
     * Contenido XML del sitemap.
     */
    public function toXml(): string
    {
        return Cache::remember(
            'catalogo.sitemap.v'.CatalogCache::version(),
            self::TTL,
            fn (): string => $this->build(),
        );
    }

    /**
     * Construye el sitemap con las URLs indexables del catálogo.
     */
    private function build(): string
    {
        $urls = [
            [
                'loc' => route('productos.index'),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
        ];

        foreach (Category::query()->active()->orderBy('name')->get() as $category) {
            $urls[] = [
                'loc' => route('productos.index', ['categoria' => $category->slug]),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        foreach (Product::query()->active()->orderBy('id')->get() as $product) {
            $urls[] = [
                'loc' => route('productos.show', $product->slug),
                'lastmod' => $product->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }

        return $this->toXmlString($urls);
    }

    /**
     * Serializa las URLs como un documento XML.
     *
     * @param  list<array{loc: string, lastmod?: string|null, changefreq: string, priority: string}>  $urls
     */
    private function toXmlString(array $urls): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($urls as $url) {
            $lines[] = '    <url>';
            $lines[] = '        <loc>'.e($url['loc']).'</loc>';

            if (! empty($url['lastmod'])) {
                $lines[] = '        <lastmod>'.e($url['lastmod']).'</lastmod>';
            }

            $lines[] = '        <changefreq>'.$url['changefreq'].'</changefreq>';
            $lines[] = '        <priority>'.$url['priority'].'</priority>';
            $lines[] = '    </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines)."\n";
    }
}
