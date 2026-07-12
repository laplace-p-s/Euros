# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

Euros は Laravel 10 製の勤怠管理 Web アプリ。ホーム画面で出勤/退勤を打刻し、月次サマリーの表示や検索画面での記録の閲覧・編集ができる。マルチユーザー対応で、すべてのクエリは `Auth::id()` でスコープされる。UI テキストやコードコメントは日本語が基本なので、新規のコメント・UI 文言も日本語で書くこと。

## コマンド

```bash
composer install && npm install   # セットアップ
cp .env.example .env && php artisan key:generate
php artisan migrate               # .env に MySQL の設定が必要

npm run dev                       # Vite 開発サーバー(アセットのみ)
npm run build                     # 本番用アセットビルド
php artisan serve                 # ローカルアプリサーバー

php artisan test                  # 全テスト実行
php artisan test --filter=RegistrationTest   # 単一テストクラス/メソッド
vendor/bin/pint                   # コードスタイル整形(Laravel Pint)
```

注意: `phpunit.xml` は DB 接続を上書きしていない(sqlite/:memory: の行はコメントアウト済み)ため、Feature テストは `.env` に設定された DB に接続する。

## アーキテクチャ

標準的な Laravel MVC 構成だが、以下がこのプロジェクト固有のポイント:

- **ドメインロジックは `app/Libs/Common.php`(static メソッド)に集約**されており、モデルには置かれていない。月次カレンダー配列の生成(`generateCalendar`)、DB データのカレンダーへの反映(`setDatabaseData`)、当日の打刻状態の取得(`getTimeInfo`)、月次集計(`getSummaryInfo`)を担う。`HomeController` と `SearchController` は両方ともこの共通カレンダー配列を通してビューを描画する。配列のキー(`s_datetime`、`e_datetime`、`work_time`、`holiday`、`is_today` など)を Blade テンプレートが直接参照する。
- **モデルは空の Eloquent クラス**(`Record`、`Memo`、`Holiday`、`HolidayTemplate`、`User`)で、リレーション・スコープ・キャストは未定義。クエリロジックはすべてコントローラーと `Common` にある。
- **打刻のデータモデル**: 1 打刻 = `records` テーブル 1 行。`method` は 1 = 出勤、2 = 退勤。`is_manual` は詳細画面から手動追加された記録を示すフラグ。`record_memos` はユーザー × 日付ごとに 1 件のメモを保持。`holidays` はユーザーごとの休日、`holiday_templates` は年ごとの共有テンプレートで、一括コピーでユーザーの休日に取り込める。
- **勤務時間の計算ルール**: 勤務時間 = (退勤 − 出勤) の分数 ÷ 60。一定時間を超えると休憩 1 時間を減算する(閾値の不統一について「既知の問題」を参照)。
- **「API」エンドポイントはセッション認証の Web ルート**: 打刻登録・メモ編集・休日削除・状態更新は `routes/web.php` の POST ルートで、`ApiController` が JSON を返し、Blade ビュー内の jQuery AJAX から呼ばれる。`routes/api.php` は未使用の Laravel ボイラープレート。
- **認証は Laravel Breeze の標準構成**(`routes/auth.php`、`app/Http/Controllers/Auth/`、`ProfileController`)。既存テストは Breeze の認証・プロフィール部分のみをカバーしている。
- **アプリ固有の設定は `config/euros.php`**(検索開始年、コピーライト表記)。`SEARCH_START_SELECT_YEAR`、`APP_VER`、`DISP_APP_VER` などの環境変数で上書きできる。

## 既知の問題

修正するかは都度判断だが、周辺コードを触る際は以下を把握しておくこと(2026-07 時点の調査)。

### 勤務時間計算の閾値不統一

休憩 1 時間を減算する閾値が箇所によって異なる: `Common::getTimeInfo` と `SearchController::detailPost` は 4.0h 超、`Common::setDatabaseData` は 4.5h 超。同じ日の勤務時間がホーム/詳細と月次一覧で食い違う可能性がある。

### バリデーション・エラー処理の不在

- `ApiController` 全メソッド、`SettingsController::holiday_add`、`SearchController::addRecord` はリクエスト値を検証せずに保存している。`//TODO:エラー制御の実装` が複数残存。
- `register_rec` の `method` は 1/2 以外の値でも保存可能。`memo_edit` の日付も未検証。

### ロジックの怪しい箇所

- `Common::getSummaryInfo` の翌月表示判定は `$now->year.$now->month` という文字列連結での年月比較(壊れやすい)。
- 同メソッドの集計コメントがコピペ重複しており、片方は条件説明が逆のまま。
- 1 日複数回打刻時の採用値が不統一: `getTimeInfo` は `first()`(desc 順)、`detailPost` はループ最後の値。
- `SearchController::addRecord` は `action` が無い場合に何も return せず空レスポンスになる。
- `SettingsController::add_holiday_from_template` は `Holiday::insert()` のため `created_at`/`updated_at` が入らず、重複取り込みのチェックもない。
- `application-logo.blade.php` のロゴ画像 src が `env('APP_URL').'/euros/'` とサブディレクトリをハードコードしており、ルート直下で動かすローカル環境(Sail 等)では 404 になる(ログイン画面のリンク切れ)。実ファイルは `public/image/euros_logo.png`。修正時は `asset('image/euros_logo.png')` に置き換える。

### 未使用・放置コード

- `routes/api.php` は未使用ボイラープレート(前述)。
- `ToolsController::paid_leave_show` にコメントアウトされた旧コードが残存。
- `records.memo` カラムは未使用(メモは `record_memos` に分離済み)。

### セキュリティ所見(2026-07 検査)

深刻な脆弱性は未検出。XSS(Blade 出力は全箇所エスケープ、jQuery は `.text()` 使用)、SQL インジェクション(全クエリが Eloquent バインディング)、IDOR(削除系は所有チェック済み、参照系は `Auth::id()` スコープ済み)、CSRF(ミドルウェア有効・除外なし)はいずれも問題なし。認証は Breeze 標準(ログインスロットリング、暗号化クッキー + HttpOnly + SameSite=Lax)。

運用上の注意点:

- **HTTP 運用が前提**になっている(search.blade.php のクリップボード処理のコメント参照)。`SESSION_SECURE_COOKIE` 未設定のため、HTTP 運用ではセッションハイジャックのリスクあり。本番は HTTPS + `SESSION_SECURE_COOKIE=true` を推奨。
- **ユーザー登録が無制限**(`/register` が常時開放)。インターネット公開時は登録制限を検討すること。
- **`.env.example` が `APP_DEBUG=true`**。本番でデバッグ有効のまま不正な入力(`tgt_date` 等に不正値 → `new Carbon()` が例外)を受けるとスタックトレースが漏洩する。
- **laravelcollective/html は開発終了(アーカイブ済み)**でセキュリティ修正が出ない。`Form::select` の使用箇所(search.blade.php)は素の HTML への置き換えが望ましい。
- アプリ内 POST エンドポイント(`register_rec` 等)にレートリミットなし(実害は小)。

## フロントエンド

Blade + Tailwind CSS + Alpine.js(Breeze スキャフォールド)に jQuery の AJAX を併用し、Vite でビルドする(`resources/css/app.css`、`resources/js/app.js`)。インタラクティブな処理の大半は `resources/js` ではなく Blade ビュー内のインライン `<script>` に書かれている。
