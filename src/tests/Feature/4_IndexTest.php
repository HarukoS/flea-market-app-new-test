<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\User;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
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
     * 全商品を取得できる
     */
    public function test_all_items_can_be_retrieved()
    {
        // テスト手順1: 商品ページを開く
        $response = $this->get('/');
        $response->assertStatus(200);

        // 期待挙動: すべての商品が表示される
        $itemsCount = Item::count();
        $response->assertSeeText(Item::first()->item_name);
    }

    /**
     * @test
     * 購入済み商品は「sold」と表示される
     */
    public function test_purchased_items_are_displayed_as_sold()
    {
        $user = User::first();
        $item = Item::first();

        Purchase::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'credit_card',
            'postal_code'    => '123-4567',
            'address'        => '東京都渋谷区1-1-1',
        ]);

        // テスト手順1: 商品ページを開き、購入済み商品を表示する
        $response = $this->get('/');
        $response->assertStatus(200);

        // 期待挙動: 購入済み商品に「Sold」のラベルが表示される
        $response->assertSee('SOLD');
    }

    /**
     * @test
     * 自分が出品した商品は表示されない
     */
    public function test_items_list_does_not_include_user_owned_items()
    {
        // テスト手順1: ユーザーにログインする
        $user = User::first();
        $this->actingAs($user);

        // テスト手順2: 商品ページを開く
        $response = $this->get('/');

        // 期待挙動: 自分が出品した商品が一覧に表示されない
        $myItems = Item::where('user_id', $user->id)->get();
        foreach ($myItems as $item) {
            $response->assertDontSee($item->item_name);
        }

        $response->assertStatus(200);
    }
}
