<?php
/**
 * サイトマップ (/sitemap.xml. 本家とちいさなサイト工房の全区画を1つにまとめる)
 * config-common.php の SITE_SECTIONS (区画ごとのルート, 投稿タイプ, ページ) と content/ の Markdown から自動で作るので,
 * 記事を足しても書き換えは要らない
 * 入口はルートの sitemap.php. /sitemap.xml は .htaccess で振り分ける
 */
if (!defined('SITE_LAYOUT')) {
  require_once dirname(__DIR__) . '/config.php';
}
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ContentLoader.php';

$urls = [];

foreach (SITE_SECTIONS as $section) {
  $root = $section['root'];
  $sectionUrls = [];
  $latestAll = '';

  foreach ($section['post_types'] ?? [] as $type => $typeConfig) {
    $config = post_type_defaults($type, $typeConfig);
    $posts = (new ContentLoader(site_file($config['dir'])))->load();
    // 記事は日付の新しい順なので, 先頭が一覧の更新日になる
    $latest = $posts[0]['date'] ?? '';
    if ($latest > $latestAll) $latestAll = $latest;

    if ($config['list']) {
      $sectionUrls[] = ['path' => $root . $config['path'], 'date' => $latest];
    }
    // 個別のページを作らない投稿タイプ (items: false) は, 一覧だけを載せる
    if ($config['items']) {
      foreach ($posts as $post) {
        $slug = rawurlencode($post['slug'] ?? '');
        $sectionUrls[] = [
          'path' => $root . $config['path'] . (SITE_PRETTY_URLS ? $slug . '/' : '?slug=' . $slug),
          'date' => $post['date'] ?? '',
        ];
      }
    }
  }

  // 投稿タイプ以外のページ
  foreach ($section['pages'] ?? [] as $pagePath) {
    $sectionUrls[] = ['path' => $root . $pagePath, 'date' => ''];
  }

  // 区画のトップ (最新の記事を載せているので, 区画の最新の日付にする)
  array_unshift($sectionUrls, ['path' => $root, 'date' => $latestAll]);
  array_push($urls, ...$sectionUrls);
}

header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url) { ?>
  <url>
    <loc><?= e(SITE_URL . $url['path']) ?></loc>
<?php if ($url['date']) { ?>
    <lastmod><?= e(ContentEngine::format_date($url['date'], true)) ?></lastmod>
<?php } ?>
  </url>
<?php } ?>
</urlset>
