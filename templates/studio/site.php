<?php
/**
 * site.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * ちいさなサイト工房の区画のヘッダー・フッターを出力する (本家は templates/site.php)
 * 中身 (サイト名, ロゴ, SNS など) は区画の設定 (templates/studio/config.php) から作る
 * templates/ より 1 段深いので, リポジトリのルートは dirname(__DIR__, 2)
 */

require_once dirname(__DIR__, 2) . '/inc/bootstrap.php';
bootstrapSection('studio');

/**
 * <head> ~ ヘッダーまで出力
 * $page:
 *   title: ページのタイトル (<title> は「title | サイト名」. 省略するとサイト名だけ)
 *   fullTitle: <title> をそのまま指定する (トップページなど)
 *   description: description (規定で SITE_DESCRIPTION)
 *   path: このページのサイトのルートからのパス (canonical, og:url に使う)
 *   image: OGP 画像 (規定で SITE_OGP_IMAGE. サイト内の画像のファイルがまだなければ SITE_OGP_IMAGE にする)
 *   imageAlt: OGP 画像の代替テキスト (規定で SITE_OGP_IMAGE_ALT. 記事の画像では記事のタイトル)
 *   type: og:type (規定で article)
 *   noindex: 検索結果に出さない (404 など)
 *   head: </head> の直前に出力する HTML (構造化データなど)
 */
function renderHeader(array $page = []): void {
  $title = $page['fullTitle'] ?? (!empty($page['title']) ? $page['title'] . ' | ' . SITE_NAME : SITE_NAME);
  $description = ($page['description'] ?? '') ?: SITE_DESCRIPTION;
  $url = absoluteUrl($page['path'] ?? '');
  $og = ogImage($page['image'] ?? '');
  $defaultAlt = defined('SITE_OGP_IMAGE_ALT') ? SITE_OGP_IMAGE_ALT : SITE_NAME;
  $imageAlt = $og['isDefault'] ? $defaultAlt : (($page['imageAlt'] ?? '') ?: ($page['title'] ?? $defaultAlt));
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
<?php $textFontUrls = textFontUrls(); ?>
<?php if ($textFontUrls) { ?>
<?php if (!SITE_FONTS_URL) { ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php } ?>
    <!-- 文字を絞った書体 (SITE_TEXT_FONTS). 小さいが, 表示を止めずに読む -->
<?php foreach ($textFontUrls as $textFontUrl) { ?>
    <link rel="preload" as="style" href="<?= e($textFontUrl) ?>" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="<?= e($textFontUrl) ?>"></noscript>
<?php } ?>
<?php } ?>
<?php if (SITE_FONTS_URL) { ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= e(SITE_FONTS_URL) ?>">
<?php } ?>
    <link rel="stylesheet" href="<?= e(assetUrl(SITE_STYLESHEET)) ?>">
    <link rel="icon" href="<?= e(assetUrl('assets/favicon.ico')) ?>" sizes="32x32">
    <link rel="icon" href="<?= e(assetUrl('assets/logo_static.svg')) ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= e(assetUrl('assets/apple-touch-icon.png')) ?>">
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
  <body>
    <header class="header">
      <p class="header__logo">
        <a href="<?= e(SITE_LOGO_LINK ?: path()) ?>">
<?php if (SITE_LOGO) { ?>
<?php
  // ロゴの横に添える文字 (SITE_LOGO_TEXT, SITE_LOGO_SUBTEXT. 文字があれば画像の alt は空にする)
  $logoText = defined('SITE_LOGO_TEXT') ? SITE_LOGO_TEXT : '';
  $logoSubtext = defined('SITE_LOGO_SUBTEXT') ? SITE_LOGO_SUBTEXT : '';
?>
          <img src="<?= e(assetUrl(SITE_LOGO)) ?>" alt="<?= $logoText ? '' : e(SITE_NAME) ?>">
<?php if ($logoText) { ?>
          <span class="header__logoText">
            <span class="header__logoName"><?= e($logoText) ?></span>
<?php if ($logoSubtext) { ?>
            <span class="header__logoSub"><?= e($logoSubtext) ?></span>
<?php } ?>
          </span>
<?php } ?>
<?php } else { ?>
          <?= e(SITE_NAME) ?>
<?php } ?>
        </a>
      </p>
<?php if (SITE_NAV) { ?>
      <nav class="header__nav" aria-label="グローバルナビゲーション">
        <ul>
<?php foreach (SITE_NAV as $navPath => $label) { ?>
          <li><a href="<?= e(path($navPath)) ?>"><?= e($label) ?></a></li>
<?php } ?>
        </ul>
      </nav>
<?php } ?>
    </header>
<?php
}

// フッター ~ </html> まで出力
function renderFooter(): void {
  ?>
    <footer id="footer" class="footer">
<?php if (SITE_FOOTER_LINKS) { ?>
      <nav class="footer__nav" aria-label="サイト内のページ">
        <ul>
<?php foreach (SITE_FOOTER_LINKS as $linkPath => $label) { ?>
          <li><a href="<?= e(path($linkPath)) ?>"><?= e($label) ?></a></li>
<?php } ?>
        </ul>
      </nav>
<?php } ?>
<?php if (SITE_SNS) { ?>
      <div class="footer__inner">
<?php foreach (SITE_SNS as $sns) { ?>
<?php $external = isExternal($sns['url']); ?>
        <a class="footer__item" href="<?= e(path($sns['url'])) ?>"<?= $external ? ' target="_blank" rel="noopener"' : '' ?>>
<?php if (!empty($sns['icon'])) { ?>
          <svg class="icon is-md" width="36" height="36" aria-hidden="true">
            <use href="<?= e(path('assets/icons.svg')) ?>#<?= e($sns['icon']) ?>"></use>
          </svg>
<?php } ?>
          <span><?= e($sns['label']) ?></span>
        </a>
<?php } ?>
      </div>
<?php } ?>
      <small class="footer__copyright">&copy; <?= e(copyrightYears()) ?> <?= e(SITE_OWNER) ?></small>
    </footer>
    <script src="<?= e(assetUrl('init.js')) ?>" type="module"></script>
  </body>
</html>
<?php
}
