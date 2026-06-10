# Laravel 10 → 13 段階アップグレード事前調査レポート

調査日: 2026-06-10
調査方法: ソースコードの静的解析 + 公式アップグレードガイド（11.x / 12.x / 13.x）+ Packagist のバージョン制約確認。コマンド実行による動作確認は未実施（本環境では実行不可）。

---

## 1. 現状サマリー

### フレームワーク・ランタイム

| 項目 | 現状 |
|---|---|
| laravel/framework | **10.50.2**（制約 `^10.0`） |
| PHP 制約（composer.json） | `^8.1 \|\| ^8.2 \|\| ^8.3` |
| Sail 実行環境（compose.yaml） | **PHP 8.3** ランタイム / MySQL 8.4 |
| DB | MySQL（SQLite 不使用 → SQLite 最低バージョン要件の影響なし） |

### composer.json 依存パッケージ一覧と Laravel 11/12/13 対応状況

| パッケージ | 現制約 | lock 実体 | L11 | L12 | L13 | 対応 |
|---|---|---|---|---|---|---|
| laravel/framework | ^10.0 | 10.50.2 | — | — | — | 段階ごとに `^11.0`→`^12.0`→`^13.0` |
| **laravelcollective/html** | ^6.4 | 6.4.1 | ❌ | ❌ | ❌ | **廃止済み（abandoned）。illuminate `^10.0` 止まり。除去必須（最大の障害）** |
| laravel/sanctum | ^3.0 | 3.3.3 | ❌(要^4.0) | ✅(4.x) | ✅(4.3+) | L11 時に `^4.0` へ |
| laravel/tinker | ^2.8 | 2.11.1 | ✅ | ✅ | ✅* | L13 ガイドは `^3.0` を指示（v3.0.2 は illuminate ^8〜^13 対応） |
| guzzlehttp/guzzle | ^7.2 | 7.11.0 | ✅ | ✅ | ✅ | 変更不要 |
| laravel/breeze (dev) | ^1.25 | — | ❌(要^2.0) | ✅(2.3+) | ✅(**2.4.2** で ^13 対応) | L11 時に `^2.0` へ。※メンテナンスモード（後述） |
| nunomaduro/collision (dev) | ^7.0 | — | ❌(要^8.1) | ✅(8.x) | ✅(8.9+) | L11 時に `^8.1` へ |
| phpunit/phpunit (dev) | ^10.0 | — | ✅(10.5/11) | ❌(要^11) | ❌(要^12) | 12→13 時に PHPUnit 12 必須（**PHP >= 8.4.1 要求**。後述） |
| spatie/laravel-ignition (dev) | ^2.0 | — | ✅ | ✅ | ✅(2.12 で ^13 対応) | 制約のままで可（L11 以降スケルトンからは削除されており、除去も選択肢） |
| laravel/pint / sail / mockery / fakerphp/faker (dev) | — | — | ✅ | ✅ | ✅ | 通常の `composer update` で追従 |

### nesbot/carbon
lock 実体は **2.73.0（Carbon 2）**。Laravel 11 は Carbon 2/3 両対応、**Laravel 12 以降は Carbon 3 必須**。本プロジェクトの勤務時間計算に直接影響する（§3 参照）。

---

## 2. アップグレード可否の結論

**結論: 要事前修正（修正項目は少なく、対応後は十分実現可能）**

- **唯一の明確なブロッカーは `laravelcollective/html`**。廃止済みで Laravel 11 以降に一切対応しない。ただし使用箇所は `resources/views/search.blade.php` の `Form::select` **2 箇所のみ**であり、除去は容易。
- それ以外のコードは Laravel 10 標準スケルトンにほぼ忠実（Kernel.php / Middleware / Providers ともカスタマイズは最小限）で、非推奨 API（`$dates`、Doctrine DBAL、`->change()` マイグレーション、float/double カラム、spatial 型、upsert、Storage、HasUuids 等）は**いずれも不使用**。
- Laravel 11 では「旧来のアプリ構造（app/Http/Kernel.php 等）をそのまま維持する」ことが公式に推奨されており、構造移行は必須ではない。
- 注意すべき実質的な挙動変化は **Carbon 3 の `diffInMinutes`（Laravel 12 で必須化）** と **PHPUnit 12 の PHP 8.4 要求（Laravel 13）** の 2 点。

---

## 3. 段階ごとの作業項目と影響箇所

### Stage 1: Laravel 10 → 11（作業量: 中）

**要件**: PHP 8.2 以上（Sail は 8.3 で充足。composer.json の `^8.1` 制約を `^8.2` 以上へ変更）

| 作業 | 影響箇所 | 備考 |
|---|---|---|
| **laravelcollective/html の除去** | `resources/views/search.blade.php:167,173`（`Form::select` 年・月セレクト） | プレーン Blade の `<select>` + `@foreach` への書き換えを推奨（2 箇所のみのため spatie/laravel-html 導入より低コスト）。`config/app.php` の aliases に `Form`/`Html` の手動登録はなく（パッケージの自動検出で読み込まれている）、composer から外すだけでよい |
| composer.json 更新 | `laravel/framework ^11.0` / `nunomaduro/collision ^8.1` / `laravel/breeze ^2.0` / `laravel/sanctum ^4.0` | |
| Sanctum 4 対応 | `config/sanctum.php:62-65` | middleware 設定キーを新形式（`authenticate_session` / `encrypt_cookies` / `validate_csrf_token`）へ更新。マイグレーション自動ロードは廃止されたが、本プロジェクトは `2019_12_14_000001_create_personal_access_tokens_table.php` を自前で保持済みのため影響なし。なお API トークン・SPA stateful 機能は実質未使用（`EnsureFrontendRequestsAreStateful` はコメントアウト）のため、実害は小さい |
| Breeze 1 → 2 | composer のみ | Breeze はスキャフォールド型のため、公開済みの auth コントローラー / ビューはそのまま動作する。再 publish は不要（するとカスタマイズが消えるので注意） |
| アプリ構造 | **変更しない**（公式推奨） | `app/Http/Kernel.php`、`config/*` 一式、`app/Providers/*` は Laravel 11 でもそのまま動作する |
| パスワード再ハッシュ | `app/Models/User.php` | 標準の `password` カラムのため対応不要。ログイン時に自動再ハッシュが走る（正常動作） |
| レート制限の秒単位化 | `app/Providers/RouteServiceProvider.php` | `Limit::perMinute(60)` の静的コンストラクタ使用のため影響なし |
| マイグレーション関連（`->change()` / float / double / SQLite） | — | 該当コードなし。影響なし |

**この段階では Carbon 2 のまま維持できる**（Laravel 11 は Carbon 2/3 両対応）。composer が Carbon 3 へ上げてしまわないよう、更新後に `composer show nesbot/carbon` で確認するか、次段階まで `"nesbot/carbon": "^2.72"` を明示することを推奨。

### Stage 2: Laravel 11 → 12（作業量: 小〜中）

**要件**: PHP 8.2 以上（変更なし）

| 作業 | 影響箇所 | 備考 |
|---|---|---|
| composer.json 更新 | `laravel/framework ^12.0` / `phpunit/phpunit ^11.0` | |
| **Carbon 3 必須化への対応（本プロジェクト最大の挙動変化）** | `app/Libs/Common.php:37`、`app/Libs/Common.php:153`、`app/Http/Controllers/SearchController.php:107` | 下記詳細 |
| PHPUnit 10 → 11 | `phpunit.xml`、`tests/` | 既存テストは Breeze 由来の標準的なもの。`phpunit.xml` のスキーマ更新（`--migrate-configuration`）程度 |
| local ディスクのルート変更（`storage/app` → `storage/app/private`） | — | `Storage::` 不使用のため影響なし |
| image バリデーションの SVG 除外 / UUIDv7 / upsert 検証 | — | 該当コードなし。影響なし |

**Carbon 3 の `diffInMinutes` 挙動変化の詳細**:

Carbon 2 では「絶対値の整数（分未満切り捨て）」を返すが、Carbon 3 では「符号付きの float（秒の端数を含む）」を返す。影響は 2 種類:

1. **丸め差異**: 現行は `diffInMinutes($e) / 60` を `round(, 1)` しており、Carbon 3 では秒の端数が結果に乗るため、表示される勤務時間が 0.1H 単位でずれるケースが生じる（例: 7時間59分30秒 → Carbon 2: 7.9H、Carbon 3: 8.0H）。
2. **符号反転**: 退勤時刻が出勤時刻より前という異常データの場合、Carbon 2 は正の値、Carbon 3 は負の値を返す。負の勤務時間が表示され、休憩控除判定（`> 4.0` / `> 4.5`）や月次集計（`> 6` / `>= 2`）も通らなくなる。

対応案: `floor($s->diffInMinutes($e, true))`（第2引数 `true` で絶対値、`floor` で従来の切り捨てを再現）に統一する。この書き換えは **Carbon 2 でも同じ結果になるため、Laravel 10 のうちに先行修正できる**（§4 参照）。

### Stage 3: Laravel 12 → 13（作業量: 小）

**要件**: **PHP 8.3 以上**（Sail は 8.3 で最低要件は充足）

| 作業 | 影響箇所 | 備考 |
|---|---|---|
| composer.json 更新 | `laravel/framework ^13.0` / `laravel/tinker ^3.0` / `phpunit/phpunit ^12.0` | **注意: PHPUnit 12 の最新系は PHP >= 8.4.1 を要求**（Packagist 実測）。Sail ランタイムを 8.3 → 8.4 に上げるのが安全（Laravel 13 は PHP 8.5 まで対応） |
| CSRF ミドルウェアの改名（`VerifyCsrfToken` → `PreventRequestForgery`） | `app/Http/Middleware/VerifyCsrfToken.php`（基底クラスを継承） | 旧名は非推奨エイリアスとして残るため即座には壊れないが、基底クラスの参照を `PreventRequestForgery` へ更新推奨。`Sec-Fetch-Site` ヘッダーによるオリジン検証が追加される点に注意（通常のブラウザ利用では問題にならない見込み） |
| cache `serializable_classes` | — | キャッシュに PHP オブジェクトを保存していない（`CACHE_DRIVER=file`、明示的な Cache 利用なし）ため影響なし |
| キャッシュプレフィックス / セッション Cookie 名のデフォルト変更 | `config/session.php` | 旧構造の config ファイル一式をアプリ側に保持しているため、フレームワーク側フォールバックは使われず**影響なしの見込み**。万一に備え `SESSION_COOKIE` を .env に明示しておくと確実（変わると全ユーザーが再ログインになる） |
| MySQL DELETE + JOIN / upsert / ポリモーフィックピボット等 | — | 該当コードなし。影響なし |

---

## 4. 事前修正が必要な項目（優先度付き）

Laravel 10 のまま先に着手でき、アップグレードの障害を取り除く修正:

| 優先度 | 項目 | 箇所 | 理由 |
|---|---|---|---|
| **高** | `laravelcollective/html` の除去（`Form::select` 2 箇所をプレーン Blade 化し、composer から削除） | `resources/views/search.blade.php:167,173` | Laravel 11 へのインストール自体をブロックする唯一のパッケージ。L10 でも除去可能で、先行リリースして安定確認できる |
| **高** | `diffInMinutes` の Carbon 3 互換化（`floor(...->diffInMinutes(..., true))` 等で「絶対値・分切り捨て」を明示） | `app/Libs/Common.php:37,153`、`app/Http/Controllers/SearchController.php:107` | Carbon 2 でも結果が変わらない書き方のため安全に先行でき、Stage 2 の挙動変化リスクを事前に消せる |
| 中 | テストスイートの整備 | `tests/` | 現状 Breeze 由来の認証テストのみで、勤怠機能（打刻・集計・休日）のテストが無い。アップグレードの回帰検知手段がないため、最低限 `Common` の勤務時間計算と主要ルートの Feature テストを追加してから着手するのが望ましい |
| 中 | Sail ランタイムの PHP 8.4 化の検討 | `compose.yaml` | Stage 3 の PHPUnit 12 が PHP 8.4 以上を要求するため。Stage 1〜2 の間に上げておくと移行が滑らか |
| 低 | ビュー内の `env()` 直接参照を `config()` 経由へ | `resources/views/layouts/footer.blade.php`、`components/application-logo.blade.php`、`vendor/mail/*/message.blade.php` | アップグレードのブロッカーではないが、`config:cache` 実行時に `env()` が null を返す既知の落とし穴。設定ファイル整理のついでに解消推奨 |
| 低 | 既存 TODO（入力検証・エラー制御）の解消 | `ApiController` ほか | アップグレードとは独立だが、回帰確認の難易度を下げる |

---

## 5. リスクと注意点

1. **動作確認手段が未整備**: 本調査は静的解析のみ。各 Stage の完了判定には Sail 環境での `php artisan test` / 手動の打刻・一覧・集計確認が必須。テストカバレッジが薄いため（§4）、特に勤務時間計算まわりは手動確認項目を用意すること。
2. **勤務時間の表示値が変わる可能性（Stage 2 / Carbon 3）**: 事前修正（floor + 絶対値の明示）を行わない場合、既存データの表示が 0.1H 単位で変動し、異常データでは負値が出る。事前修正を強く推奨。
3. **Breeze はメンテナンスモード**: Laravel 12 以降、Breeze/Jetstream は新規開発が止まり互換パッチのみ（v2.4.2 で Laravel 13 対応済み）。当面は問題ないが、長期的には公開済みの auth コードを自前管理するか、新スターターキットへの移行を検討。本プロジェクトは auth コードがすでに publish 済みのため、最悪 Breeze を dev 依存から外しても動作する。
4. **アプリ構造は L10 形式を維持する方針とする**: Laravel 11 の新スリム構造（`bootstrap/app.php` 集約、Kernel 廃止）への移行は公式に「推奨しない」とされており、本レポートも旧構造維持を前提とする。新構造へ寄せたい場合は全 Stage 完了後に別作業として実施すること。
5. **config ファイルの差分追従**: 旧構造を維持する場合でも、各バージョンの `laravel/laravel` スケルトンとの config 差分（新規キー追加）は手動で取り込む必要がある。GitHub の比較ツール（`laravel/laravel` の `10.x...11.x` 等）で確認すること。
6. **セッション**: `SESSION_DRIVER=database` + 既存 `sessions` テーブルは全バージョンでそのまま動作する。Stage 3 で Cookie 名のフォールバック仕様が変わるため、`.env` に `SESSION_COOKIE` を明示しておくと全ユーザー強制ログアウトを確実に回避できる。
7. **要確認事項**:
   - 本番サーバーの PHP バージョン（本調査では Sail の 8.3 のみ確認。Stage 1 で 8.2+、Stage 3 で 8.3+、PHPUnit 12 を使うなら開発環境 8.4+ が必要）。
   - `npm` 側は今回のアップグレードと独立（laravel-vite-plugin ^1.3 は Laravel 13 でも動作する見込みだが、Stage 完了ごとに `npm run build` の確認を推奨）。

---

## 参考情報源

- [Laravel 11.x Upgrade Guide](https://laravel.com/docs/11.x/upgrade)
- [Laravel 12.x Upgrade Guide](https://laravel.com/docs/12.x/upgrade)
- [Laravel 13.x Upgrade Guide](https://laravel.com/docs/13.x/upgrade)
- [Laravel 13 Release Notes](https://laravel.com/docs/13.x/releases)
- [Laravel Collective HTML package is abandoned — Laravel News](https://laravel-news.com/collective-html-abandoned)
- [laravelcollective/html — Packagist](https://packagist.org/packages/laravelcollective/html)（abandoned、代替: spatie/laravel-html）
- [HTML Converter (LaravelCollective → Spatie) — Laravel Shift](https://laravelshift.com/convert-laravelcollective-html-to-spatie-laravel-html)
- [Carbon 3 Migration Guide](https://carbon.nesbot.com/guide/getting-started/migration.html)
- Packagist API による各パッケージの illuminate バージョン制約実測（laravel/breeze v2.4.2、laravel/sanctum v4.3.2、laravel/tinker v3.0.2、spatie/laravel-ignition 2.12.0、nunomaduro/collision v8.9.4、phpunit/phpunit 13.x）
