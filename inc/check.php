<?php
/**
 * check.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 設置の健全性チェック (コマンドラインから実行する)
 *   php inc/check.php              手元のファイル (設定, 原稿, お問い合わせフォームの準備)
 *   php inc/check.php --http       あわせて公開中のサイト (SITE_URL + SITE_ROOT) に問い合わせる
 *   php inc/check.php --http=URL   問い合わせ先のサイトのルートの URL を指定する (例: https://staging.example.com/)
 *
 * .htaccess はドットファイルで, inc/vendor/ は Git に入らない. どちらも上げ忘れやすく, 手元では気付けないので, ここで確かめる
 * [NG] があれば終了コード 1 を返す. [WARN] は表示が壊れないものなので, 終了コードに含めない
 * .env の値は出力しない
 */

// Web からは実行しない (.htaccess が効いていない場合にも, 設定の中身を出さないように)
if (PHP_SAPI !== 'cli') {
  http_response_code(404);
  exit;
}

$root = dirname(__DIR__);
require_once __DIR__ . '/bootstrap.php';
bootstrapSection();
require_once __DIR__ . '/ContentLoader.php';
require_once __DIR__ . '/env.php';

$failures = 0;
$warnings = 0;

function report(string $status, string $message): void {
  global $failures, $warnings;
  if ($status === 'NG') $failures++;
  if ($status === 'WARN') $warnings++;
  echo "  [{$status}] {$message}\n";
}

function check(bool $ok, string $message): void {
  report($ok ? 'OK' : 'NG', $message);
}

// 原稿の日付の形 (2026-10-09, または年月だけの 2022-08)
function isValidDate(string $date): bool {
  if (!preg_match('/^(\d{4})-(\d{2})(?:-(\d{2}))?$/', $date, $parts)) return false;
  return checkdate((int)$parts[2], (int)($parts[3] ?? 1), (int)$parts[1]);
}

// ---- 設定 ----
echo "設定\n";
check(PHP_VERSION_ID >= 80000, 'PHP ' . PHP_VERSION . ' (8.0 以上)');
check((bool)preg_match('#^https?://[^/]+$#', SITE_URL), 'SITE_URL が http から始まり, 末尾にスラッシュがない');
check(str_starts_with(SITE_ROOT, '/') && str_ends_with(SITE_ROOT, '/'), 'SITE_ROOT がスラッシュで始まり, スラッシュで終わる');
$hasVendor = is_file(__DIR__ . '/vendor/autoload.php');
check($hasVendor, 'inc/vendor/ がある (inc/ で composer install. ないとサイト全体を表示できない)');
// 古い inc/vendor/ が残っていると, autoload.php はあっても Parsedown がなく, 記事の表示で初めて止まる
if ($hasVendor) {
  require_once __DIR__ . '/vendor/autoload.php';
  check(class_exists('Parsedown'), 'Parsedown がある (inc/ で composer install. ないと記事を表示できない)');
}
foreach (['.htaccess', 'inc/.htaccess', 'templates/.htaccess', 'content/.htaccess', 'content/images/.htaccess', 'inc/404.php', 'inc/sitemap.php', 'inc/robots.php'] as $file) {
  check(is_file("{$root}/{$file}"), "{$file} がある");
}
if (is_file("{$root}/.htaccess")) {
  // ErrorDocument はドメインからの絶対パスなので, SITE_ROOT を変えたときに書き換え忘れやすい
  $errorDocument = preg_match('/^\s*ErrorDocument\s+404\s+(\S+)/m', (string)file_get_contents("{$root}/.htaccess"), $match) ? $match[1] : '';
  check($errorDocument === SITE_ROOT . 'inc/404.php', '.htaccess の ErrorDocument が ' . SITE_ROOT . 'inc/404.php' . " ({$errorDocument})");
}
// 区画のフォルダの .htaccess の ErrorDocument (区画ごとの 404) が指すファイルがある
$sections = sectionMaps();
foreach ($sections as $name => $section) {
  $dir = sectionDir($section['root']);
  if ($dir === '' || !is_file("{$root}/{$dir}.htaccess")) continue;
  if (!preg_match('/^\s*ErrorDocument\s+404\s+(\S+)/m', (string)file_get_contents("{$root}/{$dir}.htaccess"), $match)) continue;
  $file = substr($match[1], strlen(rtrim(siteBase(), '/') . '/'));
  check(str_starts_with($match[1], rtrim(siteBase(), '/') . '/') && is_file("{$root}/{$file}"), "{$name}: {$dir}.htaccess の ErrorDocument のファイルがある ({$match[1]})");
}
foreach (['SITE_OGP_IMAGE', 'SITE_THUMBNAIL_IMAGE', 'SITE_LOGO'] as $name) {
  $image = defined($name) ? constant($name) : '';
  if ($image === '' || isExternal($image)) continue;
  check(siteFileExists($image), "{$name} ({$image}) がある");
}

// ---- 原稿 ----
echo "原稿\n";
$loader = new ContentLoader();
$samples = []; // HTTP のチェックに使う, 投稿タイプごとの記事を 1 つ ([区画のフォルダ, 投稿タイプ, 設定, 原稿のファイル名, 記事])
$sampleImage = '';
foreach ($sections as $sectionName => $section) {
$sectionDir = sectionDir($section['root']);
foreach ($section['postTypes'] as $type => $rawConfig) {
  $config = postTypeDefaults($type, $rawConfig);
  $isRoot = $config['path'] === '';
  $entry = $sectionDir . trim($isRoot ? $type : $config['path'], '/');
  $dir = $root . '/' . (isSharedPath($config['dir']) ? '' : $sectionDir) . trim($config['dir'], '/');
  // 複数の区画があるときは, 区画の名前を添える
  $label = count($sections) > 1 ? "{$sectionName}/{$type}" : $type;

  check(is_file("{$root}/{$entry}/index.php"), "{$label}: {$entry}/index.php がある");
  // 個別のページを持たない投稿タイプ (一覧だけ) は, リライトが要らない
  if (SITE_PRETTY_URLS && !$isRoot && $config['items']) {
    check(is_file("{$root}/{$entry}/.htaccess"), "{$label}: {$entry}/.htaccess がある (SITE_PRETTY_URLS)");
  }
  // 固定ページのリライトが効かなくなるので, ルートに置く投稿タイプの入口には .htaccess を置かない
  if ($isRoot) check(!is_file("{$root}/{$entry}/.htaccess"), "{$label}: {$entry}/.htaccess がない");
  if (!is_dir($dir)) {
    report('WARN', "{$label}: 原稿のディレクトリ {$config['dir']} がない");
    continue;
  }

  $files = glob("{$dir}/*.md") ?: [];
  $slugs = [];
  $problems = 0;
  foreach ($files as $file) {
    $name = basename($file);
    $post = $loader->read($file);
    if ($post === null) {
      report('NG', "{$label}/{$name}: frontmatter を読めない");
      $problems++;
      continue;
    }

    $required = $config['items'] ? ['title', 'slug'] : ['title', 'url'];
    // 日付の順に並べ, 日付を出す投稿タイプだけ date を求める (固定ページなどには要らない)
    if ($config['sort'] === 'date' && $config['showDate']) $required[] = 'date';
    $missing = array_filter($required, fn(string $key): bool => ($post[$key] ?? '') === '');
    if ($missing) {
      report('NG', "{$label}/{$name}: " . implode(', ', $missing) . ' がない');
      $problems++;
    }

    $date = $post['date'] ?? '';
    if ($date !== '' && !isValidDate($date)) {
      report('NG', "{$label}/{$name}: date \"{$date}\" は使えない (2026-10-09, または 2026-10 の形)");
      $problems++;
    }

    $slug = $post['slug'] ?? '';
    if ($slug !== '') {
      // page はページ送りの URL (/blog/page/2/) と重なる
      if (!preg_match('/^[A-Za-z0-9_-]+$/', $slug) || $slug === 'page') {
        report('NG', "{$label}/{$name}: slug \"{$slug}\" は使えない (英数字, ハイフン, アンダースコア. page は不可)");
        $problems++;
      }
      if (isset($slugs[$slug])) {
        report('NG', "{$label}/{$name}: slug \"{$slug}\" が {$slugs[$slug]} と重なっている");
        $problems++;
      }
      $slugs[$slug] = $name;
      // 実在するフォルダが優先されて, 原稿が黙って隠れる
      if ($isRoot && (is_dir("{$root}/{$sectionDir}{$slug}") || is_file("{$root}/{$sectionDir}{$slug}"))) {
        report('NG', "{$label}/{$name}: slug \"{$slug}\" は実在するフォルダと重なっている");
        $problems++;
      }
    }

    $image = $post['img'] ?? '';
    if ($image !== '' && !isExternal($image)) {
      if (!siteFileExists($image)) {
        report('WARN', "{$label}/{$name}: img {$image} がない");
      } elseif ($sampleImage === '' && str_contains($image, 'content/images/')) {
        $sampleImage = trim($image, '/');
      }
    }

    // 下書きは本番で 404 になるので, 公開中のサイトの確認に使わない
    if (!isset($samples["{$sectionName}/{$type}"]) && $config['items'] && !$missing && $slug !== '' && !ContentLoader::isDraft($post)) {
      $samples["{$sectionName}/{$type}"] = ['sectionDir' => $sectionDir, 'type' => $type, 'config' => $config, 'file' => $name, 'post' => $post];
    }
  }
  if (!$problems) report('OK', "{$label}: 原稿 " . count($files) . ' 件');
}
}

// ---- データファイル (content/ の JSON があるサイトだけ) ----
$jsonFiles = [];
if (is_dir("{$root}/content")) {
  foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$root}/content", FilesystemIterator::SKIP_DOTS)) as $file) {
    if (strtolower($file->getExtension()) === 'json') $jsonFiles[] = substr(str_replace('\\', '/', $file->getPathname()), strlen($root) + 1);
  }
}
if ($jsonFiles) {
  echo "データファイル\n";
  sort($jsonFiles);
  foreach ($jsonFiles as $file) {
    // 壊れていても表示は止まらず, 値が空になるだけなので, 手元では気付きにくい
    check(readJsonFile("{$root}/{$file}") !== null, "{$file} を読める");
  }
}

// ---- お問い合わせフォーム (contact/ のページがあるサイトだけ) ----
$hasContact = defined('SITE_CONTACT') && is_file($root . '/' . trim(SITE_CONTACT['path'], '/') . '/index.php');
if ($hasContact) {
  echo "お問い合わせフォーム\n";
  try {
    $env = loadEnv(SITE_CONTACT['env']);
    foreach (['ADMIN_EMAIL', 'SMTP_HOST', 'SMTP_USER', 'SMTP_PASS'] as $key) {
      check(($env[$key] ?? '') !== '', ".env に {$key} がある");
    }
    // .env.example をコピーしたまま書き換えていない
    check(($env['SMTP_HOST'] ?? '') !== 'smtp.example.com' && ($env['SMTP_PASS'] ?? '') !== '********', '.env の SMTP の設定が見本のままではない');
  } catch (RuntimeException $error) {
    report('NG', $error->getMessage());
  }
}

// ---- 公開中のサイト (HTTP) ----
$args = getopt('', ['http::']);
if (array_key_exists('http', $args)) {
  $base = is_string($args['http']) && $args['http'] !== '' ? rtrim($args['http'], '/') . '/' : SITE_URL . SITE_ROOT;
  echo "公開中のサイト ({$base})\n";

  // ステータスコード, Content-Type, 転送先, 本文 (転送はたどらない)
  $request = function (string $path) use ($base): array {
    $context = stream_context_create(['http' => [
      'method'          => 'GET',
      'ignore_errors'   => true,
      'follow_location' => 0,
      'timeout'         => 10,
      'header'          => "User-Agent: simple-cms-check\r\n",
    ]]);
    $body = @file_get_contents($base . $path, false, $context);
    $headers = function_exists('http_get_last_response_headers') ? (http_get_last_response_headers() ?? []) : ($http_response_header ?? []);
    $result = ['status' => 0, 'type' => '', 'location' => '', 'cache' => '', 'body' => $body === false ? '' : $body];
    foreach ($headers as $header) {
      if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $match)) $result['status'] = (int)$match[1];
      if (preg_match('#^Content-Type:\s*(.+)$#i', $header, $match)) $result['type'] = trim($match[1]);
      if (preg_match('#^Location:\s*(.+)$#i', $header, $match)) $result['location'] = trim($match[1]);
      if (preg_match('#^Cache-Control:\s*(.+)$#i', $header, $match)) $result['cache'] = trim($match[1]);
    }
    return $result;
  };
  $expect = function (string $path, int $expected, string $note) use ($request): void {
    $result = $request($path);
    $status = $result['status'] ?: '応答なし';
    check($result['status'] === $expected, "/{$path} が {$expected} ({$note}) → {$status}");
  };
  // 301 で, 転送先の末尾が $target になる
  $expectRedirect = function (string $path, string $target, string $note) use ($request): void {
    $result = $request($path);
    check(
      $result['status'] === 301 && str_ends_with($result['location'], $target),
      "/{$path} が /{$target} へ 301 ({$note}) → " . ($result['status'] ?: '応答なし') . ($result['location'] ? " {$result['location']}" : '')
    );
  };

  // 公開しないファイル
  $expect('templates/config.php', 403, '設定を公開しない');
  $expect('inc/helpers.php', 403, 'inc/ を公開しない');
  $expect('inc/.env', 403, '.env を公開しない');
  $expect('templates/site.php', 403, 'templates/ を公開しない');
  foreach ($samples as $sample) {
    $expect(trim($sample['config']['dir'], '/') . '/' . rawurlencode($sample['file']), 403, '原稿を公開しない');
    break;
  }
  if ($sampleImage !== '') $expect($sampleImage, 200, '原稿の画像を公開する');
  if ($jsonFiles) $expect($jsonFiles[0], 403, 'データファイルを公開しない');

  // リライト
  $sitemap = $request('sitemap.xml');
  check(
    $sitemap['status'] === 200 && str_contains($sitemap['type'], 'xml'),
    '/sitemap.xml が XML (inc/sitemap.php へのリライト) → ' . ($sitemap['status'] ?: '応答なし') . ($sitemap['type'] ? " {$sitemap['type']}" : '')
  );
  foreach ($sections as $name => $section) {
    $loc = '<loc>' . SITE_URL . rtrim($section['root'], '/') . '/</loc>';
    check(str_contains($sitemap['body'], $loc), "/sitemap.xml に {$name} のトップがある");
  }
  $robots = $request('robots.txt');
  check(
    $robots['status'] === 200 && str_contains($robots['body'], 'Sitemap: '),
    '/robots.txt に Sitemap の行がある (inc/robots.php へのリライト) → ' . ($robots['status'] ?: '応答なし')
  );

  // 記事の URL と転送
  foreach ($samples as $sample) {
    $slug = rawurlencode($sample['post']['slug']);
    $prefix = $sample['sectionDir'] . $sample['config']['path'];
    $entry = $sample['config']['path'] === '' ? $sample['sectionDir'] . $sample['type'] . '/' : $prefix;
    $expect("{$prefix}{$slug}/", 200, '記事の URL');
    $expectRedirect("{$prefix}{$slug}", "{$prefix}{$slug}/", '末尾のスラッシュ');
    $expectRedirect("{$entry}?slug={$slug}", "{$prefix}{$slug}/", 'きれいな URL へ');
    if ($sample['config']['path'] === '') $expectRedirect("{$entry}{$slug}/", "{$prefix}{$slug}/", '固定ページの入口から');
  }

  // 404 (Apache の既定の画面ではなく, このサイトの 404. noindex で 404 のページを, og:site_name で同じドメインの別のサイトと見分ける)
  $siteNameTag = '<meta property="og:site_name" content="' . e(SITE_NAME) . '">';
  foreach (['simple-cms-check-not-found/' => '固定ページのリライト', 'simple-cms-check/not/found/' => 'ErrorDocument'] as $path => $note) {
    $notFound = $request($path);
    check(
      $notFound['status'] === 404 && str_contains($notFound['body'], 'noindex') && str_contains($notFound['body'], $siteNameTag),
      "存在しない URL がサイトの 404 のページ ({$note}) → " . ($notFound['status'] ?: '応答なし')
    );
  }

  // ほかの区画の 404 (区画の .htaccess の ErrorDocument. サイト名は区画の設定にあり読めないので, noindex の 404 かだけを見る)
  foreach ($sections as $name => $section) {
    $dir = sectionDir($section['root']);
    if ($dir === '') continue;
    $notFound = $request("{$dir}simple-cms-check/not/found/");
    check(
      $notFound['status'] === 404 && str_contains($notFound['body'], 'noindex'),
      "{$name}: 存在しない URL が 404 のページ → " . ($notFound['status'] ?: '応答なし')
    );
  }

  if ($hasContact) $expect('api/send.php', 405, 'お問い合わせの送信は POST だけ');

  // ブラウザのキャッシュ. 付かなくても表示は壊れない (mod_headers がないサーバーなど) ので, [WARN] にする
  $expectCache = function (string $path, int $maxAge, string $note) use ($request): void {
    $cache = $request($path)['cache'];
    report(str_contains($cache, "max-age={$maxAge}") ? 'OK' : 'WARN', "/{$path} のキャッシュが {$maxAge} 秒 ({$note}) → " . ($cache ?: 'なし'));
  };
  $expectCache('style.css?v=1', 31536000, '?v= のある URL');
  $expectCache('style.css', 86400, '?v= のない CSS');
  if ($sampleImage !== '') $expectCache($sampleImage, 604800, '原稿の画像');

  // HTML に長いキャッシュが付くと, 原稿を置き換えてもすぐ反映されない
  $topCache = $request('')['cache'];
  check(!preg_match('/max-age=[1-9]/', $topCache), 'トップに長いキャッシュがない → ' . ($topCache ?: 'なし'));
}

$summary = $failures ? "問題が {$failures} 件あります" : '問題はありません';
echo "\n{$summary}" . ($warnings ? " (確認をおすすめするものが {$warnings} 件あります)" : '') . "\n";
exit($failures ? 1 : 0);
