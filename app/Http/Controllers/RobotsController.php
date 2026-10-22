<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Archivo robots.txt del sitio.
 */
class RobotsController extends Controller
{
    /**
     * Declara los robots de búsqueda y apunta al sitemap.
     */
    public function __invoke(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
