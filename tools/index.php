<?php
/**
 * Creative Tools (/tools/) の一覧
 * ツールは content/tools/*.md (1ツール = 1ファイル). ツールの中身は, それぞれのリポジトリから /tools/xxx/ に置く
 * ※ このディレクトリには .htaccess (リライト) を置かない (ツールのディレクトリを横取りしないように)
 */
require_once dirname(__DIR__) . '/inc/templates/links.php';

render_links('tools', [
  'audience' => 'UI/UX コンポーネント・PHP 作品集',
  'texts'    => [
    'PHP / JS でスクラッチした、実用的な機能モジュール群。',
  ],
]);
