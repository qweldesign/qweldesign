<?php
/**
 * navigation.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * パンくず, ページ送り, 前後の記事の HTML
 * データは inc/ContentNavigation.php が作る
 * このファイルはサイトが持つもの. デザインに合わせて書き換えてよい
 */

// レイアウト (renderHeader, renderFooter) は区画ごとに選ぶ (区画の地図の templates にあれば, そちら)
require_once dirname(__DIR__) . '/inc/bootstrap.php';
require_once sectionTemplate('site.php');

// パンくず (階層が 2 つ未満なら出さない)
function renderBreadcrumb(array $crumbs): void {
  if (count($crumbs) < 2) return;
?>
      <nav aria-label="パンくずリスト">
        <ol class="breadcrumb">
<?php foreach ($crumbs as $crumb) { ?>
<?php if ($crumb['path'] === null) { ?>
          <li class="breadcrumb__item is-current"><span aria-current="page"><?= e($crumb['label']) ?></span></li>
<?php } else { ?>
          <li class="breadcrumb__item"><a href="<?= e(path($crumb['path'])) ?>"><?= e($crumb['label']) ?></a></li>
<?php } ?>
<?php } ?>
        </ol>
      </nav>
<?php
}

// ページ送り (1 ページだけなら出さない. $pageUrl: ページ番号から URL を作る関数)
function renderPagination(array $pagination, callable $pageUrl): void {
  if ($pagination['totalPages'] <= 1) return;
?>
      <nav aria-label="ページ送り">
        <ul class="pagination">
<?php for ($number = 1; $number <= $pagination['totalPages']; $number++) { ?>
<?php if ($number === $pagination['page']) { ?>
          <li class="pagination__item is-current"><span aria-current="page"><?= $number ?></span></li>
<?php } else { ?>
          <li class="pagination__item"><a href="<?= e($pageUrl($number)) ?>"><?= $number ?></a></li>
<?php } ?>
<?php } ?>
        </ul>
      </nav>
<?php
}

// 前後の記事 (どちらもなければ出さない. 左が前の記事, 右が次の記事. $postUrl: 記事から URL を作る関数)
function renderPostNav(array $adjacent, callable $postUrl): void {
  if (!$adjacent['prev'] && !$adjacent['next']) return;
?>
      <nav class="postNav" aria-label="前後の記事">
<?php if ($adjacent['prev']) { ?>
        <a class="postNav__link is-prev" href="<?= e($postUrl($adjacent['prev'])) ?>">
          <span class="postNav__label">前の記事</span>
          <span class="postNav__title"><?= e($adjacent['prev']['title'] ?? '') ?></span>
        </a>
<?php } ?>
<?php if ($adjacent['next']) { ?>
        <a class="postNav__link is-next" href="<?= e($postUrl($adjacent['next'])) ?>">
          <span class="postNav__label">次の記事</span>
          <span class="postNav__title"><?= e($adjacent['next']['title'] ?? '') ?></span>
        </a>
<?php } ?>
      </nav>
<?php
}
