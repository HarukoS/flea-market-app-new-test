<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;

class LikeTest extends TestCase
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
     * いいねアイコンを押下することによって、いいねした商品として登録することができる。
     */
    public function test_user_can_like_an_item_by_clicking_like_icon()
    {
        // テスト手順1: ユーザーにログインする
        $user = User::first();
        $item = Item::first();

        // テスト手順2: 商品詳細ページを開く
        $response = $this->actingAs($user)->get(route('items.show', $item->id));
        $response->assertStatus(200);

        $initialLikes = $item->likes->count();
        $response->assertSee((string) $initialLikes);

        // テスト手順3: いいねアイコンを押下
        $response = $this->actingAs($user)->post('/like', [
            'item_id' => $item->id,
        ]);

        // 期待挙動: いいねした商品として登録され、いいね合計値が増加表示される
        $response->assertRedirect(route('items.show', $item->id));

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->get(route('items.show', $item->id));
        $response->assertSee((string) ($initialLikes + 1));
    }

    /**
     * @test
     * 追加済みのアイコンは色が変化する
     */
    public function test_like_icon_changes_color_when_item_is_liked()
    {
        // テスト手順1: ユーザーにログインする
        $user = User::first();
        $item = Item::first();

        // テスト手順2: 商品詳細ページを開く
        $response = $this->actingAs($user)->get(route('items.show', $item->id));
        $response->assertStatus(200);
        $response->assertSee('star.png');

        // テスト手順3: いいねアイコンを押下
        $this->actingAs($user)->post('/like', [
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->get(route('items.show', $item->id));
        $response->assertStatus(200);

        // 期待挙動: いいねアイコンが押下された状態では色が変化する
        $response->assertSee('star_like.png');
    }

    /**
     * @test
     * 再度いいねアイコンを押下することによって、いいねを解除することができる。
     */
    public function test_user_can_unlike_an_item_by_clicking_like_icon_again()
    {
        // テスト手順1: ユーザーにログインする
        $user = User::first();
        $item = Item::first();

        Like::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // テスト手順2: 商品詳細ページを開く
        $response = $this->actingAs($user)->get(route('items.show', $item->id));
        $response->assertStatus(200);
        $response->assertSee('star_like.png');

        // テスト手順3: いいねアイコンを押下
        $this->actingAs($user)->post('/like', [
            'item_id' => $item->id,
        ]);

        // 期待挙動: いいねが解除され、いいね合計値が減少表示される
        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->get(route('items.show', $item->id));
        $response->assertSee('star.png');
    }
}
