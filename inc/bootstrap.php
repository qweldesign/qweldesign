<?php
/**
 * bootstrap.php
 * 区画設定と共通の helper を読み込む
 */

function bootstrapSection(string $section, array $sections): array {
  static $activeSection = null;

  if (!isset($sections[$section]['config']) || !is_file($sections[$section]['config'])) {
    throw new InvalidArgumentException("区画 {$section} の設定ファイルがありません");
  }
  if ($activeSection !== null && $activeSection !== $section) {
    throw new LogicException('1つのリクエストで読み込める区画設定は1つです');
  }

  if ($activeSection === null) {
    require_once $sections[$section]['config'];
    require_once __DIR__ . '/helpers.php';
    if (!defined('SITE_ROOT')) {
      throw new LogicException("区画 {$section} の設定に SITE_ROOT がありません");
    }
    $root = rtrim(SITE_ROOT, '/') . '/';
    $base = rtrim(siteBase(), '/') . '/';
    if (!str_starts_with($root, $base)) {
      throw new LogicException('SITE_ROOT は SITE_BASE の下に設定してください');
    }
    $activeSection = $section;
    $GLOBALS['active_site_section'] = $section;
  }

  return $sections[$section];
}

function activeSection(): ?string {
  return $GLOBALS['active_site_section'] ?? null;
}
