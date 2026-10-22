<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

/**
 * Catálogo de productos de demostración.
 *
 * Los precios están expresados en CÉNTIMOS de pesos argentinos (ARS), igual
 * que la columna `price` de la tabla `products`.
 *
 * El seeder es idempotente: usa updateOrCreate sobre el slug del producto.
 */
class ProductSeeder extends Seeder
{
    /**
     * @var list<array<string, mixed>>
     */
    private const PRODUCTS = [
        // ------------------------------------------------------------------
        // Electrónica
        // ------------------------------------------------------------------
        [
            'name' => 'Auriculares Bluetooth Aurora Pro',
            'slug' => 'auriculares-bluetooth-aurora-pro',
            'category' => 'electronica',
            'description' => 'Cancelación activa de ruido, 30 horas de reproducción y estuche de carga compacto.',
            'price' => 8_999_000,
            'stock' => 24,
            'is_active' => true,
        ],
        [
            'name' => 'Smartwatch Pulse X2',
            'slug' => 'smartwatch-pulse-x2',
            'category' => 'electronica',
            'description' => 'GPS integrado, medición continua del ritmo cardíaco y pantalla AMOLED de 1,9 pulgadas.',
            'price' => 24_999_000,
            'stock' => 12,
            'is_active' => true,
        ],
        [
            'name' => 'Notebook Vega 14 pulgadas',
            'slug' => 'notebook-vega-14-pulgadas',
            'category' => 'electronica',
            'description' => 'Procesador i5 de duodécima generación, 16 GB de RAM y SSD de 512 GB.',
            'price' => 129_999_000,
            'stock' => 5,
            'is_active' => true,
        ],
        [
            'name' => 'Mouse inalámbrico Glide',
            'slug' => 'mouse-inalambrico-glide',
            'category' => 'electronica',
            'description' => 'Conexión silenciosa por Bluetooth y batería recargable de hasta 70 días.',
            'price' => 2_450_000,
            'stock' => 40,
            'is_active' => true,
        ],
        [
            'name' => 'Cable USB-C de 100W trenzado',
            'slug' => 'cable-usb-c-100w-trenzado',
            'category' => 'electronica',
            'description' => 'Trenzado de nylon, carga rápida de 100 W y longitud de 2 metros.',
            'price' => 1_290_000,
            'stock' => 80,
            'is_active' => true,
        ],
        [
            'name' => 'Power bank 20.000 mAh',
            'slug' => 'power-bank-20000-mah',
            'category' => 'electronica',
            'description' => 'Carga rápida bidireccional y tres puertos USB para usar en cualquier lugar.',
            'price' => 4_590_000,
            'stock' => 0,
            'is_active' => true,
        ],
        [
            'name' => 'Teclado mecánico Kobo',
            'slug' => 'teclado-mecanico-kobo',
            'category' => 'electronica',
            'description' => 'Switches lineales, iluminación RGB y conexión cableada desmontable.',
            'price' => 6_999_000,
            'stock' => 18,
            'is_active' => true,
        ],
        [
            'name' => 'Webcam Full HD Miro',
            'slug' => 'webcam-full-hd-miro',
            'category' => 'electronica',
            'description' => 'Enfoque automático, micrófono con reducción de ruido y soporte regulable.',
            'price' => 3_999_000,
            'stock' => 22,
            'is_active' => true,
        ],

        // ------------------------------------------------------------------
        // Hogar y Cocina
        // ------------------------------------------------------------------
        [
            'name' => 'Sartén de hierro fundido de 26 cm',
            'slug' => 'sarten-de-hierro-fundido-26-cm',
            'category' => 'hogar-y-cocina',
            'description' => 'Distribución uniforme del calor, ideal para sellar carnes y verduras.',
            'price' => 5_490_000,
            'stock' => 16,
            'is_active' => true,
        ],
        [
            'name' => 'Cafetera italiana Moka de 6 tazas',
            'slug' => 'cafetera-italiana-moka-6-tazas',
            'category' => 'hogar-y-cocina',
            'description' => 'Aluminio fundido con válvula de seguridad y base aislante térmica.',
            'price' => 3_250_000,
            'stock' => 35,
            'is_active' => true,
        ],
        [
            'name' => 'Set de cuchillos de acero inoxidable',
            'slug' => 'set-de-cuchillos-de-acero-inoxidable',
            'category' => 'hogar-y-cocina',
            'description' => 'Cinco piezas con mango de madera y hojas de 15 cm.',
            'price' => 4_790_000,
            'stock' => 14,
            'is_active' => true,
        ],
        [
            'name' => 'Organizador de cocina modular',
            'slug' => 'organizador-de-cocina-modular',
            'category' => 'hogar-y-cocina',
            'description' => 'Apilable y de fácil limpieza, ideal para ordenar alacenas y despenseros.',
            'price' => 2_890_000,
            'stock' => 27,
            'is_active' => true,
        ],
        [
            'name' => 'Termo de acero inoxidable de 1 litro',
            'slug' => 'termo-de-acero-inoxidable-1-litro',
            'category' => 'hogar-y-cocina',
            'description' => 'Doble pared de acero inoxidable: mantiene la bebida fría hasta 24 horas.',
            'price' => 7_990_000,
            'stock' => 9,
            'is_active' => true,
        ],
        [
            'name' => 'Alfombra de living reversible',
            'slug' => 'alfombra-de-living-reversible',
            'category' => 'hogar-y-cocina',
            'description' => 'Tela de polipropileno de alta resistencia y diseño reversible.',
            'price' => 11_990_000,
            'stock' => 6,
            'is_active' => true,
        ],
        [
            'name' => 'Lámpara de mesa Araña LED',
            'slug' => 'lampara-de-mesa-arana-led',
            'category' => 'hogar-y-cocina',
            'description' => 'Tres brazos regulables con luz cálida y temperatura ajustable.',
            'price' => 3_550_000,
            'stock' => 0,
            'is_active' => true,
        ],

        // ------------------------------------------------------------------
        // Moda
        // ------------------------------------------------------------------
        [
            'name' => 'Campera de denim unissex',
            'slug' => 'campera-de-denim-unisex',
            'category' => 'moda',
            'description' => 'Denim rígido de 12 oz con corte relajado y detalles en cobre.',
            'price' => 9_890_000,
            'stock' => 21,
            'is_active' => true,
        ],
        [
            'name' => 'Zapatillas urbanas Running 3',
            'slug' => 'zapatillas-urbanas-running-3',
            'category' => 'moda',
            'description' => 'Suela de goma con amortiguación y capellada de mesh transpirable.',
            'price' => 13_490_000,
            'stock' => 30,
            'is_active' => true,
        ],
        [
            'name' => 'Camisa de lino premium',
            'slug' => 'camisa-de-lino-premium',
            'category' => 'moda',
            'description' => 'Lino europeo de secado rápido, ideal para climas cálidos.',
            'price' => 6_490_000,
            'stock' => 25,
            'is_active' => true,
        ],
        [
            'name' => 'Bolso tote de lona',
            'slug' => 'bolso-tote-de-lona',
            'category' => 'moda',
            'description' => 'Lona de algodón reforzada con interior amplio y cierre de cremallera.',
            'price' => 4_450_000,
            'stock' => 33,
            'is_active' => true,
        ],
        [
            'name' => 'Bufanda de lana merino',
            'slug' => 'bufanda-de-lana-merino',
            'category' => 'moda',
            'description' => 'Punto fino tejido en Argentina, muy suave y abrigado.',
            'price' => 2_990_000,
            'stock' => 48,
            'is_active' => true,
        ],
        [
            'name' => 'Reloj clásico de acero',
            'slug' => 'reloj-clasico-de-acero',
            'category' => 'moda',
            'description' => 'Movimiento de quarts, caja de acero 316L y cristal de zafiro.',
            'price' => 18_990_000,
            'stock' => 11,
            'is_active' => false,
        ],
        [
            'name' => 'Gorra bordada Snapback',
            'slug' => 'gorra-bordada-snapback',
            'category' => 'moda',
            'description' => 'Frente regulable y bordado frontal de alta densidad.',
            'price' => 2_490_000,
            'stock' => 60,
            'is_active' => true,
        ],

        // ------------------------------------------------------------------
        // Deportes
        // ------------------------------------------------------------------
        [
            'name' => 'Bicicleta urbana Ruta 700',
            'slug' => 'bicicleta-urbana-ruta-700',
            'category' => 'deportes',
            'description' => 'Marco de aluminio, 21 velocidades y frenos de disco mecánico.',
            'price' => 74_999_000,
            'stock' => 4,
            'is_active' => true,
        ],
        [
            'name' => 'Set de mancuernas de 20 kg',
            'slug' => 'set-de-mancuernas-de-20-kg',
            'category' => 'deportes',
            'description' => 'Par de mancuernas con revestimiento de neopreno y barras ajustables.',
            'price' => 8_990_000,
            'stock' => 13,
            'is_active' => true,
        ],
        [
            'name' => 'Esterilla de yoga de 6 mm',
            'slug' => 'esterilla-de-yoga-de-6-mm',
            'category' => 'deportes',
            'description' => 'Superficie texturada antideslizante y material libre de ftalatos.',
            'price' => 3_490_000,
            'stock' => 29,
            'is_active' => true,
        ],
        [
            'name' => 'Zapatillas de running Trail X',
            'slug' => 'zapatillas-de-running-trail-x',
            'category' => 'deportes',
            'description' => 'Suela con tacos de agarre en terreno irregular y drop de 6 mm.',
            'price' => 15_990_000,
            'stock' => 17,
            'is_active' => true,
        ],
        [
            'name' => 'Botiquín de primeros auxilios de 12 piezas',
            'slug' => 'botiquin-de-primeros-auxilios-12-piezas',
            'category' => 'deportes',
            'description' => 'Incluye apósitos, vendas y elementos de curación en una bolsa práctica.',
            'price' => 2_790_000,
            'stock' => 45,
            'is_active' => true,
        ],
        [
            'name' => 'Pulsera de actividad Activity',
            'slug' => 'pulsera-de-actividad-activity',
            'category' => 'deportes',
            'description' => 'Mide pasos, calorías y sueño; resistente al agua hasta 50 metros.',
            'price' => 4_290_000,
            'stock' => 26,
            'is_active' => true,
        ],
        [
            'name' => 'Pelota de fútbol profesional',
            'slug' => 'pelota-de-futbol-profesional',
            'category' => 'deportes',
            'description' => 'Construcción termosellada con cámara de aire de alta retención.',
            'price' => 3_850_000,
            'stock' => 38,
            'is_active' => true,
        ],

        // ------------------------------------------------------------------
        // Libros
        // ------------------------------------------------------------------
        [
            'name' => 'Clean Architecture',
            'slug' => 'clean-architecture',
            'category' => 'libros',
            'description' => 'Un manual sobre límites del software y diseño de sistemas que envejecen bien.',
            'price' => 4_590_000,
            'stock' => 19,
            'is_active' => true,
        ],
        [
            'name' => 'Diseño de interfaces',
            'slug' => 'diseno-de-interfaces',
            'category' => 'libros',
            'description' => 'Principios de diseño visual, jerarquía y usabilidad aplicados a productos digitales.',
            'price' => 5_200_000,
            'stock' => 15,
            'is_active' => true,
        ],
        [
            'name' => 'Cien años de soledad',
            'slug' => 'cien-anos-de-soledad',
            'category' => 'libros',
            'description' => 'Edición de bolsillo con prólogo y cronología de la obra.',
            'price' => 1_890_000,
            'stock' => 52,
            'is_active' => true,
        ],
        [
            'name' => 'Programación en PHP 8',
            'slug' => 'programacion-en-php-8',
            'category' => 'libros',
            'description' => 'Guía completa con tipado estricto, enums, fibers y testing.',
            'price' => 6_890_000,
            'stock' => 10,
            'is_active' => true,
        ],
        [
            'name' => 'Psicología de las decisiones',
            'slug' => 'psicologia-de-las-decisiones',
            'category' => 'libros',
            'description' => 'Cómo los sesgos cognitivos influyen en las elecciones cotidianas.',
            'price' => 3_990_000,
            'stock' => 23,
            'is_active' => true,
        ],
        [
            'name' => 'Cocina argentina de estación',
            'slug' => 'cocina-argentina-de-estacion',
            'category' => 'libros',
            'description' => 'Recetas tradicionales con ingredientes de estación y técnica profesional.',
            'price' => 7_450_000,
            'stock' => 8,
            'is_active' => true,
        ],

        // ------------------------------------------------------------------
        // Juguetes
        // ------------------------------------------------------------------
        [
            'name' => 'Rompecabezas de 1000 piezas',
            'slug' => 'rompecabezas-de-1000-piezas',
            'category' => 'juguetes',
            'description' => 'Piezas de cartón resistente con imagen en alta definición.',
            'price' => 2_490_000,
            'stock' => 42,
            'is_active' => true,
        ],
        [
            'name' => 'Kit de experimentos científicos',
            'slug' => 'kit-de-experimentos-cientificos',
            'category' => 'juguetes',
            'description' => 'Más de 30 experimentos seguros para aprender en casa.',
            'price' => 5_690_000,
            'stock' => 20,
            'is_active' => true,
        ],
        [
            'name' => 'Pelota antiestrés',
            'slug' => 'pelota-antiestres',
            'category' => 'juguetes',
            'description' => 'Espuma viscoelástica con textura suave y colores surtidos.',
            'price' => 1_250_000,
            'stock' => 75,
            'is_active' => true,
        ],
        [
            'name' => 'Auto a control remoto 4x4',
            'slug' => 'auto-a-control-remoto-4x4',
            'category' => 'juguetes',
            'description' => 'Tracción integral, luces LED y control de 2,4 GHz.',
            'price' => 8_990_000,
            'stock' => 7,
            'is_active' => true,
        ],
        [
            'name' => 'Juego de mesa Catan',
            'slug' => 'juego-de-mesa-catan',
            'category' => 'juguetes',
            'description' => 'Edición en español con tableros de fieltro y fichas de madera.',
            'price' => 7_990_000,
            'stock' => 11,
            'is_active' => true,
        ],

        // ------------------------------------------------------------------
        // Belleza y Cuidado Personal
        // ------------------------------------------------------------------
        [
            'name' => 'Crema facial hidratante',
            'slug' => 'crema-facial-hidratante',
            'category' => 'belleza-y-cuidado-personal',
            'description' => 'Ácido hialurónico y ceramidas para reforzar la barrera cutánea.',
            'price' => 3_290_000,
            'stock' => 36,
            'is_active' => true,
        ],
        [
            'name' => 'Protector solar FPS 50',
            'slug' => 'protector-solar-fps-50',
            'category' => 'belleza-y-cuidado-personal',
            'description' => 'Filtro solar de amplio espectro, resistente al agua y de rápida absorción.',
            'price' => 2_840_000,
            'stock' => 50,
            'is_active' => true,
        ],
        [
            'name' => 'Champú natural sin sulfatos',
            'slug' => 'champu-natural-sin-sulfatos',
            'category' => 'belleza-y-cuidado-personal',
            'description' => 'Tensioactivos de origen vegetal y activos de argán.',
            'price' => 2_190_000,
            'stock' => 44,
            'is_active' => true,
        ],
        [
            'name' => 'Perfume eau de toilette de 100 ml',
            'slug' => 'perfume-eau-de-toilette-100-ml',
            'category' => 'belleza-y-cuidado-personal',
            'description' => 'Aromas cítricas y florales con base de cedro y vetiver.',
            'price' => 11_990_000,
            'stock' => 13,
            'is_active' => true,
        ],
        [
            'name' => 'Set de hidratación facial',
            'slug' => 'set-de-hidratacion-facial',
            'category' => 'belleza-y-cuidado-personal',
            'description' => 'Tónico, sérum y crema en un solo kit para el cuidado diario.',
            'price' => 6_490_000,
            'stock' => 16,
            'is_active' => true,
        ],
        [
            'name' => 'Cepillo de madera para el cabello',
            'slug' => 'cepillo-de-madera-para-el-cabello',
            'category' => 'belleza-y-cuidado-personal',
            'description' => 'Cerdas de ilustración natural que no desenredan el cabello.',
            'price' => 1_890_000,
            'stock' => 58,
            'is_active' => true,
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');

        foreach (self::PRODUCTS as $product) {
            $attributes = Arr::except($product, 'category');
            $attributes['category_id'] = $categories[$product['category']]->id;

            Product::updateOrCreate(
                ['slug' => $product['slug']],
                $attributes,
            );
        }
    }
}
