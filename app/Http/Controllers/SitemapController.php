<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;

/**
 * Sitemap XML del catálogo.
 */
class SitemapController extends Controller
{
    /**
     * Devuelve el sitemap con las URLs indexables del catálogo.
     */
    public function __invoke(SitemapService $sitemap): Response
    {
        return response($sitemap->toXml(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
