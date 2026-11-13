<?php

namespace Database\Seeders;

use App\Models\Flavor;
use App\Models\KnowledgeChunk;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Presentation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Usuarios de prueba (Admin y Cliente)
        $admin = User::firstOrCreate(
            ['email' => 'admin@postrecito.com'],
            [
                'name' => 'Janet (Postrecito de Jaan)',
                'password' => Hash::make('password'),
                'phone' => '+52 55 1234 5678',
                'is_admin' => true,
                'default_address' => 'Taller Artesanal Postrecito #100, CDMX',
                'default_zone' => 'Zona Centro / Roma / Condesa',
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'cliente@ejemplo.com'],
            [
                'name' => 'Carlos Mendoza',
                'password' => Hash::make('password'),
                'phone' => '+52 55 9876 5432',
                'is_admin' => false,
                'default_address' => 'Av. Insurgentes Sur 1450, Depto 402, Benito Juárez',
                'default_zone' => 'Zona Sur (Del Valle / Coyoacán / Nápoles)',
            ]
        );

        // 2. Sabores de Postrecito de Jaan
        $flavorsData = [
            [
                'name' => 'Pay de Limón',
                'slug' => 'pay-de-limon',
                'description' => 'El clásico equilibrado entre lo cítrico y lo dulce con base crujiente de galleta dorada y crema sedosa.',
                'icon' => 'fa-solid fa-lemon',
                'bg_color' => 'bg-emerald-100',
                'text_color' => 'text-emerald-700',
                'is_favorite' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Fresas con Crema',
                'slug' => 'fresas-con-crema',
                'description' => 'Trozos generosos de fresa fresca sumergidos en la secreta crema artesanal dulce de Janet.',
                'icon' => 'fa-solid fa-strawberry',
                'bg_color' => 'bg-rose-100',
                'text_color' => 'text-rose-700',
                'is_favorite' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Carlota de Mango',
                'slug' => 'carlota-de-mango',
                'description' => 'Todo el sabor tropical del mango de temporada en capas suaves, refrescantes y deliciosas.',
                'icon' => 'fa-solid fa-certificate',
                'bg_color' => 'bg-amber-100',
                'text_color' => 'text-amber-700',
                'is_favorite' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Duraznos con Crema',
                'slug' => 'duraznos-con-crema',
                'description' => 'Dulce, suave y reconfortante. Trozos tiernos de durazno en almíbar combinados con la crema madre.',
                'icon' => 'fa-solid fa-circle',
                'bg_color' => 'bg-orange-100',
                'text_color' => 'text-orange-700',
                'is_favorite' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Mousse de Oreo',
                'slug' => 'mousse-de-oreo',
                'description' => 'Para los amantes del chocolate: crema batida ultra esponjosa cargada con tropezones de galleta Oreo.',
                'icon' => 'fa-solid fa-cookie',
                'bg_color' => 'bg-slate-200',
                'text_color' => 'text-slate-800',
                'is_favorite' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Uvas con Crema',
                'slug' => 'uvas-con-crema',
                'description' => 'Frescura crujiente de uvas sin semilla que contrastan armónicamente con la base cremosa.',
                'icon' => 'fa-solid fa-cubes',
                'bg_color' => 'bg-purple-100',
                'text_color' => 'text-purple-700',
                'is_favorite' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Tapioca',
                'slug' => 'tapioca',
                'description' => 'Textura suave, perlada y reconfortante, con notas dulces y acaneladas inspiradas en el arroz con leche de hogar.',
                'icon' => 'fa-solid fa-bowl-food',
                'bg_color' => 'bg-yellow-100',
                'text_color' => 'text-yellow-800',
                'is_favorite' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Congeladas de Rompope',
                'slug' => 'congeladas-de-rompope',
                'description' => '¡El auténtico sabor de la nostalgia! Preparadas artesanalmente con la receta tradicional de la abuela.',
                'icon' => 'fa-solid fa-snowflake',
                'bg_color' => 'bg-yellow-200',
                'text_color' => 'text-yellow-900',
                'is_favorite' => false,
                'sort_order' => 8,
            ],
        ];

        $flavors = [];
        foreach ($flavorsData as $data) {
            $flavors[$data['slug']] = Flavor::updateOrCreate(['slug' => $data['slug']], $data);
        }

        // 3. Presentaciones (Inspiradas en Postrecito y Mi Mandadito)
        $presentationsData = [
            [
                'name' => 'Vaso Individual',
                'slug' => 'vaso-individual-200ml',
                'capacity' => '200 ml',
                'base_price' => 35.00,
                'description' => 'El antojo perfecto para ti. Práctico y con la porción exacta de felicidad para una persona.',
                'icon' => 'fa-solid fa-glass-water',
                'badge' => '¡Popular!',
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Medio Litro',
                'slug' => 'medio-litro-500ml',
                'capacity' => '500 ml',
                'base_price' => 75.00,
                'description' => 'Ideal para compartir en pareja o si 200 ml simplemente no son suficientes.',
                'icon' => 'fa-solid fa-jar',
                'badge' => null,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Familiar',
                'slug' => 'familiar-1-litro',
                'capacity' => '1 Litro',
                'base_price' => 135.00,
                'description' => 'Para compartir en la mesa con la familia o llevar a comidas de fin de semana.',
                'icon' => 'fa-solid fa-jug-detergent',
                'badge' => 'Mejor Valor',
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Formato Pay',
                'slug' => 'formato-pay-extendida',
                'capacity' => 'Extendida (Molde completo)',
                'base_price' => 220.00,
                'description' => 'Presentación en molde de pay artesanal. Perfecta para cumpleaños, reuniones o regalo especial.',
                'icon' => 'fa-solid fa-chart-pie',
                'badge' => 'Para eventos',
                'is_featured' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Pack Mandadito 4 Vasos',
                'slug' => 'pack-mandadito-4-vasos',
                'capacity' => '4 x 200 ml surtidos',
                'base_price' => 125.00,
                'description' => 'Combo ahorro estilo Mi Mandadito: 4 vasitos individuales para probar varios sabores a precio especial.',
                'icon' => 'fa-solid fa-boxes-stacked',
                'badge' => 'Promo Pack',
                'is_featured' => true,
                'sort_order' => 5,
            ],
        ];

        $presentations = [];
        foreach ($presentationsData as $data) {
            $presentations[$data['slug']] = Presentation::updateOrCreate(['slug' => $data['slug']], $data);
        }

        // 4. Catálogo de Productos
        $products = [];

        // Para cada sabor, creamos las presentaciones individuales y familiares
        foreach ($flavors as $fSlug => $flavor) {
            // Vaso 200 ml
            $products[] = Product::updateOrCreate(
                ['sku' => "VASO-{$flavor->id}"],
                [
                    'flavor_id' => $flavor->id,
                    'presentation_id' => $presentations['vaso-individual-200ml']->id,
                    'name' => "{$flavor->name} - Vaso 200 ml",
                    'slug' => Str::slug("{$flavor->name} vaso 200ml"),
                    'description' => "{$flavor->description} Porción individual lista para comer.",
                    'price' => 35.00,
                    'original_price' => null,
                    'stock' => 45,
                    'badge' => $flavor->is_favorite ? 'Favorito' : null,
                    'is_available' => true,
                    'is_promo' => false,
                    'is_returnable_container' => false,
                ]
            );

            // Medio Litro 500 ml
            $products[] = Product::updateOrCreate(
                ['sku' => "MEDIO-{$flavor->id}"],
                [
                    'flavor_id' => $flavor->id,
                    'presentation_id' => $presentations['medio-litro-500ml']->id,
                    'name' => "{$flavor->name} - Medio Litro (500 ml)",
                    'slug' => Str::slug("{$flavor->name} medio litro 500ml"),
                    'description' => "Frasco mediano de 500 ml de {$flavor->name}. Textura cremosa inigualable.",
                    'price' => 75.00,
                    'original_price' => 80.00,
                    'stock' => 30,
                    'badge' => 'Ahorro',
                    'is_available' => true,
                    'is_promo' => true,
                    'is_returnable_container' => true,
                ]
            );

            // 1 Litro Familiar
            $products[] = Product::updateOrCreate(
                ['sku' => "LITRO-{$flavor->id}"],
                [
                    'flavor_id' => $flavor->id,
                    'presentation_id' => $presentations['familiar-1-litro']->id,
                    'name' => "{$flavor->name} - Familiar 1 Litro",
                    'slug' => Str::slug("{$flavor->name} familiar 1 litro"),
                    'description' => "Presentación familiar de 1 litro de {$flavor->name}. ¡Que nadie se quede con las ganas!",
                    'price' => 135.00,
                    'original_price' => 150.00,
                    'stock' => 20,
                    'badge' => 'Familiar',
                    'is_available' => true,
                    'is_promo' => true,
                    'is_returnable_container' => true,
                ]
            );
        }

        // Formatos especiales Pay (Pay de Limón, Fresas con Crema, Carlota de Mango)
        $payFlavors = ['pay-de-limon', 'fresas-con-crema', 'carlota-de-mango'];
        foreach ($payFlavors as $slug) {
            if (isset($flavors[$slug])) {
                $fl = $flavors[$slug];
                $products[] = Product::updateOrCreate(
                    ['sku' => "PAY-{$fl->id}"],
                    [
                        'flavor_id' => $fl->id,
                        'presentation_id' => $presentations['formato-pay-extendida']->id,
                        'name' => "Pay Completo de {$fl->name}",
                        'slug' => Str::slug("pay completo {$fl->name}"),
                        'description' => "Molde extendido artesanal de {$fl->name} para 8 a 10 personas. Ideal para eventos.",
                        'price' => 220.00,
                        'original_price' => 250.00,
                        'stock' => 10,
                        'badge' => 'Especial Eventos',
                        'is_available' => true,
                        'is_promo' => true,
                        'is_returnable_container' => false,
                    ]
                );
            }
        }

        // Pack Mandadito Surtido
        $firstFlavor = $flavors['fresas-con-crema'];
        $products[] = Product::updateOrCreate(
            ['sku' => 'PACK-4VASOS-SURTIDO'],
            [
                'flavor_id' => $firstFlavor->id,
                'presentation_id' => $presentations['pack-mandadito-4-vasos']->id,
                'name' => 'Pack Mandadito Degustación (4 Vasos 200 ml)',
                'slug' => 'pack-mandadito-degustacion-4-vasos',
                'description' => 'Incluye 4 vasitos a elegir: Pay de Limón, Fresas con Crema, Mousse de Oreo y Carlota de Mango. El favorito para consentir a la familia.',
                'price' => 125.00,
                'original_price' => 140.00,
                'stock' => 25,
                'badge' => 'Súper Promo',
                'is_available' => true,
                'is_promo' => true,
                'is_returnable_container' => false,
            ]
        );

        // 5. Pedido de ejemplo
        $firstProd = Product::first();
        $secondProd = Product::skip(1)->first();

        $sampleOrder = Order::updateOrCreate(
            ['order_number' => 'MP-DEMO-001'],
            [
                'user_id' => $customer->id,
                'status' => 'confirmado',
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'delivery_zone' => 'Zona Sur (Del Valle / Coyoacán / Nápoles)',
                'delivery_address' => $customer->default_address,
                'scheduled_date' => now()->addDay()->toDateString(),
                'scheduled_slot' => '13:00 - 16:00',
                'payment_method' => 'efectivo',
                'subtotal' => ($firstProd->price * 2) + $secondProd->price,
                'delivery_fee' => 0.00,
                'total' => ($firstProd->price * 2) + $secondProd->price,
                'notes' => 'Por favor tocar el interfono 402 al llegar.',
                'source' => 'web',
            ]
        );

        OrderItem::updateOrCreate(
            ['order_id' => $sampleOrder->id, 'product_id' => $firstProd->id],
            [
                'product_name' => $firstProd->name,
                'quantity' => 2,
                'unit_price' => $firstProd->price,
                'subtotal' => $firstProd->price * 2,
            ]
        );

        OrderItem::updateOrCreate(
            ['order_id' => $sampleOrder->id, 'product_id' => $secondProd->id],
            [
                'product_name' => $secondProd->name,
                'quantity' => 1,
                'unit_price' => $secondProd->price,
                'subtotal' => $secondProd->price,
            ]
        );

        // 6. Base de Conocimiento RAG inicial (Servicio, Políticas, Historia de Janet y Catálogo)
        $knowledgeItems = [
            [
                'source_type' => 'service_policy',
                'source_id' => null,
                'title' => 'Historia y Misión de Postrecito de Jaan',
                'content' => "Postrecito de Jaan nació del talento, curiosidad y dedicación de Janet. Tras meses de experimentación y búsqueda de la textura perfecta, Janet creó una crema base secreta inigualable que es el alma de cada postre. Con más de un año deleitando a sus clientes, todos los postres son 100% artesanales, frescos del día, sin conservadores y preparados con ingredientes premium.",
                'metadata' => ['category' => 'historia', 'tags' => ['janet', 'artesanal', 'origen']],
            ],
            [
                'source_type' => 'service_policy',
                'source_id' => null,
                'title' => 'Zonas de Cobertura y Envíos Programados (Estilo Mi Mandadito)',
                'content' => "Al igual que Mi Mandadito de GEPP, en Mi Postrecito trabajamos con entregas programadas a domicilio para asegurar la máxima frescura de la crema. Cobertura: Zona Centro (Roma, Condesa, Cuauhtémoc, Juárez), Zona Sur (Del Valle, Narvarte, Coyoacán, Nápoles, San Ángel) y Zona Poniente (Polanco, Anzures, San Miguel Chapultepec). Horarios de entrega en franjas: 10:00 a 13:00 hrs, 13:00 a 16:00 hrs, y 16:00 a 19:00 hrs. El envío es GRATIS en pedidos a partir de $150 MXN (o costo fijo de $35 MXN para pedidos menores).",
                'metadata' => ['category' => 'envios', 'tags' => ['cobertura', 'horarios', 'costo_envio']],
            ],
            [
                'source_type' => 'service_policy',
                'source_id' => null,
                'title' => 'Métodos de Pago y Proceso de Compra',
                'content' => "Aceptamos pago en efectivo contra entrega, transferencia bancaria (SPEI con envío de comprobante) o pago con tarjeta física al recibir mediante terminal móvil. Puedes explorar todo el catálogo sin registrarte, pero para confirmar un pedido es indispensable iniciar sesión o crear tu cuenta (solo toma 30 segundos) para programar tu dirección y horario de entrega.",
                'metadata' => ['category' => 'pagos', 'tags' => ['efectivo', 'transferencia', 'tarjeta', 'login_requerido']],
            ],
            [
                'source_type' => 'service_policy',
                'source_id' => null,
                'title' => 'Conservación y Consumo Recomendado',
                'content' => "Nuestros postres deben mantenerse en refrigeración entre 2°C y 6°C para conservar la textura sedosa de la crema de Janet. Su tiempo de vida óptimo es de hasta 5 días en refrigeración. Las 'Congeladas de Rompope' deben conservarse en congelador.",
                'metadata' => ['category' => 'conservacion', 'tags' => ['refrigeracion', 'vida_util', 'congeladas']],
            ],
            [
                'source_type' => 'service_policy',
                'source_id' => null,
                'title' => 'Presentaciones y Precios Oficiales',
                'content' => "Presentaciones disponibles: 1) Vaso Individual (200 ml) a $35 MXN (ideal antojo individual). 2) Frasco Medio Litro (500 ml) a $75 MXN (ideal parejas o golosos). 3) Frasco Familiar (1 Litro) a $135 MXN (ahorro familiar). 4) Formato Pay extendido completo para eventos a $220 MXN (8 a 10 porciones). 5) Pack Mandadito Degustación (4 Vasos de 200 ml) a $125 MXN en promoción especial.",
                'metadata' => ['category' => 'precios', 'tags' => ['precios', 'tamanos', 'litro', 'vaso']],
            ],
        ];

        // Añadir cada producto al conocimiento
        foreach (Product::with(['flavor', 'presentation'])->get() as $product) {
            $knowledgeItems[] = [
                'source_type' => 'product',
                'source_id' => $product->id,
                'title' => "Producto: {$product->name}",
                'content' => $product->toRagDocument(),
                'metadata' => [
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'price' => (float) $product->price,
                    'flavor' => $product->flavor?->name,
                    'presentation' => $product->presentation?->name,
                ],
            ];
        }

        foreach ($knowledgeItems as $item) {
            KnowledgeChunk::updateOrCreate(
                [
                    'source_type' => $item['source_type'],
                    'source_id' => $item['source_id'],
                    'title' => $item['title'],
                ],
                [
                    'content' => $item['content'],
                    'metadata' => $item['metadata'],
                    'is_active' => true,
                ]
            );
        }
    }
}
