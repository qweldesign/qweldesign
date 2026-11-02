<?php
/**
 * postList.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 記事一覧 (.postList.is-list)
 * アイキャッチがない記事は, SITE_THUMBNAIL_IMAGE (config.php) を代わりに表示する
 */

require_once dirname(__DIR__) . '/bootstrap.php';

/**
 * $posts: ContentEngine::get_posts() の戻り値
 * $type: 投稿タイプ (記事の URL に使う)
 * $showDate: 日付を表示するか (規定で投稿タイプの show_date)
 */
function render_post_list(array $posts, string $type, ?bool $showDate = null): void {
  $showDate ??= post_type($type)['show_date'];
?>
        <ul class="postList is-list">
<?php foreach ($posts as $post) { ?>
<?php $url = post_url($type, $post); ?>
          <li class="postList__item postItem">
            <figure class="postItem__image">
              <a href="<?= e($url) ?>" tabindex="-1">
                <img src="<?= e(image_url($post['img'] ?? null)) ?>" alt="" loading="lazy">
              </a>
            </figure>
            <div class="postItem__content">
<?php if ($showDate && !empty($post['date'])) { ?>
              <div class="postItem__info">
                <?= date_tag($post['date'], 'postItem__date') ?>
              </div>
<?php } ?>
              <h2 class="postItem__heading">
                <a href="<?= e($url) ?>"><?= e($post['title'] ?? '') ?></a>
              </h2>
<?php if (!empty($post['summary'])) { ?>
              <p class="postItem__excerpt"><?= e($post['summary']) ?></p>
<?php } ?>
              <a class="postItem__more" href="<?= e($url) ?>" aria-hidden="true" tabindex="-1">view more &gt;</a>
            </div>
          </li>
<?php } ?>
        </ul>
<?php
}

// 記事の小さな一覧 (個別記事の横に並べる「最新記事」など. .postIndex)
function render_post_index(array $posts, string $type): void {
?>
        <ul class="postIndex">
<?php foreach ($posts as $post) { ?>
          <li class="postIndex__item">
            <a href="<?= e(post_url($type, $post)) ?>">
              <img class="postIndex__image" src="<?= e(image_url($post['img'] ?? null)) ?>" alt="" loading="lazy">
<?php if (post_type($type)['show_date'] && !empty($post['date'])) { ?>
              <?= date_tag($post['date'], 'postIndex__date') ?>
<?php } ?>
              <span class="postIndex__title"><?= e($post['title'] ?? '') ?></span>
            </a>
          </li>
<?php } ?>
        </ul>
<?php
}
