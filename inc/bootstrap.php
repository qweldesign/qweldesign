<?php
/**
 * bootstrap.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * ページの準備: 区画の設定, 関数, その区画のヘッダー・フッターの部品を読む
 * テンプレート (inc/templates/*.php) とページは, partials/ を直接ではなく, これを読む
 *
 * 区画の設定は1回のリクエストで1つだけ (PHP の定数は上書きできないため)
 *   本家のページ: 何もしなくてよい (設定がまだなければ config.php を読む)
 *   工房のページ: 先に studio-config.php を読んでから, これ (やテンプレート) を読む
 */

if (!defined('SITE_LAYOUT')) {
  require_once dirname(__DIR__) . '/config.php';
}
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/partials/' . SITE_LAYOUT . '.php';
