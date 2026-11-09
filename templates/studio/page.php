<?php
/**
 * page.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * ちいさなサイト工房の固定ページ (/small-site-studio/xxx/. 本家のものは templates/page.php)
 * 本文のあとに, 実績の個別ページと同じ形で相談へ案内する
 * templates/ より 1 段深いので, リポジトリのルートは dirname(__DIR__, 2)
 */

require_once dirname(__DIR__, 2) . '/inc/bootstrap.php';
require_once sectionTemplate('navigation.php');

/**
 * $view: templates/page.php と同じ (type, config, posts, path, post, content, crumbs, head)
 */
function renderPage(array $view): void {
  $post = $view['post'];
  renderHeader(['title' => $post['title'] ?? '', 'description' => $post['summary'] ?? '', 'path' => $view['path'], 'image' => $post['img'] ?? '', 'head' => $view['head']]);
?>
    <main id="main" class="main">
<?php renderBreadcrumb($view['crumbs']); ?>
      <h1 class="main__title"><?= e($post['title'] ?? '') ?></h1>
      <div class="entry">
<?= $view['content'] ?>
      </div>
      <div class="workCta">
        <p>ご不明な点は、お気軽にご相談ください。</p>
        <p class="workCta__links">
          <a class="studio__button is-main" href="<?= e(path()) ?>#contact">相談する</a>
          <a class="studio__button" href="<?= e(path()) ?>">トップへ戻る</a>
        </p>
      </div>
    </main>
<?php
  renderFooter();
}
