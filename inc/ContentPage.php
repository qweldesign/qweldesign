<?php
/**
 * ContentPage.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 投稿タイプの URL を受けて, 必要なデータを集め, 画面のテンプレートを選ぶ
 * HTML はテンプレート (templates/) が持つ. 区画の地図に templates があれば, その区画のテンプレートを優先する (sectionTemplate())
 *
 * 使い方 (投稿タイプのディレクトリの index.php):
 *   require_once dirname(__DIR__) . '/inc/ContentPage.php';
 *   ContentPage::render('blog');
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/ContentLoader.php';
require_once __DIR__ . '/ContentNavigation.php';
require_once sectionTemplate('postList.php');
require_once sectionTemplate('postSingle.php');
require_once sectionTemplate('page.php');
require_once sectionTemplate('notFound.php');

class ContentPage {
  public static function render(string $type): void {
    $config = postType($type);
    $loader = new ContentLoader();
    $posts = $loader->load(siteFile($config['dir']), null, $config['sort']);

    if (isset($_GET['slug'])) {
      self::renderSingle($type, $config, $loader, $posts);
    } else {
      self::renderList($type, $config, $posts);
    }
  }

  // 一覧. ルートに置く投稿タイプ (固定ページ) に一覧はない
  private static function renderList(string $type, array $config, array $posts): void {
    $number = is_string($_GET['page'] ?? null) ? (int)$_GET['page'] : 1;
    $pagination = ContentNavigation::paginate($posts, $number, $config['count']);
    if ($config['path'] === '' || $pagination['outOfRange']) {
      self::renderMissing($type, $config, $posts);
      return;
    }

    $page = $pagination['page'];
    $crumbs = self::crumbs($config, $page > 1 ? ["{$page}ページ目"] : []);
    renderPostList([
      'type'       => $type,
      'config'     => $config,
      'posts'      => $posts,
      'path'       => pagePath($type, $page),
      'title'      => $page > 1 ? "{$config['label']} ({$page}ページ目)" : $config['label'],
      'pagination' => $pagination,
      'crumbs'     => $crumbs,
      'head'       => breadcrumbJsonLd($crumbs),
    ]);
  }

  // 個別記事と固定ページ. 配列などの文字列でない slug は, 見つからない扱いにする
  private static function renderSingle(string $type, array $config, ContentLoader $loader, array $posts): void {
    $slug = $_GET['slug'];
    $post = $config['items'] && is_string($slug) ? $loader->find($posts, $slug) : null;
    if ($post === null) {
      self::renderMissing($type, $config, $posts);
      return;
    }

    $crumbs = self::crumbs($config, [$post['title'] ?? '']);
    $view = [
      'type'    => $type,
      'config'  => $config,
      'posts'   => $posts,
      'path'    => postPath($type, $post),
      'post'    => $post,
      'content' => $loader->toHtml($post['content'], path()),
      'crumbs'  => $crumbs,
      'head'    => (ContentLoader::isDraft($post) ? "    <meta name=\"robots\" content=\"noindex\">\n" : '') . postJsonLd($type, $post) . breadcrumbJsonLd($crumbs),
    ];
    if ($config['layout'] === 'page') {
      renderPage($view);
    } else {
      renderPostSingle($view + ['adjacent' => ContentNavigation::adjacent($posts, $slug)]);
    }
  }

  private static function renderMissing(string $type, array $config, array $posts): void {
    http_response_code(404);
    $isRoot = $config['path'] === '';
    renderNotFound([
      'posts'     => $posts,
      'crumbs'    => self::crumbs($config, ['ページが見つかりません']),
      'backUrl'   => $isRoot ? path() : typeUrl($type),
      'backLabel' => $isRoot ? 'トップページへ' : "{$config['label']}の一覧へ",
    ]);
  }

  // パンくず. ルートに置く投稿タイプと breadcrumb が false の投稿タイプは, 投稿タイプの階層を出さない
  private static function crumbs(array $config, array $current): array {
    $items = SITE_BREADCRUMB;
    if ($config['path'] !== '' && $config['breadcrumb']) $items[] = ['label' => $config['label'], 'path' => $config['path']];
    foreach ($current as $label) $items[] = ['label' => $label];
    return ContentNavigation::breadcrumbs($items);
  }
}
