<?php
/**
 * サイトマップの入口 (本家: /sitemap.xml. .htaccess でここに振り分ける)
 * 本家の設定を読んでから, 共通の処理 (inc/sitemap.php) へ
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/sitemap.php';
