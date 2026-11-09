<?php
/**
 * 実績 (個別のページ: /small-site-studio/works/xxx/)
 * 原稿は content/works/*.md に置く (Co-Pilot と共通). URL は works/.htaccess のリライトで振り分ける
 * 一覧はトップページの実績のセクションなので, /works/ に来た場合はそこへ転送する
 * 画面は templates/studio/postSingle.php (区画を選んでから ContentPage を読む)
 */
require_once dirname(__DIR__, 2) . '/inc/bootstrap.php';
bootstrapSection('studio');

if (!isset($_GET['slug'])) {
  header('Location: ' . path() . '#works', true, 301);
  exit;
}

require_once dirname(__DIR__, 2) . '/inc/ContentPage.php';

ContentPage::render('works');
