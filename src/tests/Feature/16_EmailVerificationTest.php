<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 会員登録後に認証メールが送信される
     */
    public function test_verification_email_is_sent_after_registration()
    {
        Notification::fake();

        // テスト手順1: 会員登録をする
        $user = User::factory()->unverified()->create();

        // テスト手順2: 認証メールを送信する
        $user->sendEmailVerificationNotification();

        // 期待挙動：登録したメールアドレス宛に認証メールが送信されている
        Notification::assertSentTo($user, \App\Notifications\CustomVerifyEmail::class);
    }

    /**
     * @test
     * メール認証を完了すると商品一覧ページに遷移する
     */
    public function test_user_is_redirected_to_item_list_after_email_verification()
    {
        $user = User::factory()->unverified()->create();

        // テスト手順1: メール認証を完了する
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        // テスト手順2: 商品一覧画面を表示する
        // 期待挙動：商品一覧画面に遷移する
        $response->assertRedirect('/');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
