<?php
/**
 * notFound.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 404 (ページが見つかりません) のページ. 区画のレイアウト (SITE_LAYOUT) に合わせて組む
 * 入口は区画のルートの 404.php. Apache の ErrorDocument (.htaccess) から呼ばれる
 * ※ 投稿タイプの存在しない記事・範囲外のページ番号は, posts.php が自分で 404 を返す
 *
 * 使い方 (区画のルートの 404.php):
 *   require_once __DIR__ . '/inc/templates/notFound.php';
 *   render_not_found();
 * 工房の区画では, 先に studio-config.php を読む
 */

require_once dirname(__DIR__) . '/bootstrap.php';

function render_not_found(): void {
  // 404.php を直接開いた場合も 404 を返す
  http_response_code(404);
  $title = 'ページが見つかりません';
  $text  = 'お探しのページは、削除されたか、URLが変更された可能性があります。';
  $breadcrumb = new ContentNavigation([
    'parents'     => array_map(fn($crumb) => ['label' => $crumb['label'], 'path' => path($crumb['path'])], SITE_BREADCRUMB),
    'subdirLabel' => $title,
    'subdirPath'  => null,
  ]);

  render_header([
    'title'   => $title,
    'noindex' => true,
  ]);
?>
<?php if (SITE_LAYOUT === 'site') { ?>
    <main>
      <div class="flightHero is-sub">
        <?= $breadcrumb->breadcrumb() ?>

        <h1 class="flightHero__title">Page Not Found<span class="flightHero__mark" aria-hidden="true">✶</span></h1>
        <p class="flightHero__audience"><?= e($title) ?></p>
        <p class="flightHero__text"><?= e($text) ?></p>
      </div>

      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div class="section__content">
              <a class="button" href="<?= e(path()) ?>"><?= e(SITE_NAME) ?> のトップへ</a>
            </div>
          </div>
        </div>
      </section>
    </main>
<?php } else { ?>
    <main id="main" class="main">
      <?= $breadcrumb->breadcrumb() ?>

      <h1 class="main__title"><?= e($title) ?></h1>
      <p><?= e($text) ?></p>
      <p><a class="studio__button is-main" href="<?= e(path()) ?>"><?= e(SITE_NAME) ?>のトップへ</a></p>
    </main>
<?php } ?>
<?php
  render_footer();
}
