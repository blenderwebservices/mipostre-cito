<?php

namespace Tests\Feature;

use App\Livewire\CartCheckout;
use App\Livewire\HomePage;
use App\Livewire\MenuCatalog;
use App\Livewire\OrdersPage;
use App\Livewire\ProfilePage;
use App\Livewire\RagChatbot;
use App\Models\KnowledgeChunk;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
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

    public function test_landing_page_shows_active_campaign_and_coverage_validator(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Buen Fin Dulce de Jaan')
            ->assertSee('Verifica tu Cobertura');

        Livewire::test(HomePage::class)
            ->set('coverageQuery', '06700')
            ->call('checkCoverage')
            ->assertNoRedirect()
            ->assertSee('Sí tenemos cobertura en Roma Norte');

        // Dirección completa en México relacionada con CP registrado en RAG/Zonas (06870 Chabacano)
        Livewire::test(HomePage::class)
            ->call('openCoverageModal')
            ->set('coverageAddress', 'Calzada Chabacano 45, Col. Chabacano, CDMX, México')
            ->call('checkCoverage')
            ->assertNoRedirect()
            ->assertSee('Chabacano');

        // Dirección externa a México debe ser rechazada
        Livewire::test(HomePage::class)
            ->call('openCoverageModal')
            ->set('coverageAddress', '123 Main St, New York, USA')
            ->call('checkCoverage')
            ->assertSee('externa a México');

        // CP mexicano válido pero sin cobertura activa
        Livewire::test(HomePage::class)
            ->set('coverageQuery', '99999')
            ->call('checkCoverage')
            ->assertSee('aún no se encuentra en nuestras zonas de cobertura activa');
    }

    public function test_guest_can_browse_menu_and_promos_without_logging_in(): void
    {
        $this->get('/menu')
            ->assertStatus(200)
            ->assertSee('Pay de Limón')
            ->assertSee('Fresas con Crema');

        $this->get('/promos')
            ->assertStatus(200)
            ->assertSee('BUENFIN20')
            ->assertSee('BIENVENIDO15');
    }

    public function test_guest_can_add_to_cart_but_confirm_order_redirects_to_login(): void
    {
        $product = Product::with('variations')->first();
        $variation = $product->variations->first();

        Livewire::test(MenuCatalog::class)
            ->call('addToCart', $product->id)
            ->assertSee('Agregado');

        $this->assertArrayHasKey($variation->id, session('postrecito_cart'));

        Livewire::test(CartCheckout::class)
            ->call('confirmOrder')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_apply_coupon_and_place_order(): void
    {
        $user = User::where('email', 'cliente@ejemplo.com')->first();
        $variation = ProductVariation::where('price', 135.00)->first(); // Familiar 1L ($135)

        $this->actingAs($user);

        // 2 x $135 = $270 subtotal -> qualifies for BUENFIN20 (20% = $54 discount) & free shipping (>= $200)
        session()->put('postrecito_cart', [
            $variation->id => [
                'variation_id' => $variation->id,
                'product_id' => $variation->product_id,
                'product_name' => $variation->product->name,
                'variation_name' => $variation->name,
                'sku' => $variation->sku,
                'price' => (float) $variation->price,
                'quantity' => 2,
            ],
        ]);

        Livewire::test(CartCheckout::class)
            ->set('couponCode', 'BUENFIN20')
            ->call('applyCoupon')
            ->assertSet('couponValid', true)
            ->call('confirmOrder')
            ->assertRedirect(route('orders.index'));

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'subtotal' => 270.00,
            'discount_amount' => 54.00,
            'shipping_cost' => 0.00,
            'total_amount' => 216.00,
            'source' => 'web',
        ]);
    }

    public function test_protected_routes_require_login_and_support_reorder_and_address_book(): void
    {
        $this->get('/mis-pedidos')->assertRedirect('/login');
        $this->get('/profile')->assertRedirect('/login');

        $user = User::where('email', 'cliente@ejemplo.com')->first();
        $order = Order::where('user_id', $user->id)->first();

        $this->actingAs($user);

        $this->get('/mis-pedidos')->assertStatus(200)->assertSee($order->order_number);
        $this->get('/profile')->assertStatus(200)->assertSee('Libreta de Direcciones');

        // One-Click Reorder
        Livewire::test(OrdersPage::class)
            ->call('reorder', $order->id)
            ->assertRedirect(route('cart'));

        $this->assertNotEmpty(session('postrecito_cart'));

        // Address creation
        Livewire::test(ProfilePage::class)
            ->set('tag', 'Casa Mamá')
            ->set('street', 'Calle Amores')
            ->set('exterior_number', '45')
            ->set('neighborhood', 'Del Valle')
            ->set('zip_code', '03100')
            ->call('saveAddress');

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'tag' => 'Casa Mamá',
        ]);
    }

    public function test_rag_service_indexes_with_laravel_ai_embeddings_fake_and_retrieves(): void
    {
        Embeddings::fake();
        config()->set('ai.providers.gemini.key', 'fake-key-for-test');

        $ragService = app(RagService::class);
        $indexedCount = $ragService->indexAll(force: true);

        $this->assertGreaterThan(0, $indexedCount);
        Embeddings::assertGenerated(fn ($prompt) => count($prompt->inputs) === 1);

        $chunk = KnowledgeChunk::whereNotNull('embedding')->first();
        $this->assertNotNull($chunk);

        $results = $ragService->retrieve('Pay de Limón', topK: 3, minSimilarity: -1.0);
        $this->assertNotEmpty($results);
    }

    public function test_rag_chatbot_requires_login_for_guest_and_places_order_for_user(): void
    {
        app(RagService::class)->indexAll(force: true);

        Livewire::test(RagChatbot::class)
            ->call('toggleChat')
            ->set('userMessage', 'Quiero pedir un Pay de Limón')
            ->call('sendMessage')
            ->assertSee('Iniciar Sesión para Pedir');

        $user = User::where('email', 'cliente@ejemplo.com')->first();
        $this->actingAs($user);
        $before = Order::where('user_id', $user->id)->count();

        Livewire::test(RagChatbot::class)
            ->call('toggleChat')
            ->set('userMessage', 'Quiero pedir un Pay de Limón')
            ->call('sendMessage')
            ->assertSee('Ver mis pedidos');

        $this->assertEquals($before + 1, Order::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'source' => 'chatbot',
        ]);
    }

    public function test_rag_chatbot_displays_mocking_and_live_ai_status_indicators(): void
    {
        config()->set('ai.providers.gemini.key', null);

        Livewire::test(RagChatbot::class)
            ->assertSee('Modo Mocking')
            ->call('toggleChat')
            ->assertSee('Modo Mocking');

        config()->set('ai.providers.gemini.key', 'test-gemini-key');

        Livewire::test(RagChatbot::class)
            ->assertSee('IA Activa (Gemini)')
            ->call('toggleChat')
            ->assertSee('IA Activa');
    }

    public function test_rag_chatbot_quick_question_balloons_return_relevant_answers(): void
    {
        app(RagService::class)->indexAll(force: true);

        Livewire::test(RagChatbot::class)
            ->call('toggleChat')
            ->assertSeeHtml('<strong>Postrecito Bot</strong>')
            ->call('askQuickQuestion', '¿Qué sabores y tamaños tienen?')
            ->assertSee('Pay de Limón')
            ->assertSee('Vaso Individual')
            ->call('askQuickQuestion', '¿Cuáles son las zonas y horarios de entrega?')
            ->assertSee('10:00 - 13:00');
    }

    public function test_filament_knowledge_chunk_edit_renders_metadata_without_object_object(): void
    {
        $admin = User::where('email', 'admin@postrecito.com')->first();
        $chunk = KnowledgeChunk::whereNotNull('metadata')->first();

        $this->actingAs($admin)
            ->get("/admin/knowledge-chunks/{$chunk->id}/edit")
            ->assertStatus(200)
            ->assertDontSee('[object Object]');
    }

    public function test_filament_admin_panel_and_v2_resources_are_accessible_only_to_admin(): void
    {
        $customer = User::where('email', 'cliente@ejemplo.com')->first();
        $admin = User::where('email', 'admin@postrecito.com')->first();

        $this->actingAs($customer)->get('/admin')->assertStatus(403);

        $this->actingAs($admin)->get('/admin')->assertStatus(200)->assertSee('Ver Tienda (Front)');
        $this->actingAs($admin)->get('/admin/categories')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/products')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/orders')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/shipping-zones')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/coupons')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/campaigns')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/knowledge-chunks')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/users')->assertStatus(200);
    }
}
