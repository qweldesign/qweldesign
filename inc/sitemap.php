<?php
/**
 * サイトマップ (/sitemap.xml)
 * 区画の投稿タイプ (templates/sections.php) と content/ の原稿から作るので, 記事を足しても書き換えは要らない
 * /sitemap.xml へのアクセスは, ルートの .htaccess でここに振り分ける
 */

require_once __DIR__ . '/bootstrap.php';
bootstrapSection();
require_once __DIR__ . '/ContentLoader.php';

$today = date('Y-m-d');
// 更新日にできる日付 (形が違う日付と, 未来の日付は除く. 未来の日付の記事は, これから開催する催しなど)
$lastmodOf = function (array $post) use ($today): string {
  $date = formatDate($post['date'] ?? '', '-');
  return preg_match('/^\d{4}-\d{2}(-\d{2})?$/', $date) && $date <= $today ? $date : '';
};

$urls = [];
$latestAll = '';

foreach (array_keys(SITE_POST_TYPES) as $type) {
  $config = postType($type);
  // 下書きは確認用サーバーでも載せない
  $posts = (new ContentLoader())->load(siteFile($config['dir']), null, $config['sort'], false);
  $lastmods = array_map($lastmodOf, $posts);
  $latest = $lastmods ? max($lastmods) : '';
  if ($latest > $latestAll) $latestAll = $latest;

  // ルートに置く投稿タイプ (固定ページ) に一覧はない
  if ($config['list'] && $config['path'] !== '') {
    $urls[] = ['path' => typePath($type), 'date' => $latest];
  }
  // 個別のページを持たない投稿タイプは, 一覧だけを載せる
  if (!$config['items']) continue;
  foreach ($posts as $index => $post) {
    if (!empty($post['slug'])) $urls[] = ['path' => postPath($type, $post), 'date' => $lastmods[$index]];
  }
}

foreach (defined('SITE_PAGES') ? SITE_PAGES : [] as $page) {
  $urls[] = ['path' => $page, 'date' => ''];
}

// トップページ (最新記事を載せるので, 全体の最新の日付にする)
array_unshift($urls, ['path' => '', 'date' => $latestAll]);

header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url) { ?>
  <url>
    <loc><?= e(absoluteUrl($url['path'])) ?></loc>
<?php if ($url['date']) { ?>
    <lastmod><?= e($url['date']) ?></lastmod>
<?php } ?>
  </url>
<?php } ?>
</urlset>
