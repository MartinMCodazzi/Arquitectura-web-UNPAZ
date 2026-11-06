<?php

namespace App\DTO;

use App\Models\Category;

/**
 * Categoría reducida a los datos que necesita el catálogo.
 *
 * Existe porque la caché de Laravel 13 no deserializa clases PHP
 * (`cache.serializable_classes => false`): lo que se guarda en caché son
 * arrays planos y los objetos se rehidratan en memoria (ver ADR-0006).
 */
final readonly class CategoryOption
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
    ) {}

    /**
     * Construye la opción a partir del modelo.
     */
    public static function fromModel(Category $category): self
    {
        return new self(
            id: $category->id,
            name: $category->name,
            slug: $category->slug,
        );
    }

    /**
     * Reconstruye la opción a partir de su versión serializable.
     *
     * @param  array{id: int, name: string, slug: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            name: (string) $data['name'],
            slug: (string) $data['slug'],
        );
    }

    /**
     * Versión serializable de la opción (datos planos).
     *
     * @return array{id: int, name: string, slug: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
