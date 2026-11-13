<?php

namespace App\Services;

use App\Models\KnowledgeChunk;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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
     * Uses Laravel AI Embeddings (Gemini), falling back to local normalized hash vector if API key is not yet set.
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
            $embedding = $this->generateEmbedding($chunk->content);
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

        // Deterministic fallback vector (so the system works out-of-the-box before placing GEMINI_API_KEY in .env)
        return $this->generateDeterministicVector($text, $dimensions);
    }

    /**
     * Retrieve the top-K relevant knowledge chunks using cosine similarity over embeddings.
     *
     * @return Collection<int, array{chunk: KnowledgeChunk, similarity: float}>
     */
    public function retrieve(string $query, int $topK = 5, float $minSimilarity = 0.15): Collection
    {
        $queryEmbedding = $this->generateEmbedding($query);
        $chunks = KnowledgeChunk::where('is_active', true)->whereNotNull('embedding')->get();

        if ($chunks->isEmpty()) {
            // Index on the fly if not yet indexed
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

    /**
     * Process a chat interaction:
     * 1. Retrieval (Embeddings + Cosine Similarity)
     * 2. Context augmented generation
     * 3. Intent detection (Order placement or question answering)
     *
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{
     *     reply: string,
     *     order_placed: bool,
     *     order: ?Order,
     *     requires_auth: bool,
     *     retrieved_chunks: array<string>
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
Eres "Postrecito Bot", el asistente virtual y sommelier dulce oficial de "Postrecito de Jaan" (inspirado en la practicidad y entregas programadas de Mi Mandadito).
Tu objetivo es ayudar a los clientes a conocer nuestros postres artesanales de Janet, resolver dudas de sabores, tamaños, precios, envíos y armar/confirmar pedidos.

REGLAS DE ATENCIÓN:
1. Sé cálido, antojable, claro y servicial. Usa un tono alegre pero profesional.
2. Basa tus respuestas rigurosamente en el SIGUIENTE CONTEXTO RECUPERADO (RAG) de nuestro catálogo y servicio:
---
{$contextText}
---

DATOS DEL CLIENTE ACTUAL:
- Autenticado: {$this->formatAuthStatus($user)}

POLÍTICA DE PEDIDOS:
- Los clientes pueden consultar el menú, armar su lista y pedir recomendaciones sin registrarse.
- Si un usuario no registrado pide hacer o confirmar un pedido, indícale amablemente los productos seleccionados y dile que para completar la orden debe iniciar sesión o crear su cuenta rápida en el botón "Iniciar Sesión" del menú superior.
- Si el usuario YA ESTÁ AUTENTICADO y pide concretar un pedido indicando qué desea (por ejemplo: "quiero pedir 2 pays de limón vaso individual y 1 familiar de fresas"), debes confirmar el pedido y responder con un JSON en tu salida.

FORMATO DE RESPUESTA:
Debes responder SIEMPRE en formato JSON válido con la siguiente estructura:
{
  "reply": "Tu mensaje explicativo o de respuesta amigable en markdown para el cliente.",
  "action": "none|request_login|place_order",
  "order_data": {
    "items": [
      {
        "product_id": 1,
        "product_name": "Nombre aproximado o SKU",
        "quantity": 2
      }
    ],
    "delivery_zone": "Zona del cliente o default",
    "delivery_address": "Dirección especificada o la de su perfil",
    "scheduled_date": "YYYY-MM-DD (fecha para entrega, por defecto mañana si no se especifica)",
    "scheduled_slot": "10:00 - 13:00 | 13:00 - 16:00 | 16:00 - 19:00",
    "payment_method": "efectivo | transferencia | tarjeta_entrega",
    "notes": "Notas adicionales o vacio"
  }
}
Si la acción es "none", deja "order_data" como null.
PROMPT;

        if (! empty($apiKey)) {
            try {
                $llmResult = $this->callGeminiApi($systemPrompt, $userMessage, $history, $apiKey, $chatModel);
                if ($llmResult) {
                    return $this->handleParsedResponse($llmResult, $user, $retrieved);
                }
            } catch (Throwable $e) {
                Log::warning('Direct Gemini Chat generation failed, falling back to local deterministic RAG response: ' . $e->getMessage());
            }
        }

        // Fallback local RAG response engine when API key is pending or unreachable
        return $this->deterministicLocalRagEngine($userMessage, $user, $retrieved);
    }

    /**
     * Execute Gemini API call with structured JSON response.
     */
    protected function callGeminiApi(string $systemPrompt, string $userMessage, array $history, string $apiKey, string $model): ?array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $contents = [];
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => "INSTRUCCIONES DEL SISTEMA:\n" . $systemPrompt]],
        ];
        $contents[] = [
            'role' => 'model',
            'parts' => [['text' => 'Entendido. Actuaré como Postrecito Bot respetando el catálogo y respondiendo en el JSON indicado.']],
        ];

        foreach (array_slice($history, -6) as $msg) {
            $role = ($msg['role'] ?? 'user') === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content'] ?? '']],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userMessage]],
        ];

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.2,
                'responseMimeType' => 'application/json',
            ],
        ];

        $response = Http::timeout(30)->post($url, $payload);

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

    /**
     * Handle LLM parsed response and create order if requested.
     */
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
            $reply .= "\n\n💡 *Recuerda que para guardar tu entrega programada necesitas iniciar sesión. Haz clic en el botón superior para ingresar o registrarte en 10 segundos.*";
        } elseif ($action === 'place_order' && $user && ! empty($orderData['items'])) {
            $order = $this->createOrderFromData($user, $orderData, source: 'chatbot');
            if ($order) {
                $orderPlaced = true;
                $reply .= "\n\n🎉 **¡Pedido confirmado con éxito!** Folio: **#{$order->order_number}** por un total de **\${$order->total} MXN**. Puedes consultar el estado en 'Mis Pedidos'.";
            }
        }

        return [
            'reply' => $reply,
            'order_placed' => $orderPlaced,
            'order' => $order,
            'requires_auth' => $requiresAuth,
            'retrieved_chunks' => $retrieved->pluck('chunk.title')->all(),
        ];
    }

    /**
     * Local deterministic RAG response engine (operates cleanly even without API key set).
     */
    protected function deterministicLocalRagEngine(string $userMessage, ?User $user, Collection $retrieved): array
    {
        $lower = mb_strtolower($userMessage);
        $orderPlaced = false;
        $order = null;
        $requiresAuth = false;

        // Intent detection: wants to place order
        $isOrderingIntent = str_contains($lower, 'pedir') ||
            str_contains($lower, 'ordenar') ||
            str_contains($lower, 'comprar') ||
            str_contains($lower, 'hacer pedido') ||
            str_contains($lower, 'quiero');

        // Check if message references products
        $matchedProducts = Product::with(['flavor', 'presentation'])
            ->where('is_available', true)
            ->get()
            ->filter(function (Product $p) use ($lower) {
                $fName = mb_strtolower($p->flavor?->name ?? '');
                $pName = mb_strtolower($p->name);
                return (! empty($fName) && str_contains($lower, $fName)) || str_contains($lower, $pName);
            });

        if ($isOrderingIntent && $matchedProducts->isNotEmpty()) {
            if (! $user) {
                $requiresAuth = true;
                $prodNames = $matchedProducts->take(3)->pluck('name')->implode(', ');
                $reply = "¡Excelente elección! Veo que te interesa ordenar: **{$prodNames}**.\n\n" .
                    "Al igual que en Mi Mandadito, para programar tu fecha, franja horaria y dirección de entrega **es necesario que inicies sesión o te registres**.\n" .
                    "Por favor haz clic en **'Iniciar Sesión / Registro'** en la barra superior y tu pedido quedará listo al instante.";
            } else {
                // User is authenticated, create order for matching products
                $items = [];
                foreach ($matchedProducts->take(3) as $prod) {
                    $items[] = [
                        'product_id' => $prod->id,
                        'product_name' => $prod->name,
                        'quantity' => 1,
                    ];
                }

                $orderData = [
                    'items' => $items,
                    'delivery_zone' => $user->default_zone ?? 'Zona Centro / Roma / Condesa',
                    'delivery_address' => $user->default_address ?? 'Dirección registrada en perfil',
                    'scheduled_date' => now()->addDay()->toDateString(),
                    'scheduled_slot' => '13:00 - 16:00',
                    'payment_method' => 'efectivo',
                    'notes' => 'Pedido generado automáticamente desde el Chatbot RAG',
                ];

                $order = $this->createOrderFromData($user, $orderData, source: 'chatbot');
                $orderPlaced = true;
                $reply = "¡Listo, **{$user->name}**! He registrado tu pedido **#{$order->order_number}** con entrega programada para mañana entre **13:00 y 16:00 hrs** en **{$order->delivery_address}**.\n\n" .
                    "Total a pagar en efectivo contra entrega: **\${$order->total} MXN**.\n" .
                    "¡Muchas gracias por apoyar a Postrecito de Jaan! Puedes revisar los detalles en la sección **Mis Pedidos**.";
            }

            return [
                'reply' => $reply,
                'order_placed' => $orderPlaced,
                'order' => $order,
                'requires_auth' => $requiresAuth,
                'retrieved_chunks' => $retrieved->pluck('chunk.title')->all(),
            ];
        }

        // Q&A using retrieved context
        if ($retrieved->isNotEmpty()) {
            $bestChunk = $retrieved->first()['chunk'];
            $reply = "En **Postrecito de Jaan** cuidamos cada detalle artesanal:\n\n" .
                "**{$bestChunk->title}**\n" .
                "{$bestChunk->content}\n\n" .
                "¿Te gustaría que te ayude a programar una entrega a tu domicilio o cotizar algún tamaño para tu evento?";
        } else {
            $reply = "¡Hola! Bienvenido a **Postrecito de Jaan** (la dulzura artesanal estilo Mi Mandadito).\n\n" .
                "Contamos con 8 deliciosos sabores artesanales (Pay de Limón, Fresas con Crema, Mousse de Oreo, Carlota de Mango, etc.) en Vaso de 200 ml (\$35), Medio Litro (\$75), Familiar de 1 Litro (\$135) y Formato Pay (\$220).\n\n" .
                "¿Qué sabor se te antoja probar hoy?";
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
     * Create an order from structured data.
     */
    public function createOrderFromData(User $user, array $orderData, string $source = 'web'): Order
    {
        $itemsData = $orderData['items'] ?? [];
        $subtotal = 0.0;
        $orderItems = [];

        foreach ($itemsData as $item) {
            $product = null;
            if (! empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
            }
            if (! $product && ! empty($item['product_name'])) {
                $product = Product::where('name', 'like', "%{$item['product_name']}%")->first();
            }

            if ($product) {
                $qty = max(1, (int) ($item['quantity'] ?? 1));
                $price = (float) $product->price;
                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $lineTotal,
                ];
            }
        }

        // If no valid items parsed, pick the first product as safety default
        if (empty($orderItems)) {
            $defaultProduct = Product::first();
            $qty = 1;
            $subtotal = (float) $defaultProduct->price;
            $orderItems[] = [
                'product_id' => $defaultProduct->id,
                'product_name' => $defaultProduct->name,
                'quantity' => $qty,
                'unit_price' => $subtotal,
                'subtotal' => $subtotal,
            ];
        }

        // Delivery fee: free from $150 MXN, else $35 MXN
        $deliveryFee = $subtotal >= 150.00 ? 0.00 : 35.00;
        $total = $subtotal + $deliveryFee;

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => 'confirmado',
            'customer_name' => $orderData['customer_name'] ?? $user->name,
            'customer_phone' => $orderData['customer_phone'] ?? $user->phone ?? '5500000000',
            'delivery_zone' => $orderData['delivery_zone'] ?? $user->default_zone ?? 'Zona Centro / Roma / Condesa',
            'delivery_address' => $orderData['delivery_address'] ?? $user->default_address ?? 'Domicilio principal',
            'scheduled_date' => $orderData['scheduled_date'] ?? now()->addDay()->toDateString(),
            'scheduled_slot' => $orderData['scheduled_slot'] ?? '13:00 - 16:00',
            'payment_method' => $orderData['payment_method'] ?? 'efectivo',
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
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
     * Generate a deterministic normalized vector for text based on char-trigrams.
     * Guaranteed to produce stable, semantic-friendly vectors without external network calls.
     *
     * @return array<float>
     */
    protected function generateDeterministicVector(string $text, int $dimensions): array
    {
        $vector = array_fill(0, $dimensions, 0.0);
        $clean = mb_strtolower(trim($text));
        $len = mb_strlen($clean);

        if ($len < 3) {
            $clean = str_pad($clean, 3, ' ');
            $len = 3;
        }

        for ($i = 0; $i <= $len - 3; $i++) {
            $tri = mb_substr($clean, $i, 3);
            $hash = crc32($tri);
            $idx = abs($hash) % $dimensions;
            $vector[$idx] += 1.0;
        }

        // Add word token projections
        $words = preg_split('/\s+/u', $clean);
        foreach ($words as $w) {
            if ($w === '') continue;
            $idx = abs(crc32($w)) % $dimensions;
            $vector[$idx] += 2.0;
        }

        // Normalize to unit length (L2 norm)
        $normSq = 0.0;
        foreach ($vector as $v) {
            $normSq += $v * $v;
        }

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
            return 'No autenticado (Invitado). No puede confirmar pedidos directamente sin iniciar sesión.';
        }

        return "Autenticado como {$user->name} ({$user->email}). Tel: {$user->phone}, Dirección: {$user->default_address}, Zona: {$user->default_zone}. Puede confirmar pedidos directamente.";
    }
}

