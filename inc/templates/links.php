<?php
/**
 * links.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 作品・ツールの一覧 (個別のページを作らない投稿タイプ. config.php で items: false)
 * 1作品 = 1つの Markdown. 一覧から各作品の url へ直接リンクする (中身は別のリポジトリから置く)
 *
 * 原稿 (content/{投稿タイプ}/*.md) の frontmatter:
 *   title: 名前 (欧文)
 *   summary: 説明 (和文)
 *   url: 作品の URL (サイトのルートからのパス, または http から始まる URL. 外部のサイトは別のタブで開く)
 *   order: 並び順 (小さい順)
 *   draft: "true" で一覧に出さない
 *
 * 使い方 (一覧を置くディレクトリの index.php):
 *   require_once dirname(__DIR__) . '/inc/templates/links.php';
 *   render_links('creative', ['audience' => '…', 'texts' => ['…']]);
 * ※ このディレクトリには投稿タイプ用の .htaccess (リライト) を置かない (作品のディレクトリを横取りしないように)
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/ContentLoader.php';

/**
 * $type: 投稿タイプ (config.php の SITE_POST_TYPES のキー)
 * $options:
 *   heading: 見出し (規定で投稿タイプの label)
 *   audience: 見出しの下の和文
 *   texts: 導入文 (段落ごとの配列. 規定で投稿タイプの lead)
 */
function render_links(string $type, array $options = []): void {
  $config = post_type($type);
  $items = (new ContentLoader(site_file($config['dir'])))->load();
  usort($items, fn($a, $b) => (int)($a['order'] ?? PHP_INT_MAX) <=> (int)($b['order'] ?? PHP_INT_MAX));

  $breadcrumb = new ContentNavigation([
    'parents'     => array_map(fn($crumb) => ['label' => $crumb['label'], 'path' => path($crumb['path'])], SITE_BREADCRUMB),
    'subdirLabel' => $config['label'],
    'subdirPath'  => type_url($type),
  ]);
  $texts = $options['texts'] ?? array_filter([$config['lead']]);

  render_header([
    'title'       => $config['label'],
    'description' => $config['lead'],
    'path'        => type_path($type),
  ]);
?>
    <main>
      <div class="flightHero is-sub">
        <?= $breadcrumb->breadcrumb() ?>

        <h1 class="flightHero__title"><?= e($options['heading'] ?? $config['label']) ?><span class="flightHero__mark" aria-hidden="true">✶</span></h1>
<?php if (!empty($options['audience'])) { ?>
        <p class="flightHero__audience"><?= e($options['audience']) ?></p>
<?php } ?>
<?php foreach ($texts as $text) { ?>
        <p class="flightHero__text"><?= e($text) ?></p>
<?php } ?>
      </div>

      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div class="section__content">
              <ul class="flightLinks">
<?php foreach ($items as $item) { ?>
<?php $external = (bool)preg_match('#^https?://#', $item['url'] ?? ''); ?>
                <li><a href="<?= e(path($item['url'] ?? '')) ?>"<?= $external ? ' target="_blank" rel="noopener"' : '' ?>><span class="flightLinks__name"><?= e($item['title'] ?? '') ?></span><span class="flightLinks__note"><?= e($item['summary'] ?? '') ?></span></a></li>
<?php } ?>
              </ul>
            </div>
          </div>
        </div>
      </section>
    </main>
<?php
  render_footer();
}
