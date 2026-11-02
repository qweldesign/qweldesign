<?php
/**
 * Creative Crafts (/creative/) の一覧
 * 作品は content/creative/*.md (1作品 = 1ファイル). 作品の中身は, それぞれのリポジトリから /creative/xxx/ に置く
 * ※ このディレクトリには .htaccess (リライト) を置かない (作品のディレクトリを横取りしないように)
 */
require_once dirname(__DIR__) . '/inc/templates/links.php';

render_links('creative', [
  'audience' => 'インタラクティブ表現・JavaScript 作品集',
  'texts'    => [
    'Vanilla JS でスクラッチした、アニメーションやインタラクティブな表現作品群。',
    '2025年から、Three / Vue / React 各ライブラリ・フレームワークも併用しています。',
  ],
]);
