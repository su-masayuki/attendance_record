# Attendance Record

勤怠管理アプリケーション（Laravel + Docker環境）

## 環境

- PHP 8.x
- Laravel 10.x
- MySQL 8.0
- Mailtrap（メール認証用）
- Docker, Docker Compose

## セットアップ手順

1. リポジトリをクローン
    ```bash
    git clone https://github.com/su-masayuki/attendance_record.git
    cd attendance_record
    ```

2. Docker コンテナを起動
    ```bash
    docker compose up -d
    ```

3. コンテナ内で依存パッケージをインストール
    ```bash
    docker compose exec app composer install
    ```

4. `.env` ファイルを設定
    ```bash
    cp .env.example .env
    docker compose exec app php artisan key:generate
    ```

5. データベースマイグレーション＆シーディング
    ```bash
    docker compose exec app php artisan migrate --seed
    ```

6. Mailtrap設定を `.env` に追加（メール認証用）

## 主な機能

- ユーザー登録・ログイン（メール認証あり）
- 出勤・退勤・休憩時間の記録
- 勤怠一覧表示
- 修正申請機能（承認フロー）
- 管理者による勤怠管理・承認機能
- CSVエクスポート機能

## ディレクトリ構成

- `src/` : アプリケーション本体
- `docker/` : Docker設定ファイル

## 重要なコマンド

- コンテナ起動: `docker compose up -d`
- コンテナ停止: `docker compose down`
- マイグレーション: `docker compose exec app php artisan migrate`
- シーディング: `docker compose exec app php artisan db:seed`

## 注意事項

- 管理者ログインと一般ユーザーのログインは動線が分かれています。

## ログイン情報

### 管理者ログイン
- メールアドレス: admin@coachtech.com
- パスワード: password

### 一般ユーザーログイン
- 西 伶奈  
  - メールアドレス: reina.n@coachtech.com
  - パスワード: password
- 山田 太郎  
  - メールアドレス: taro.y@coachtech.com
  - パスワード: password
- 増田 一世  
  - メールアドレス: issei.m@coachtech.com
  - パスワード: password
- 山本 敬吉  
  - メールアドレス: keikichi.y@coachtech.com
  - パスワード: password
- 秋田 朋美  
  - メールアドレス: tomomi.a@coachtech.com
  - パスワード: password
- 中西 教夫  
  - メールアドレス: norio.n@coachtech.com
  - パスワード: password