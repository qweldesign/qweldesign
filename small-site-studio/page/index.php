<?php
/**
 * ちいさなサイト工房の固定ページの入口 (/small-site-studio/xxx/ → ここ)
 * 原稿は content/small-site-studio/page/*.md. URL は工房の .htaccess のリライトで振り分ける
 * 画面は templates/studio/page.php (区画を選んでから ContentPage を読む)
 * ※ このディレクトリには .htaccess を置かない (固定ページのリライトが効かなくなるため)
 */
require_once dirname(__DIR__, 2) . '/inc/bootstrap.php';
bootstrapSection('studio');
require_once dirname(__DIR__, 2) . '/inc/ContentPage.php';

ContentPage::render('page');
