<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            'Database\Seeders\UsersTableSeeder',
        ]);

        $this->user = User::first();
    }

    /**
     * @test
     * 変更項目が初期値として過去設定されていること（プロフィール画像、ユーザー名、郵便番号、住所）
     */
    public function test_editable_fields_are_prepopulated_with_existing_values()
    {
        // テスト手順1: ユーザーにログインする
        $this->actingAs($this->user);

        // テスト手順2: プロフィール編集ページを開く
        $response = $this->get(route('profile'));
        $response->assertStatus(200);

        // 期待挙動: 各項目の初期値が正しく表示されている
        // 名前
        $response->assertSee($this->user->name);
        // 郵便番号
        $response->assertSee($this->user->postal_code);
        // 住所
        $response->assertSee($this->user->address);
        // 建物名
        $response->assertSee($this->user->building);
        // プロフィール画像URL
        $response->assertSee(asset('storage/' . $this->user->image));
    }
}
