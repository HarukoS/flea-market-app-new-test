<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Item;
use App\Models\User;
use App\Models\Like;

class ItemSearchTest extends TestCase
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
     * 「商品名」で部分一致検索ができる
     */
    public function test_can_search_items_by_partial_name()
    {
        // テスト手順1: 検索欄にキーワードを入力
        $keyword = '腕時計';

        $response = $this->get('/?search=' . $keyword);

        $response->assertStatus(200);

        // 期待挙動：部分一致する商品が表示される
        $matchingItems = Item::where('item_name', 'like', "%{$keyword}%")->get();
        foreach ($matchingItems as $item) {
            $response->assertSee($item->item_name);
        }
    }

    /**
     * @test
     * 検索状態がマイリストでも保持されている
     */
    public function test_search_filters_are_preserved_in_my_list()
    {
        $user = User::first();
        $itemOwner = User::where('id', '<>', $user->id)->first();

        $keyword = '腕時計';
        $likedItem = Item::where('item_name', 'like', "%{$keyword}%")
            ->where('user_id', $itemOwner->id)
            ->first();

        if (!$likedItem) {
            $likedItem = Item::create([
                'user_id' => $itemOwner->id,
                'item_name' => 'テスト' . $keyword,
                'condition_id' => 1,
                'price' => 1000,
                'image_url' => 'test.jpg',
                'description' => 'テスト説明',
            ]);
        }

        Like::create([
            'user_id' => $user->id,
            'item_id' => $likedItem->id,
        ]);

        // テスト手順1: ホームページで商品を検索
        $response = $this->actingAs($user)->get('/?search=' . $keyword);
        $response->assertStatus(200);

        // テスト手順2: 検索結果が表示される
        $response->assertSee($keyword);
        $response->assertSee($likedItem->item_name);

        // テスト手順3: マイリストページに遷移
        $response = $this->get('/?tab=mylist&search=' . $keyword);
        $response->assertStatus(200);

        // 期待挙動：検索キーワードが保持されている
        $response->assertSee($keyword);
        $response->assertSee($likedItem->item_name);
    }
}
