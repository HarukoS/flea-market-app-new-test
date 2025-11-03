<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTest extends TestCase
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
     * 購入するボタンを押下すると購入が完了する
     */
    public function test_user_can_complete_purchase_by_clicking_buy_button()
    {
        // テスト手順1: ユーザーにログインする
        $user = User::first();
        $item = Item::first();

        // テスト手順2: 商品購入画面を開く
        $response = $this->actingAs($user)->get(route('purchase.page', $item->id));
        $response->assertStatus(200);
        $response->assertSee($item->item_name);

        // テスト手順3: 商品を選択して「購入する」ボタンを押下
        $postData = [
            'item_id' => $item->id,
            'payment_method' => 'カード支払い',
            'postal_code' => $user->postal_code ?? '100-0001',
            'address' => $user->address ?? '東京都千代田区1-1',
        ];
        $response = $this->actingAs($user)->post(route('purchase.pre', ['item' => $item->id]), $postData);

        // 期待挙動：購入が完了する
        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    /**
     * @test
     * 購入した商品は一覧画面にて「sold」と表示される
     */
    public function test_purchased_items_are_marked_as_sold_in_list()
    {
        // テスト手順1: ユーザーにログインする
        $user = User::first();
        $itemOwner = User::where('id', '!=', $user->id)->first();
        $item = Item::where('user_id', $itemOwner->id)->first();

        // テスト手順2: 商品購入画面を開く
        $response = $this->actingAs($user)->get(route('purchase.page', $item->id));
        $response->assertStatus(200);
        $response->assertSee($item->item_name);

        // テスト手順3: 商品を選択して「購入する」ボタンを押下
        $postData = [
            'item_id' => $item->id,
            'payment_method' => 'カード支払い',
            'postal_code' => $user->postal_code ?? '100-0001',
            'address' => $user->address ?? '東京都千代田区1-1',
        ];
        $response = $this->actingAs($user)->post(route('purchase.pre', ['item' => $item->id]), $postData);

        // テスト手順4: 商品一覧画面を表示する
        $response = $this->actingAs($user)->get(route('items.index'));
        $response->assertStatus(200);

        // 期待挙動：購入した商品が「sold」として表示されている
        $response->assertSee('SOLD');
    }

    /**
     * @test
     * 「プロフィール／購入した商品一覧」に追加されている
     */
    public function test_purchased_items_appear_in_user_profile_purchase_list()
    {
        // テスト手順1: ユーザーにログインする
        $user = User::first();
        $itemOwner = User::where('id', '!=', $user->id)->first();
        $item = Item::where('user_id', $itemOwner->id)->first();

        // テスト手順2: 商品購入画面を開く
        $response = $this->actingAs($user)->get(route('purchase.page', $item->id));
        $response->assertStatus(200);
        $response->assertSee($item->item_name);

        // テスト手順3: 商品を選択して「購入する」ボタンを押下
        $postData = [
            'item_id' => $item->id,
            'payment_method' => 'カード支払い',
            'postal_code' => $user->postal_code ?? '100-0001',
            'address' => $user->address ?? '東京都千代田区1-1',
        ];
        $response = $this->actingAs($user)->post(route('purchase.pre', ['item' => $item->id]), $postData);

        // テスト手順4: プロフィール画面を表示する
        $response = $this->actingAs($user)->get(route('mypage', ['page' => 'buy']));
        $response->assertStatus(200);

        // 期待挙動：購入した商品がプロフィールの購入した商品一覧に追加されている
        $response->assertSee($item->item_name);
    }
}
