<?php

it('returns a successful response', function () {
    $response = $this->get('/');

    // La portada redirige al catálogo, que es la URL canónica del contenido.
    $response->assertRedirect(route('productos.index'));

    $this->get(route('productos.index'))->assertOk();
});
