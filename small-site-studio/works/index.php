<?php
/**
 * 実績 (個別のページ: /works/xxx/)
 * 原稿は content/works/*.md に置く. URL は works/.htaccess のリライトで振り分ける
 * 一覧は トップページの実績セクションなので, /works/ に来た場合はそこへ転送する
 */
require_once dirname(__DIR__, 2) . '/studio-config.php';
require_once dirname(__DIR__, 2) . '/inc/templates/posts.php';

if (!isset($_GET['slug'])) {
  header('Location: ' . path() . '#works', true, 301);
  exit;
}

render_posts('works', [
  'latest'   => 0,
  'adjacent' => false,
  // そのサイト自体のページと紛らわしくないよう, 制作実績であることを title と description に書く
  'head'     => function (array $page, ContentEngine $cms): array {
    $page['title'] = $cms->get_title() . 'の制作実績';
    $page['image_alt'] = $cms->get_title() . 'のサイト画面';
    $summary = rtrim($cms->get_meta('summary'), '。');
    $role = str_replace(' / ', '・', $cms->get_meta('role'));
    $page['description'] = ($summary !== '' ? $summary . '。' : '')
      . 'ちいさなサイト工房 (QWEL.DESIGN) が' . ($role !== '' ? $role : '制作') . 'を担当しています。';
    return $page;
  },
  // 業種・担当範囲・実際のサイトへのリンク (frontmatter の category, role, website)
  'meta'     => function (ContentEngine $cms): void {
    $website = $cms->get_meta('website');
?>
          <dl class="workMeta">
<?php if ($cms->get_meta('category')) { ?>
            <div><dt>業種</dt><dd><?= e($cms->get_meta('category')) ?></dd></div>
<?php } ?>
<?php if ($cms->get_meta('role')) { ?>
            <div><dt>担当</dt><dd><?= e($cms->get_meta('role')) ?></dd></div>
<?php } ?>
<?php if ($website) { ?>
            <div><dt>サイト</dt><dd><a href="<?= e($website) ?>" target="_blank" rel="noopener"><?= e(preg_replace('#^https?://#', '', $website)) ?></a></dd></div>
<?php } ?>
          </dl>
<?php // サイトの画面 (画像がまだ置かれていなければ出さない) ?>
<?php if ($cms->get_meta('img') && is_file(site_file($cms->get_meta('img')))) { ?>
          <figure class="workMeta__image">
            <img src="<?= e(image_url($cms->get_meta('img'))) ?>" alt="<?= e($cms->get_title()) ?>のサイト画面">
          </figure>
<?php } ?>
<?php
  },
  // 相談への案内
  'after'    => function (ContentEngine $cms): void {
?>
          <div class="workCta">
            <p>このようなサイトを作りたい方は、お気軽にご相談ください。</p>
            <p class="workCta__links">
              <a class="studio__button is-main" href="<?= e(path()) ?>#contact">相談する</a>
              <a class="studio__button" href="<?= e(path()) ?>#works">ほかの実績を見る</a>
            </p>
          </div>
<?php
  },
]);
