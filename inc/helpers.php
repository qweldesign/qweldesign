<?php
/**
 * helpers.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * テンプレートで使う関数 (エスケープ, URL, 日付, 投稿タイプ)
 * サイトの設定 (config.php) を読み込んだあとに使う
 */

require_once __DIR__ . '/ContentEngine.php';

// HTMLエスケープ
function e(?string $value): string {
  return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// サイトのルートからのパス (例: content/images/a.jpg) を, ドメインからの絶対パスにする
// 先頭の「/」「./」はサイトのルートとみなす. http から始まる URL はそのまま返す
function path(string $path = ''): string {
  if (preg_match('#^https?://#', $path)) return $path;
  return SITE_ROOT . preg_replace('#^\.?/#', '', $path);
}

// サイトのルートからのパスを絶対URLにする (OGP, canonical 用)
function absolute_url(string $path): string {
  return preg_match('#^https?://#', $path) ? $path : SITE_URL . path($path);
}

// サイトのルートからのパスを, サーバー上のファイルのパスにする (クエリ文字列は除く)
function site_file(string $path): string {
  return dirname(__DIR__) . '/' . preg_replace(['#^\.?/#', '#[?\#].*$#'], '', $path);
}

/**
 * OGP 画像の URL と大きさ
 * サイト内の画像でファイルがまだない場合は, SITE_OGP_IMAGE に置き換える (共有したときのプレビューが空にならないように)
 * 戻り値: ['url' => 絶対URL, 'width' => int|null, 'height' => int|null, 'is_default' => SITE_OGP_IMAGE か]
 */
function og_image(string $img): array {
  $isDefault = $img === SITE_OGP_IMAGE;
  $size = false;
  if (!preg_match('#^https?://#', $img)) {
    if (!$isDefault && !is_file(site_file($img))) {
      $img = SITE_OGP_IMAGE;
      $isDefault = true;
    }
    $file = site_file($img);
    if (is_file($file)) $size = @getimagesize($file);
  }
  return [
    'url'        => absolute_url($img),
    'width'      => $size ? $size[0] : null,
    'height'     => $size ? $size[1] : null,
    'is_default' => $isDefault,
  ];
}

// 投稿タイプの設定 (未指定の項目は既定値で補う)
function post_type(string $type): array {
  if (!isset(SITE_POST_TYPES[$type])) {
    throw new InvalidArgumentException("投稿タイプ {$type} は config.php にありません");
  }
  return SITE_POST_TYPES[$type] + [
    'label'     => $type,
    'path'      => $type . '/',
    'dir'       => 'content/' . $type,
    'count'     => 10,
    'show_date' => true,
    'lead'      => '',
    'list'      => true,
    'items'     => true,
  ];
}

// 投稿タイプの一覧・記事・ページ送りのパス (サイトのルートからのパス. canonical などに使う)
function type_path(string $type): string {
  return post_type($type)['path'];
}

function post_path(string $type, array $post): string {
  $slug = rawurlencode($post['slug'] ?? '');
  return type_path($type) . (SITE_PRETTY_URLS ? $slug . '/' : '?slug=' . $slug);
}

// $page は %d も受け付ける (ContentNavigation の pageUrl 用)
function page_path(string $type, int|string $page): string {
  if ($page === 1) return type_path($type);
  return type_path($type) . (SITE_PRETTY_URLS ? 'page/' . $page . '/' : '?page=' . $page);
}

// 上のパスを, リンクに使う URL (ドメインからの絶対パス) にしたもの
function type_url(string $type): string {
  return path(type_path($type));
}

function post_url(string $type, array $post): string {
  return path(post_path($type, $post));
}

function page_url(string $type, int|string $page): string {
  return path(page_path($type, $page));
}

// 記事の画像の URL (画像がなければ既定のサムネイル)
function image_url(?string $img): string {
  return path(($img ?? '') ?: SITE_THUMBNAIL_IMAGE);
}

// 記事の日付の time 要素 (年月のみの日付にも対応する. 日付がなければ空文字)
function date_tag(?string $date, string $class = ''): string {
  if (!$date) return '';
  $classAttr = $class !== '' ? ' class="' . e($class) . '"' : '';
  return '<time' . $classAttr . ' datetime="' . e(ContentEngine::format_date($date, true)) . '">'
    . e(ContentEngine::format_date($date)) . '</time>';
}

// 著作権表示の年 (例: 2019 - 2026. 開始年と同じ年は範囲表記にしない)
function copyright_years(): string {
  $current = (int)date('Y');
  return SITE_START_YEAR < $current ? SITE_START_YEAR . ' - ' . $current : (string)$current;
}

// 投稿タイプの ContentEngine を作る (パンくず・ページ送りの設定も済ませる)
function content_engine(string $type, array $options = []): ContentEngine {
  $config = post_type($type);
  $parents = array_map(fn($crumb) => ['label' => $crumb['label'], 'path' => path($crumb['path'])], SITE_BREADCRUMB);
  return new ContentEngine($options + [
    'dir'     => dirname(__DIR__) . '/' . trim($config['dir'], '/') . '/',
    'count'   => $config['count'],
    'baseUrl' => path(),
    'navigation' => [
      'parents'      => $parents,
      'subdirLabel'  => $config['label'],
      'subdirPath'   => type_url($type),
      'pageUrl'      => page_url($type, '%d'),
      'firstPageUrl' => type_url($type),
    ],
  ]);
}
