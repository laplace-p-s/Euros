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
- **勤務時間の計算ルール**: 勤務時間 = (退勤 − 出勤) の分数 ÷ 60。一定時間を超えると休憩 1 時間を減算する。ただし閾値が現状コード内で不統一(`Common::getTimeInfo` と `SearchController::detailPost` は 4.0h 超、`Common::setDatabaseData` は 4.5h 超)。
- **「API」エンドポイントはセッション認証の Web ルート**: 打刻登録・メモ編集・休日削除・状態更新は `routes/web.php` の POST ルートで、`ApiController` が JSON を返し、Blade ビュー内の jQuery AJAX から呼ばれる。`routes/api.php` は未使用の Laravel ボイラープレート。
- **認証は Laravel Breeze の標準構成**(`routes/auth.php`、`app/Http/Controllers/Auth/`、`ProfileController`)。既存テストは Breeze の認証・プロフィール部分のみをカバーしている。
- **アプリ固有の設定は `config/euros.php`**(検索開始年、コピーライト表記)。`SEARCH_START_SELECT_YEAR`、`APP_VER`、`DISP_APP_VER` などの環境変数で上書きできる。

## フロントエンド

Blade + Tailwind CSS + Alpine.js(Breeze スキャフォールド)に jQuery の AJAX を併用し、Vite でビルドする(`resources/css/app.css`、`resources/js/app.js`)。インタラクティブな処理の大半は `resources/js` ではなく Blade ビュー内のインライン `<script>` に書かれている。
