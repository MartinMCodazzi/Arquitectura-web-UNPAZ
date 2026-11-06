<?php

namespace App\DTO;

use App\Models\Category;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Support\Number;

/**
 * Resumen de producto para el listado del catálogo.
 *
 * Es el objeto que consume la vista del catálogo. Cuando el listado sale de la
 * caché hay que rehidratar un array plano (ADR-0006) y cuando sale de la base
 * se convierte el modelo Eloquent: en los dos casos la vista recibe un
 * `ProductSummary`.
 *
 * Por eso expone los mismos atributos públicos que el modelo (`name`,
 * `formatted_price`, `is_in_stock`, `image_url`, `category`) y el método
 * `thumbnailUrl()`: la card del catálogo funciona con las dos formas.
 */
final readonly class ProductSummary
{
    /**
     * Precio formateado en pesos (por ejemplo, `$ 18.900,00`).
     */
    public string $formatted_price;

    /**
     * ¿El producto tiene existencias?
     */
    public bool $is_in_stock;

    /**
     * URL de la miniatura del producto (400 px).
     */
    public ?string $image_url;

    /**
     * @param  list<string>  $image_formats  Formatos publicados de la miniatura.
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public int $price,
        public int $stock,
        public ?CategoryOption $category = null,
        public ?string $image_version = null,
        public array $image_formats = [],
    ) {
        $this->formatted_price = Number::currency(
            $this->price / 100,
            config('store.currency'),
            config('store.locale'),
        );

        $this->is_in_stock = $this->stock > 0;

        $this->image_url = $this->thumbnailUrl(400, 'webp');
    }

    /**
     * Construye el resumen a partir del modelo.
     */
    public static function fromModel(Product $product): self
    {
        $images = app(ImageService::class);
        $thumbnails = $images->thumbnailInfo($product);

        return new self(
            id: $product->id,
            name: $product->name,
            slug: $product->slug,
            price: $product->price,
            stock: $product->stock,
            category: $product->category instanceof Category
                ? CategoryOption::fromModel($product->category)
                : null,
            image_version: $thumbnails['version'] ?? null,
            image_formats: $thumbnails['formats'] ?? [],
        );
    }

    /**
     * Reconstruye el resumen a partir de su versión serializable.
     *
     * @param  array{id: int, name: string, slug: string, price: int, stock: int, category: array{id: int, name: string, slug: string}|null, image_version: string|null, image_formats: list<string>}  $data
     */
    public static function fromArray(array $data): self
    {
        $category = $data['category'] ?? null;

        return new self(
            id: (int) $data['id'],
            name: (string) $data['name'],
            slug: (string) $data['slug'],
            price: (int) $data['price'],
            stock: (int) $data['stock'],
            category: is_array($category) ? CategoryOption::fromArray($category) : null,
            image_version: $data['image_version'] ?? null,
            image_formats: (array) ($data['image_formats'] ?? []),
        );
    }

    /**
     * Versión serializable del resumen (datos planos, sin objetos PHP).
     *
     * @return array{id: int, name: string, slug: string, price: int, stock: int, category: array{id: int, name: string, slug: string}|null, image_version: string|null, image_formats: list<string>}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'stock' => $this->stock,
            'category' => $this->category?->toArray(),
            'image_version' => $this->image_version,
            'image_formats' => $this->image_formats,
        ];
    }

    /**
     * URL de la miniatura del producto en el ancho y formato pedidos.
     *
     * Devuelve `null` si el producto no tiene miniatura publicada en ese
     * formato: en ese caso la vista usa el texto alternativo.
     */
    public function thumbnailUrl(int $width = 400, string $format = 'webp'): ?string
    {
        if ($this->image_version === null || ! in_array($format, $this->image_formats, true)) {
            return null;
        }

        return route('imagenes.show', [
            'path' => sprintf(
                'products/%d/%d-%s.%s',
                $this->id,
                $width,
                $this->image_version,
                $format,
            ),
        ]);
    }
}
