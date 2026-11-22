<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_storefront_pages_load_successfully(): void
    {
        foreach (['splash', 'shop', 'collections', 'lookbooks', 'archive', 'story', 'run-club', 'help', 'order.track'] as $routeName) {
            $this->get(route($routeName, [], false))->assertStatus(200);
        }
    }

    public function test_guest_cannot_access_admin_or_logo_studio(): void
    {
        $this->get(route('admin.dashboard', [], false))->assertRedirect('/shop');
        $this->get(route('logo.studio', [], false))->assertRedirect('/shop');
    }

    public function test_admin_can_access_dashboard_and_logo_studio(): void
    {
        $admin = User::where('is_admin', true)->firstOrFail();

        $this->actingAs($admin)->get(route('admin.dashboard', [], false))->assertStatus(200);
        $this->actingAs($admin)->get(route('logo.studio', [], false))->assertStatus(200);
    }

    public function test_add_to_cart_and_predictive_search_work(): void
    {
        $this->getJson(route('search', ['q' => 'tee'], false))
            ->assertStatus(200)
            ->assertJsonStructure(['results']);

        $this->postJson(route('cart.add', [], false), [
            'slug' => 'kotb-block-heavyweight-tee',
            'size' => 'L',
            'quantity' => 1,
        ])->assertStatus(200)->assertJson(['count' => 1]);
    }

    public function test_paymongo_webhook_marks_order_paid(): void
    {
        $order = \App\Models\Order::create([
            'order_number' => 'KOTB-2026-9999',
            'customer_email' => 'buyer@kotb.club',
            'payment_method' => 'paymongo',
            'payment_status' => 'pending_payment',
            'subtotal' => 950.00,
            'total' => 1100.00,
            'items' => [],
            'status' => 'pending_verification',
        ]);

        $this->postJson('/webhooks/paymongo', [
            'data' => [
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'attributes' => [
                            'reference_number' => 'KOTB-2026-9999',
                        ],
                    ],
                ],
            ],
        ])->assertStatus(200)->assertJson(['received' => true]);

        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertEquals('confirmed', $order->fresh()->status);
    }

    public function test_product_fit_review_can_be_submitted(): void
    {
        $this->post('/products/kotb-block-heavyweight-tee/reviews', [
            'reviewer_name' => 'Juan',
            'rating' => 5,
            'fit_feedback' => 'Boxy Oversized',
            'comment' => 'Heavy 240 GSM cotton, super clean print.',
        ])->assertRedirect();

        $this->assertDatabaseHas('product_reviews', [
            'reviewer_name' => 'Juan',
            'fit_feedback' => 'Boxy Oversized',
        ]);
    }

    public function test_live_chat_saves_and_admin_can_reply(): void
    {
        $this->postJson('/chat/send', ['message' => 'What is your sizing?'])
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'What is your sizing?'])
            ->assertJsonFragment(['sender' => 'admin']);

        $msg = \App\Models\ChatMessage::where('sender', 'visitor')->first();
        $this->assertNotNull($msg);

        $admin = User::where('is_admin', true)->firstOrFail();
        $this->actingAs($admin)->post("/admin/chat/{$msg->session_id}/reply", [
            'message' => 'Yes, Size L is in stock!',
        ])->assertRedirect();

        $this->assertDatabaseHas('chat_messages', [
            'session_id' => $msg->session_id,
            'sender' => 'admin',
            'message' => 'Yes, Size L is in stock!',
        ]);
    }
}
