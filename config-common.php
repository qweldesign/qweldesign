<?php
/**
 * 共通の設定 (本家 config.php と, ちいさなサイト工房 studio-config.php の両方が読む)
 * 区画 (本家・工房) によって変わらない値だけをここに書く
 *
 * 区画の設定は「1回のリクエストで1つだけ」読む (PHP の定数は上書きできないため)
 *   本家のページ:  config.php        (inc/bootstrap.php が規定で読む)
 *   工房のページ:  studio-config.php (ページの先頭で読んでから inc/bootstrap.php を読む)
 */

// 日付の判定・表示のタイムゾーン
date_default_timezone_set('Asia/Tokyo');

// サイトの URL (末尾のスラッシュなし. OGP, canonical の絶対URLに使う)
const SITE_URL = 'https://qwel.design';
// リポジトリのルートの URL のパス (assets/, content/ など, 区画をまたいで共通のファイルの置き場所)
// 区画のルート (SITE_ROOT) とは別. assets/, content/ から始まるパスは, path() がここから作る
const SITE_BASE = '/';
// 言語
const SITE_LANG = 'ja';
const SITE_OG_LOCALE = 'ja_JP';
// Google アナリティクス (不要なら空文字)
const SITE_GA_ID = 'G-28NN8JBME5';
// 運営者 (著作権表示)
const SITE_OWNER = 'QWEL.DESIGN';
// URL の形式 (false: ?slug=xxx, ?page=2 / true: xxx/, page/2/ ※ .htaccess のリライトが必要)
const SITE_PRETTY_URLS = true;

/**
 * 区画の地図 (区画ごとのルート, 投稿タイプ, 投稿タイプ以外のページ)
 * 区画の設定 (config.php, studio-config.php) は, ここから自分の区画の値を読む
 * サイトマップ (/sitemap.xml) は, ここから全区画の URL を1つにまとめる
 *
 * root: 区画のルートのパス (ページの URL は path() でここから作る)
 * pages: 投稿タイプ以外のページ (区画のルートからのパス. サイトマップに載せる. 区画のトップは自動で載る)
 * post_types: 投稿タイプ
 *   キー: 投稿タイプの名前
 *   label: 表示名 (一覧の見出し, パンくず)
 *   path: 一覧の URL (区画のルートからのパス)
 *   dir: Markdown を置くディレクトリ (リポジトリのルートからのパス. content/ 以下)
 *   list: 一覧ページをサイトマップに載せるか (規定で true)
 *   items: 個別のページを作るか (規定で true. false なら一覧から各作品の url へ直接リンクし, サイトマップにも載せない)
 *   count: 一覧の1ページあたりの件数 (null で全件)
 *   show_date: 日付を表示するか
 *   lead: 一覧の見出しの下の文
 */
const SITE_SECTIONS = [
  // 本家 (QWEL in Action). creative, tools は一覧だけ (各作品の中身は, それぞれのリポジトリから置く)
  // そのため creative/, tools/ には投稿タイプ用の .htaccess (リライト) を置かない (作品のディレクトリを横取りしないように)
  'main' => [
    'root'       => '/',
    'pages'      => [
      'co-pilot/',
      'contact/',
    ],
    'post_types' => [
      'creative' => [
        'label'     => 'Creative Crafts',
        'path'      => 'creative/',
        'dir'       => 'content/creative',
        'items'     => false,
        'count'     => null,
        'show_date' => false,
        'lead'      => 'Vanilla JS でスクラッチした、アニメーションやインタラクティブな表現作品の一覧です。',
      ],
      'tools' => [
        'label'     => 'Creative Tools',
        'path'      => 'tools/',
        'dir'       => 'content/tools',
        'items'     => false,
        'count'     => null,
        'show_date' => false,
        'lead'      => 'PHP / JS でスクラッチした、実用的な機能モジュールの一覧です。',
      ],
    ],
  ],
  // ちいさなサイト工房 (/small-site-studio/)
  // 実績の一覧はトップの実績セクション. /works/ はそこへ転送するので, サイトマップには個別のページだけを載せる
  // 実績の frontmatter に category (業種), role (担当範囲), website (実際のサイトの URL) を書く
  'studio' => [
    'root'       => '/small-site-studio/',
    'pages'      => [
      'contact/',
    ],
    'post_types' => [
      'works' => [
        'label'     => '実績',
        'path'      => 'works/',
        'dir'       => 'content/works',
        'list'      => false,
        'count'     => null,
        'show_date' => false,
        'lead'      => '',
      ],
    ],
  ],
];
