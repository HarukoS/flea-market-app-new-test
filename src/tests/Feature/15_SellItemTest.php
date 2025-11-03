<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SellItemTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            'Database\Seeders\UsersTableSeeder',
            'Database\Seeders\CategoriesTableSeeder',
            'Database\Seeders\ConditionsTableSeeder',
        ]);

        $this->user = User::first();
    }

    /**
     * @test
     * 商品出品画面にて必要な情報が保存できること（カテゴリ、商品の状態、商品名、ブランド名、商品の説明、販売価格）
     */
    public function test_item_listing_information_can_be_saved()
    {
        // テスト手順1: ユーザーにログインする
        $this->actingAs($this->user);

        // テスト手順2: 商品出品画面を開く
        $response = $this->get('/sell');
        $response->assertStatus(200);
        $response->assertViewIs('sell');

        // テスト手順3: 各項目に適切な情報を入力して保存する
        Storage::fake('public');

        $postData = [
            'item_image'  => UploadedFile::fake()->create('item.jpg', 100, 'image/jpeg'),
            'categories'  => [1],
            'condition'   => 1,
            'item_name'   => 'テスト商品',
            'brand_name'  => 'テストブランド',
            'description' => 'テスト用説明文',
            'price'       => 5000,
        ];

        $response = $this->actingAs($this->user)->post('/sellitem', $postData);

        $response->assertStatus(200);
        $response->assertViewIs('mypage');

        // 期待挙動: 各項目が正しく保存されている
        $item = Item::first();
        $this->assertEquals($this->user->id, $item->user_id);
        $this->assertEquals('テスト商品', $item->item_name);
        $this->assertEquals('テストブランド', $item->brand_name);
        $this->assertEquals('テスト用説明文', $item->description);
        $this->assertEquals(5000, $item->price);
        $this->assertEquals(1, $item->condition_id);

        $this->assertTrue($item->categories()->where('category_id', 1)->exists());

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $disk->assertExists($item->item_image);
    }
}
