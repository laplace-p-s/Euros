# Euros — プロジェクト概要

勤怠打刻・月次集計 Web アプリ。個人ユーザーが出勤/退勤を打刻し、月次カレンダーで記録を管理する。

- **Framework:** Laravel 10 / PHP 8.1–8.3
- **DB:** MySQL（セッションも DB 管理、タイムアウト 10 分）
- **Auth:** Laravel Breeze（メール認証付き）+ Laravel Sanctum（API）

---

## フロントエンド技術スタック

| ツール | バージョン | 用途 |
|---|---|---|
| TailwindCSS | 3.1 | スタイリング（ダークモード対応） |
| AlpineJS | 3.4 | ナビゲーション等の軽量インタラクション |
| jQuery | 3.6 | AJAX・DOM 操作のメイン処理 |
| FontAwesome | 5.15 | アイコン |
| Vite | 6.4 | ビルドツール（laravel-vite-plugin） |

---

## 画面一覧

| URL | 画面名 | 概要 |
|---|---|---|
| `/home` | ホーム | 本日の打刻状況 + 月次サマリー |
| `/search` | 月次検索 | カレンダー形式の月次一覧 |
| `/detail` (POST) | 打刻詳細 | 特定日の打刻レコード詳細・削除 |
| `/settings` | 設定メニュー | 設定トップ |
| `/settings/holiday` | 休日管理 | 休日の追加・削除 |
| `/tools` | ツールメニュー | ツール一覧 |
| `/paid_leave` | 有給計算 | 有給休暇計算ツール |
| `/profile` | プロフィール | 名前・メール・パスワード変更、アカウント削除 |
| `/login` 等 | 認証画面群 | Breeze 標準（ログイン・登録・パスリセット等） |

### AJAX エンドポイント（ApiController）

| URL | 機能 |
|---|---|
| POST `/register_rec` | 出退勤打刻 |
| POST `/memo_edit` | メモ作成・更新 |
| POST `/renewal_info` | 当日打刻情報の再取得 |
| POST `/settings/holiday/delete` | 休日削除 |

---

## 主要モデルとリレーション

```
User (users)
  │
  ├─── Record (records)        method: 1=出勤 / 2=退勤, is_manual: 0=自動 / 1=手動
  ├─── Memo (memos)            日付ごとのメモ（1日1件）
  └─── Holiday (holidays)      ユーザー個別の休日設定

HolidayTemplate (holiday_templates)  年別共通テンプレート（user_id なし）
```

モデルにリレーション定義はなし。Controller・`Common.php` でクエリを直接記述する。

---

## 設計パターン・独自規約

### `app/Libs/Common.php` に業務ロジックを集約

Services 層は存在しない。勤務時間計算・カレンダー生成・月次集計はすべてこの静的クラスに記述する。

| メソッド | 役割 |
|---|---|
| `getTimeInfo()` | 当日の出退勤時刻・勤務時間取得 |
| `generateCalendar(year, month)` | カレンダー骨格生成 |
| `setDatabaseData(&list, year, month)` | カレンダーに DB 値を充填 |
| `getSummaryInfo(now, calendar, current_now)` | 月次集計（フルday / ハーフday / 休日出勤を区分） |

勤務時間の控除ルール: **4.5 時間超の場合は自動で 1 時間の昼休みを控除**。

### AJAX は ApiController に集約

画面用コントローラーとは別に、jQuery から叩く AJAX 専用コントローラー（`ApiController`）を分離する。

### HolidayTemplate → Holiday の二層構造

年別の休日テンプレートをマスターデータとして持ち、ユーザーが自分の `holidays` テーブルに一括コピーする。

---

## 新規画面の追加パターン

```
1. app/Http/Controllers/XxxController.php を作成
   └── Controller を extend
   └── 複雑な計算は Common::xxx() を呼び出す

2. routes/web.php の auth ミドルウェアグループにルートを追加
   Route::get('/xxx', [XxxController::class, 'show'])->name('xxx.show');

3. resources/views/xxx.blade.php を作成
   @extends('layouts.app')
   @section('header') ... @endsection
   @section('content') ... @endsection

4. ナビゲーションに追加する場合
   → resources/views/layouts/navigation.blade.php を編集

5. AJAX が必要な場合
   → ApiController に新メソッド追加 + web.php に POST ルートを追加
   → フロントは jQuery の $.ajax / $.post で呼び出す

6. 入力バリデーションが必要な場合
   → app/Http/Requests/XxxRequest.php を作成して FormRequest を使う
```

---

## コーディング規約

### 全般
- コメントは日本語で記述する
- バリデーションは `app/Http/Requests/` の FormRequest クラスに分離する（コントローラー内で `$request->validate()` は使わない）
- DB アクセスは Eloquent を使用し、生クエリ（`DB::statement` 等）は最小限にとどめる
- XSS 対策として Blade テンプレートは必ず `{{ }}` でエスケープ出力する（`{!! !!}` は原則使用禁止）

### ルーティング
- 全ルートは `routes/web.php` の `auth` ミドルウェアグループ内に定義する
- Breeze が提供する認証ルート以外は必ず認証必須にする

### データアクセス
- 他ユーザーのデータへのアクセスは必ず `user_id` で絞り込む（例: `Record::where('user_id', Auth::id())->...`）
- マイグレーションファイルは新規作成のみ。既存ファイルを直接編集しない

---

## セキュリティ規約

| 脅威 | 対策 |
|---|---|
| XSS | Blade の `{{ }}` エスケープを徹底。`{!! !!}` は使用禁止 |
| 認可バイパス | 全クエリに `WHERE user_id = Auth::id()` を付与。他ユーザーのリソースにアクセスさせない |
| CSRF | Laravel の CSRF トークン（`@csrf`）を全フォームに付与（フレームワーク標準） |
| 未認証アクセス | 全ルートに `auth` + `verified` ミドルウェアを適用 |
| バリデーション漏れ | ユーザー入力は必ず FormRequest でバリデーションしてからモデルに渡す |
| 生クエリ | 生クエリを使う場合は必ずバインディングを使用し、文字列結合によるクエリ組み立ては禁止 |

---

## 環境情報

### 開発環境

- **OS:** macOS（ホストに PHP / Composer なし）
- **コンテナ管理:** Docker Desktop（Laravel Sail）
- **コマンド実行:** 必ず `./vendor/bin/sail` 経由で行う
  - 例: `./vendor/bin/sail artisan migrate`
- DB・PHP はすべてコンテナ内で動作。ホストから直接 `php` / `composer` は使用しない
- メール認証は `.env` の `MAIL_*` を設定するか、`log` ドライバーでログ確認

### 本番環境
- 不明（未定義）

---

## テスト方針

`tests/Feature/Auth/` に Laravel Breeze が生成した認証系テスト（ログイン・登録・パスワードリセット等）と `ProfileTest` が存在する。

アプリ固有のテスト（打刻・カレンダー・月次集計）は現状なし。将来的に打刻・認証・カレンダー集計の Feature テストを追加予定。

```bash
# テスト実行
./vendor/bin/sail artisan test
```

---

## 開発コマンド

```bash
# コンテナ起動（バックグラウンド）
./vendor/bin/sail up -d

# コンテナ停止
./vendor/bin/sail down

# マイグレーション
./vendor/bin/sail artisan migrate

# フロントエンド開発サーバー
./vendor/bin/sail npm run dev

# フロントエンドビルド
./vendor/bin/sail npm run build

# テスト実行
./vendor/bin/sail artisan test
```

---

## Claude Code への作業指示ルール

Claude Code がこのプロジェクトで作業する際は以下を必ず守ること。

1. **実装前に CLAUDE.md を読み**、既存のパターン・規約に従う
2. **新規ファイルを作成した場合**は CLAUDE.md の該当セクション（画面一覧・Common.php メソッド一覧等）への反映を提案する
3. **マイグレーションは新規作成のみ**。既存のマイグレーションファイルは絶対に編集しない
4. **Common.php に新メソッドを追加した場合**は「設計パターン・独自規約」のメソッド一覧に追記する
5. **迷ったときは実装しない**。疑問点を箇条書きにして確認を求める

---

## 人間が修正する際のルール

1. **設計方針・規約を変更した場合**は CLAUDE.md を更新してからコミットする
2. **Common.php にメソッドを追加した場合**はメソッド一覧（設計パターン・独自規約セクション）を更新する
3. **新規画面を追加した場合**は画面一覧（画面一覧セクション）を更新する
