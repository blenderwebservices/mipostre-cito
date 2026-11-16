<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\KnowledgeChunk;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ShippingZone;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Embeddings;
use Throwable;

class RagService
{
    /**
     * Index or re-index all active knowledge chunks by generating embeddings.
     * Uses Laravel AI Embeddings (Gemini), falling back to local normalized vector if API key is not yet set.
     */
    public function indexAll(bool $force = false): int
    {
        $query = KnowledgeChunk::query()->where('is_active', true);
        if (! $force) {
            $query->whereNull('embedding');
        }

        $chunks = $query->get();
        $count = 0;

        foreach ($chunks as $chunk) {
            $embedding = $this->generateEmbedding("{$chunk->title}\n{$chunk->content}");
            if ($embedding) {
                $chunk->update(['embedding' => $embedding]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Generate embedding for a single text input using Laravel AI SDK Embeddings (Gemini).
     *
     * @return array<float>
     */
    public function generateEmbedding(string $text): array
    {
        $apiKey = config('ai.providers.gemini.key');
        $dimensions = (int) config('ai.providers.gemini.models.embeddings.dimensions', 768);
        $model = config('ai.providers.gemini.models.embeddings.default', 'gemini-embedding-001');

        if (! empty($apiKey)) {
            try {
                $response = Embeddings::for([$text])
                    ->dimensions($dimensions)
                    ->generate(provider: 'gemini', model: $model);

                if (! empty($response->embeddings[0])) {
                    return $response->embeddings[0];
                }
            } catch (Throwable $e) {
                Log::warning('Gemini embedding generation failed, falling back to local vector representation: ' . $e->getMessage());
            }
        }

        return $this->generateDeterministicVector($text, $dimensions);
    }

    /**
     * Retrieve the top-K relevant knowledge chunks using cosine similarity over embeddings.
     *
     * @return Collection<int, array{chunk: KnowledgeChunk, similarity: float}>
     */
    public function retrieve(string $query, int $topK = 5, float $minSimilarity = 0.10): Collection
    {
        $queryEmbedding = $this->generateEmbedding($query);
        $chunks = KnowledgeChunk::where('is_active', true)->whereNotNull('embedding')->get();

        if ($chunks->isEmpty()) {
            $this->indexAll();
            $chunks = KnowledgeChunk::where('is_active', true)->whereNotNull('embedding')->get();
        }

        $scored = $chunks->map(function (KnowledgeChunk $chunk) use ($queryEmbedding) {
            $embedding = $chunk->embedding;
            $similarity = is_array($embedding) ? $this->cosineSimilarity($queryEmbedding, $embedding) : 0.0;

            return [
                'chunk' => $chunk,
                'similarity' => $similarity,
            ];
        });

        return $scored
            ->filter(fn ($item) => $item['similarity'] >= $minSimilarity)
            ->sortByDesc('similarity')
            ->take($topK)
            ->values();
    }

    /**
     * Calculate cosine similarity between two numeric vectors.
     *
     * @param  array<float>  $vectorA
     * @param  array<float>  $vectorB
     */
    public function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        $count = min(count($vectorA), count($vectorB));

        if ($count === 0) {
            return 0.0;
        }

        for ($i = 0; $i < $count; $i++) {
            $a = (float) $vectorA[$i];
            $b = (float) $vectorB[$i];
            $dotProduct += $a * $b;
            $normA += $a * $a;
            $normB += $b * $b;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }

    public function isLiveAiEnabled(): bool
    {
        return filled(config('ai.providers.gemini.key'));
    }

    /**
     * Process a chat interaction using RAG (Embeddings + Retrieval, NO agents).
     *
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{
     *     reply: string,
     *     order_placed: bool,
     *     order: ?Order,
     *     requires_auth: bool,
     *     retrieved_chunks: array<string>,
     *     ai_mode: 'live'|'mocking'
     * }
     */
    public function chat(string $userMessage, ?User $user = null, array $history = []): array
    {
        $retrieved = $this->retrieve($userMessage, topK: 5);
        $contextText = $retrieved->map(function ($item) {
            /** @var KnowledgeChunk $chunk */
            $chunk = $item['chunk'];
            return "### {$chunk->title}\n{$chunk->content}";
        })->implode("\n\n");

        $apiKey = config('ai.providers.gemini.key');
        $chatModel = config('ai.providers.gemini.models.text.default', 'gemini-2.5-flash');

        $systemPrompt = <<<PROMPT
Eres "Postrecito Bot", el asistente virtual oficial de "Postrecito de Jaan" (con UX estilo Mi Mandadito).
Ayudas a los clientes a conocer el menú, tamaños, precios, zonas de cobertura por código postal, cupones/campañas vigentes y a realizar pedidos.

REGLAS:
1. Sé cálido, claro y servicial.
2. Basa tus respuestas estrictamente en el siguiente CONTEXTO RECUPERADO (RAG):
---
{$contextText}
---

CLIENTE ACTUAL:
{$this->formatAuthStatus($user)}

POLÍTICA DE PEDIDOS:
- Cualquiera puede explorar el menú, consultar precios, cupones y cobertura sin registrarse.
- Para confirmar un pedido es obligatorio estar autenticado. Si el usuario es invitado y quiere pedir, indica que debe iniciar sesión en /login.
- Si el usuario YA ESTÁ AUTENTICADO y pide concretar un pedido, responde con action="place_order" y los datos en order_data.

FORMATO DE RESPUESTA (JSON válido obligatorio):
{
  "reply": "Tu respuesta amigable para el cliente.",
  "action": "none|request_login|place_order",
  "order_data": {
    "items": [
      {
        "product_name": "Nombre del producto (ej. Pay de Limón)",
        "variation_name": "Tamaño (ej. Vaso Individual (200 ml), Medio Litro (500 ml), Familiar (1 Litro))",
        "quantity": 1
      }
    ],
    "coupon_code": "Opcional si mencionó cupón",
    "delivery_date": "YYYY-MM-DD",
    "delivery_time_slot": "10:00 - 13:00 | 13:00 - 16:00 | 16:00 - 19:00",
    "payment_method": "efectivo | transferencia",
    "notes": ""
  }
}
Si action es "none", deja "order_data" en null.
PROMPT;

        if (! empty($apiKey)) {
            try {
                $llmResult = $this->callGeminiApi($systemPrompt, $userMessage, $history, $apiKey, $chatModel);
                if ($llmResult) {
                    $handled = $this->handleParsedResponse($llmResult, $user, $retrieved);
                    $handled['ai_mode'] = 'live';

                    return $handled;
                }
            } catch (Throwable $e) {
                Log::warning('Gemini Chat generation failed, falling back to local deterministic RAG response: ' . $e->getMessage());
            }
        }

        $fallback = $this->deterministicLocalRagEngine($userMessage, $user, $retrieved);
        $fallback['ai_mode'] = 'mocking';

        return $fallback;
    }

    protected function callGeminiApi(string $systemPrompt, string $userMessage, array $history, string $apiKey, string $model): ?array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $contents = [
            ['role' => 'user', 'parts' => [['text' => "INSTRUCCIONES DEL SISTEMA:\n" . $systemPrompt]]],
            ['role' => 'model', 'parts' => [['text' => 'Entendido. Responderé en el formato JSON especificado.']]],
        ];

        foreach (array_slice($history, -6) as $msg) {
            $contents[] = [
                'role' => ($msg['role'] ?? 'user') === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content'] ?? '']],
            ];
        }

        $contents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];

        $response = Http::timeout(30)->post($url, [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.2,
                'responseMimeType' => 'application/json',
            ],
        ]);

        if ($response->successful()) {
            $text = $response->json('candidates.0.content.parts.0.text');
            if ($text) {
                $decoded = json_decode($text, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return null;
    }

    protected function handleParsedResponse(array $parsed, ?User $user, Collection $retrieved): array
    {
        $action = $parsed['action'] ?? 'none';
        $reply = $parsed['reply'] ?? '¡Hola! Con gusto te ayudo con tus postres.';
        $orderData = $parsed['order_data'] ?? null;
        $orderPlaced = false;
        $order = null;
        $requiresAuth = false;

        if ($action === 'request_login' || (! $user && $action === 'place_order')) {
            $requiresAuth = true;
            $reply .= "\n\n💡 *Para confirmar tu pedido y programar la entrega necesitas iniciar sesión.*";
        } elseif ($action === 'place_order' && $user && ! empty($orderData['items'])) {
            $order = $this->createOrderFromData($user, $orderData, source: 'chatbot');
            $orderPlaced = true;
            $reply .= "\n\n🎉 **¡Pedido confirmado!** Folio: **#{$order->order_number}** por un total de **\${$order->total_amount} MXN**.";
        }

        return [
            'reply' => $reply,
            'order_placed' => $orderPlaced,
            'order' => $order,
            'requires_auth' => $requiresAuth,
            'retrieved_chunks' => $retrieved->pluck('chunk.title')->all(),
        ];
    }

    protected function deterministicLocalRagEngine(string $userMessage, ?User $user, Collection $retrieved): array
    {
        $lower = mb_strtolower($userMessage);
        $isOrderingIntent = str_contains($lower, 'pedir') ||
            str_contains($lower, 'ordenar') ||
            str_contains($lower, 'comprar') ||
            str_contains($lower, 'hacer pedido') ||
            str_contains($lower, 'quiero');

        $matchedProduct = Product::with('variations')
            ->where('is_active', true)
            ->get()
            ->first(fn (Product $p) => str_contains($lower, mb_strtolower($p->name)));

        if ($isOrderingIntent && $matchedProduct) {
            if (! $user) {
                return [
                    'reply' => "¡Excelente elección! Veo que deseas ordenar **{$matchedProduct->name}**.\n\nPara confirmar tu pedido, aplicar cupones y programar tu dirección de entrega, **inicia sesión o regístrate** en un instante.",
                    'order_placed' => false,
                    'order' => null,
                    'requires_auth' => true,
                    'retrieved_chunks' => $retrieved->pluck('chunk.title')->all(),
                ];
            }

            $variation = $matchedProduct->variations()->where('is_active', true)->first();
            $order = $this->createOrderFromData($user, [
                'items' => [[
                    'product_variation_id' => $variation?->id,
                    'product_name' => $matchedProduct->name,
                    'quantity' => 1,
                ]],
                'delivery_date' => now()->addDay()->toDateString(),
                'delivery_time_slot' => '13:00 - 16:00',
                'payment_method' => 'efectivo',
                'notes' => 'Pedido generado desde Postrecito Bot (RAG)',
            ], source: 'chatbot');

            return [
                'reply' => "¡Listo, **{$user->name}**! He registrado tu pedido **#{$order->order_number}** ({$matchedProduct->name} - {$variation?->name}) para mañana entre **13:00 y 16:00 hrs**.\n\nTotal: **\${$order->total_amount} MXN**.",
                'order_placed' => true,
                'order' => $order,
                'requires_auth' => false,
                'retrieved_chunks' => $retrieved->pluck('chunk.title')->all(),
            ];
        }

        if ($retrieved->isNotEmpty()) {
            $topChunks = $retrieved->take(2)->map(function ($item) {
                /** @var KnowledgeChunk $c */
                $c = $item['chunk'];
                return "**{$c->title}**\n{$c->content}";
            })->implode("\n\n");

            $reply = "En **Postrecito de Jaan** te comparto esta información:\n\n{$topChunks}\n\n¿Te gustaría agregar algún sabor al carrito o hacer un pedido?";
        } else {
            $reply = "¡Hola! Bienvenido a **Postrecito de Jaan**. Pregúntame por nuestros 8 sabores artesanales, tamaños (200ml, 500ml, 1L, Formato Pay), cupones vigentes o zonas de envío.";
        }

        return [
            'reply' => $reply,
            'order_placed' => false,
            'order' => null,
            'requires_auth' => false,
            'retrieved_chunks' => $retrieved->pluck('chunk.title')->all(),
        ];
    }

    /**
     * Create an Order applying dynamic Coupons and ShippingZones.
     */
    public function createOrderFromData(User $user, array $orderData, string $source = 'web'): Order
    {
        $itemsData = $orderData['items'] ?? [];
        $subtotal = 0.0;
        $orderItems = [];

        foreach ($itemsData as $item) {
            $variation = null;
            if (! empty($item['product_variation_id'])) {
                $variation = ProductVariation::with('product')->find($item['product_variation_id']);
            }
            if (! $variation && ! empty($item['product_name'])) {
                $prod = Product::where('name', 'like', "%{$item['product_name']}%")->first();
                if ($prod) {
                    $varQuery = $prod->variations()->where('is_active', true);
                    if (! empty($item['variation_name'])) {
                        $matchedVar = (clone $varQuery)->where('name', 'like', "%{$item['variation_name']}%")->first();
                        $variation = $matchedVar ?? $varQuery->first();
                    } else {
                        $variation = $varQuery->first();
                    }
                }
            }

            if ($variation) {
                $qty = max(1, (int) ($item['quantity'] ?? 1));
                $price = (float) $variation->price;
                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'product_variation_id' => $variation->id,
                    'product_name_snapshot' => $variation->product?->name ?? 'Postrecito',
                    'variation_name_snapshot' => $variation->name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $lineTotal,
                ];
            }
        }

        if (empty($orderItems)) {
            $defaultVar = ProductVariation::with('product')->first();
            $subtotal = (float) $defaultVar->price;
            $orderItems[] = [
                'product_variation_id' => $defaultVar->id,
                'product_name_snapshot' => $defaultVar->product->name,
                'variation_name_snapshot' => $defaultVar->name,
                'quantity' => 1,
                'unit_price' => $subtotal,
                'subtotal' => $subtotal,
            ];
        }

        // Dirección y Zona de Envío dinámica
        $address = ! empty($orderData['address_id'])
            ? Address::where('user_id', $user->id)->find($orderData['address_id'])
            : $user->defaultAddress();

        $shippingZone = ! empty($orderData['shipping_zone_id'])
            ? ShippingZone::find($orderData['shipping_zone_id'])
            : ($address ? ShippingZone::findByQuery($address->zip_code) : ShippingZone::where('is_active', true)->first());

        $shippingCost = $shippingZone
            ? $shippingZone->calculateShippingCost($subtotal)
            : ($subtotal >= 200.00 ? 0.00 : 35.00);

        // Cupón dinámico
        $coupon = null;
        $discountAmount = 0.00;
        if (! empty($orderData['coupon_id'])) {
            $coupon = Coupon::find($orderData['coupon_id']);
        } elseif (! empty($orderData['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper(trim($orderData['coupon_code'])))->first();
        }

        if ($coupon) {
            $validation = $coupon->isValidForSubtotal($subtotal);
            if ($validation['valid']) {
                $discountAmount = $coupon->calculateDiscount($subtotal);
                $coupon->increment('times_used');
            } else {
                $coupon = null;
            }
        }

        $totalAmount = max(0, ($subtotal - $discountAmount) + $shippingCost);
        $addressSnapshot = $orderData['delivery_address_snapshot']
            ?? ($address ? $address->fullAddress() : 'Domicilio registrado en perfil');

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'user_id' => $user->id,
            'address_id' => $address?->id,
            'coupon_id' => $coupon?->id,
            'shipping_zone_id' => $shippingZone?->id,
            'customer_name' => $orderData['customer_name'] ?? $user->name,
            'customer_phone' => $orderData['customer_phone'] ?? $user->phone_number ?? '',
            'delivery_address_snapshot' => $addressSnapshot,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'shipping_cost' => $shippingCost,
            'total_amount' => $totalAmount,
            'delivery_date' => $orderData['delivery_date'] ?? now()->addDay()->toDateString(),
            'delivery_time_slot' => $orderData['delivery_time_slot'] ?? '13:00 - 16:00',
            'status' => 'pending',
            'payment_method' => $orderData['payment_method'] ?? 'efectivo',
            'notes' => $orderData['notes'] ?? null,
            'source' => $source,
        ]);

        foreach ($orderItems as $item) {
            $item['order_id'] = $order->id;
            OrderItem::create($item);
        }

        return $order;
    }

    /**
     * @return array<float>
     */
    protected function generateDeterministicVector(string $text, int $dimensions): array
    {
        $vector = array_fill(0, $dimensions, 0.0);

        // Normalizar acentos, minúsculas y eliminar signos de puntuación
        $normalized = strtr(mb_strtolower(trim($text)), [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n',
        ]);
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $normalized);
        $clean = trim(preg_replace('/\s+/u', ' ', $clean));

        $stopwords = [
            'de', 'la', 'el', 'los', 'las', 'un', 'una', 'unos', 'unas',
            'y', 'o', 'en', 'con', 'para', 'por', 'que', 'cuales', 'cual',
            'son', 'es', 'tienen', 'tiene', 'esta', 'del', 'al', 'a',
            'su', 'sus', 'te', 'se', 'mi', 'tu', 'si', 'no', 'como',
        ];

        $words = array_values(array_filter(
            explode(' ', $clean),
            fn (string $w): bool => $w !== '' && ! in_array($w, $stopwords, true)
        ));

        if (empty($words)) {
            $words = explode(' ', $clean);
        }

        $filteredText = implode(' ', $words);
        $len = mb_strlen($filteredText);

        if ($len >= 3) {
            for ($i = 0; $i <= $len - 3; $i++) {
                $tri = mb_substr($filteredText, $i, 3);
                $idx = abs(crc32($tri)) % $dimensions;
                $vector[$idx] += 1.0;
            }
        }

        foreach ($words as $w) {
            if (mb_strlen($w) < 2) {
                continue;
            }
            $idx = abs(crc32("w:{$w}")) % $dimensions;
            $vector[$idx] += 3.5;
        }

        $normSq = array_sum(array_map(fn ($v) => $v * $v, $vector));
        if ($normSq > 0) {
            $norm = sqrt($normSq);
            for ($i = 0; $i < $dimensions; $i++) {
                $vector[$i] = round($vector[$i] / $norm, 6);
            }
        }

        return $vector;
    }

    protected function formatAuthStatus(?User $user): string
    {
        if (! $user) {
            return 'Invitado (No autenticado). Debe iniciar sesión en /login para confirmar pedidos.';
        }

        $addr = $user->defaultAddress()?->fullAddress() ?? 'Sin dirección predeterminada';
        return "Autenticado: {$user->name} ({$user->email}), Tel: {$user->phone_number}, Dirección: {$addr}.";
    }
}
