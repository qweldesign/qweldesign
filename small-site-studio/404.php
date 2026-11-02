<?php
/**
 * 404 (ページが見つかりません. ちいさなサイト工房の区画)
 * small-site-studio/.htaccess の ErrorDocument から呼ばれる
 */
require_once dirname(__DIR__) . '/studio-config.php';
require_once dirname(__DIR__) . '/inc/templates/notFound.php';

render_not_found();
