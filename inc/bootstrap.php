<?php
/**
 * bootstrap.php
 * 区画の設定と共通の helper を読み込む
 *
 * 区画の地図は templates/sections.php ([区画の名前 => ['config' => 設定ファイル, 'root' => ..., 'pages' => [...], 'postTypes' => [...], 'templates' => テンプレートの置き場]])
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

/**
 * 区画のテンプレートのファイルのパス (例: sectionTemplate('page.php'))
 * 地図の templates (その区画のテンプレートの置き場) にあればそれを, なければ templates/ のファイルを返す
 * 区画ごとにレイアウト (site.php) や画面のテンプレートを差し替え, 差し替えないものは共通のものを使う
 */
function sectionTemplate(string $file): string {
  // 区画をまだ選んでいなければ, 最初の区画を選ぶ
  $dir = bootstrapSection()['templates'] ?? null;
  if ($dir !== null && is_file($dir . '/' . $file)) return $dir . '/' . $file;
  return dirname(__DIR__) . '/templates/' . $file;
}

/**
 * 全区画のルート, 固定ページ, 投稿タイプ ([区画の名前 => ['root' => ..., 'pages' => [...], 'postTypes' => [...]]])
 * 選んだ区画は定数から, ほかの区画は地図から取る. 地図に root がない区画 (設定ファイルに書く古い形) は, 選んでいなければ飛ばす
 * サイトマップと健全性チェックが, 1 回のリクエストで全区画を見るために使う
 */
function sectionMaps(): array {
  bootstrapSection();
  $maps = [];
  foreach (siteSections() as $name => $map) {
    if ($name === activeSection()) {
      $maps[$name] = [
        'root'      => SITE_ROOT,
        'pages'     => defined('SITE_PAGES') ? SITE_PAGES : [],
        'postTypes' => defined('SITE_POST_TYPES') ? SITE_POST_TYPES : [],
      ];
    } elseif (isset($map['root'])) {
      $maps[$name] = ['root' => $map['root'], 'pages' => $map['pages'] ?? [], 'postTypes' => $map['postTypes'] ?? []];
    }
  }
  return $maps;
}

// 区画のルートの, リポジトリのルート (SITE_BASE) からのパス (例: 本家は '', 工房は 'small-site-studio/')
function sectionDir(string $root): string {
  $base = rtrim(siteBase(), '/') . '/';
  return substr(rtrim($root, '/') . '/', strlen($base));
}

// 選んだ区画の名前 (まだ選んでいなければ null)
function activeSection(): ?string {
  return defined('SITE_SECTION') ? SITE_SECTION : null;
}
