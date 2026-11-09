<?php
/**
 * notFound.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 見つからないページ (本家の区画. 夜間飛行のテーマ. 工房は templates/studio/notFound.php)
 * 入口は inc/404.php (Apache の ErrorDocument) と, 投稿タイプの存在しない記事 (inc/ContentPage.php)
 * このファイルはサイトが持つもの. デザインに合わせて書き換えてよい
 */

require_once __DIR__ . '/navigation.php';

/**
 * $view:
 *   posts: 投稿タイプの全記事 (最新記事などに使う. inc/404.php から呼ばれたときは空)
 *   crumbs: パンくず
 *   backUrl, backLabel: 戻るリンク (トップへ戻るときは「サイト名 のトップへ」)
 */
function renderNotFound(array $view): void {
  $title = 'ページが見つかりません';
  $text = 'お探しのページは、削除されたか、URLが変更された可能性があります。';
  $backLabel = $view['backUrl'] === path() ? SITE_NAME . ' のトップへ' : $view['backLabel'];
  renderHeader(['title' => $title, 'noindex' => true]);
?>
    <main>
      <div class="flightHero is-sub">
<?php renderBreadcrumb($view['crumbs']); ?>
        <h1 class="flightHero__title">Page Not Found<span class="flightHero__mark" aria-hidden="true">✶</span></h1>
        <p class="flightHero__audience"><?= e($title) ?></p>
        <p class="flightHero__text"><?= e($text) ?></p>
      </div>

      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div class="section__content">
              <a class="button" href="<?= e($view['backUrl']) ?>"><?= e($backLabel) ?></a>
            </div>
          </div>
        </div>
      </section>
    </main>
<?php
  renderFooter();
}
