<?php
/**
 * posts.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 投稿タイプの一覧と個別記事を出力する共通テンプレート
 * 一覧: config.php の path, 個別記事: ?slug=xxx (SITE_PRETTY_URLS なら xxx/)
 *
 * 使い方 (一覧を置くディレクトリの index.php):
 *   require_once __DIR__ . '/inc/templates/posts.php';
 *   render_posts('blog');
 */

require_once __DIR__ . '/postList.php';

/**
 * $type: 投稿タイプ (config.php の SITE_POST_TYPES のキー)
 * $options:
 *   intro: 一覧の1ページ目の見出しの代わりに出力する関数 (トップページの紹介文など)
 *   latest: 個別記事の横に並べる最新記事の件数 (規定で 4. 0 で出さない)
 *   adjacent: 個別記事の下に前後の記事へのリンクを出すか (規定で true)
 *   description: 一覧ページの description (規定で lead)
 *   meta: 個別記事の見出しの下に出力する関数 (引数は ContentEngine. frontmatter の独自の項目を出すときなど)
 *   after: 個別記事の本文の後に出力する関数 (引数は ContentEngine. 問い合わせへの案内など)
 *   head: 個別記事の <head> の値 (render_header の $page) を受け取り, 書き換えて返す関数 (引数は $page と ContentEngine)
 */
function render_posts(string $type, array $options = []): void {
  $config = post_type($type);
  $label  = $config['label'];
  $latest = $options['latest'] ?? 4;
  $cms = content_engine($type);

  // 記事が見つからない場合と, 一覧のページ番号が範囲外の場合は 404
  $notFound = $cms->is_not_found();
  if ($notFound) {
    http_response_code(404);
  }

  if ($cms->is_found()) {
    $page = [
      'title'       => $cms->get_title(),
      'description' => $cms->get_meta('summary'),
      'path'        => post_path($type, ['slug' => $cms->get_meta('slug')]),
      'image'       => $cms->get_meta('img'),
      'image_alt'   => $cms->get_title(),
    ];
    // title, description などを投稿タイプに合わせて書き換える
    if (isset($options['head'])) $page = $options['head']($page, $cms);
  } else {
    $pageNumber = $cms->get_page();
    $page = [
      'title'       => $notFound ? 'ページが見つかりません' : ($pageNumber > 1 ? "{$label} ({$pageNumber}ページ目)" : ($config['path'] === '' ? '' : $label)),
      'description' => $options['description'] ?? $config['lead'],
      'path'        => page_path($type, $pageNumber),
      'type'        => $config['path'] === '' && $pageNumber === 1 ? 'website' : 'article',
      'noindex'     => $notFound,
    ];
  }

  render_header($page);
?>
    <main id="main" class="main">
      <?= $cms->get_breadcrumb() ?>
<?php if ($cms->is_found()) { ?>
<?php if ($latest > 0) { ?>
      <div class="main__row">
        <article class="main__article">
<?php } else { ?>
      <div>
        <article>
<?php } ?>
          <h1 class="main__title"><?= e($cms->get_title()) ?></h1>
<?php if ($config['show_date'] && $cms->get_meta('date')) { ?>
          <p class="main__date"><?= date_tag($cms->get_meta('date')) ?></p>
<?php } ?>
<?php if (isset($options['meta'])) $options['meta']($cms); ?>
          <div class="entry">
<?= $cms->get_content() ?>
          </div>
<?php if (isset($options['after'])) $options['after']($cms); ?>
<?php if ($options['adjacent'] ?? true) { ?>
<?php $adjacent = $cms->get_adjacent(); ?>
          <nav class="postNav" aria-label="前後の記事">
<?php if ($adjacent['prev']) { ?>
            <a class="postNav__link is-prev" href="<?= e(post_url($type, $adjacent['prev'])) ?>">
              <span class="postNav__label">新しい記事</span>
              <span class="postNav__title"><?= e($adjacent['prev']['title'] ?? '') ?></span>
            </a>
<?php } ?>
<?php if ($adjacent['next']) { ?>
            <a class="postNav__link is-next" href="<?= e(post_url($type, $adjacent['next'])) ?>">
              <span class="postNav__label">前の記事</span>
              <span class="postNav__title"><?= e($adjacent['next']['title'] ?? '') ?></span>
            </a>
<?php } ?>
          </nav>
<?php } ?>
        </article>
<?php if ($latest > 0) { ?>
        <aside class="main__aside">
          <h2>最新記事</h2>
<?php render_post_index($cms->get_posts(1, $latest), $type); ?>
        </aside>
<?php } ?>
      </div>
<?php } elseif ($notFound) { ?>
      <h1 class="main__title">ページが見つかりません</h1>
      <p>お探しのページは、削除されたか、URLが変更された可能性があります。</p>
      <p><a href="<?= e(type_url($type)) ?>"><?= e($label) ?>の一覧へ</a></p>
<?php } else { ?>
<?php if (isset($options['intro']) && $cms->get_page() === 1) { ?>
<?php $options['intro'](); ?>
<?php } else { ?>
      <h1 class="main__title"><?= e($label) ?></h1>
<?php if ($config['lead']) { ?>
      <p class="main__lead"><?= e($config['lead']) ?></p>
<?php } ?>
<?php } ?>
<?php render_post_list($cms->get_posts(), $type); ?>
      <?= $cms->pagination() ?>
<?php } ?>
    </main>
<?php
  render_footer();
}
