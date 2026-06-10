# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

Euros は Laravel 10 製の勤怠管理 Web アプリケーション。出勤・退勤を Web ページ上で記録し、月単位で一覧表示する。一覧からメモを含む記録を TSV 形式でクリップボードへコピーする機能を持つ。認証機能により、1 サーバーを複数人で共有しつつ個人ごとのデータを保持できる（README.md より）。

- PHP: `^8.1 || ^8.2 || ^8.3` / Laravel Framework `^10.0`
- 認証: Laravel Breeze（require-dev）+ Sanctum
- フロントエンド: Vite + Tailwind CSS 3（`@tailwindcss/forms`、`dark:` バリアント使用）+ Alpine.js + jQuery + Font Awesome 5（CDN）
- DB: MySQL（`.env.example` より）。セッションも DB 保存（`SESSION_DRIVER=database`）

## 実行環境に関する注意

この開発環境では Docker / Sail は動作していない。コマンドの実行や動作確認はできない前提で、ソースコードの静的解析のみで判断すること。

## コマンド（参考: README.md / composer.json / package.json）

```bash
composer install          # PHP 依存のインストール
npm install               # JS 依存のインストール
npm run dev               # Vite 開発サーバー
npm run build             # フロントエンドビルド
php artisan migrate       # マイグレーション
vendor/bin/phpunit                    # テスト実行（phpunit.xml あり）
vendor/bin/phpunit --filter <名前>    # 単一テストの実行
vendor/bin/pint           # コードフォーマット（Laravel Pint、require-dev）
```

## アーキテクチャ

### 全体構造

- ルート定義: `routes/web.php`（アプリ本体）+ `routes/auth.php`（Breeze 認証）。アプリのルートはすべて `->middleware(['auth', 'verified'])` 付き。
- ユーザー登録ルートは `config('auth.enable_registration')`（環境変数 `ENABLE_REGISTRATION`）で有効/無効を切り替える。
- 共通ロジックは `app/Libs/Common.php` に static メソッドとして集約。コントローラーから `Common::xxx()` で呼び出す。
- モデル（`app/Models/`: `Record` / `Memo` / `Holiday` / `HolidayTemplate`）は `HasFactory` のみの空クラス。リレーションやスコープは定義されておらず、クエリはコントローラーと `Common` 内でクエリビルダーを直接組み立てる。
- 一覧画面のデータは「`Common::generateCalendar()` で 1 か月分の配列を生成 → `Common::setDatabaseData()` が参照渡し（`array &$result_list`）で DB データを書き込む」という流れで構築する。

### コントローラー（app/Http/Controllers/）

| コントローラー | 役割 |
|---|---|
| `HomeController` | ホーム画面。当日の出退勤時刻と月次集計を表示 |
| `SearchController` | 月別一覧（`/search`）・日別詳細（`/detail`）・手動レコード追加（`/add_record`） |
| `ApiController` | JSON を返す POST エンドポイント群（打刻登録 `/register_rec`、メモ編集 `/memo_edit`、時刻情報更新 `/renewal_info`、休日削除 `/settings/holiday/delete`） |
| `SettingsController` | 設定トップと休日設定（個別追加・テンプレートからの一括追加） |
| `ToolsController` | ツールトップと有給休暇画面（`paid_leave` はビュー表示のみ） |
| `ProfileController` ほか `Auth/` 配下 | Breeze 由来のプロフィール・認証処理 |

### データモデル

- `records`: 打刻レコード。`user_id` / `record_date`(datetime) / `method`（**1=出勤、2=退勤**）/ `memo` / `is_manual`（手動追加時に 1）
- `memos`: 日単位のメモ。`user_id` / `record_date`(date) / `memo`。マイグレーションファイル名は `create_record_memos_table` だがテーブル名は `memos`、モデルは `Memo`
- `holidays`: ユーザーごとの休日設定。`user_id` / `holiday_date` / `name` / `note`
- `holiday_templates`: 年単位の休日テンプレート。`user_id` を持たない全ユーザー共通マスタ（`year` で絞り込み、`Holiday::insert()` で一括コピー）

### ドメインロジック上の注意点

- ユーザーデータを扱うクエリは必ず `where('user_id', Auth::id())` で絞り込む（全コントローラー・`Common` で徹底されている）。
- 勤務時間計算: 出勤と退勤の差分を `diffInMinutes / 60` で時間換算し、小数第 1 位に丸める。休憩 1 時間の控除閾値は `Common::getTimeInfo()` と `SearchController::detailPost()` が **4.0 時間超**、`Common::setDatabaseData()` が **4.5 時間超** と実装が分かれている。同一日に複数打刻がある場合、一覧では最後のレコードが採用される。
- 月次集計（`Common::getSummaryInfo()`）: 勤務時間 6 時間超で 1.0 日、2 時間以上で 0.5 日と数え、土日（`week` が 0 か 6）または休日設定日は平日とは別カウント（`w_day_h`）。
- 日付をまたぐ勤務は考慮しない（コード内コメントに明記）。
- バリデーションは Breeze 由来の `ProfileUpdateRequest` 以外に FormRequest はなく、アプリ独自のエンドポイントには入力検証・エラー制御が未実装（`//TODO:エラー制御の実装` コメントが複数残っている）。

### ビュー（resources/views/）

```
resources/views/
├── layouts/        app（認証後共通）, guest（認証前）, navigation, footer, top_button
├── components/     Breeze 由来の Blade コンポーネント（x-nav-link 等）
├── auth/           Breeze 認証画面
├── profile/        プロフィール編集（partials あり）
├── vendor/mail/    メールテンプレート
└── home / search / detail / tools / paid_leave /
    settings_top / settings_holiday / top / welcome  ← ページは views/ 直下にフラット配置
```

- 認証後ページは `layouts/app.blade.php` をベースに `layouts.navigation` / `layouts.footer` / `layouts.top_button` を `@include` する。
- `top_button.blade.php` のように、ビュー内に jQuery を使う `<script type="module">` を直接記述するスタイル。

### 設定

- 独自設定は `config/euros.php`（`StartSelectYear` / `CopyRightYear` / `CopyRightName`）。対応する環境変数は `SEARCH_START_SELECT_YEAR` / `COPYRIGHT_YEAR` / `COPYRIGHT_NAME`。
- その他の独自環境変数: `APP_VER` / `DISP_APP_VER`（フッターのバージョン表示。`footer.blade.php` では `env()` を直接参照）、`ENABLE_REGISTRATION`、`CORS_ALLOWED_ORIGINS`。

## コードスタイル（実際のコードから読み取った規約）

- コメントは日本語で記述する。処理ブロックの先頭に `//出勤データリスト` のような見出しコメントを付けるスタイル。
- メソッド名は混在している: `Common` や `ApiController` は camelCase（`memoEdit` 等）、`SettingsController` / `ToolsController` は snake_case（`holiday_show`、`paid_leave_show` 等）。修正時は対象クラス内の既存スタイルに合わせる。
- ビュー変数は `compact()` でまとめて `view()` に渡す。
- 日付処理は全面的に `Carbon` を使用。曜日付き表示は `isoFormat('YYYY/MM/DD (ddd)')`。
- 未使用コードはコメントアウトのまま残されていることがある（削除済み機能の痕跡等）。既存の TODO コメント（共通処理化・エラー制御）は把握した上で変更すること。
