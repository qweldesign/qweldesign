<?php
/**
 * チラシ (A4 両面) の原稿
 * ちいさなサイト工房 (/small-site-studio/) のチラシ. 工房の区画の設定 (templates/studio/config.php) を読む
 * 金額・返事の日数・制作期間は content/small-site-studio/options.json (siteOption() で読む), 実績は投稿タイプ works (content/works/*.md) から入る
 *
 * 画面で確認するページと, 書き出し (tools/flyer/export.js) を分けている
 *   画面: tools/flyer/ (入稿用. 塗り足し 3mm を含む 216 × 303 mm. 仕上がりの線を点線で示す), tools/flyer/?bleed=0 (家庭用. A4 ちょうど)
 *   書き出し: cd tools/flyer && npm install && node export.js [dpi]
 *     → tools/flyer/out/ に PNG (面ごと) と PDF (寸法を正確に合わせ, 仕上がりの範囲 TrimBox も入れる) を出力する
 *   ?print を付けると, 書き出し用の表示になる (案内文・仕上がりの線・影を出さず, 面を隙間なく並べる)
 *   ブラウザの「印刷」でも PDF にできるが, Chrome は用紙の幅を 0.96pt 刻みに丸めるため, 入稿には export.js を使う
 * 塗り足しに伸ばすのは, 端まで色のある帯 (表のファーストビュー, 表裏の下端の帯, 裏の上端の線) だけ
 * 文字や QR コードは, 仕上がりの線から 5mm 以上内側に置く
 *
 * QR コード (tools/flyer/qr.svg) の行き先は https://qwel.design/small-site-studio/ (URL を変えたら作り直す)
 */
require_once dirname(__DIR__, 2) . '/inc/bootstrap.php';
bootstrapSection('studio');
require_once dirname(__DIR__, 2) . '/inc/ContentLoader.php';

// 金額の表示
function flyerYen(int $amount): string {
  return number_format($amount);
}

// 税込の金額 (1円未満は切り捨て)
function flyerTaxIncluded(int $amount): string {
  return number_format((int)floor($amount * (1 + siteOption('tax_rate'))));
}

// サイトのルートからのパスを, このページからの相対パスにする (SITE_ROOT に関係なく画像が出るように)
function flyerSrc(string $path): string {
  return '../../' . ltrim($path, '/');
}

$worksConfig = postType('works');
$works = array_slice((new ContentLoader())->load(siteFile($worksConfig['dir']), null, $worksConfig['sort']), 0, 4);
$build = siteOption('price_build');
$care  = siteOption('price_care');
$url   = absoluteUrl('');
$qr    = file_get_contents(__DIR__ . '/qr.svg');

// 塗り足し (mm). ?bleed=0 で A4 ちょうどの版にする
$bleed = (isset($_GET['bleed']) && $_GET['bleed'] === '0') ? 0 : 3;
$sheetW = 210 + $bleed * 2;
$sheetH = 297 + $bleed * 2;

// 書き出し用の表示 (?print)
$isPrint = isset($_GET['print']);
?>
<!DOCTYPE html>
<html lang="<?= e(SITE_LANG) ?>"<?= $isPrint ? ' class="is-print"' : '' ?>>
<head>
<meta charset="utf-8">
<meta name="robots" content="noindex, nofollow">
<title>チラシ (<?= $bleed ? "入稿用 {$sheetW} × {$sheetH} mm" : 'A4' ?>) | <?= e(SITE_NAME) ?></title>
<?php if (SITE_FONTS_URL) { ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?= e(SITE_FONTS_URL) ?>">
<?php } ?>
<link rel="stylesheet" href="../../<?= e(SITE_STYLESHEET) ?>">
<style>
  /* 印刷の設定 (余白なし. 1枚目が表, 2枚目が裏) */
  @page {
    size: <?= $sheetW ?>mm <?= $sheetH ?>mm;
    margin: 0;
  }

  :root {
    /* 塗り足しと, 仕上がりからの余白 (左右・下) */
    --bleed: <?= $bleed ?>mm;
    --pad-x: calc(15mm + var(--bleed));
    --pad-bottom: calc(12mm + var(--bleed));
    --blue: var(--color--primary);
    --blue-dark: var(--color--primary--dark);
    --blue-light: var(--color--primary--light);
    --orange: var(--color--secondary);
    --orange-dark: var(--color--secondary--dark);
    --orange-light: var(--color--secondary--light);
    --ink: var(--color--darkest);
    --ink-dim: var(--color--darker);
    --mincho: "Shippori Mincho", "Yu Mincho", serif;
    --mono: "IBM Plex Mono", ui-monospace, monospace;
  }

  html, body {
    margin: 0;
    background: #e5e5e5;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  .sheet {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 4mm;
    width: <?= $sheetW ?>mm;
    height: <?= $sheetH ?>mm;
    margin: 10mm auto;
    padding: var(--bleed) var(--pad-x) var(--pad-bottom);
    box-sizing: border-box;
    overflow: hidden;
    background: #fff;
    color: var(--ink);
    font-family: "Noto Sans JP", "Yu Gothic", sans-serif;
    font-size: 10pt;
    line-height: 1.65;
    box-shadow: 0 2mm 6mm rgba(0, 0, 0, 0.15);
  }

  /* 裏はロゴの帯を置かず, 上端をオレンジの線だけにする */
  .sheet.is-back {
    gap: 4.5mm;
    padding-top: 6mm;
    border-top: calc(3mm + var(--bleed)) solid var(--orange);
  }

  /* 仕上がりの線 (画面だけ. 塗り足しがあるときに, 断裁される位置を点線で示す) */
  .trim {
    position: absolute;
    inset: var(--bleed);
    z-index: 10;
    border: 0.3mm dashed #e0245e;
    pointer-events: none;
  }

  /* 裏は上端の線 (border) の分, 内側から数えて 1.2mm 上が仕上がりの線 */
  .sheet.is-back .trim {
    top: -1.2mm;
  }

  .guide {
    width: <?= $sheetW ?>mm;
    margin: 10mm auto -6mm;
    color: #555;
    font: 9pt/1.5 "Noto Sans JP", sans-serif;
  }

  /* 印刷と書き出し (?print) では, 案内文・仕上がりの線・影を出さず, 面を隙間なく並べる */
  @media print {
    html, body {
      background: #fff;
    }

    .sheet {
      margin: 0;
      box-shadow: none;
      break-after: page;
    }

    .trim,
    .guide {
      display: none;
    }

    .sheet:last-child {
      break-after: auto;
    }
  }

  .is-print,
  .is-print body {
    background: #fff;
  }

  .is-print .sheet {
    margin: 0;
    box-shadow: none;
    break-after: page;
  }

  .is-print .sheet:last-child {
    break-after: auto;
  }

  .is-print .trim,
  .is-print .guide {
    display: none;
  }

  /* style.css の要素の余白 (li の margin など) を打ち消す */
  .sheet p,
  .sheet h1,
  .sheet h2,
  .sheet ul,
  .sheet ol,
  .sheet li,
  .sheet figure {
    margin: 0;
  }

  .sheet li + li {
    margin-top: 0.8mm;
  }

  /* 上端: ロゴとサービス名 (LP のヘッダーと同じ) */
  .brand {
    display: flex;
    align-items: center;
    gap: 3mm;
    margin: 0 calc(-1 * var(--pad-x));
    padding: 5mm var(--pad-x) 3mm;
    border-bottom: 1.2mm solid var(--orange);
  }

  .brand img {
    width: 15mm;
  }

  .brand__name {
    font-family: var(--mincho);
    font-size: 15pt;
    font-weight: 700;
    line-height: 1.35;
  }

  .brand__sub {
    color: var(--ink-dim);
    font-family: var(--mono);
    font-size: 7.5pt;
    letter-spacing: 0.12em;
  }

  /* 見出し */
  .label {
    color: var(--blue-dark);
    font-family: var(--mono);
    font-size: 9pt;
    line-height: 1.2;
    letter-spacing: 0.16em;
    text-transform: uppercase;
  }

  .label::before {
    content: "✦ ";
    color: var(--orange);
  }

  .heading {
    font-family: var(--mincho);
    font-size: 14pt;
    font-weight: 700;
    line-height: 1.4;
  }

  .note {
    color: var(--ink-dim);
    font-size: 8pt;
  }

  .mark {
    background: linear-gradient(transparent 60%, var(--orange) 60%);
  }

  /* 表: ファーストビュー */
  .hero {
    display: flex;
    flex-direction: column;
    gap: 3mm;
    margin: 0 calc(-1 * var(--pad-x));
    padding: 6mm var(--pad-x) 1.5mm;
    background: linear-gradient(to bottom, var(--blue-light), var(--blue-light) 75%, #fff);
  }

  .hero__title {
    font-family: var(--mincho);
    font-size: 27pt;
    font-weight: 700;
    line-height: 1.65;
  }

  .hero__title span {
    display: block;
    font-size: 18pt;
  }

  .hero__tagline {
    font-family: var(--mincho);
    font-size: 15pt;
    font-weight: 700;
  }

  .hero__body {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 63mm;
    gap: 5mm;
    align-items: start;
    margin-block-start: 3mm;
  }

  .shot {
    display: block;
    width: 100%;
    aspect-ratio: 16 / 10;
    object-fit: cover;
    object-position: top;
    border: 0.3mm solid rgba(0, 0, 0, 0.3);
    border-radius: 3mm;
    background: var(--blue-light);
    box-sizing: border-box;
  }

  .chips {
    display: flex;
    flex-direction: column;
    gap: 3mm;
  }

  .chip {
    padding: 3mm 6mm;
    border: 0.6mm solid var(--blue);
    border-radius: 3mm;
    background: #fff;
  }

  .chip.is-care {
    border-color: var(--orange);
  }

  .tag {
    display: inline-block;
    padding: 0.3mm 2.4mm;
    border-radius: 3mm;
    background: var(--blue-dark);
    color: #fff;
    font-family: var(--mono);
    font-size: 7.5pt;
    letter-spacing: 0.12em;
  }

  .tag.is-care {
    background: var(--orange);
    color: var(--ink);
  }

  .chip__label {
    margin-top: 1mm !important;
    color: var(--ink-dim);
    font-size: 9pt;
  }

  .chip__price {
    font-size: 18pt;
    font-weight: 700;
    line-height: 1.35;
  }

  .chip__price small {
    font-size: 9pt;
  }

  /* 表: 向いている方 */
  .fit {
    display: grid;
    grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr);
    gap: 4mm;
  }

  .card {
    padding: 3mm 4.5mm;
    border: 0.3mm solid var(--color--lighter);
    border-radius: 3mm;
    background: #fff;
  }

  .card.is-build {
    border: 0.6mm solid var(--blue);
  }

  .card.is-care {
    border: 0.6mm solid var(--orange);
  }

  .card.is-muted {
    border-color: var(--color--lighter);
    color: var(--ink-dim);
    font-size: 9pt;
  }

  .card__title {
    margin-bottom: 1.5mm !important;
    font-weight: 700;
  }

  .list {
    padding: 0;
    list-style: none;
  }

  .list li {
    position: relative;
    padding-left: 4.5mm;
  }

  .list li::before {
    position: absolute;
    left: 0;
    font-weight: 700;
  }

  .list.is-check li::before {
    content: "○";
    color: var(--blue-dark);
  }

  .list.is-dash li::before {
    content: "－";
  }

  .list.is-dot li::before {
    content: "●";
    color: var(--orange-dark);
    font-size: 7.5pt;
    top: 0.6mm;
  }

  /* 表: 理由 (3つを横に) */
  .reasons {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 3mm;
    padding: 0;
    list-style: none;
    counter-reset: reason;
  }

  .reasons li {
    padding: 3mm 4.5mm;
    border: 0.4mm solid var(--orange);
    border-radius: 3mm;
    counter-increment: reason;
    font-size: 8.25pt;
  }

  .reasons li::before {
    content: counter(reason, decimal-leading-zero);
    display: block;
    color: var(--orange-dark);
    font-family: var(--mono);
    font-size: 12pt;
    line-height: 1.35;
  }

  .reasons b {
    display: block;
    font-size: 12pt;
  }

  /* 下端: QR コードと連絡先 (表裏共通) */
  .contact {
    display: flex;
    align-items: center;
    gap: 6mm;
    margin: auto calc(-1 * var(--pad-x)) calc(-1 * var(--pad-bottom));
    padding: 5mm var(--pad-x) calc(6mm + var(--bleed));
    background: var(--blue-dark);
    color: #fff;
  }

  .contact__qr {
    flex: 0 0 auto;
    width: 27mm;
    height: 27mm;
    padding: 2mm;
    border-radius: 2mm;
    background: #fff;
    box-sizing: border-box;
  }

  .contact__qr svg {
    display: block;
    width: 100%;
    height: 100%;
  }

  .contact__lead {
    font-family: var(--mincho);
    font-size: 15pt;
    font-weight: 700;
    line-height: 1.5;
  }

  .contact__text {
    font-size: 9pt;
  }

  .contact__url {
    margin-top: 1mm !important;
    color: var(--orange);
    font-family: var(--mono);
    font-size: 9pt;
    letter-spacing: 0.04em;
  }

  /* 裏: 実績 (2 × 2) */
  .works {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 3mm 5mm;
    padding: 0;
    list-style: none;
  }

  .works .shot {
    aspect-ratio: 16 / 6;
  }

  .works__name {
    margin-top: 1.5mm !important;
    font-size: 9pt;
    font-weight: 700;
    line-height: 1.5;
  }

  .works__kind {
    color: var(--ink-dim);
    font-size: 7.5pt;
    line-height: 1.5;
  }

  .works__url {
    color: var(--blue-dark);
    font-family: var(--mono);
    font-size: 7.5pt;
  }

  /* 裏: 料金 */
  .prices {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 4mm;
  }

  .price {
    font-size: 18pt;
    font-weight: 700;
    line-height: 1.35;
  }

  .price small {
    font-size: 9pt;
  }

  .included {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.5mm 2mm;
    margin-top: 1.5mm !important;
    padding: 0;
    list-style: none;
    font-size: 9pt;
  }

  .included li + li {
    margin-top: 0;
  }

  .included li::before {
    content: "✓ ";
    color: var(--blue-dark);
    font-weight: 700;
  }

  .promise {
    padding: 3mm 4.5mm;
    border-radius: 3mm;
    background: var(--orange-light);
    font-size: 9pt;
  }

  /* 裏: 流れ (4つを横に) */
  .flow {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 3mm;
    padding: 0;
    list-style: none;
  }

  .flow li {
    padding-top: 1.5mm;
    border-top: 0.6mm dotted var(--orange-dark);
    font-size: 9pt;
    line-height: 1.5;
  }

  .step {
    display: block;
    color: var(--blue-dark);
    font-family: var(--mono);
    font-size: 7.5pt;
    letter-spacing: 0.12em;
  }

  .flow b {
    display: block;
  }

  .flow small {
    display: block;
    margin-top: 0.5mm;
    color: var(--ink-dim);
    font-size: 7.5pt;
  }

  /* 裏: 作る人 (QR の帯の中に) */
  .pilot {
    display: flex;
    align-items: center;
    gap: 3mm;
    margin-left: auto;
    max-width: 70mm;
    font-size: 9pt;
    line-height: 1.65;
  }

  .pilot img {
    flex: 0 0 auto;
    width: 18mm;
    height: 18mm;
    border: 0.5mm solid var(--orange);
    border-radius: 50%;
    object-fit: cover;
  }

  .pilot b {
    font-family: var(--mincho);
    font-size: 12pt;
  }
</style>
</head>
<body data-sheet-width="<?= $sheetW ?>" data-sheet-height="<?= $sheetH ?>" data-bleed="<?= $bleed ?>">
<?php if ($bleed) { ?>
  <p class="guide">入稿用 (塗り足し <?= $bleed ?>mm を含む <?= $sheetW ?> × <?= $sheetH ?> mm). 赤い点線が仕上がりの線. 家庭のプリンター用は <a href="?bleed=0">A4 の版</a>. 書き出しは tools/flyer/export.js で</p>
<?php } else { ?>
  <p class="guide">家庭・コンビニのプリンター用 (A4). 印刷所に入稿するときは <a href="./">塗り足しつきの版</a>. 書き出しは tools/flyer/export.js で</p>
<?php } ?>

  <!-- 表 -->
  <section class="sheet" aria-label="表">
<?php if ($bleed) { ?>
    <div class="trim" aria-hidden="true"></div>
<?php } ?>
    <header class="brand">
      <img src="../../<?= e(SITE_LOGO) ?>" alt="">
      <div>
        <p class="brand__name"><?= e(SITE_LOGO_TEXT) ?></p>
        <p class="brand__sub"><?= e(SITE_LOGO_SUBTEXT) ?></p>
      </div>
    </header>

    <div class="hero">
      <p class="label">Small Site Studio</p>
      <h1 class="hero__title">更新を頼める、壊れにくい。<span>小さな事業のためのウェブサイト</span></h1>
      <p class="hero__tagline"><span class="mark"><?= e(siteOption('tagline')) ?></span></p>
      <div class="hero__body">
<?php if ($works && !empty($works[0]['img'])) { ?>
        <img class="shot" src="<?= e(flyerSrc($works[0]['img'])) ?>" alt="<?= e($works[0]['title'] ?? '') ?>のサイト画面">
<?php } ?>
        <div class="chips">
          <div class="chip">
            <span class="tag">つくる</span>
            <p class="chip__label">制作費</p>
            <p class="chip__price"><?= flyerYen($build) ?><small>円〜（税別）</small></p>
            <p class="note">税込 <?= flyerTaxIncluded($build) ?>円〜</p>
          </div>
          <div class="chip is-care">
            <span class="tag is-care">まもる</span>
            <p class="chip__label">ケア 月額</p>
            <p class="chip__price"><?= flyerYen($care) ?><small>円〜（税別）</small></p>
            <p class="note">税込 <?= flyerTaxIncluded($care) ?>円〜</p>
          </div>
          <p class="note">※ <?= e(siteOption('price_as_of')) ?>現在の料金です。</p>
        </div>
      </div>
    </div>

    <div>
      <p class="label">For You</p>
      <h2 class="heading">こんな方に向いています</h2>
    </div>
    <div class="fit">
      <div class="card is-build">
        <ul class="list is-check">
          <li>自分では更新しないので、<span class="mark">更新ごと頼みたい</span></li>
          <li>今のサイトが古い、更新が止まっている</li>
          <li>作った業者と連絡がつかない、遠くて相談しづらい</li>
        </ul>
      </div>
      <div class="card is-muted">
        <p class="card__title">向いていないかもしれない方</p>
        <ul class="list is-dash">
          <li>自分で頻繁に更新したい</li>
          <li>通販や予約の仕組みが必要</li>
        </ul>
      </div>
    </div>

    <div>
      <p class="label">Why It Lasts</p>
      <h2 class="heading">軽くて、壊れにくい理由</h2>
    </div>
    <ol class="reasons">
      <li><b>面倒な更新作業がない</b>管理画面もデータベースも持たないので、止まる心配がありません。</li>
      <li><b>表示が軽い</b>電波の弱い海辺でも、すぐに開きます。</li>
      <li><b>原稿を渡すだけ</b>写真と文章を送ってもらえば、こちらで掲載します。</li>
    </ol>

    <footer class="contact">
      <div class="contact__qr" role="img" aria-label="ウェブサイトの QR コード"><?= $qr ?></div>
      <div>
        <p class="contact__lead">実績と料金の詳しい内容、<br>ご相談はウェブサイトから</p>
        <p class="contact__text">ご相談は無料です。<?= (int)siteOption('reply_days') ?>営業日以内にお返事します。</p>
        <p class="contact__url"><?= e(preg_replace('#^https?://#', '', $url)) ?></p>
      </div>
    </footer>
  </section>

  <!-- 裏 -->
  <section class="sheet is-back" aria-label="裏">
<?php if ($bleed) { ?>
    <div class="trim" aria-hidden="true"></div>
<?php } ?>
    <div>
      <p class="label">Works</p>
      <h2 class="heading">いま動いている、越前海岸のサイト</h2>
      <p class="note">見本ではなく、実際に使われているサイトです。どれも作るだけでなく、運営にも関わっています。</p>
    </div>
    <ul class="works">
<?php foreach ($works as $work) { ?>
      <li>
<?php if (!empty($work['img'])) { ?>
        <img class="shot" src="<?= e(flyerSrc($work['img'])) ?>" alt="<?= e($work['title'] ?? '') ?>のサイト画面">
<?php } ?>
        <p class="works__name"><?= e($work['title'] ?? '') ?></p>
        <p class="works__kind"><?= e($work['category'] ?? '') ?> ／ <?= e($work['role'] ?? '') ?></p>
<?php if (!empty($work['website'])) { ?>
        <p class="works__url"><?= e(preg_replace('#^https?://#', '', $work['website'])) ?></p>
<?php } ?>
      </li>
<?php } ?>
    </ul>

    <div>
      <p class="label">Price</p>
      <h2 class="heading">料金</h2>
    </div>
    <div class="prices">
      <div class="card is-build">
        <span class="tag">つくる</span>
        <p class="chip__label">制作費</p>
        <p class="price"><?= flyerYen($build) ?><small>円〜（税別） 税込 <?= flyerTaxIncluded($build) ?>円〜</small></p>
        <ul class="included">
          <li>トップページ</li>
          <li>事業の紹介</li>
          <li>料金・メニュー</li>
          <li>お知らせ</li>
          <li>アクセス</li>
          <li>問い合わせ</li>
          <li>スマホ対応</li>
          <li>SNS・地図の案内</li>
        </ul>
      </div>
      <div class="card is-care">
        <span class="tag is-care">まもる</span>
        <p class="chip__label">ケア 月額</p>
        <p class="price"><?= flyerYen($care) ?><small>円〜（税別） 税込 <?= flyerTaxIncluded($care) ?>円〜</small></p>
        <ul class="list is-dot">
          <li>お知らせの掲載・小さな直し：月 <?= (int)siteOption('care_updates') ?> 回</li>
          <li>サーバーとドメインの見守り</li>
          <li>ご連絡への返事：<?= (int)siteOption('reply_days') ?>営業日以内</li>
        </ul>
      </div>
    </div>
    <p class="note">※ <?= e(siteOption('price_as_of')) ?>現在の料金です。最新の料金はウェブサイトでご確認ください。</p>
    <!--<div class="promise">
      <b>ドメインとサーバーは、お店の名義で。</b>ケアを解約しても、サイトはそのまま残ります。
    </div>-->

    <div>
      <p class="label">Flight Plan</p>
      <h2 class="heading">ご依頼の流れ</h2>
    </div>
    <ol class="flow">
      <li><span class="step">STEP 1</span><b>相談（無料）</b></li>
      <li><span class="step">STEP 2</span><b>お話と素材のお預かり</b><small>今ある写真やチラシで大丈夫です</small></li>
      <li><span class="step">STEP 3</span><b>制作と確認</b>
<?php if (siteOption('build_weeks')) { ?>
        <small>素材が揃ってから約 <?= (int)siteOption('build_weeks') ?> 週間</small>
<?php } ?>
      </li>
      <li><span class="step">STEP 4</span><b>公開、そしてケアへ</b></li>
    </ol>

    <footer class="contact">
      <div class="contact__qr" role="img" aria-label="ウェブサイトの QR コード"><?= $qr ?></div>
      <div>
        <p class="contact__lead">ご相談はウェブサイトから</p>
        <p class="contact__text">ご相談は無料です。<?= (int)siteOption('reply_days') ?>営業日以内にお返事します。</p>
        <p class="contact__url"><?= e(preg_replace('#^https?://#', '', $url)) ?></p>
      </div>
      <div class="pilot">
        <img src="<?= e(flyerSrc(siteOption('profile_image'))) ?>" alt="">
        <p><b>伊藤 大悟</b><br>QWEL.DESIGN 代表。2019年に福井市の越前海岸へ移住し、地域のサイトを一緒に運営しています。</p>
      </div>
    </footer>
  </section>

</body>
</html>
