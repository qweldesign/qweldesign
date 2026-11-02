<?php
/**
 * サイトマップ (区画ごと. 例: /sitemap.xml)
 * 区画の設定の投稿タイプと content/ の Markdown から自動で作るので, 記事を足しても書き換えは要らない
 * 入口は区画ごとの sitemap.php (その区画の設定を読んでから, これを読む). /sitemap.xml は .htaccess で振り分ける
 */
if (!defined('SITE_LAYOUT')) {
  require_once dirname(__DIR__) . '/config.php';
}
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ContentLoader.php';

$urls = [];
$latestAll = '';

foreach (array_keys(SITE_POST_TYPES) as $type) {
  $config = post_type($type);
  $posts = (new ContentLoader(site_file($config['dir'])))->load();
  // 記事は日付の新しい順なので, 先頭が一覧の更新日になる
  $latest = $posts[0]['date'] ?? '';
  if ($latest > $latestAll) $latestAll = $latest;

  if ($config['list']) {
    $urls[] = ['path' => type_path($type), 'date' => $latest];
  }
  // 個別のページを作らない投稿タイプ (items: false) は, 一覧だけを載せる
  if ($config['items']) {
    foreach ($posts as $post) {
      $urls[] = ['path' => post_path($type, $post), 'date' => $post['date'] ?? ''];
    }
  }
}

// 投稿タイプ以外のページ (config.php の SITE_PAGES)
foreach (SITE_PAGES as $pagePath) {
  $urls[] = ['path' => $pagePath, 'date' => ''];
}

// トップページ (最新記事を載せているので, 全体の最新の日付にする)
array_unshift($urls, ['path' => '', 'date' => $latestAll]);

header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url) { ?>
  <url>
    <loc><?= e(absolute_url($url['path'])) ?></loc>
<?php if ($url['date']) { ?>
    <lastmod><?= e(ContentEngine::format_date($url['date'], true)) ?></lastmod>
<?php } ?>
  </url>
<?php } ?>
</urlset>
