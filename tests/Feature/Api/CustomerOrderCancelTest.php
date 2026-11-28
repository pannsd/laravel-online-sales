<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerOrderCancelTest extends TestCase
{
    use RefreshDatabase;

    private function createOrderForUser(User $user, string $status = 'pending', int $initialStock = 10, int $orderQty = 3): array
    {
        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik-' . uniqid(),
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Keyboard Mechanical',
            'slug' => 'keyboard-mechanical-' . uniqid(),
            'sku' => 'SKU-' . uniqid(),
            'price' => 250000,
            'stock' => $initialStock,
            'is_active' => true,
        ]);

        $order = Order::create([
            'code' => 'ORD-' . strtoupper(uniqid()),
            'user_id' => $user->id,
            'recipient_name' => $user->name,
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 10',
            'shipping_courier' => 'JNE',
            'shipping_service' => 'REG',
            'shipping_eta' => '2-3 hari',
            'subtotal' => 750000,
            'shipping_cost' => 15000,
            'grand_total' => 765000,
            'status' => $status,
            'ordered_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'price' => 250000,
            'quantity' => $orderQty,
            'subtotal' => 750000,
        ]);

        return [$order, $product];
    }

    public function test_customer_can_cancel_their_own_pending_order_and_restore_stock(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$order, $product] = $this->createOrderForUser($customer, 'pending', initialStock: 7, orderQty: 3);

        Sanctum::actingAs($customer);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertOk()
            ->assertJsonPath('message', 'Pesanan berhasil dibatalkan dan stok produk telah dikembalikan.')
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10, // 7 + 3 kembali
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'IN',
            'quantity' => 3,
            'reference' => 'CANCEL-' . $order->code,
        ]);
    }

    public function test_customer_cannot_cancel_non_pending_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$order, $product] = $this->createOrderForUser($customer, 'confirmed', initialStock: 7, orderQty: 3);

        Sanctum::actingAs($customer);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(409)
            ->assertJsonPath('message', 'Hanya pesanan dengan status pending yang dapat dibatalkan oleh customer.');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 7, // stok tidak berubah
        ]);
    }

    public function test_customer_cannot_cancel_another_users_order(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        [$order] = $this->createOrderForUser($owner, 'pending');

        Sanctum::actingAs($otherCustomer);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(403);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);
    }
}