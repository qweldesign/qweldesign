# QWEL Markdown Engine (Simple CMS)

Markdown の原稿から、データベースなしでサイトを作るPHPのエンジン。

ブログや作品集、小さな事業のサイトなど、軽量なサイトを作るためのもの。

---

## 概要・機能 | Overview & Features

- Markdown を HTMLに変換して表示
- データベース不要、原稿はファイルで管理
- 複数の投稿タイプ (ブログ、お知らせなど) をディレクトリごとに管理
- 固定ページを `/slug/` で表示
- サイトマップと robots.txt を自動で作成
- 設置の抜けを確かめる健全性チェック
- canonical、OGP、構造化データ (JSON-LD) を出力
- 個別のページを持たない投稿タイプ (リンク集、作品集など)
- 公開前の下書きを確認用サーバーで表示
- 料金などの値を JSON で管理するデータファイル
- ブラウザのキャッシュ、画像の属性、Web フォントの読み方で表示を速く
- ページ送り、パンくず、前後の記事、最新記事
- `/blog/xxx/` 形式のURL
- タイトル、説明文、ナビ、フッターを `templates/config.php` から出力
- お問い合わせフォーム (入力 → 確認 → 完了。管理者宛と自動返信のメールをSMTPで送信)

---

## 目的・背景 | Purpose & Background

- 環境構築を手軽に行える、軽量なCMSが欲しかった
- 管理画面の実装・運用・保守に伴うコストとセキュリティリスクを抑えたかった
- お客さまから文章と画像を提供してもらい、更新はエンジニアが行う運用を前提にした
- Markdown をそのまま運用し、Git で保守できる環境を作りたかった
- 納品したサイトは、作ったときの版のまま動かし続けられるようにしたかった

---

## 設計・実装 | Design & Implementation

**核は小さく保ち、テンプレートはサイトが持つものとして作る**。

| 場所 | 持ち主 | 役割 |
| --- | --- | --- |
| `inc/` | エンジン | 区画設定の初期化、原稿の読み込み、データの用意、フォームの検証と送信、404、サイトマップ、robots.txt |
| `templates/` | サイト | HTML。デザインに合わせて書き換える |
| `templates/sections.php` | サイト | 区画の地図 (区画ごとの設定ファイル、ルート、固定ページ、投稿タイプ) |
| `templates/config.php` | サイト | 本家区画の設定 |
| `content/` | サイト | 原稿と画像 |

- **読み込みと表示を分ける:** `ContentLoader` と `ContentNavigation` はデータだけを返し、HTMLは `templates/` に書く
- **`ContentPage` が画面を選ぶ:** URLを受けて必要なデータを集め、投稿タイプの設定に応じてテンプレートを呼ぶ
- **frontmatter の値はすべて文字列:** 1 行に 1 項目の `key: value` だけを読む
- **秘密の値だけを `.env` に置く:** 宛先とSMTPの設定以外は `templates/config.php` に書く
- **外部依存は最小限:** Markdown の変換に Parsedown、メール送信に PHPMailer を Composer で使う
- **作らないもの:** 管理画面、データベース、ベーシック認証、CSSとJSのビルドの仕組み

### 区画設定の初期化

複数の区画を持つサイトでは、ページ入口が `templates/sections.php` の地図から区画を選び、テンプレートや helper より先に初期化する。設定は定数で読み込むため、1 回のリクエストで初期化できる区画は 1 つだけ。区画を選ばない入口 (サイトマップ、robots.txt、送信の入口、健全性チェック) と、区画を選ばずにテンプレートを読んだページは、最初の区画を使う。

```php
require_once __DIR__ . '/inc/bootstrap.php';

bootstrapSection('main');
```

地図には、区画ごとに設定ファイル (`config`)、ルート (`root`)、固定ページ (`pages`)、投稿タイプ (`postTypes`) を書く。`bootstrapSection()` は `root`, `pages`, `postTypes` から `SITE_ROOT`, `SITE_PAGES`, `SITE_POST_TYPES` を作ってから設定ファイルを読む (地図に書かなければ、これまでどおり設定ファイルに書く)。サイトマップや健全性チェックが、1 回のリクエストで全区画を見られるようにするため。設定ファイルには、それ以外の設定値を書く。

区画ごとに見た目を変えるときは、地図の `templates` にその区画のテンプレートの置き場 (例: `templates/studio`) を書く。`sectionTemplate('page.php')` は、置き場にファイルがあればそれを、なければ `templates/` のファイルを返す。`ContentPage` と `inc/404.php` は、これで画面のテンプレートを選ぶ。レイアウト (`site.php`) だけを置けば、パンくずなどの部品は共通のものを使える。置き場のテンプレートは `templates/` より 1 段深いので、`dirname(__DIR__)` などの相対パスに気をつける。区画の 404 は、区画の入口 (例: `small-site-studio/404.php`) で区画を選んでから `inc/404.php` を読む。`SITE_BASE` は共有 `assets/`・`content/` の URL 基点で、`SITE_ROOT` はその下に置く。`path()` は区画内 URL と共有ファイル URL を使い分け、`siteFile()` はリポジトリ内の対応するファイルを参照する。

---

## 使い方 | Usage

### 動作環境

- PHP 8.0 以上
- Apache 2.4.10 以上 (mod_rewrite、`.htaccess` を有効にする `AllowOverride`。ブラウザのキャッシュに mod_headers)
- Composer
- Sass (`scss/` を書き換えるとき)

### セットアップ

1. `inc/` で `composer install` を実行し、Parsedown と PHPMailer を導入する
2. `inc/.env.example` を `inc/.env` としてコピーし、宛先とSMTPの設定を書き換える
3. `templates/config.php` を書き換える
4. `.htaccess` を含めて、サーバーにアップロードする

`inc/vendor/` がないと、サイト全体を表示できない。`.htaccess` はドットファイルなので、アップロードを忘れないようにすること。サブディレクトリに置くときは、`.htaccess` の `ErrorDocument` のパスを `SITE_ROOT` に合わせる。robots.txt はドメインのルートでしか読まれないので、ドメインのルートの robots.txt に `Sitemap:` の行を書く。構造化データの `author` は `SITE_AUTHOR` に書く (空のままだと出力されず、リッチリザルトテストで指摘される)。

手元では、PHPの確認用サーバーで表示を確かめられる。

```
php -S 127.0.0.1:8000
```

確認用サーバーは `.htaccess` を読まないので、URLは `/blog/?slug=xxx` の形になる (`SITE_PRETTY_URLS` が自動で `false` になる)。お問い合わせフォームの送信を試すときは、`SITE_URL` を手元のURLに変える。

### ブラウザのキャッシュ

ルートの `.htaccess` で、静的ファイルに `Cache-Control` を付ける。HTML には付けないので、原稿を置き換えるとすぐ反映される。mod_headers がないサーバーでは付かないだけで、表示には影響しない。`expr=` を使うので Apache 2.4.10 以上が必要で、古いサーバーでは 500 になる。

| 対象 | 期間 | 置き換えたとき |
| --- | --- | --- |
| `?v=` が付いた URL (`assetUrl()` で出す CSS, JS, ロゴなど) | 1 年 | URL が変わるので、すぐ反映される |
| `?v=` のない画像とフォント (原稿の画像など) | 7 日 | 同じ名前だと、再訪問の人には最大 7 日古いまま。すぐ反映したいときはファイル名を変える |
| `?v=` のない CSS と JS (`init.js` が読み込む `js/*.js`) | 1 日 | 再訪問の人には最大 1 日古いまま |

### 画像の属性

本文の画像には、大きさと読み込み方の属性を自動で付ける。1 枚目はすぐ読み、2 枚目以降は遅延する。原稿の HTML に `width` か `loading` を書いた画像には触らない。

テンプレートに書く画像には、`imageAttrs()` を使う。

```php
<img src="<?= e(path('assets/hero.jpg')) ?>" alt=""<?= imageAttrs('assets/hero.jpg', 'high') ?>>
```

第 2 引数は `lazy` (既定), `eager`, `high` (すぐ読み、優先度も上げる。ファーストビューの大きな画像に使う)。

画像そのものの大きさは縮小しない。大きな画像は、サイトごとに作り直す。

### Web フォント

`SITE_FONTS_URL` の CSS は、表示を止めずに読む。読み終わるまでは代わりのフォントで表示するので、URLに `display=swap` を付ける。

日本語のフォントの CSS は数百 KB になることがある。使う書体と太さだけに絞ると、転送量そのものが減る。

既定では `Noto Sans JP` を可変フォント (`wght@100..900`) で読む。太さを並べて指定するより CSS が軽い。日本語の Web フォントは、ページで使う文字を含む分だけを取得するが、それでも 1 ページで約 1 MB になる。表示の速さを優先するなら、`SITE_FONTS_URL` を空にして OS の書体に任せる (Mac や iPhone ではヒラギノ、Windows では游ゴシックや Noto Sans JP など。OS によって見た目が変わる)。

書体を変えるときは、`SITE_FONTS_URL` と一緒に、`scss/_variables.scss` の `@forward "settings" with (...)` に `$font-family-sans` などを足す。CSS が名前を挙げる書体と、読み込む書体を揃える。

ヒーローの見出しなど、決まった文言だけに使う書体は、`SITE_TEXT_FONTS` で文字を絞って読める。書体のファイルが数 KB で済むので、本文は OS の書体に任せ、目立つ所だけ Web フォントにするときに使う。

```php
const SITE_TEXT_FONTS = [
  ['family' => 'Zen Kaku Gothic New:wght@700', 'text' => '海辺の古民家に泊まる'],
];
```

- `family` は書体と太さ。Google Fonts の URL の `family=` と同じ書き方 (空白はそのままでよい)
- `text` は表示する文言をそのまま書く。重なる文字と改行は除いて URL に入れる (`textFontUrl()`)
- 文言を変えたら `text` も直す。抜けた文字は代わりの書体で表示され、見た目では気づきにくい。健全性チェックは文字の抜けを見ない
- CSS の `font-family` には、`SITE_FONTS_URL` の書体とは別に、この書体の名前を書く

### 配色の段階

メインカラー (`$colors`) から、色相を保って OKLCH の明度 (`l`) と、必要なら彩度 (`c`) を変えた段階の色を作れる。白地の文字に使う `dark`、帯やカードの面に使う `light` など。既定では作らない。使うときは、`scss/_variables.scss` の `@forward "settings" with (...)` に足す。

```scss
$color-tones: (dark: (l: 54%), light: (l: 96%, c: 0.02)),
```

`:root` に `--color--primary--dark: oklch(54% 0.119 238)` のような値が出力され、SCSS では `theme-color("primary", "dark")` で使える。値は丸める (明度は整数の %、彩度は小数第 3 位、色相は整数の度)。画面で出せる色 (sRGB) の外に出るときは、彩度だけを下げて収める。

OKLCH の明度は目の明るさに近いので、同じ `l` なら色が違っても読みやすさがそろう (HSL の明度では、黄色だけ極端に明るくなる)。`dark` が白地で 4.5:1 のコントラストに届かない色は、ビルドのときに警告が出る。色ごとに変えるときは `$color-tone-overrides: (secondary: (dark: (l: 50%)))` のように書く。`oklch()` は Safari 15.4、Chrome 111、Firefox 113 から使える。

### LCP の画像

CSS の背景画像など、発見が遅れる LCP の候補の画像は、`preloadImage()` で先に読める。URLは実際に表示する画像と同じにする (違うと 2 度読む) ので、`assetUrl()` の `?v=` が付く画像には使わない。

```php
renderHeader(['head' => preloadImage('assets/hero.jpg')]);
```

### 健全性チェック

設置に抜けがないかを、コマンドラインで確かめる。問題があれば `[NG]` を出し、終了コード 1 を返す。`[WARN]` は表示が壊れないものなので、終了コードに含めない。

```
php inc/check.php              手元のファイル (設定, 原稿, データファイル, お問い合わせフォームの準備)
php inc/check.php --http       あわせて公開中のサイトに問い合わせる
php inc/check.php --http=URL   問い合わせ先のサイトのルートの URL を指定する
```

`--http` では、`.htaccess` が効いているか (`inc/`, `templates/`, 原稿、データファイルの拒否、きれいな URL、転送、404、`/sitemap.xml`, `/robots.txt`、キャッシュ) を確かめる。`.htaccess` はアップロードし忘れても表示が崩れないので、アップロードのあとに実行する。

### 構成

```
index.php         トップページ (サイトが自由に書き換える)
blog/             投稿タイプ「blog」の入口 (index.php, .htaccess)
page/             固定ページの入口 (index.php だけ)
contact/          お問い合わせフォームの入口 (入力、confirm/、thanks/)
api/send.php      お問い合わせフォームの送信
content/          原稿とデータファイル
inc/              エンジン (404.php, sitemap.php, robots.php を含む)
templates/        サイトが持つテンプレートと設定 (config.php)
scss/, style.css  スタイル
js/, init.js      JavaScript
```

`content/` (`content/images/` の画像を除く), `inc/` (`404.php`, `sitemap.php`, `robots.php` を除く), `templates/` はWebから読めない。

### 原稿

原稿の冒頭に frontmatter を書く。

```
---
title: "記事のタイトル"
slug: "my-first-post"
date: "2026-10-08"
summary: "一覧と description に使う要約"
img: "/content/images/my-first-post/00.jpg"
---

本文の Markdown

![画像の説明](/content/images/my-first-post/01.jpg)
```

画像は `content/images/` に置き、`/content/images/...` の形で書く。サブディレクトリに置いたサイトでも、`/content/` で始まるパスは `SITE_ROOT` に合わせて置き換わる。それ以外の `/about/` のようなサイト内のリンクは置き換わらないので、サブディレクトリに置く場合はパスを合わせる。

| 項目 | 内容 |
| --- | --- |
| `title` | タイトル |
| `slug` | URLに使う英数字、ハイフン、アンダースコア |
| `date` | 日付。`2026-10-09` または `2026-10` の形で書く |
| `summary` | 要約 (任意) |
| `img` | 一覧の画像 (任意。サイトのルートからのパス) |
| `url` | リンク先 (`items: false` の投稿タイプ) |
| `order` | 並び順 (`sort: order` の投稿タイプ) |
| `draft` | `"true"` で下書きにする (任意) |

原稿と画像はリポジトリには入れない (`.gitignore` で除外。サンプルの `content/blog/welcome.md` と、`.htaccess` だけを管理)。

### 下書き

原稿に `draft: "true"` と書くと、下書きになる。

- 本番では読み込まない。一覧、最新記事、サイトマップに出ず、URLを直接開いても 404 になる
- 確認用サーバー (`php -S`) でだけ、一覧、個別記事、最新記事に出る。タイトルに `[下書き]` が付き、個別のページには `noindex` が付く。サイトマップには出ない
- XAMPP などの手元のサーバーは、本番と同じ扱いになる

テンプレートの書き換えは要らない。テンプレートから `ContentLoader` で原稿を読む場合も同じように働く。

### データファイル

料金や数値など、記事ではない値は `content/` の JSON に書く。テンプレートを書き換えずに値を変えられる。

```json
{
  "minPrice": 12000,
  "replyDays": "3"
}
```

`content/options.json` に書いた値は、テンプレートから次のように使う。

```php
<?= e((string)siteOption('replyDays')) ?>営業日以内
<?= e(fillOptions('{minPrice}円から')) ?>
```

| 関数 | 内容 |
| --- | --- |
| `contentJson('prices')` | `content/prices.json` を配列で返す。`contentJson('shop/fares')` のように、下のディレクトリも読める |
| `siteOption('minPrice')` | `content/options.json` の値を返す (なければ `null`)。区画ごとに分けるときは、区画の設定に `SITE_OPTIONS` (例: `'small-site-studio/options'`) を書くと、`content/small-site-studio/options.json` を読む |
| `fillOptions('{minPrice}円から')` | 文中の `{名前}` を `siteOption()` の値に置き換える (整数は 3 桁区切り。値がないときはそのまま残す。エスケープはしない) |

読めない JSON は空の配列になり、ページの表示は止まらない。内容はサーバーのエラーログに残るので、健全性チェックで確かめる。JSON はWebから読めない。`contentJson()` の引数には、URLのパラメータなど、外から来た値を渡さない。

### 原稿を別のリポジトリで管理する

原稿の履歴を残したいときは、原稿だけのリポジトリを作る。サイトのリポジトリの `.gitignore` は原稿を除外しているので、設定の変更は要らない。

1. 原稿のリポジトリを作る。リポジトリのルートが、サイトの `content/` の中身にあたる (`blog/`, `page/`, `images/` など)
2. `.htaccess` は原稿のリポジトリに入れない。サイトのリポジトリのものを使う
3. 手元で確かめるときは、原稿をサイトの `content/` にコピーする
4. 公開するときは、原稿をサーバーの `content/` にアップロードする。サーバーにしかないファイルを消す同期は使わない (`content/.htaccess` と `content/images/.htaccess` が消えて、原稿が公開されてしまう)

**書き換えるのは原稿のリポジトリのほうにする。** サイトの `content/` は Git の管理の外にあるので、`git status` に変更が出ず、消えても Git では戻せない。ブランチの切り替えや `git reset` で、同じ名前のファイルが上書きされたり消えたりすることもある。

### 投稿タイプを増やす

1. `templates/sections.php` の区画の `postTypes` に追加する (例: `news`)
2. `blog/` を `news/` としてコピーし、`index.php` の `ContentPage::render('blog')` を `'news'` に変える
3. `content/news/` に原稿を置く

項目と既定値は `inc/helpers.php` の `postType()` を参照。

### 固定ページ

`content/page/about.md` に原稿を置くと、`/about/` で開ける。

- 実在するフォルダと同じ slug (`blog`, `contact`, `page`, `templates` など) は使えない
- 階層は 1 段だけ
- `page/` に `.htaccess` を置かない

### お問い合わせフォーム

項目は `templates/config.php` の `SITE_CONTACT_FIELDS` から、入力欄、確認画面、メール本文、サーバー側の検証をまとめて作る。

- 項目の種類: `text`, `email`, `tel`, `url`, `textarea`, `select`, `radio`, `checkbox`, `date`
- 最初の `email` の項目を、自動返信の宛先と管理者宛メールの返信先にする
- トップページのセクションなどに置く場合は、`SITE_CONTACT` の `formUrl` を設定し、その場所で `renderContactForm()` を呼ぶ

迷惑メール対策として、次のものがある。

- Origin と Referer の確認: 送信元が `SITE_URL` と一致しない送信は 403 を返す
- 見えない入力欄: 値があれば、送ったふりをして破棄する
- 送信間隔の制限: 同じセッションから `SITE_CONTACT` の `interval` 秒以内に続けて送信すると 429 を返す

`.env` にはSMTPのパスワードが含まれるので、Git には含めない。`inc/.htaccess` でWebから読めないようにしているが、可能であれば公開ディレクトリの外に置き、`SITE_CONTACT` の `env` を書き換えるとより安全。設定や送信に失敗すると 500 を返し、内容をサーバーのエラーログに残す。

---

## ライセンス | License

MIT License

詳しくは LICENSE ファイルをご覧ください。  
See the LICENSE file for details.  

---

## 制作者 | Author

[QWEL.DESIGN](https://qwel.design)  
福井を拠点に活動するフロントエンド開発者  
Front-end developer based in Fukui, Japan  
