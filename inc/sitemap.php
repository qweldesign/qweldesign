<?php
/**
 * サイトマップ (/sitemap.xml)
 * 区画の地図 (templates/sections.php) の全区画の投稿タイプと, content/ の原稿から作るので, 記事を足しても書き換えは要らない
 * 区画ごとに, トップ, 投稿タイプの一覧と記事, 固定ページの順に載せる
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
// 原稿のディレクトリ (content/ などの共有の置き場は, リポジトリのルートから. それ以外は区画のルートから)
$dirOf = fn(string $dir, string $sectionDir): string => dirname(__DIR__) . '/' . (isSharedPath($dir) ? '' : $sectionDir) . trim($dir, '/');

$urls = [];
foreach (sectionMaps() as $section) {
  $root = rtrim($section['root'], '/') . '/';
  $sectionDir = sectionDir($root);
  $sectionUrls = [];
  $latestAll = '';

  foreach ($section['postTypes'] as $type => $rawConfig) {
    $config = postTypeDefaults($type, $rawConfig);
    // 下書きは確認用サーバーでも載せない
    $posts = (new ContentLoader())->load($dirOf($config['dir'], $sectionDir), null, $config['sort'], false);
    $lastmods = array_map($lastmodOf, $posts);
    $latest = $lastmods ? max($lastmods) : '';
    if ($latest > $latestAll) $latestAll = $latest;

    // ルートに置く投稿タイプ (固定ページ) に一覧はない
    if ($config['list'] && $config['path'] !== '') {
      $sectionUrls[] = ['path' => $config['path'], 'date' => $latest];
    }
    // 個別のページを持たない投稿タイプは, 一覧だけを載せる
    if (!$config['items']) continue;
    foreach ($posts as $index => $post) {
      if (!empty($post['slug'])) $sectionUrls[] = ['path' => postPathOf($type, $config, $post), 'date' => $lastmods[$index]];
    }
  }

  foreach ($section['pages'] as $page) {
    $sectionUrls[] = ['path' => $page, 'date' => ''];
  }

  // 区画のトップ (最新記事を載せるので, 区画の最新の日付にする)
  array_unshift($sectionUrls, ['path' => '', 'date' => $latestAll]);
  foreach ($sectionUrls as $url) {
    $urls[] = ['loc' => SITE_URL . $root . $url['path'], 'date' => $url['date']];
  }
}

header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url) { ?>
  <url>
    <loc><?= e($url['loc']) ?></loc>
<?php if ($url['date']) { ?>
    <lastmod><?= e($url['date']) ?></lastmod>
<?php } ?>
  </url>
<?php } ?>
</urlset>
