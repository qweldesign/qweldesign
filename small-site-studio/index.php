<?php
/**
 * トップページ (ちいさなサイト工房の紹介)
 * パンフレットの QR コードや紹介から来た人が, 料金と実績を確かめて, 相談まで進むためのページ
 * 金額などの値は content/small-site-studio/options.json (site_option() で読む), よくある不安は content/small-site-studio/faq.json に書く
 * 実績は投稿タイプ works (content/works/*.md) から読む. 個別のページは works/index.php
 * 相談フォームは /contact/ と同じもの (確認・完了の画面は /contact/confirm/, /contact/thanks/)
 */
require_once dirname(__DIR__) . '/studio-config.php';
require_once dirname(__DIR__) . '/inc/templates/studio-contact.php';

// 金額の表示 (例: 200,000)
function studio_yen(int $amount): string {
  return number_format($amount);
}

// 税込の金額 (1円未満は切り捨て)
function studio_tax_included(int $amount): string {
  return number_format((int)floor($amount * (1 + site_option('tax_rate'))));
}

// 画像 (ファイルがなければ, 名前を書いた枠を出す)
// $eager: 最初の画面に見える画像は遅延読み込みにせず, 優先して読み込む
function studio_image(string $src, string $alt, string $class, bool $eager = false): string {
  if ($src !== '' && is_file(site_file($src))) {
    $loading = $eager ? ' fetchpriority="high"' : ' loading="lazy"';
    return '<img class="' . e($class) . '" src="' . e(path($src)) . '" alt="' . e($alt) . '"' . $loading . '>';
  }
  return '<span class="' . e($class) . ' is-placeholder">' . e($alt) . '</span>';
}

render_header([
  'full_title' => SITE_TOP_TITLE,
  'path'       => '',
  'type'       => 'website',
]);

$build = site_option('price_build');
$care  = site_option('price_care');

// パンくず (QWEL in Action > ちいさなサイト工房). 最後の階層 (このサイトのトップ) を現在地にする
$crumbs = array_map(fn($crumb) => ['label' => $crumb['label'], 'path' => path($crumb['path'])], SITE_BREADCRUMB);
$current = array_pop($crumbs);
$breadcrumb = new ContentNavigation([
  'parents'     => $crumbs,
  'subdirLabel' => $current['label'],
  'subdirPath'  => $current['path'],
]);

// 実績 (content/works/*.md. 公開日の新しい順)
$works = content_engine('works', ['slug' => null, 'page' => 1])->get_posts(1, PHP_INT_MAX);
?>
    <main id="main" class="studio">

      <!-- ファーストビュー -->
      <section class="studio__hero">
        <div class="studio__inner">
          <?= $breadcrumb->breadcrumb() ?>
          <p class="studio__label">Small Site Studio</p>
          <h1 class="studio__title">更新を頼める、<br>壊れにくい。<br><span>小さな事業のための<br class="is-sp">ウェブサイト</span></h1>
          <p class="studio__tagline"><span><?= e(site_option('tagline')) ?></span></p>
          <div class="studio__chips">
            <div class="studio__chip is-build">
              <span class="studio__tag is-build">つくる</span>
              <span class="studio__chipLabel">制作費</span>
              <span class="studio__chipPrice"><?= studio_yen($build) ?><small>円〜</small></span>
              <span class="studio__chipNote">税込 <?= studio_tax_included($build) ?>円〜</span>
            </div>
            <div class="studio__chip is-care">
              <span class="studio__tag is-care">まもる</span>
              <span class="studio__chipLabel">ケア 月額</span>
              <span class="studio__chipPrice"><?= studio_yen($care) ?><small>円〜</small></span>
              <span class="studio__chipNote">税込 <?= studio_tax_included($care) ?>円〜</span>
            </div>
          </div>
<?php if ($works) { ?>
          <figure class="studio__heroImage">
            <?= studio_image($works[0]['img'] ?? '', ($works[0]['title'] ?? '') . 'のサイト画面', 'studio__shot', true) ?>
          </figure>
<?php } ?>
        </div>
      </section>

      <!-- 向いている方 -->
      <section class="studio__section">
        <div class="studio__inner">
          <p class="studio__label">For You</p>
          <h2 class="studio__heading">こんな方に向いています</h2>
          <div class="studio__card is-build">
            <ul class="studio__checks">
              <li>自分では更新しないので、<span class="studio__mark">更新ごと頼みたい</span></li>
              <li>今のサイトが古い、更新が止まっている</li>
              <li>作った業者と連絡がつかない、遠くて相談しづらい</li>
            </ul>
          </div>
          <div class="studio__card is-muted">
            <p class="studio__cardTitle">向いていないかもしれない方</p>
            <ul class="studio__dashes">
              <li>自分で頻繁に更新したい</li>
              <li>通販や、日時・空き状況を管理する予約の仕組みが必要</li>
            </ul>
            <p class="studio__note">※ 申し込みフォームまでは対応します。</p>
          </div>
        </div>
      </section>

      <!-- 実績 -->
      <section id="works" class="studio__section is-blue">
        <div class="studio__inner">
          <p class="studio__label">Works</p>
          <h2 class="studio__heading">いま動いている、越前海岸のサイト</h2>
          <p class="studio__lead">見本ではなく、実際に使われているサイトです。どれも作るだけでなく、運営にも関わっています。</p>
          <ul class="studio__works">
<?php foreach ($works as $work) { ?>
<?php $detailUrl = post_url('works', $work); ?>
            <li class="studio__work">
              <a class="studio__workImage" href="<?= e($detailUrl) ?>" tabindex="-1" aria-hidden="true">
                <?= studio_image($work['img'] ?? '', ($work['title'] ?? '') . 'のサイト画面', 'studio__shot') ?>
              </a>
              <h3 class="studio__workName"><a class="studio__workLink" href="<?= e($detailUrl) ?>"><?= e($work['title'] ?? '') ?></a></h3>
              <p class="studio__workKind"><?= e($work['category'] ?? '') ?> ／ <?= e($work['role'] ?? '') ?></p>
<?php if (!empty($work['website'])) { ?>
              <p class="studio__workUrl"><a href="<?= e($work['website']) ?>" target="_blank" rel="noopener"><?= e(preg_replace('#^https?://#', '', $work['website'])) ?> ↗</a></p>
<?php } ?>
            </li>
<?php } ?>
          </ul>
        </div>
      </section>

      <!-- 壊れにくい理由 -->
      <section class="studio__section">
        <div class="studio__inner">
          <p class="studio__label">Why It Lasts</p>
          <h2 class="studio__heading">軽くて、壊れにくい理由</h2>
          <ol class="studio__reasons">
            <li class="studio__card is-care">
              <p class="studio__cardTitle">面倒なバージョンアップがない</p>
              <p>管理画面もデータベースも持たないので、更新作業で止まる心配がありません。</p>
            </li>
            <li class="studio__card is-care">
              <p class="studio__cardTitle">表示が軽い</p>
              <p>電波の弱い海辺でも、すぐに開きます。</p>
            </li>
            <li class="studio__card is-care">
              <p class="studio__cardTitle">原稿を渡すだけ</p>
              <p>LINE やメールで写真と文章を送ってもらえば、こちらで掲載します。</p>
            </li>
          </ol>
          <p class="studio__note"><a href="https://github.com/qweldesign/qwel-tools-md-engine">仕組みが気になる方へ（技術的な説明）</a></p>
        </div>
      </section>

      <!-- 含まれるもの -->
      <section class="studio__section is-blue">
        <div class="studio__inner">
          <p class="studio__label">What's Included</p>
          <h2 class="studio__heading">制作費に含まれるもの</h2>
          <div class="studio__card">
            <ul class="studio__included">
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
          <div class="studio__card is-muted">
            <p class="studio__cardTitle">別料金（ご相談ください）</p>
            <p>通販・予約システム ／ 写真撮影 ／ 原稿の作成 ／ ページの追加 ／ 紙のパンフレット</p>
          </div>
        </div>
      </section>

      <!-- 料金 -->
      <section id="price" class="studio__section">
        <div class="studio__inner">
          <p class="studio__label">Price</p>
          <h2 class="studio__heading">料金</h2>
          <div class="studio__prices">
            <div class="studio__card is-build">
              <span class="studio__tag is-build">つくる</span>
              <p class="studio__priceLabel">制作費</p>
              <p class="studio__price"><?= studio_yen($build) ?><small>円〜（税別）</small></p>
              <p class="studio__note">税込 <?= studio_tax_included($build) ?>円〜。上の構成の範囲なら追加はありません。</p>
            </div>
            <div class="studio__card is-care">
              <span class="studio__tag is-care">まもる</span>
              <p class="studio__priceLabel">ケア 月額</p>
              <p class="studio__price"><?= studio_yen($care) ?><small>円〜（税別）</small></p>
              <p class="studio__note">税込 <?= studio_tax_included($care) ?>円〜。年払いもできます。</p>
              <ul class="studio__dots">
                <li>お知らせの掲載・小さな直し：月 <?= (int)site_option('care_updates') ?> 回</li>
                <li>サーバーとドメインの見守り</li>
                <li>ご連絡への返事：<?= (int)site_option('reply_days') ?>営業日以内</li>
              </ul>
            </div>
          </div>
          <div class="studio__card is-muted">
            <p class="studio__cardTitle">追加作業</p>
            <p>回数を超える更新やページ追加は、事前に目安をお伝えします。</p>
          </div>
          <div class="studio__promise">
            <p class="studio__cardTitle">ドメインとサーバーは、お店の名義で</p>
            <p>ケアを解約しても、サイトはそのまま残ります。</p>
          </div>
          <p class="studio__note">※ 料金は改定することがあります。お見積もりの時点の料金でご案内します。</p>
<?php if (site_option('invoice')) { ?>
          <p class="studio__note">※ 適格請求書（インボイス）を発行できます。</p>
<?php } ?>
        </div>
      </section>

      <!-- 流れ -->
      <section class="studio__section is-orange">
        <div class="studio__inner">
          <p class="studio__label">Flight Plan</p>
          <h2 class="studio__heading">ご依頼の流れ</h2>
          <ol class="studio__flow">
            <li>
              <span class="studio__step">STEP 1</span>
              <p class="studio__cardTitle">相談（無料）</p>
            </li>
            <li>
              <span class="studio__step">STEP 2</span>
              <p class="studio__cardTitle">お話を聞いて、素材をお預かり</p>
              <p class="studio__note">今ある写真、メニュー、チラシで大丈夫です。</p>
            </li>
            <li>
              <span class="studio__step">STEP 3</span>
              <p class="studio__cardTitle">制作と確認</p>
<?php if (site_option('build_weeks')) { ?>
              <p class="studio__note">素材が揃ってから約 <?= (int)site_option('build_weeks') ?> 週間</p>
<?php } else { ?>
              <p class="studio__note">期間の目安は、ご相談のときにお伝えします。</p>
<?php } ?>
            </li>
            <li>
              <span class="studio__step">STEP 4</span>
              <p class="studio__cardTitle">公開、そしてケアへ</p>
            </li>
          </ol>
        </div>
      </section>

      <!-- よくある不安 -->
      <section class="studio__section">
        <div class="studio__inner">
          <p class="studio__label">Q &amp; A</p>
          <h2 class="studio__heading">よくある不安</h2>
          <div class="studio__faq">
<?php foreach (content_json('small-site-studio/faq')['faq'] ?? [] as $i => $faq) { ?>
            <details class="studio__card"<?= $i === 0 ? ' open' : '' ?>>
              <summary><span class="is-q">Q.</span> <?= e($faq['q']) ?></summary>
              <p><span class="is-a">A.</span> <?= e($faq['a']) ?></p>
            </details>
<?php } ?>
          </div>
        </div>
      </section>

      <!-- 作っている人 -->
      <section class="studio__section is-blue">
        <div class="studio__inner">
          <p class="studio__label">Pilot</p>
          <h2 class="studio__heading">作っている人について</h2>
          <div class="studio__card studio__profile">
            <div class="studio__profileHead">
              <?= studio_image(site_option('profile_image'), '伊藤 大悟', 'studio__face') ?>
              <div>
                <p class="studio__profileName">伊藤 大悟</p>
                <p class="studio__profileOrg">QWEL.DESIGN</p>
              </div>
            </div>
            <p>2017年に開業。2019年に福井市の越前海岸へ移住し、地域の団体やお店のサイトを、当事者として一緒に運営しています。</p>
            <p class="studio__note"><a href="https://qwel.design/#profile">詳しいプロフィール</a></p>
          </div>
        </div>
      </section>

      <!-- 相談フォーム -->
      <section id="contact" class="studio__section is-orange">
        <div class="studio__inner">
          <p class="studio__label">Contact</p>
          <h2 class="studio__heading">まずは、お気軽にご相談ください</h2>
          <div class="studio__card studio__form">
<?php render_contact_form(); ?>
          </div>
<?php if (site_option('line_url')) { ?>
          <p class="studio__line"><a class="studio__button" href="<?= e(site_option('line_url')) ?>" target="_blank" rel="noopener">公式 LINE で相談する</a></p>
<?php } ?>
        </div>
      </section>

      <!-- スマートフォンの画面下に固定する, 相談への近道 -->
      <nav class="studio__bar" aria-label="相談への近道">
        <a class="studio__button" href="#price">料金を見る</a>
        <a class="studio__button is-main" href="#contact">相談する</a>
      </nav>
    </main>
<?php render_footer(); ?>
