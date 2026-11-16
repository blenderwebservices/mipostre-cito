<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\KnowledgeChunk;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ShippingZone;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Usuarios
        $admin = User::updateOrCreate(
            ['email' => 'admin@postrecito.com'],
            [
                'name' => 'Janet (Postrecito de Jaan)',
                'phone_number' => '+52 55 1234 5678',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );

        $customer = User::updateOrCreate(
            ['email' => 'cliente@ejemplo.com'],
            [
                'name' => 'Carlos Mendoza',
                'phone_number' => '+52 55 9876 5432',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );

        // 2. Direcciones del cliente de prueba
        $defaultAddress = Address::updateOrCreate(
            ['user_id' => $customer->id, 'tag' => 'Casa'],
            [
                'street' => 'Av. Insurgentes Sur',
                'exterior_number' => '1450',
                'interior_number' => '402',
                'neighborhood' => 'Del Valle',
                'zip_code' => '03100',
                'references' => 'Edificio blanco frente al parque, tocar interfono 402.',
                'is_default' => true,
            ]
        );

        Address::updateOrCreate(
            ['user_id' => $customer->id, 'tag' => 'Oficina'],
            [
                'street' => 'Calle Colima',
                'exterior_number' => '210',
                'interior_number' => 'PB',
                'neighborhood' => 'Roma Norte',
                'zip_code' => '06700',
                'references' => 'Portón negro, dejar en recepción.',
                'is_default' => false,
            ]
        );

        // 3. Zonas de Envío (Cobertura)
        $zonesData = [
            ['zip_code' => '06700', 'neighborhood' => 'Roma Norte', 'zone_name' => 'Zona Centro', 'delivery_cost' => 25.00, 'min_order_for_free_shipping' => 150.00],
            ['zip_code' => '06140', 'neighborhood' => 'Condesa', 'zone_name' => 'Zona Centro', 'delivery_cost' => 25.00, 'min_order_for_free_shipping' => 150.00],
            ['zip_code' => '03100', 'neighborhood' => 'Del Valle', 'zone_name' => 'Zona Sur', 'delivery_cost' => 35.00, 'min_order_for_free_shipping' => 200.00],
            ['zip_code' => '03020', 'neighborhood' => 'Narvarte', 'zone_name' => 'Zona Sur', 'delivery_cost' => 30.00, 'min_order_for_free_shipping' => 180.00],
            ['zip_code' => '04000', 'neighborhood' => 'Coyoacán Centro', 'zone_name' => 'Zona Sur', 'delivery_cost' => 40.00, 'min_order_for_free_shipping' => 220.00],
            ['zip_code' => '11560', 'neighborhood' => 'Polanco', 'zone_name' => 'Zona Poniente', 'delivery_cost' => 35.00, 'min_order_for_free_shipping' => 200.00],
            ['zip_code' => '03810', 'neighborhood' => 'Nápoles', 'zone_name' => 'Zona Sur', 'delivery_cost' => 30.00, 'min_order_for_free_shipping' => 180.00],
            ['zip_code' => '06870', 'neighborhood' => 'Chabacano', 'zone_name' => 'Zona Centro', 'delivery_cost' => 15.00, 'min_order_for_free_shipping' => 180.00],
        ];

        $zones = [];
        foreach ($zonesData as $z) {
            $zones[$z['zip_code']] = ShippingZone::updateOrCreate(['zip_code' => $z['zip_code']], $z + ['is_active' => true]);
        }

        // 4. Categorías
        $categoriesData = [
            ['name' => 'Clásicos Cremosos', 'slug' => 'clasicos-cremosos', 'description' => 'La receta base secreta de Janet en sus sabores tradicionales.', 'icon' => 'fa-solid fa-ice-cream', 'bg_color' => 'bg-pink-100', 'text_color' => 'text-pink-600', 'sort_order' => 1],
            ['name' => 'Frutales Frescos', 'slug' => 'frutales-frescos', 'description' => 'Trozos de fruta natural en crema artesanal.', 'icon' => 'fa-solid fa-lemon', 'bg_color' => 'bg-emerald-100', 'text_color' => 'text-emerald-700', 'sort_order' => 2],
            ['name' => 'Chocolate & Galleta', 'slug' => 'chocolate-galleta', 'description' => 'Mousses y combinaciones intensas para los amantes del cacao.', 'icon' => 'fa-solid fa-cookie', 'bg_color' => 'bg-slate-200', 'text_color' => 'text-slate-800', 'sort_order' => 3],
            ['name' => 'Nostalgia & Tradición', 'slug' => 'nostalgia-tradicion', 'description' => 'Sabores caseros inspirados en las recetas de la abuela.', 'icon' => 'fa-solid fa-snowflake', 'bg_color' => 'bg-amber-100', 'text_color' => 'text-amber-700', 'sort_order' => 4],
            ['name' => 'Packs Mandadito', 'slug' => 'packs-mandadito', 'description' => 'Combos de ahorro para compartir o degustar varios sabores.', 'icon' => 'fa-solid fa-boxes-stacked', 'bg_color' => 'bg-blue-100', 'text_color' => 'text-blue-700', 'sort_order' => 5],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['slug']] = Category::updateOrCreate(['slug' => $c['slug']], $c + ['is_active' => true]);
        }

        // 5. Productos (Los 8 sabores de Postrecito de Jaan + Pack) y sus Variaciones
        $productsCatalog = [
            [
                'category' => 'clasicos-cremosos',
                'name' => 'Pay de Limón',
                'description' => 'El clásico equilibrado entre lo cítrico y lo dulce con base crujiente de galleta dorada y crema sedosa.',
                'badge' => 'Favorito',
                'is_favorite' => true,
                'has_pay' => true,
            ],
            [
                'category' => 'frutales-frescos',
                'name' => 'Fresas con Crema',
                'description' => 'Trozos generosos de fresa fresca sumergidos en la secreta crema artesanal dulce de Janet.',
                'badge' => 'Top Ventas',
                'is_favorite' => true,
                'has_pay' => true,
            ],
            [
                'category' => 'frutales-frescos',
                'name' => 'Carlota de Mango',
                'description' => 'Todo el sabor tropical del mango de temporada en capas suaves, refrescantes y deliciosas.',
                'badge' => 'Temporada',
                'is_favorite' => false,
                'has_pay' => true,
            ],
            [
                'category' => 'frutales-frescos',
                'name' => 'Duraznos con Crema',
                'description' => 'Dulce, suave y reconfortante. Trozos tiernos de durazno en almíbar combinados con nuestra crema madre.',
                'badge' => null,
                'is_favorite' => false,
                'has_pay' => false,
            ],
            [
                'category' => 'chocolate-galleta',
                'name' => 'Mousse de Oreo',
                'description' => 'Para los amantes del chocolate: crema batida ultra esponjosa cargada con tropezones de galleta Oreo.',
                'badge' => 'Favorito',
                'is_favorite' => true,
                'has_pay' => false,
            ],
            [
                'category' => 'frutales-frescos',
                'name' => 'Uvas con Crema',
                'description' => 'Frescura crujiente de uvas sin semilla que contrastan armónicamente con la base cremosa.',
                'badge' => null,
                'is_favorite' => false,
                'has_pay' => false,
            ],
            [
                'category' => 'nostalgia-tradicion',
                'name' => 'Tapioca Artesanal',
                'description' => 'Textura suave, perlada y reconfortante, con notas dulces y acaneladas inspiradas en el arroz con leche.',
                'badge' => null,
                'is_favorite' => false,
                'has_pay' => false,
            ],
            [
                'category' => 'nostalgia-tradicion',
                'name' => 'Congeladas de Rompope',
                'description' => '¡El auténtico sabor de la nostalgia! Preparadas artesanalmente con la receta tradicional de la abuela.',
                'badge' => 'Receta Abuela',
                'is_favorite' => false,
                'has_pay' => false,
            ],
        ];

        foreach ($productsCatalog as $idx => $pData) {
            $slug = Str::slug($pData['name']);
            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $categories[$pData['category']]->id,
                    'name' => $pData['name'],
                    'description' => $pData['description'],
                    'base_price' => 35.00,
                    'badge' => $pData['badge'],
                    'is_favorite' => $pData['is_favorite'],
                    'is_active' => true,
                ]
            );

            $num = $idx + 1;
            ProductVariation::updateOrCreate(
                ['sku' => "PJ-{$num}-200"],
                [
                    'product_id' => $product->id,
                    'name' => 'Vaso Individual (200 ml)',
                    'price' => 35.00,
                    'original_price' => null,
                    'stock_quantity' => 50,
                    'is_returnable_container' => false,
                    'is_active' => true,
                ]
            );

            ProductVariation::updateOrCreate(
                ['sku' => "PJ-{$num}-500"],
                [
                    'product_id' => $product->id,
                    'name' => 'Medio Litro (500 ml)',
                    'price' => 75.00,
                    'original_price' => 85.00,
                    'stock_quantity' => 30,
                    'is_returnable_container' => true,
                    'is_active' => true,
                ]
            );

            ProductVariation::updateOrCreate(
                ['sku' => "PJ-{$num}-1000"],
                [
                    'product_id' => $product->id,
                    'name' => 'Familiar (1 Litro)',
                    'price' => 135.00,
                    'original_price' => 150.00,
                    'stock_quantity' => 20,
                    'is_returnable_container' => true,
                    'is_active' => true,
                ]
            );

            if ($pData['has_pay']) {
                ProductVariation::updateOrCreate(
                    ['sku' => "PJ-{$num}-PAY"],
                    [
                        'product_id' => $product->id,
                        'name' => 'Formato Pay (Extendida)',
                        'price' => 220.00,
                        'original_price' => 250.00,
                        'stock_quantity' => 10,
                        'is_returnable_container' => false,
                        'is_active' => true,
                    ]
                );
            }
        }

        // Pack Mandadito
        $packProduct = Product::updateOrCreate(
            ['slug' => 'pack-mandadito-degustacion'],
            [
                'category_id' => $categories['packs-mandadito']->id,
                'name' => 'Pack Mandadito Degustación',
                'description' => 'Combo especial estilo Mi Mandadito para probar 4 sabores favoritos o llevar a reuniones.',
                'base_price' => 125.00,
                'badge' => 'Súper Promo',
                'is_favorite' => true,
                'is_active' => true,
            ]
        );

        ProductVariation::updateOrCreate(
            ['sku' => 'PJ-PACK-4V'],
            [
                'product_id' => $packProduct->id,
                'name' => '4 Vasos Surtidos (200 ml c/u)',
                'price' => 125.00,
                'original_price' => 140.00,
                'stock_quantity' => 25,
                'is_returnable_container' => false,
                'is_active' => true,
            ]
        );

        ProductVariation::updateOrCreate(
            ['sku' => 'PJ-PACK-2L'],
            [
                'product_id' => $packProduct->id,
                'name' => 'Pack Fiesta (2 Litros a elegir)',
                'price' => 250.00,
                'original_price' => 270.00,
                'stock_quantity' => 15,
                'is_returnable_container' => true,
                'is_active' => true,
            ]
        );

        // 6. Cupones de Descuento
        $coupons = [
            'BIENVENIDO15' => Coupon::updateOrCreate(
                ['code' => 'BIENVENIDO15'],
                [
                    'description' => '15% de descuento en tu pedido de postres artesanales.',
                    'discount_type' => 'percentage',
                    'discount_value' => 15.00,
                    'min_cart_value' => 100.00,
                    'valid_from' => now()->subDays(10)->toDateString(),
                    'valid_until' => now()->addMonths(6)->toDateString(),
                    'usage_limit' => 500,
                    'is_active' => true,
                ]
            ),
            'BUENFIN20' => Coupon::updateOrCreate(
                ['code' => 'BUENFIN20'],
                [
                    'description' => '20% de descuento por temporada Buen Fin en pedidos desde $150 MXN.',
                    'discount_type' => 'percentage',
                    'discount_value' => 20.00,
                    'min_cart_value' => 150.00,
                    'valid_from' => now()->subDays(5)->toDateString(),
                    'valid_until' => now()->addDays(20)->toDateString(),
                    'usage_limit' => 200,
                    'is_active' => true,
                ]
            ),
            'DULCE30' => Coupon::updateOrCreate(
                ['code' => 'DULCE30'],
                [
                    'description' => '$30 MXN de descuento directo en compras mayores a $140 MXN.',
                    'discount_type' => 'fixed',
                    'discount_value' => 30.00,
                    'min_cart_value' => 140.00,
                    'valid_from' => now()->subDays(5)->toDateString(),
                    'valid_until' => now()->addMonths(3)->toDateString(),
                    'usage_limit' => 300,
                    'is_active' => true,
                ]
            ),
            'AMOR24' => Coupon::updateOrCreate(
                ['code' => 'AMOR24'],
                [
                    'description' => 'Cupón especial de San Valentín: 15% de descuento.',
                    'discount_type' => 'percentage',
                    'discount_value' => 15.00,
                    'min_cart_value' => 120.00,
                    'valid_from' => now()->year . '-02-10',
                    'valid_until' => now()->year . '-02-15',
                    'usage_limit' => 150,
                    'is_active' => true,
                ]
            ),
        ];

        // 7. Campañas de Temporada (Festividades mexicanas y comerciales)
        $year = now()->year;
        $campaignsData = [
            // Campaña activa hoy (para demostración inmediata en Landing)
            [
                'name' => 'Buen Fin Dulce de Jaan',
                'slug' => 'buen-fin',
                'subtitle' => '¡Aprovecha 20% OFF en toda la tienda!',
                'description' => 'Usa el cupón BUENFIN20 en pedidos mayores a $150 MXN y disfruta la crema secreta de Janet con envío rápido.',
                'start_date' => now()->subDays(3)->toDateString(),
                'end_date' => now()->addDays(10)->toDateString(),
                'is_active' => true,
                'theme_color' => '#db2777',
                'coupon_id' => $coupons['BUENFIN20']->id,
            ],
            ['name' => 'Día de la Candelaria', 'slug' => 'dia-de-la-candelaria', 'subtitle' => 'El toque dulce después de los tamales', 'description' => 'Presentaciones familiares de 1 Litro ideales para compartir en familia.', 'start_date' => "{$year}-02-01", 'end_date' => "{$year}-02-04", 'is_active' => true, 'theme_color' => '#d97706', 'coupon_id' => null],
            ['name' => 'Día del Amor y la Amistad', 'slug' => 'dia-del-amor', 'subtitle' => 'Regala dulzura hecha en casa', 'description' => 'Combos de Medio Litro y Pay de Limón para sorprender a esa persona especial.', 'start_date' => "{$year}-02-10", 'end_date' => "{$year}-02-15", 'is_active' => true, 'theme_color' => '#e11d48', 'coupon_id' => $coupons['AMOR24']->id],
            ['name' => 'Día de la Primavera', 'slug' => 'dia-de-la-primavera', 'subtitle' => 'Sabores frutales frescos de temporada', 'description' => 'Carlota de Mango, Fresas con Crema y Uvas con Crema súper refrescantes.', 'start_date' => "{$year}-03-20", 'end_date' => "{$year}-03-25", 'is_active' => true, 'theme_color' => '#059669', 'coupon_id' => null],
            ['name' => '5 de Mayo', 'slug' => '5-de-mayo', 'subtitle' => 'Celebra con sabores tradicionales', 'description' => 'Tapioca artesanal y Congeladas de Rompope con receta de la abuela.', 'start_date' => "{$year}-05-04", 'end_date' => "{$year}-05-06", 'is_active' => true, 'theme_color' => '#0284c7', 'coupon_id' => null],
            ['name' => 'Día de la Madre', 'slug' => 'dia-de-la-madre', 'subtitle' => 'Consiente a mamá con un Formato Pay', 'description' => 'Moldes extendidos de Pay de Limón y Fresas con Crema para celebrar en grande.', 'start_date' => "{$year}-05-07", 'end_date' => "{$year}-05-11", 'is_active' => true, 'theme_color' => '#db2777', 'coupon_id' => null],
            ['name' => 'Día de la Independencia', 'slug' => 'dia-de-la-independencia', 'subtitle' => '¡Viva el sabor artesanal mexicano!', 'description' => 'Packs familiares para tu noche mexicana.', 'start_date' => "{$year}-09-14", 'end_date' => "{$year}-09-17", 'is_active' => true, 'theme_color' => '#16a34a', 'coupon_id' => null],
            ['name' => 'Halloween y Día de Muertos', 'slug' => 'halloween', 'subtitle' => 'Dulce o Truco con Mousse de Oreo', 'description' => 'Postres espeluznantemente deliciosos para compartir.', 'start_date' => "{$year}-10-28", 'end_date' => "{$year}-11-03", 'is_active' => true, 'theme_color' => '#7c3aed', 'coupon_id' => null],
            ['name' => 'Día de la Revolución', 'slug' => 'dia-de-la-revolucion', 'subtitle' => 'Puente dulce en casa', 'description' => 'Entregas programadas todo el fin de semana.', 'start_date' => "{$year}-11-18", 'end_date' => "{$year}-11-21", 'is_active' => true, 'theme_color' => '#b45309', 'coupon_id' => null],
            ['name' => 'Viernes Negro', 'slug' => 'viernes-negro', 'subtitle' => 'Descuentos especiales de Black Friday', 'description' => 'Promociones exclusivas en Packs Mandadito.', 'start_date' => "{$year}-11-25", 'end_date' => "{$year}-11-29", 'is_active' => true, 'theme_color' => '#1e293b', 'coupon_id' => null],
            ['name' => 'Navidad Postrecito', 'slug' => 'navidad', 'subtitle' => 'El postre estrella de tu cena navideña', 'description' => 'Reserva con anticipación tus Formatos Pay y Litros Familiares.', 'start_date' => "{$year}-12-15", 'end_date' => "{$year}-12-26", 'is_active' => true, 'theme_color' => '#dc2626', 'coupon_id' => null],
            ['name' => 'Fin de Año', 'slug' => 'fin-de-ano', 'subtitle' => 'Despide el año con dulzura', 'description' => 'Uvas con Crema y Congeladas de Rompope para brindar en familia.', 'start_date' => "{$year}-12-27", 'end_date' => "{$year}-12-31", 'is_active' => true, 'theme_color' => '#ca8a04', 'coupon_id' => null],
        ];

        foreach ($campaignsData as $camp) {
            Campaign::updateOrCreate(['slug' => $camp['slug']], $camp);
        }

        // 8. Pedido de ejemplo
        $firstVar = ProductVariation::first();
        $secondVar = ProductVariation::skip(2)->first();
        $zone = $zones['03100'];

        $subtotal = ($firstVar->price * 2) + $secondVar->price;
        $shipping = $zone->calculateShippingCost($subtotal);

        $sampleOrder = Order::updateOrCreate(
            ['order_number' => 'PJ-DEMO-001'],
            [
                'user_id' => $customer->id,
                'address_id' => $defaultAddress->id,
                'coupon_id' => null,
                'shipping_zone_id' => $zone->id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone_number,
                'delivery_address_snapshot' => $defaultAddress->fullAddress(),
                'subtotal' => $subtotal,
                'discount_amount' => 0.00,
                'shipping_cost' => $shipping,
                'total_amount' => $subtotal + $shipping,
                'delivery_date' => now()->addDay()->toDateString(),
                'delivery_time_slot' => '13:00 - 16:00',
                'status' => 'preparing',
                'payment_method' => 'efectivo',
                'notes' => 'Tocar el interfono 402.',
                'source' => 'web',
            ]
        );

        OrderItem::updateOrCreate(
            ['order_id' => $sampleOrder->id, 'product_variation_id' => $firstVar->id],
            [
                'product_name_snapshot' => $firstVar->product->name,
                'variation_name_snapshot' => $firstVar->name,
                'quantity' => 2,
                'unit_price' => $firstVar->price,
                'subtotal' => $firstVar->price * 2,
            ]
        );

        OrderItem::updateOrCreate(
            ['order_id' => $sampleOrder->id, 'product_variation_id' => $secondVar->id],
            [
                'product_name_snapshot' => $secondVar->product->name,
                'variation_name_snapshot' => $secondVar->name,
                'quantity' => 1,
                'unit_price' => $secondVar->price,
                'subtotal' => $secondVar->price,
            ]
        );

        // 9. Base de Conocimiento RAG (Productos, Zonas, Cupones, Campañas, Políticas)
        $this->seedKnowledgeChunks();
    }

    protected function seedKnowledgeChunks(): void
    {
        $chunks = [
            [
                'source_type' => 'service_policy',
                'source_id' => null,
                'title' => 'Historia y Receta de Postrecito de Jaan',
                'content' => 'Postrecito de Jaan nació del talento y curiosidad de Janet. Tras meses de prueba y error creó su receta secreta de crema artesanal. Todos los postres son 100% hechos en casa, frescos y sin conservadores.',
                'metadata' => ['category' => 'historia'],
            ],
            [
                'source_type' => 'service_policy',
                'source_id' => null,
                'title' => 'Sabores, Tamaños y Precios del Menú Completo',
                'content' => "Nuestros 8 sabores artesanales son: Pay de Limón, Fresas con Crema, Carlota de Mango, Duraznos con Crema, Mousse de Oreo, Uvas con Crema, Tapioca Artesanal y Congeladas de Rompope.\nTamaños y precios: Vaso Individual (200 ml) \$35 MXN, Medio Litro (500 ml) \$75 MXN, Familiar (1 Litro) \$135 MXN, Formato Pay Extendida \$220 MXN (disponible en Pay de Limón, Fresas con Crema y Carlota de Mango), Pack Mandadito 4 Vasos \$125 MXN y Pack Fiesta 2 Litros \$250 MXN.",
                'metadata' => ['category' => 'menu'],
            ],
            [
                'source_type' => 'service_policy',
                'source_id' => null,
                'title' => 'Política de Pedidos, Entregas Programadas y Pagos',
                'content' => 'Puedes explorar el menú, verificar tu código postal de cobertura y armar tu carrito sin registrarte. Para confirmar un pedido es obligatorio iniciar sesión o crear tu cuenta. Horarios de entrega: 10:00 - 13:00, 13:00 - 16:00 y 16:00 - 19:00 hrs. Métodos de pago: Efectivo contra entrega, Transferencia SPEI y Tarjeta al recibir.',
                'metadata' => ['category' => 'politicas'],
            ],
        ];

        // Zonas de envío
        $zonesText = ShippingZone::where('is_active', true)->get()
            ->map(fn (ShippingZone $z) => "CP {$z->zip_code} ({$z->neighborhood}, {$z->zone_name}): Envío \${$z->delivery_cost} MXN (Gratis desde \${$z->min_order_for_free_shipping} MXN)")
            ->implode("\n");

        $chunks[] = [
            'source_type' => 'shipping_zone',
            'source_id' => null,
            'title' => 'Zonas de Cobertura y Costos de Envío',
            'content' => "Cobertura actual por Código Postal y Colonia:\n{$zonesText}",
            'metadata' => ['category' => 'envios'],
        ];

        // Cupones activos
        $couponsText = Coupon::where('is_active', true)->get()
            ->map(fn (Coupon $c) => "Cupón {$c->code}: {$c->description} (Compra mínima: \${$c->min_cart_value} MXN)")
            ->implode("\n");

        $chunks[] = [
            'source_type' => 'coupon',
            'source_id' => null,
            'title' => 'Cupones y Promociones Vigentes',
            'content' => "Cupones de descuento disponibles:\n{$couponsText}",
            'metadata' => ['category' => 'promos'],
        ];

        // Campañas activas
        foreach (Campaign::currentlyActive()->get() as $camp) {
            $chunks[] = [
                'source_type' => 'campaign',
                'source_id' => $camp->id,
                'title' => "Campaña Activa: {$camp->name}",
                'content' => "{$camp->subtitle}. {$camp->description} Vigente del {$camp->start_date->format('d/m/Y')} al {$camp->end_date->format('d/m/Y')}." . ($camp->coupon ? " Cupón asociado: {$camp->coupon->code}." : ''),
                'metadata' => ['campaign_id' => $camp->id],
            ];
        }

        // Productos y variaciones
        foreach (Product::with(['category', 'variations'])->where('is_active', true)->get() as $product) {
            $chunks[] = [
                'source_type' => 'product',
                'source_id' => $product->id,
                'title' => "Producto: {$product->name}",
                'content' => $product->toRagDocument(),
                'metadata' => ['product_id' => $product->id],
            ];
        }

        foreach ($chunks as $item) {
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
