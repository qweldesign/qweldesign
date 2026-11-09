<?php
/**
 * postSingle.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * ちいさなサイト工房の実績の個別ページ (/small-site-studio/works/xxx/. 本家のものは templates/postSingle.php)
 * 業種・担当範囲・実際のサイト (frontmatter の category, role, website) と画面を出し, 相談へ案内する
 * 前後の記事は出さない (一覧はトップの実績のセクション)
 * templates/ より 1 段深いので, リポジトリのルートは dirname(__DIR__, 2)
 */

require_once dirname(__DIR__, 2) . '/inc/bootstrap.php';
require_once sectionTemplate('navigation.php');

/**
 * $view: templates/postSingle.php と同じ (type, config, posts, path, post, content, adjacent, crumbs, head)
 */
function renderPostSingle(array $view): void {
  $post = $view['post'];
  $title = $post['title'] ?? '';
  $website = $post['website'] ?? '';
  // そのサイト自体のページと紛らわしくないよう, 制作実績であることを title と description に書く
  $summary = rtrim($post['summary'] ?? '', '。');
  $role = str_replace(' / ', '・', $post['role'] ?? '');
  renderHeader([
    'title'       => $title . 'の制作実績',
    'description' => ($summary !== '' ? $summary . '。' : '') . 'ちいさなサイト工房 (QWEL.DESIGN) が' . ($role !== '' ? $role : '制作') . 'を担当しています。',
    'path'        => $view['path'],
    'image'       => $post['img'] ?? '',
    'imageAlt'    => $title . 'のサイト画面',
    'head'        => $view['head'],
  ]);
?>
    <main id="main" class="main">
<?php renderBreadcrumb($view['crumbs']); ?>
      <div>
        <article>
          <h1 class="main__title"><?= e($title) ?></h1>
          <dl class="workMeta">
<?php if (!empty($post['category'])) { ?>
            <div><dt>業種</dt><dd><?= e($post['category']) ?></dd></div>
<?php } ?>
<?php if (!empty($post['role'])) { ?>
            <div><dt>担当</dt><dd><?= e($post['role']) ?></dd></div>
<?php } ?>
<?php if ($website) { ?>
            <div><dt>サイト</dt><dd><a href="<?= e($website) ?>" target="_blank" rel="noopener"><?= e(preg_replace('#^https?://#', '', $website)) ?></a></dd></div>
<?php } ?>
          </dl>
<?php // サイトの画面 (画像がまだ置かれていなければ出さない) ?>
<?php if (!empty($post['img']) && siteFileExists($post['img'])) { ?>
          <figure class="workMeta__image">
            <img src="<?= e(path($post['img'])) ?>" alt="<?= e($title) ?>のサイト画面"<?= imageAttrs($post['img'], 'eager') ?>>
          </figure>
<?php } ?>
          <div class="entry">
<?= $view['content'] ?>
          </div>
          <div class="workCta">
            <p>このようなサイトを作りたい方は、お気軽にご相談ください。</p>
            <p class="workCta__links">
              <a class="studio__button is-main" href="<?= e(path()) ?>#contact">相談する</a>
              <a class="studio__button" href="<?= e(path()) ?>#works">ほかの実績を見る</a>
            </p>
          </div>
        </article>
      </div>
    </main>
<?php
  renderFooter();
}
