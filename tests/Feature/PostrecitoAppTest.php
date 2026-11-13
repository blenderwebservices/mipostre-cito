<?php

namespace Tests\Feature;

use App\Livewire\RagChatbot;
use App\Livewire\StoreCatalog;
use App\Models\KnowledgeChunk;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\RagService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Embeddings;
use Livewire\Livewire;
use Tests\TestCase;

class PostrecitoAppTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_can_view_public_catalog_without_logging_in(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Mi Postrecito');
        $response->assertSee('Pay de Limón');
        $response->assertSee('Fresas con Crema');
        $response->assertSee('Vaso Individual');
    }

    public function test_guest_cannot_access_mis_pedidos_and_is_redirected_to_login(): void
    {
        $this->get('/mis-pedidos')
            ->assertRedirect('/login');
    }

    public function test_guest_can_add_to_cart_but_checkout_redirects_to_login(): void
    {
        $product = Product::first();

        Livewire::test(StoreCatalog::class)
            ->call('addToCart', $product->id, 2)
            ->assertSet('cart.' . $product->id . '.quantity', 2)
            ->call('proceedToCheckout')
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_place_order_from_catalog(): void
    {
        $user = User::where('email', 'cliente@ejemplo.com')->first();
        $product = Product::where('price', '>=', 135)->first() ?? Product::first();

        $this->actingAs($user);

        Livewire::test(StoreCatalog::class)
            ->call('addToCart', $product->id, 2)
            ->set('customerName', $user->name)
            ->set('customerPhone', '5511223344')
            ->set('deliveryAddress', 'Calle Dulce 123, Col. Centro')
            ->set('scheduledDate', now()->addDay()->toDateString())
            ->set('scheduledSlot', '13:00 - 16:00')
            ->set('paymentMethod', 'efectivo')
            ->call('submitOrder')
            ->assertRedirect(route('orders.index'));

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'delivery_address' => 'Calle Dulce 123, Col. Centro',
            'source' => 'web',
        ]);

        $this->get('/mis-pedidos')
            ->assertStatus(200)
            ->assertSee('Calle Dulce 123, Col. Centro');
    }

    public function test_rag_service_indexes_with_laravel_ai_embeddings_fake_and_retrieves_by_cosine_similarity(): void
    {
        Embeddings::fake();
        config()->set('ai.providers.gemini.key', 'fake-key-for-test');

        $ragService = app(RagService::class);
        $indexedCount = $ragService->indexAll(force: true);

        $this->assertGreaterThan(0, $indexedCount);
        Embeddings::assertGenerated(fn ($prompt) => count($prompt->inputs) === 1);

        $chunk = KnowledgeChunk::whereNotNull('embedding')->first();
        $this->assertNotNull($chunk);
        $this->assertIsArray($chunk->embedding);

        $results = $ragService->retrieve('Pay de Limón', topK: 3, minSimilarity: -1.0);
        $this->assertNotEmpty($results);
    }

    public function test_rag_chatbot_requires_login_when_guest_tries_to_order(): void
    {
        app(RagService::class)->indexAll(force: true);

        Livewire::test(RagChatbot::class)
            ->call('toggleChat')
            ->set('userMessage', 'Quiero pedir un Pay de Limón')
            ->call('sendMessage')
            ->assertSee('Iniciar Sesión para Pedir');
    }

    public function test_rag_chatbot_places_order_for_authenticated_user(): void
    {
        app(RagService::class)->indexAll(force: true);
        $user = User::where('email', 'cliente@ejemplo.com')->first();

        $this->actingAs($user);

        $initialOrders = Order::where('user_id', $user->id)->count();

        Livewire::test(RagChatbot::class)
            ->call('toggleChat')
            ->set('userMessage', 'Quiero pedir un Pay de Limón')
            ->call('sendMessage')
            ->assertSee('Ver mis pedidos');

        $this->assertEquals($initialOrders + 1, Order::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'source' => 'chatbot',
        ]);
    }

    public function test_filament_admin_panel_is_restricted_to_admin_users(): void
    {
        $customer = User::where('email', 'cliente@ejemplo.com')->first();
        $admin = User::where('email', 'admin@postrecito.com')->first();

        $this->actingAs($customer)
            ->get('/admin')
            ->assertStatus(403);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get('/admin/products')
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get('/admin/orders')
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get('/admin/knowledge-chunks')
            ->assertStatus(200);
    }
}
