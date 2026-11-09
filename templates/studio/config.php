<?php
/**
 * ちいさなサイト工房 (/small-site-studio/) の区画設定
 * サイト名, ロゴ, パンくずなど, 工房の区画だけの値をここにまとめる
 * ルート, 固定ページ, 投稿タイプは templates/sections.php, 本家と共通の値は templates/common.php (この設定の最後に読む)
 */

// サイト名 (<title> の「記事タイトル | サイト名」と og:site_name に使う. お問い合わせの送信元にもなる)
const SITE_NAME = 'ちいさなサイト工房';
// トップページ (紹介ページ) の <title>
const SITE_TOP_TITLE = 'ちいさなサイト工房｜福井の小さな事業のためのウェブサイト制作・更新代行';
// サイトの説明 (description の既定値)
const SITE_DESCRIPTION = '更新を頼める、壊れにくい。福井・越前海岸の QWEL.DESIGN が、小さな事業のためのウェブサイトを作り、お知らせの掲載や小さな直しまで引き受けます。';
// Google Fonts などの読み込み (不要なら空文字)
const SITE_FONTS_URL = 'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Noto+Sans+JP:wght@400;700&family=Shippori+Mincho:wght@500;700&display=swap';
const SITE_TEXT_FONTS = [];
// OGP 画像の既定値 (content/images/banners/ は本家のバナーと共用. 1200 × 630. 原稿は tools/ogp/small-site-studio.html)
// 画像を作り直したときは, ファイル名を変えると SNS や LINE の古いキャッシュを避けられる
const SITE_OGP_IMAGE = 'content/images/banners/small-site-studio.jpg';
const SITE_OGP_IMAGE_ALT = 'ちいさなサイト工房 — 更新を頼める、壊れにくい。小さな事業のためのウェブサイト';
// 記事一覧のサムネイルの既定値 (記事にアイキャッチがない場合)
const SITE_THUMBNAIL_IMAGE = 'content/images/banners/small-site-studio.jpg';
// スタイルシート (リポジトリのルートからのパス. scss/studio.scss から書き出す)
const SITE_STYLESHEET = 'studio.css';

// ヘッダーのロゴ (画像のパスとリンク先. 画像がなければ SITE_NAME を文字で出す)
const SITE_LOGO = 'assets/logo.svg';
const SITE_LOGO_LINK = '';
// ロゴの横に添える文字 (任意)
const SITE_LOGO_TEXT = 'ちいさなサイト工房';
const SITE_LOGO_SUBTEXT = 'by QWEL.DESIGN 福井・越前海岸';

// ヘッダーのナビ (サイトのルートからのパス => 表示名. 不要なら空の配列)
const SITE_NAV = [];

// パンくずの先頭の階層 (path はサイトのルートからのパス, または http から始まる URL)
// 本家 (QWEL in Action) から始め, 本家へ戻る道にする. 最後の階層が, このサイトのトップ
const SITE_BREADCRUMB = [
  ['label' => 'QWEL in Action', 'path' => 'https://qwel.design/'],
  ['label' => 'ちいさなサイト工房', 'path' => ''],
];

// フッターのリンク (サイトのルートからのパス => 表示名)
const SITE_FOOTER_LINKS = [
  'https://qwel.design/' => 'QWEL in Action',
];

// フッターの SNS など (url は http から始まる URL, またはサイトのルートからのパス. icon は icons.svg の symbol の id)
// 本家・Co-Pilot と同じ並び (note, GitHub, Contact)
const SITE_SNS = [
  ['label' => 'note', 'url' => 'https://note.com/qweldesign', 'icon' => 'si-journal'],
  ['label' => 'GitHub', 'url' => 'https://github.com/qweldesign', 'icon' => 'si-github'],
  ['label' => 'Contact Me', 'url' => 'https://qwel.design/contact/', 'icon' => 'si-mail'],
];

// robots.txt はドメインのルートの 1 つだけ (本家の設定から作る)
const SITE_ROBOTS_DISALLOW = [];

// 数値などの設定 (content/small-site-studio/options.json. 料金, 返事の日数, 制作期間など)
// siteOption() で読み, 文中の {reply_days} などは fillOptions() で差し込む. 「制作費に含まれるもの」(included), 別料金 (extras), よくある不安 (faq) もここ
const SITE_OPTIONS = 'small-site-studio/options';

// 本家と共通の値 (URL, GA, 運営者, お問い合わせなど). フォームの送信元に SITE_NAME を使うので, 最後に読む
require_once dirname(__DIR__) . '/common.php';
