<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

/**
 * Catálogo de productos gamer y tecnología.
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
    public const PRODUCTS = [
        // ------------------------------------------------------------------
        // Componentes de PC
        // ------------------------------------------------------------------
        [
            'name' => 'Placa de Video NVIDIA GeForce RTX 4070 Super 12GB',
            'slug' => 'placa-de-video-geforce-rtx-4070-super-12gb',
            'category' => 'componentes-pc',
            'description' => 'Arquitectura Ada Lovelace, 12 GB GDDR6X, DLSS 3 y Ray Tracing en tiempo real para jugar en 1440p y 4K con máxima fluidez.',
            'price' => 849_999_00,
            'stock' => 8,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1587202372634-32705e3bf49c?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Procesador AMD Ryzen 7 7800X3D 8 Cores 5.0GHz',
            'slug' => 'procesador-amd-ryzen-7-7800x3d',
            'category' => 'componentes-pc',
            'description' => 'El rey del rendimiento en videojuegos con tecnología 3D V-Cache, 8 núcleos, 16 hilos y socket AM5 de última generación.',
            'price' => 589_000_00,
            'stock' => 12,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1555680202-c86f0e12f086?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Memoria RAM Corsair Vengeance RGB DDR5 32GB (2x16GB) 6000MHz',
            'slug' => 'memoria-ram-corsair-vengeance-rgb-ddr5-32gb',
            'category' => 'componentes-pc',
            'description' => 'Kit dual channel de 32 GB DDR5 a 6000 MHz con disipador de aluminio negro e iluminación RGB direccionable con iCUE.',
            'price' => 189_500_00,
            'stock' => 25,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1562976540-1502c2145186?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Disco SSD NVMe M.2 Kingston KC3000 2TB PCIe 4.0',
            'slug' => 'ssd-nvme-kingston-kc3000-2tb',
            'category' => 'componentes-pc',
            'description' => 'Velocidades extremas de lectura y escritura de hasta 7.000 MB/s con controlador Phison E18 y disipador de grafeno.',
            'price' => 210_000_00,
            'stock' => 18,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Fuente de Poder Corsair RM850x 850W 80 Plus Gold Modular',
            'slug' => 'fuente-corsair-rm850x-850w-gold',
            'category' => 'componentes-pc',
            'description' => 'Certificación 80 PLUS Gold, cableado totalmente modular, capacitores japoneses a 105 °C y ventilador silencioso Zero RPM.',
            'price' => 225_000_00,
            'stock' => 10,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&w=800&q=80',
        ],

        // ------------------------------------------------------------------
        // Periféricos Gamer
        // ------------------------------------------------------------------
        [
            'name' => 'Teclado Mecánico HyperX Alloy Origins RGB Switches Red',
            'slug' => 'teclado-mecanico-hyperx-alloy-origins-rgb',
            'category' => 'perifericos-gamer',
            'description' => 'Cuerpo íntegramente de aluminio de grado aeronáutico, switches mecánicos HyperX Red lineales y retroiluminación RGB dinámica.',
            'price' => 125_000_00,
            'stock' => 15,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Mouse Gamer Inalámbrico Logitech G Pro X Superlight 2',
            'slug' => 'mouse-logitech-g-pro-x-superlight-2',
            'category' => 'perifericos-gamer',
            'description' => 'Sensor HERO 2 de 32.000 DPI, menos de 60 gramos de peso, switches óptico-mecánicos LIGHTFORCE y conectividad inalámbrica LIGHTSPEED.',
            'price' => 179_900_00,
            'stock' => 20,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Auriculares Gamer HyperX Cloud Alpha Wireless 300hs',
            'slug' => 'auriculares-hyperx-cloud-alpha-wireless',
            'category' => 'perifericos-gamer',
            'description' => 'Increíble autonomía de hasta 300 horas de batería, audio espacial DTS Headphone:X y transductores de doble cámara de 50 mm.',
            'price' => 249_000_00,
            'stock' => 14,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Mousepad Gamer SteelSeries QcK Prism Cloth XL RGB',
            'slug' => 'mousepad-steelseries-qck-prism-xl',
            'category' => 'perifericos-gamer',
            'description' => 'Superficie de tela microtejida exclusiva para un deslizamiento veloz y preciso, base antideslizante e iluminación RGB personalizable.',
            'price' => 49_990_00,
            'stock' => 30,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Micrófono USB Gamer Rode X XCM-50 Condenser',
            'slug' => 'microfono-usb-rode-x-xcm-50',
            'category' => 'perifericos-gamer',
            'description' => 'Cápsula de condensador premium para streaming y gaming, procesamiento DSP interno por hardware y conexión digital USB-C sin latencia.',
            'price' => 165_000_00,
            'stock' => 7,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?auto=format&fit=crop&w=800&q=80',
        ],

        // ------------------------------------------------------------------
        // Monitores Gamer
        // ------------------------------------------------------------------
        [
            'name' => 'Monitor Gamer ASUS TUF Gaming 27\'\' 165Hz IPS QHD',
            'slug' => 'monitor-asus-tuf-gaming-27-165hz',
            'category' => 'monitores-gamer',
            'description' => 'Resolución 2560x1440 2K, panel Fast IPS de 1 ms, 165 Hz, compatibilidad con G-Sync y tecnología antidesenfoque ELMB Sync.',
            'price' => 430_000_00,
            'stock' => 9,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Monitor Gamer Curvo Samsung Odyssey G5 32\'\' 165Hz 1000R',
            'slug' => 'monitor-samsung-odyssey-g5-32-curvo',
            'category' => 'monitores-gamer',
            'description' => 'Curvatura 1000R ultraenvolvente adaptada al ojo humano, refresco de 165 Hz, 1 ms de respuesta y resolución WQHD HDR10.',
            'price' => 489_000_00,
            'stock' => 6,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1547082299-de196ea013d6?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Monitor Gamer BenQ ZOWIE XL2546K 24.5\'\' 240Hz eSports',
            'slug' => 'monitor-benq-zowie-xl2546k-240hz',
            'category' => 'monitores-gamer',
            'description' => 'El monitor estándar en competencias mundiales: tecnología DyAc⁺ de máxima nitidez en movimientos rápidos, 240 Hz nativos y base regulable.',
            'price' => 620_000_00,
            'stock' => 5,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=800&q=80',
        ],

        // ------------------------------------------------------------------
        // Notebooks & PCs Gamer
        // ------------------------------------------------------------------
        [
            'name' => 'Notebook Gamer Lenovo Legion Pro 5 16\'\' RTX 4060 i7 14th',
            'slug' => 'notebook-lenovo-legion-pro-5-rtx-4060',
            'category' => 'notebooks-y-pcs',
            'description' => 'Pantalla WQXGA 240Hz de 16 pulgadas, procesador Intel Core i7-14700HX, 32 GB RAM DDR5, 1 TB SSD y GPU NVIDIA GeForce RTX 4060 8GB.',
            'price' => 1_899_000_00,
            'stock' => 4,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Notebook Gamer ASUS ROG Zephyrus G14 OLED Ryzen 9 RTX 4070',
            'slug' => 'notebook-asus-rog-zephyrus-g14-oled',
            'category' => 'notebooks-y-pcs',
            'description' => 'Chasis de aluminio mecanizado CNC ultraliviano, pantalla ROG Nebula OLED 3K 120Hz, procesador AMD Ryzen 9 8945HS y RTX 4070.',
            'price' => 2_450_000_00,
            'stock' => 3,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'PC Armada Gamer Ultra RTX 4070Ti Super / Ryzen 7 7800X3D',
            'slug' => 'pc-armada-gamer-ultra-rtx-4070ti-super',
            'category' => 'notebooks-y-pcs',
            'description' => 'PC de escritorio de gama alta armada y testeada para jugar en 4K y streamear. Refrigeración líquida 360mm RGB, 32GB RAM DDR5 y 2TB SSD NVMe.',
            'price' => 2_750_000_00,
            'stock' => 3,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1593640408182-31c70c8268f5?auto=format&fit=crop&w=800&q=80',
        ],

        // ------------------------------------------------------------------
        // Consolas y Videojuegos
        // ------------------------------------------------------------------
        [
            'name' => 'Consola Sony PlayStation 5 Slim 1TB Edición Estándar',
            'slug' => 'consola-sony-playstation-5-slim-1tb',
            'category' => 'consolas-y-gaming',
            'description' => 'Nuevo diseño estilizado con lector de discos Ultra HD Blu-ray, disco SSD ultrarrápido de 1 TB, audio 3D y gatillos adaptativos.',
            'price' => 980_000_00,
            'stock' => 11,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Joystick Inalámbrico Sony DualSense Blanco PS5 / PC',
            'slug' => 'joystick-sony-dualsense-blanco-ps5',
            'category' => 'consolas-y-gaming',
            'description' => 'Gatillos adaptativos con tensión dinámica según el juego, retroalimentación háptica inmersiva, panel táctil y micrófono integrado.',
            'price' => 115_000_00,
            'stock' => 22,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1600080972464-8e5f35f63d08?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Consola Microsoft Xbox Series X 1TB Negra',
            'slug' => 'consola-xbox-series-x-1tb',
            'category' => 'consolas-y-gaming',
            'description' => 'La consola más potente de Microsoft con 12 teraflops de potencia gráfica, arquitectura Xbox Velocity, Quick Resume y 4K a 120 FPS.',
            'price' => 1_050_000_00,
            'stock' => 5,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1621259182978-fbf93132d53d?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Consola Nintendo Switch OLED 64GB Edición Neón',
            'slug' => 'consola-nintendo-switch-oled-neon',
            'category' => 'consolas-y-gaming',
            'description' => 'Pantalla OLED vibrante de 7 pulgadas con colores intensos y alto contraste, base con puerto LAN por cable y 64 GB de almacenamiento.',
            'price' => 599_000_00,
            'stock' => 15,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1578303512597-81e6cc155b3e?auto=format&fit=crop&w=800&q=80',
        ],

        // ------------------------------------------------------------------
        // Sillas y Setup Gamer
        // ------------------------------------------------------------------
        [
            'name' => 'Silla Gamer Ergonómica Cougar Armor Titan Pro Royal',
            'slug' => 'silla-gamer-cougar-armor-titan-pro',
            'category' => 'sillas-y-setup',
            'description' => 'Estructura de acero reforzada de alta resistencia, tapizado en gamuza premium transpirable, soporte lumbar 3D y reposabrazos 4D.',
            'price' => 495_000_00,
            'stock' => 7,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1598550476439-6847785fcea6?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Escritorio Gamer Ergonómico ThunderX3 ED7 Eléctrico',
            'slug' => 'escritorio-gamer-thunderx3-ed7',
            'category' => 'sillas-y-setup',
            'description' => 'Ajuste de altura eléctrico motorizado con memoria programable, alfombrilla XXL impermeable integrada y organizador de cables oculto.',
            'price' => 410_000_00,
            'stock' => 4,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1598550476439-6847785fcea6?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Brazo Articulado a Gas para Doble Monitor Gamer 17\'\' a 32\'\'',
            'slug' => 'brazo-articulado-doble-monitor-gamer',
            'category' => 'sillas-y-setup',
            'description' => 'Sistema de resorte a gas de movimiento fluido en 360°, compatibilidad VESA 75/100, puertos USB 3.0 frontales en la base.',
            'price' => 92_000_00,
            'stock' => 16,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1586775490184-b79f0621891f?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'name' => 'Luz de Fondo RGB para Monitor Barra Gamer Screenbar',
            'slug' => 'barra-luz-rgb-monitor-gamer-screenbar',
            'category' => 'sillas-y-setup',
            'description' => 'Iluminación trasera RGB reactiva y barra de luz frontal asimétrica antifatiga visual, control táctil y alimentación USB.',
            'price' => 45_000_00,
            'stock' => 28,
            'is_active' => true,
            'image_url' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=800&q=80',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');
        $images = app(ImageService::class);
        $disk = Storage::disk('public');

        foreach (self::PRODUCTS as $productData) {
            $categorySlug = $productData['category'];
            if (! isset($categories[$categorySlug])) {
                continue;
            }

            $attributes = Arr::except($productData, ['category', 'image_url']);
            $attributes['category_id'] = $categories[$categorySlug]->id;

            /** @var Product $product */
            $product = Product::updateOrCreate(
                ['slug' => $productData['slug']],
                $attributes,
            );

            // Asegurar imagen original del producto
            $originalPath = sprintf('products/%d/original.jpg', $product->id);
            $fullOriginalPath = $disk->path($originalPath);

            if (! $disk->exists($originalPath) || filesize($fullOriginalPath) < 5000) {
                $disk->makeDirectory(dirname($originalPath));

                // Intentar descargar imagen real si está disponible
                $downloaded = false;
                if (! empty($productData['image_url'])) {
                    $context = stream_context_create([
                        'http' => [
                            'timeout' => 5,
                            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                        ],
                    ]);
                    $content = @file_get_contents($productData['image_url'], false, $context);
                    if ($content !== false && strlen($content) > 1000) {
                        file_put_contents($fullOriginalPath, $content);
                        $downloaded = true;
                    }
                }

                // Si no se pudo descargar, generar gráfico gamer limpio con GD
                if (! $downloaded) {
                    $this->createFallbackImage($fullOriginalPath, $product->name, $categories[$categorySlug]->name);
                }
            }

            if ($product->image_path !== $originalPath) {
                $product->update(['image_path' => $originalPath]);
            }

            // Generar miniaturas WebP y AVIF
            $images->generateThumbnails($product);
        }
    }

    /**
     * Genera una imagen estética estilo gamer en caso de no tener conexión.
     */
    private function createFallbackImage(string $target, string $title, string $category): void
    {
        $size = 800;
        $img = imagecreatetruecolor($size, $size);

        $darkBg = imagecolorallocate($img, 15, 23, 42); // slate-900
        imagefilledrectangle($img, 0, 0, $size, $size, $darkBg);

        // Acentos gamer
        $orange = imagecolorallocate($img, 249, 115, 22);
        $cyan = imagecolorallocate($img, 56, 189, 248);
        $white = imagecolorallocate($img, 248, 250, 252);
        $gray = imagecolorallocate($img, 148, 163, 184);

        imagesetthickness($img, 4);
        imagerectangle($img, 24, 24, $size - 24, $size - 24, $orange);
        imagesetthickness($img, 1);

        imageline($img, 24, 100, $size - 24, 100, $cyan);

        // Texto informativo
        imagestring($img, 5, 40, 50, 'GAMER HARDWARE - '.strtoupper($category), $cyan);
        imagestring($img, 5, 40, 140, substr($title, 0, 40), $white);
        if (strlen($title) > 40) {
            imagestring($img, 5, 40, 170, substr($title, 40, 40), $white);
        }

        imagejpeg($img, $target, 90);
        imagedestroy($img);
    }
}
