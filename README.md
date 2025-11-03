## 環境構築

### Dockerビルド
    1. [git clone リンク](git@github.com:HarukoS/flea-market-app-new.git)
    2. docker-compose up -d --build
    *MySQLは、OSによって起動しない場合があるのでそれぞれのPCに合わせてdocker-compose.ymlファイルを編集してください。

### Laravel環境構築
    1. docker-compose exec php bash
    2. composer install
    3. .env.exampleファイルから.envを作成し、環境変数を変更
        .envにSTRIPE_KEYを追加
        STRIPE_KEY=pk_test_xxxxxxxxxxxxxxx
        STRIPE_SECRET=sk_test_xxxxxxxxxxxxxxx
        MAIL_FROM_NAME="Coachtech Flea Market App"
    4. php artisan key:generate
    5. php artisan migrate
    6. php artisan db:seed
    7. php artisan storage:link

### ダミーデータ説明
## ユーザー一覧
  ①鈴木太郎
  Name: Taro Suzuki
  Email: user1@gmail.com
  
  ②鈴木花子
  Name: Hanako Suzuki
  Email: user2@gmail.com
  
  ※パスワードは全て"password"

## 商品画像
  商品画像はお手数ですがReleasesの「item_image」のZipファイルをダウンロードしていただき、Storageディレクトリ（src>storage>app>public>item_image）に保存をお願いいたします。

## Stripe決済
  Stripe決済画面ではテスト用カード番号「4242 4242 4242 4242」をお使いください。

## mailhog
 URL: http://localhost:8025

### PHP Unitテスト環境構築
  1. MySQLコンテナ上でテスト用データベース作成
    $ mysql -u root -p
    > CREATE DATABASE demo_test;
    > SHOW DATABASES;
  2. .envファイルをコピーして.env.testingを作成し、環境変数を変更
    APP_NAME=Laravel
    APP_ENV=test
    APP_KEY=
    APP_DEBUG=true
    APP_URL=http://localhost
    DB_CONNECTION=mysql_test
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=demo_test
    DB_USERNAME=root
    DB_PASSWORD=root
  3. php artisan key:generate --env=testing
  4. php artisan config:clear
  5. php artisan migrate --env=testing
  6. php artisan test --testsuite=Feature
