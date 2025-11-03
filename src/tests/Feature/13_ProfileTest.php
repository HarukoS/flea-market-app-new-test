<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileTest extends TestCase
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
     * 必要な情報が取得できる（プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧）
     */
    public function test_user_profile_information_is_retrieved()
    {
        // テスト手順1: ユーザーにログインする
        $user = User::first();
        $this->actingAs($user);

        // テスト手順2: プロフィールページを開く
        $response = $this->get(route('mypage', ['page' => 'sell']));
        $response->assertStatus(200);

        // 期待挙動: プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧が正しく表示される
        //出品した商品一覧
        $userItems = Item::where('user_id', $user->id)->get();
        foreach ($userItems as $item) {
            $response->assertSee($item->item_name);
        }

        $itemOwner = User::where('id', '!=', $user->id)->first();
        $item = Item::where('user_id', $itemOwner->id)->first();

        Purchase::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'カード支払い',
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区1-1-1',
        ]);

        //購入した商品一覧
        $response = $this->get(route('mypage', ['page' => 'buy']));
        $response->assertStatus(200);
        $purchasedItems = Purchase::where('user_id', $user->id)->get()->pluck('item_id');
        foreach ($purchasedItems as $itemId) {
            $item = Item::find($itemId);
            $response->assertSee($item->item_name);
        }

        //ユーザー名
        $response->assertSee($user->name);

        //プロフィール画像
        if ($user->image) {
            $response->assertSee(asset('storage/' . $user->image));
        }
    }
}