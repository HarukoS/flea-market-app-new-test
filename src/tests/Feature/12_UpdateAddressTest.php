<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateAddressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            'Database\Seeders\UsersTableSeeder',
            'Database\Seeders\ConditionsTableSeeder',
            'Database\Seeders\ItemsTableSeeder',
        ]);
    }

    /**
     * @test
     * 送付先住所変更画面にて登録した住所が商品購入画面に反映されている
     */
    public function test_updated_address_is_reflected_in_purchase_page()
    {
        // テスト手順1: ユーザーにログインする
        $user = User::first();
        $item = Item::first();

        $this->actingAs($user);

        // テスト手順2: 送付先住所変更画面で住所を登録する
        $response = $this->get(route('purchase.address.edit', $item->id));
        $response->assertStatus(200);

        $addressData = [
            'postal_code' => '123-4567',
            'address'     => '東京都新宿区1-1-1',
            'building'    => 'テストビル101',
        ];

        $response = $this->post(route('purchase.address.update', $item->id), $addressData);
        $response->assertRedirect(route('purchase.page', $item->id));

        $this->assertEquals(session('purchase_address.postal_code'), '123-4567');
        $this->assertEquals(session('purchase_address.address'), '東京都新宿区1-1-1');
        $this->assertEquals(session('purchase_address.building'), 'テストビル101');

        // テスト手順3: 商品購入画面を再度開く
        $response = $this->get(route('purchase.page', $item->id));

        // 期待挙動：登録した住所が商品購入画面に正しく反映される
        $response->assertStatus(200);
        $response->assertSee('〒 123-4567');
        $response->assertSee('東京都新宿区1-1-1');
        $response->assertSee('テストビル101');
    }

    /**
     * @test
     * 購入した商品に送付先住所が紐づいて登録される
     */
    public function test_address_is_linked_to_purchased_item()
    {
        // テスト手順1: ユーザーにログインする
        $user = User::first();
        $item = Item::first();

        $this->actingAs($user);

        // テスト手順2: 送付先住所変更画面で住所を登録する
        $response = $this->get(route('purchase.address.edit', $item->id));
        $response->assertStatus(200);

        $addressData = [
            'postal_code' => '123-4567',
            'address'     => '東京都新宿区1-1-1',
            'building'    => 'テストビル101',
        ];
        session(['purchase_address' => $addressData]);

        // テスト手順3: 商品を購入する
        $postData = [
            'item_id'        => $item->id,
            'payment_method' => 'カード支払い',
            'postal_code'    => $addressData['postal_code'],
            'address'        => $addressData['address'],
            'building'       => $addressData['building'],
        ];

        $this->actingAs($user)->post(route('purchase.pre', ['item' => $item->id]), $postData);

        //期待挙動： 正しく送付先住所が紐づいている
        $this->assertDatabaseHas('purchases', [
            'user_id'     => $user->id,
            'item_id'     => $item->id,
            'postal_code' => '123-4567',
            'address'     => '東京都新宿区1-1-1',
            'building'    => 'テストビル101',
        ]);
    }
}