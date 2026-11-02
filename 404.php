<?php
/**
 * 404 (ページが見つかりません. 本家の区画)
 * .htaccess の ErrorDocument から呼ばれる. 工房の区画 (/small-site-studio/) は small-site-studio/404.php
 */
require_once __DIR__ . '/inc/templates/notFound.php';

render_not_found();
