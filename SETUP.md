# 設置の手順 | Setup

QWEL in Action (https://qwel.design) を、手元とサーバーに設置する手順。
エンジンは [QWEL Markdown Engine](https://github.com/qweldesign/qwel-tools-md-engine) で、本家 (/)・Co-Pilot (/co-pilot/)・ちいさなサイト工房 (/small-site-studio/) の3つをこの1つのリポジトリで動かしている。

---

## 必要なもの

- PHP 8.0 以上
- Apache (mod_rewrite と, `.htaccess` を有効にする `AllowOverride`)
- Composer (お問い合わせフォームのメール送信に使う PHPMailer の導入)
- Sass (`scss/` を書き換えたとき)
- Node.js (`tools/` のチラシ・名刺の書き出しのときだけ)

---

## 構成

```
config-common.php    区画 (本家・工房) で共通の設定. 区画の地図 SITE_SECTIONS (ルート, 投稿タイプ, ページ)
config.php           本家の区画の設定 (サイト名, ナビ, お問い合わせの項目など)
studio-config.php    工房の区画の設定
inc/                 エンジン・テンプレート・部品 (Web からは読めない)
content/             原稿 (Markdown) と JSON のデータ, 画像 (画像以外は Web からは読めない)
scss/                style.css (本家) と studio.css (工房) の原稿
init.js, js/         共用の JavaScript
tools/               OGP 画像・名刺・チラシの原稿と書き出し
404.php              404 のページ (工房は small-site-studio/404.php)
sitemap.php          全区画のサイトマップ (/sitemap.xml)
```

区画の設定は1回のリクエストで1つだけ読む (PHP の定数は上書きできないため)。本家のページは `inc/bootstrap.php` が `config.php` を読み、工房のページは先頭で `studio-config.php` を読む。

---

## content リポジトリ

原稿のうち、公開しないもの (実績・作品) は、非公開の content リポジトリで管理する。このリポジトリには入れない。

| 場所 | 中身 | 置き場所 |
| --- | --- | --- |
| `content/images/banners/` | バナー・OGP 画像 | このリポジトリにも残す |
| `content/works/*.md` | 実績 (工房の実績, Co-Pilot の公開している実績) | content リポジトリ |
| `content/creative/*.md` | Creative Crafts の一覧 | content リポジトリ |
| `content/tools/*.md` | Creative Tools の一覧 | content リポジトリ (contact-form だけ残す) |
| `content/co-pilot/records.json` | Co-Pilot の匿名の実績 | content リポジトリ |
| `content/options.json` ほかの JSON | 数値 (返信の日数, 最低受注額など), 料金の目安, よくある質問 | このリポジトリにも残す |

- content リポジトリの中身は、同じパスで `content/` 以下に置く。
- content リポジトリがない環境でも、ページは表示できる。実績がなければ、工房と Co-Pilot の実績のセクション (と Co-Pilot のメニューの「Flight Records」) は出さない。Creative Crafts の一覧は空になる。

---

## 手元で動かす

```bash
php -S localhost:8000
```

- PHP の組み込みサーバーは `.htaccess` を読まない。トップ・Co-Pilot・工房のトップ・お問い合わせは表示できるが、次は Apache でしか動かない。
  - 実績の個別ページ (`/small-site-studio/works/xxx/`) と `/sitemap.xml` のリライト
  - `config.php` などの拒否, 404 のページ, 旧 URL からの転送
  - 存在しないファイルの URL (`/sitemap.xml` など) は、組み込みサーバーがトップページを返す
- お問い合わせの送信を試すときは、下の「お問い合わせフォーム」の準備をする。

---

## お問い合わせフォーム

フォームは本家の `/contact/` と工房の `/small-site-studio/contact/` の2つ。どちらも `inc/send.php` で送る。

1. `inc/` で `composer install` を実行し、PHPMailer を `inc/vendor/` に入れる
2. `inc/.env.example` を `inc/.env` としてコピーし、宛先と SMTP の設定を書く
3. 項目や文言は、`config.php` / `studio-config.php` の `SITE_CONTACT` と `SITE_CONTACT_FIELDS` で変える

`inc/.env` (SMTP のパスワード) と `inc/vendor/` は Git に入れない。`inc/.htaccess` で Web からは読めないが、`.env` を公開ディレクトリの外に置く場合は `SITE_CONTACT` の `env` を書き換える。

設定に不備があると、送信の API は 500 を返し、理由をサーバーのエラーログに残す。

---

## サーバーへのアップロード

デプロイは自動化していない。リポジトリの中身を、ドメインのルートに手動でアップロードする。

- アップロードするもの: リポジトリの中身 (`.htaccess` を含む), `inc/vendor/`, `inc/.env`, content リポジトリの中身 (`content/` 以下)
- アップロードしないもの: `.git/`, `node_modules/`, `tools/*/out/`

| URL | 期待する結果 |
| --- | --- |
| `/config.php` `/config-common.php` `/studio-config.php` | 403 |
| `/inc/` `/content/` | 403 |
| `/sitemap.xml` | サイトマップの XML |
| `/tools/contact-form/` | `/contact/` へ 301 |
| `/api/send.php` (GET) | 405 |
| 存在しない URL (`/xxx`, `/small-site-studio/xxx`) | 404 (それぞれの区画の 404 のページ) |

サイトマップは Search Console に `/sitemap.xml` を登録する。
