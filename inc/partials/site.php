<?php
/**
 * site.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 本家の区画 (SITE_LAYOUT 'site') のヘッダー・フッターを出力する
 * 中身 (サイト名, ナビ, SNS など) は区画の設定 (config.php) から作る. 読み込みは inc/bootstrap.php から
 */

require_once dirname(__DIR__) . '/helpers.php';

/**
 * <head> ~ ヘッダーまで出力
 * $page:
 *   title: ページのタイトル (<title> は「title | サイト名」. 省略するとサイト名だけ)
 *   full_title: <title> をそのまま指定する (トップページなど)
 *   description: description (規定で SITE_DESCRIPTION)
 *   path: このページのサイトのルートからのパス (canonical, og:url に使う)
 *   image: OGP 画像 (規定で SITE_OGP_IMAGE. サイト内の画像のファイルがまだなければ SITE_OGP_IMAGE にする)
 *   image_alt: OGP 画像の代替テキスト (規定で SITE_OGP_IMAGE_ALT. 記事の画像では記事のタイトル)
 *   type: og:type (規定で article)
 *   noindex: 検索結果に出さない (404 など)
 *   nav: ヘッダーのメニュー ([href => 表示名]. 規定で SITE_NAV. ページの中のセクションへのメニューを渡すと, 表示中のセクションに合わせて灯る)
 *   brand_tag: ロゴを囲む要素 (規定で p. トップは h1)
 *   footer_links: フッターの文字のリンク ([サイトのルートからのパス => 表示名]. 規定で SITE_FOOTER_LINKS)
 *   head: </head> の直前に出力する HTML (構造化データなど)
 */
function render_header(array $page = []): void {
  // フッターでも使う値 (footer_links) を覚えておく
  $GLOBALS['site_page'] = $page;
  $title = $page['full_title'] ?? (!empty($page['title']) ? $page['title'] . ' | ' . SITE_NAME : SITE_NAME);
  $description = ($page['description'] ?? '') ?: SITE_DESCRIPTION;
  $url = absolute_url($page['path'] ?? '');
  $og = og_image(($page['image'] ?? '') ?: SITE_OGP_IMAGE);
  $defaultAlt = defined('SITE_OGP_IMAGE_ALT') ? SITE_OGP_IMAGE_ALT : SITE_NAME;
  $imageAlt = $og['is_default'] ? $defaultAlt : (($page['image_alt'] ?? '') ?: ($page['title'] ?? $defaultAlt));
  ?>
<!DOCTYPE html>
<html lang="<?= e(SITE_LANG) ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--
      Built with QWEL Markdown Engine (Simple CMS)
      A lightweight, file-based CMS that renders Markdown as HTML.
      Created by QWEL.DESIGN - https://qwel.design
      Source: https://github.com/qweldesign/qwel-tools-md-engine (MIT License)
    -->
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>">
<?php if (!empty($page['noindex'])) { ?>
    <meta name="robots" content="noindex">
<?php } else { ?>
    <link rel="canonical" href="<?= e($url) ?>">
<?php } ?>
    <meta property="og:type" content="<?= e($page['type'] ?? 'article') ?>">
    <meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:url" content="<?= e($url) ?>">
<?php if (defined('SITE_OG_LOCALE') && SITE_OG_LOCALE) { ?>
    <meta property="og:locale" content="<?= e(SITE_OG_LOCALE) ?>">
<?php } ?>
    <meta property="og:image" content="<?= e($og['url']) ?>">
<?php if ($og['width'] && $og['height']) { ?>
    <meta property="og:image:width" content="<?= $og['width'] ?>">
    <meta property="og:image:height" content="<?= $og['height'] ?>">
<?php } ?>
    <meta property="og:image:alt" content="<?= e($imageAlt) ?>">
    <meta name="twitter:card" content="summary_large_image">
<?php if (SITE_FONTS_URL) { ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= e(SITE_FONTS_URL) ?>">
<?php } ?>
    <link rel="stylesheet" href="<?= path('style.css') ?>">
    <link rel="icon" href="/assets/favicon.ico" sizes="32x32">
    <link rel="icon" href="/assets/logo.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/assets/apple-touch-icon.png">
    <!-- 選んだ便 (昼・夜) をページをまたいで保つ: 描画の前に戻す (js/scene-switch.js) -->
    <script>try{const s=sessionStorage.getItem('qwel-flight-scene');if(s==='day'||s==='night')document.documentElement.dataset.scene=s}catch(e){}</script>
<?php if (SITE_GA_ID) { ?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e(SITE_GA_ID) ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', '<?= e(SITE_GA_ID) ?>');
    </script>
<?php } ?>
<?= $page['head'] ?? '' ?>
  </head>
  <body class="is-flight">
    <div data-scroll-sentinel></div>
    <canvas class="dotBand" data-dot-band aria-hidden="true"></canvas>
    <header id="header" class="header" data-active-header>
      <nav id="gNav" class="gNav">
<?php
  // ロゴ (昼の便・夜の便. 夜の便の source は js/scene-switch.js が切り替える)
  $brandTag = ($page['brand_tag'] ?? 'p') === 'h1' ? 'h1' : 'p';
  // メニュー: ページの中のセクションへのメニュー (nav) は href をそのまま使い, 表示中のセクションに合わせて灯す
  $isPageNav = isset($page['nav']);
  $nav = $page['nav'] ?? array_combine(array_map('path', array_keys(SITE_NAV)), array_values(SITE_NAV));
?>
        <<?= $brandTag ?> id="siteBrand" class="gNav__siteBrand" data-site-brand>
          <a href="<?= e(SITE_LOGO_LINK ?: path()) ?>">
            <picture>
              <source srcset="<?= path(SITE_LOGO_NIGHT) ?>" media="(prefers-color-scheme: dark)" data-night-source>
              <img src="<?= path(SITE_LOGO) ?>" alt="<?= e(SITE_NAME) ?>">
            </picture>
          </a>
        </<?= $brandTag ?>>
<?php if ($nav) { ?>
        <ul class="gNav__primaryMenu visible-lg" data-primary-menu>
<?php foreach ($nav as $href => $label) { ?>
          <li class="gNav__menuItem"<?= $isPageNav ? ' data-spy-nav' : '' ?>><a href="<?= e($href) ?>"><?= e($label) ?></a></li>
<?php } ?>
        </ul>
<?php } ?>
      </nav>
    </header>
<?php
}

// フッター ~ </html> まで出力 (文字のリンク / アイコン / 著作権表示 の3段. ちいさなサイト工房と同じ組み方)
function render_footer(): void {
  $links = $GLOBALS['site_page']['footer_links'] ?? SITE_FOOTER_LINKS;
  ?>
    <footer id="footer" class="footer">
<?php if ($links) { ?>
      <nav class="footer__nav" aria-label="サイト内のページ">
        <ul>
<?php foreach ($links as $linkPath => $label) { ?>
          <li><a href="<?= e(path($linkPath)) ?>"><?= e($label) ?></a></li>
<?php } ?>
        </ul>
      </nav>
<?php } ?>
<?php if (SITE_SNS) { ?>
      <div class="footer__inner">
<?php foreach (SITE_SNS as $sns) { ?>
<?php $external = (bool)preg_match('#^https?://#', $sns['url']); ?>
        <a class="footer__item" href="<?= e(path($sns['url'])) ?>"<?= $external ? ' target="_blank" rel="noopener"' : '' ?>>
<?php if (!empty($sns['icon'])) { ?>
          <svg class="icon is-<?= e($sns['icon']) ?> is-md" width="36" height="36" aria-hidden="true">
            <use href="<?= path('assets/icons.svg') ?>#<?= e($sns['icon']) ?>"></use>
          </svg>
<?php } ?>
          <span><?= e($sns['label']) ?></span>
        </a>
<?php } ?>
      </div>
<?php } ?>
      <small class="footer__copyright">&copy; <?= e(copyright_years()) ?> <?= e(SITE_OWNER) ?></small>
    </footer>
    <script src="<?= path('init.js') ?>" type="module"></script>
  </body>
</html>
<?php
}
