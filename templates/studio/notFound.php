<?php
/**
 * notFound.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 見つからないページ (ちいさなサイト工房の区画. 本家のものは templates/notFound.php)
 * 入口は small-site-studio/404.php (工房の .htaccess の ErrorDocument) と, 存在しない実績 (inc/ContentPage.php)
 * templates/ より 1 段深いので, リポジトリのルートは dirname(__DIR__, 2)
 */

require_once dirname(__DIR__, 2) . '/inc/bootstrap.php';
require_once sectionTemplate('navigation.php');

/**
 * $view: templates/notFound.php と同じ (posts, crumbs, backUrl, backLabel)
 * 工房では, 戻るリンクはいつもトップへ (実績の一覧はトップの実績のセクション)
 */
function renderNotFound(array $view): void {
  $title = 'ページが見つかりません';
  renderHeader(['title' => $title, 'noindex' => true]);
?>
    <main id="main" class="main">
<?php renderBreadcrumb($view['crumbs']); ?>
      <h1 class="main__title"><?= e($title) ?></h1>
      <p>お探しのページは、削除されたか、URLが変更された可能性があります。</p>
      <p><a class="studio__button is-main" href="<?= e(path()) ?>"><?= e(SITE_NAME) ?>のトップへ</a></p>
    </main>
<?php
  renderFooter();
}
