<?php
/**
 * 404 (ページが見つかりません. ちいさなサイト工房の区画)
 * small-site-studio/.htaccess の ErrorDocument から呼ばれる. 区画を選んでから, エンジンの 404 (inc/404.php) を読む
 * 画面は templates/studio/notFound.php
 */
require_once dirname(__DIR__) . '/inc/bootstrap.php';
bootstrapSection('studio');

require dirname(__DIR__) . '/inc/404.php';
