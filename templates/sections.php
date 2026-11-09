<?php
/**
 * 区画の地図 (区画ごとの設定ファイル, ルート, 固定ページ, 投稿タイプ)
 * 1 回のリクエストで読む区画の設定は 1 つだけ (PHP の定数は上書きできないため). inc/bootstrap.php の bootstrapSection() で選ぶ
 * 区画のルート, 固定ページ, 投稿タイプは, ここから SITE_ROOT, SITE_PAGES, SITE_POST_TYPES を作る
 * (サイトマップや健全性チェックが, 1 回のリクエストで全区画を見られるように, 設定ファイルではなくここに書く)
 *
 * キー: 区画の名前 (最初の区画が既定. サイトマップ, robots.txt, 送信の入口などは既定の区画の設定を読む)
 * config: 区画の設定ファイル (サイト名, ナビ, OGP など)
 * root: 区画のルートの URL のパス (SITE_ROOT. SITE_BASE の下に置く)
 * pages: 投稿タイプ以外のページ (SITE_PAGES. 区画のルートからのパス. サイトマップに載せる)
 * postTypes: 投稿タイプ (SITE_POST_TYPES. 書き方は README の「投稿タイプを増やす」)
 * templates: その区画のテンプレートの置き場 (任意. ここにあるファイルが templates/ より優先される. inc/bootstrap.php の sectionTemplate())
 */

return [
  // 本家 (QWEL in Action)
  'main' => [
    'config'    => __DIR__ . '/config.php',
    'root'      => '/',
    'pages'     => [
      'co-pilot/',
      'contact/',
    ],
    // creative, tools は一覧だけ (items: false)。各作品の中身は, それぞれのリポジトリから /creative/xxx/, /tools/xxx/ に置く
    // そのため creative/, tools/ には投稿タイプ用の .htaccess (リライト) を置かない (作品のディレクトリを横取りしないように)
    // このサイトだけのキー (templates/postList.php が読む):
    //   audience: 見出しの下の和文 (読み手・ページの用途)
    //   texts: 導入文 (段落ごとの配列)
    'postTypes' => [
      'creative' => [
        'label'    => 'Creative Crafts',
        'path'     => 'creative/',
        'dir'      => 'content/creative',
        'items'    => false,
        'count'    => null,
        'showDate' => false,
        'sort'     => 'order',
        'lead'     => 'Vanilla JS でスクラッチした、アニメーションやインタラクティブな表現作品の一覧です。',
        'audience' => 'インタラクティブ表現・JavaScript 作品集',
        'texts'    => [
          'Vanilla JS でスクラッチした、アニメーションやインタラクティブな表現作品群。',
          '2025年から、Three / Vue / React 各ライブラリ・フレームワークも併用しています。',
        ],
      ],
      'tools' => [
        'label'    => 'Creative Tools',
        'path'     => 'tools/',
        'dir'      => 'content/tools',
        'items'    => false,
        'count'    => null,
        'showDate' => false,
        'sort'     => 'order',
        'lead'     => 'PHP / JS でスクラッチした、実用的な機能モジュールの一覧です。',
        'audience' => 'UI/UX コンポーネント・PHP 作品集',
        'texts'    => [
          'PHP / JS でスクラッチした、実用的な機能モジュール群。',
        ],
      ],
    ],
  ],
  // ちいさなサイト工房 (/small-site-studio/). レイアウトと画面は templates/studio/ (なければ templates/)
  // 実績の一覧はトップの実績のセクション. /works/ はそこへ転送するので, サイトマップには個別のページだけを載せる (list: false)
  // 実績の frontmatter に category (業種), role (担当範囲), website (実際のサイトの URL) を書く (Co-Pilot と共通の原稿)
  'studio' => [
    'config'    => __DIR__ . '/studio/config.php',
    'root'      => '/small-site-studio/',
    'pages'     => [
      'contact/',
    ],
    'templates' => __DIR__ . '/studio',
    'postTypes' => [
      'works' => [
        'label'    => '実績',
        'path'     => 'works/',
        'dir'      => 'content/works',
        'list'     => false,
        'count'    => null,
        'showDate' => false,
        'sort'     => 'order',
      ],
    ],
  ],
];
