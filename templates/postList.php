<?php
/**
 * postList.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 投稿タイプの一覧のページ
 * このファイルはサイトが持つもの. デザインに合わせて書き換えてよい
 */

require_once __DIR__ . '/navigation.php';

/**
 * $view:
 *   type, config: 投稿タイプの名前と設定
 *   posts: 投稿タイプの全記事 (最新記事などに使う)
 *   path: このページのパス (canonical に使う)
 *   title: ページのタイトル
 *   pagination: ContentNavigation::paginate() の戻り値
 *   crumbs: パンくず
 *   head: 構造化データ (renderHeader() の head に渡す)
 */
function renderPostList(array $view): void {
  $config = $view['config'];
  $posts = $view['pagination']['posts'];
  // 導入文 (このサイトだけのキー texts. なければ lead)
  $texts = $config['texts'] ?? array_filter([$config['lead']]);
  renderHeader(['title' => $view['title'], 'description' => $config['lead'], 'path' => $view['path'], 'head' => $view['head']]);
?>
    <main>
      <div class="flightHero is-sub">
<?php renderBreadcrumb($view['crumbs']); ?>
        <h1 class="flightHero__title"><?= e($config['label']) ?><span class="flightHero__mark" aria-hidden="true">✶</span></h1>
<?php if (!empty($config['audience'])) { ?>
        <p class="flightHero__audience"><?= e($config['audience']) ?></p>
<?php } ?>
<?php foreach ($texts as $text) { ?>
        <p class="flightHero__text"><?= e($text) ?></p>
<?php } ?>
      </div>

      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div class="section__content">
<?php if (!$posts) { ?>
              <p><?= e($config['empty']) ?></p>
<?php } elseif (!$config['items']) { ?>
<?php renderPostLinks($posts, $view['type']); ?>
<?php } else { ?>
<?php renderPostItems($posts, $view['type']); ?>
<?php renderPagination($view['pagination'], fn(int $number): string => pageUrl($view['type'], $number)); ?>
<?php } ?>
            </div>
          </div>
        </div>
      </section>
    </main>
<?php
  renderFooter();
}

// 作品・ツールの並び (個別のページを持たない投稿タイプ). 名前 (欧文) と説明 (和文) を 1 行に並べ, 原稿の url へ直接リンクする
function renderPostLinks(array $posts, string $type): void {
?>
              <ul class="flightLinks">
<?php foreach ($posts as $post) { ?>
<?php $url = postUrl($type, $post); ?>
                <li><a href="<?= e($url) ?>"<?= isExternal($url) ? ' target="_blank" rel="noopener"' : '' ?>><span class="flightLinks__name"><?= e($post['title'] ?? '') ?></span><span class="flightLinks__note"><?= e($post['summary'] ?? '') ?></span></a></li>
<?php } ?>
              </ul>
<?php
}

// 記事の並び (一覧のページとトップページで使う). 画像は原稿の img, なければ SITE_THUMBNAIL_IMAGE
function renderPostItems(array $posts, string $type): void {
  $showDate = postType($type)['showDate'];
?>
      <ul class="postList is-list">
<?php foreach (array_values($posts) as $index => $post) { ?>
<?php $url = postUrl($type, $post); ?>
<?php $image = ($post['img'] ?? '') ?: SITE_THUMBNAIL_IMAGE; ?>
        <li class="postList__item postItem">
<?php if ($image) { ?>
          <figure class="postItem__image">
            <img src="<?= e(path($image)) ?>" alt=""<?= imageAttrs($image, $index === 0 ? 'high' : 'lazy') ?>>
          </figure>
<?php } ?>
          <div class="postItem__content">
<?php if ($showDate && !empty($post['date'])) { ?>
            <p class="postItem__info"><?= dateTag($post['date']) ?></p>
<?php } ?>
            <h2 class="postItem__heading"><a href="<?= e($url) ?>"<?= isExternal($url) ? ' target="_blank" rel="noopener"' : '' ?>><?= e($post['title'] ?? '') ?></a></h2>
<?php if (!empty($post['summary'])) { ?>
            <p class="postItem__excerpt"><?= e($post['summary']) ?></p>
<?php } ?>
          </div>
        </li>
<?php } ?>
      </ul>
<?php
}
