<?php
/**
 * bootstrap.php
 * 区画の設定と共通の helper を読み込む
 *
 * 区画の地図は templates/sections.php ([区画の名前 => ['config' => 設定ファイル, 'root' => ..., 'pages' => [...], 'postTypes' => [...]]])
 * 1 回のリクエストで読む区画は 1 つだけ (PHP の定数は上書きできないため)
 *
 * 使い方 (区画のページの先頭):
 *   require_once .../inc/bootstrap.php;
 *   bootstrapSection('main');
 * サイトマップや送信の入口など, 区画を決めない入口は bootstrapSection() (最初の区画)
 */

// 区画の地図 (templates/sections.php. 1 回だけ読む)
function siteSections(): array {
  static $sections = null;
  return $sections ??= require dirname(__DIR__) . '/templates/sections.php';
}

/**
 * 区画を選び, その設定を読む ($section を省くと最初の区画)
 * 地図に root, pages, postTypes があれば, SITE_ROOT, SITE_PAGES, SITE_POST_TYPES を作ってから設定を読む
 * (ない場合は, これまでどおり設定ファイルに書く)
 * 戻り値: 地図のその区画の値
 */
function bootstrapSection(?string $section = null): array {
  static $activeSection = null;

  $sections = siteSections();
  $section ??= $activeSection ?? array_key_first($sections);
  if (!isset($sections[$section]['config']) || !is_file($sections[$section]['config'])) {
    throw new InvalidArgumentException("区画 {$section} の設定ファイルがありません");
  }
  if ($activeSection !== null && $activeSection !== $section) {
    throw new LogicException('1つのリクエストで読み込める区画設定は1つです');
  }

  if ($activeSection === null) {
    $map = $sections[$section];
    define('SITE_SECTION', $section);
    if (isset($map['root'])) define('SITE_ROOT', $map['root']);
    if (isset($map['pages'])) define('SITE_PAGES', $map['pages']);
    if (isset($map['postTypes'])) define('SITE_POST_TYPES', $map['postTypes']);
    require_once $map['config'];
    require_once __DIR__ . '/helpers.php';
    if (!defined('SITE_ROOT')) {
      throw new LogicException("区画 {$section} の SITE_ROOT がありません");
    }
    $root = rtrim(SITE_ROOT, '/') . '/';
    $base = rtrim(siteBase(), '/') . '/';
    if (!str_starts_with($root, $base)) {
      throw new LogicException('SITE_ROOT は SITE_BASE の下に設定してください');
    }
    $activeSection = $section;
  }

  return $sections[$section];
}

// 選んだ区画の名前 (まだ選んでいなければ null)
function activeSection(): ?string {
  return defined('SITE_SECTION') ? SITE_SECTION : null;
}
