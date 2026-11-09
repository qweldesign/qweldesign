<?php
/**
 * Co-Pilot ― 制作会社・広告代理店の方へ (/co-pilot/)
 * 本文はこのテンプレートに書く. ヘッダー・フッター・<head> は templates/site.php (設定は templates/config.php)
 * ヘッダーのメニューは, このページの各セクションへ (表示中のセクションに合わせて灯る)
 *
 * content/ で管理するもの (いずれ別のリポジトリにする):
 *   実績: content/co-pilot/records.json (匿名), content/works/*.md (公開している実績)
 *   料金の目安: content/co-pilot/fares.json
 *   数値 (返信の日数, 修正の回数, 最低受注額, 特急の割増, 同時にお受けする件数など): content/options.json
 * 部品 (実績, 料金の目安) は templates/copilot.php
 * #contact のフォームは /contact/ と共通 (templates/contact.php の renderContactForm)
 * 稼働状況 ([data-flight-status]) は content/options.json の flight_status (open / consult) で切り替える. 受付中の年月は js/flight-status.js が添える
 */
require_once dirname(__DIR__) . '/inc/bootstrap.php';
$sections = require dirname(__DIR__) . '/templates/sections.php';
bootstrapSection('main', $sections);
require_once dirname(__DIR__) . '/templates/site.php';
require_once dirname(__DIR__) . '/templates/contact.php';
require_once dirname(__DIR__) . '/templates/copilot.php';

// content/options.json の値 (整数は 3 桁区切り)
$value = fn(string $key): string => e(fillOptions('{' . $key . '}'));

// 実績 (content がない環境では空. どちらも空なら, 実績のセクションとメニューごと出さない)
$status = copilotStatus();
$records = copilotRecords();
$works = copilotWorks();
$nav = [
  '#route'   => 'Route',
  '#records' => 'Flight Records',
  '#fare'    => 'Fare',
  '#terms'   => 'Terms',
  '#contact' => 'Contact',
];
if (!$records && !$works) unset($nav['#records']);

renderHeader([
  'fullTitle'   => 'Co-Pilot ― 制作会社・広告代理店の方へ | WordPress・UI/UX制作の委託 | QWEL in Action',
  'description' => '福井・越前海岸の QWEL.DESIGN が、制作会社・広告代理店からの委託で、デザインカンプからのコーディング、WordPressのオリジナルテーマ開発、JavaScriptによるフルスクラッチのUI/UX実装をお受けします。対応範囲、料金の目安、取引条件のご案内です。',
  'path'        => 'co-pilot/',
  'image'       => 'content/images/banners/co-pilot.jpg',
  'imageAlt'    => 'Co-Pilot ― 制作会社・広告代理店の方へ',
  'nav'         => $nav,
]);
?>
    <main>
      <!-- ファーストビュー (本家のトップと見分けられるよう, パンくずを添えて左寄せの小さめの見出しにする) -->
      <div class="flightHero is-sub copilotHero">
<?php renderBreadcrumb(ContentNavigation::breadcrumbs([...SITE_BREADCRUMB, ['label' => 'Co-Pilot']])); ?>
        <h1 class="flightHero__title">Co-Pilot<span class="flightHero__mark" aria-hidden="true">✶</span></h1>
        <p class="flightHero__audience">制作会社・広告代理店の方へ</p>
        <p class="copilotHero__catch">WordPress のオリジナルテーマから、<br>インタラクションの実装まで。</p>
        <p class="flightHero__text">デザインカンプからのコーディング、WordPress のテーマ開発、JavaScript によるフルスクラッチの UI 実装をお受けします。
          <br>機長は御社。QWEL.DESIGN は <em>副操縦士として、隣の席で操縦を支えます</em>。</p>
        <div class="copilotIntro">
          <a class="button" href="#contact">ご相談・お見積もり</a>
          <p class="copilotStatus<?= $status['class'] ?>" data-flight-status="<?= e($status['state']) ?>"><?= e($status['text']) ?></p>
        </div>
      </div>

      <!-- 任せられること -->
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="route" class="section__content" data-spy-section>
              <div data-readable>
                <h2 class="section__heading">Route</h2>
                <p class="section__subHeading">任せられること</p>
              </div>
              <ul class="section__list">
                <li class="section__listItem" data-readable>
                  <h3>Coding</h3>
                  <p>デザインカンプ (Figma / XD / AI / PSD) からのコーディング。スマホ対応はすべてに含みます。</p>
                </li>
                <li class="section__listItem" data-readable>
                  <h3>WordPress</h3>
                  <p>既製テーマに頼らないオリジナルテーマ。カスタム投稿タイプ、カスタムフィールドで、更新する人に合わせた管理画面にします。</p>
                </li>
                <li class="section__listItem" data-readable>
                  <h3>Interaction</h3>
                  <p>Vanilla JS によるフルスクラッチの UI 実装。SPA にも対応します。</p>
                </li>
                <li class="section__listItem" data-readable>
                  <h3>PHP &amp; API</h3>
                  <p>確認画面つきのフォーム、営業日カレンダー、外部 API との連携。</p>
                </li>
                <li class="section__listItem" data-readable>
                  <h3>Maintenance</h3>
                  <p>既存サイトの改修、他社からの引き継ぎ、保守・運用。</p>
                </li>
                <li class="section__listItem" data-readable>
                  <h3>Web Application</h3>
                  <p>TypeScript / React / Vue を使った Web アプリのフロントエンドを、チームの一員として。</p>
                </li>
              </ul>
              <h3 class="section__caption" data-readable>お受けしていないこと</h3>
              <p class="copilotNote" data-readable>ゼロからのデザイン (ワイヤーフレームからのデザインはご相談ください) ／ 大規模な EC サイト・決済の仕組み ／ 24時間の監視・即時の障害対応</p>
            </div>
          </div>
        </div>
      </section>

      <!-- 実績 -->
<?php if ($records || $works) { ?>
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="records" class="section__content" data-spy-section>
              <div data-readable>
                <h2 class="section__heading">Flight Records</h2>
                <p class="section__subHeading">実績</p>
              </div>
              <div data-readable>
                <p>多くのご依頼は秘密保持のため、匿名で掲載しています。ご紹介しているのは一部です。</p>
              </div>
<?php if ($records) { ?>
<?php renderCopilotRecords($records); ?>
<?php } ?>
<?php if ($works) { ?>
              <h3 class="section__caption" data-readable>公開している制作と運営 ― 越前海岸の地域サイト</h3>
<?php renderCopilotShots($works); ?>
<?php } ?>
              <div data-readable>
                <p class="copilotNote">コードの見本は <a href="https://github.com/qweldesign" target="_blank" rel="noopener">GitHub</a> でご覧いただけます。</p>
              </div>
            </div>
          </div>
        </div>
      </section>
<?php } ?>

      <!-- 品質の基準 -->
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="instruments" class="section__content">
              <div data-readable>
                <h2 class="section__heading">Instruments</h2>
                <p class="section__subHeading">品質の基準</p>
              </div>
              <dl class="section__specs is-wide" data-readable>
                <div class="section__spec">
                  <dt>対応ブラウザ</dt>
                  <dd>Chrome / Safari / Edge / Firefox の最新版、iOS・Android の標準ブラウザ</dd>
                </div>
                <div class="section__spec">
                  <dt>スマホ対応</dt>
                  <dd>すべての制作に含みます</dd>
                </div>
                <div class="section__spec">
                  <dt>アクセシビリティ</dt>
                  <dd>見出しの構造、代替テキスト、キーボード操作、コントラストに配慮</dd>
                </div>
                <div class="section__spec">
                  <dt>表示速度</dt>
                  <dd>表示速度を測りながら仕上げ、納品時に PageSpeed Insights の結果を共有します</dd>
                </div>
                <div class="section__spec">
                  <dt>コード</dt>
                  <dd>自作の SCSS フレームワーク、一貫した命名規則、jQuery 不使用</dd>
                </div>
                <div class="section__spec">
                  <dt>納品物</dt>
                  <dd>Git リポジトリ、テーマ一式、必要に応じて更新手順の説明</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </section>

      <!-- 進め方 -->
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="plan" class="section__content">
              <div data-readable>
                <h2 class="section__heading">Flight Plan</h2>
                <p class="section__subHeading">進め方</p>
              </div>
              <ol class="section__timeline" data-readable>
                <li class="section__timelineItem">
                  <span class="section__timelineYear">STEP 1</span>
                  <p><b>ご相談</b> ― 資料の URL だけでも</p>
                </li>
                <li class="section__timelineItem">
                  <span class="section__timelineYear">STEP 2</span>
                  <p><b>お見積もり</b> ― 資料をいただいてから <?= $value('reply_days') ?>営業日以内</p>
                </li>
                <li class="section__timelineItem">
                  <span class="section__timelineYear">STEP 3</span>
                  <p><b>ご発注・制作</b> ― 進み具合を随時共有</p>
                </li>
                <li class="section__timelineItem">
                  <span class="section__timelineYear">STEP 4</span>
                  <p><b>確認・修正</b> ― デザインどおりに組んだ後の修正は <span class="copilotValue"><?= $value('revisions') ?>回</span>まで</p>
                </li>
                <li class="section__timelineItem">
                  <span class="section__timelineYear">STEP 5</span>
                  <p><b>納品・検収</b> ― Git リポジトリでお渡し</p>
                </li>
              </ol>
              <dl class="section__specs is-wide" data-readable>
                <div class="section__spec">
                  <dt>受け取れる資料</dt>
                  <dd>Figma / XD / AI / PSD、仕様書、ワイヤーフレームなど。</dd>
                </div>
                <div class="section__spec">
                  <dt>連絡手段</dt>
                  <dd>メール、Slack など。進行中の案件は<?= $value('reply_days_active') ?>営業日以内、新しいご相談は<?= $value('reply_days') ?>営業日以内にお返事します。</dd>
                </div>
                <div class="section__spec">
                  <dt>打ち合わせ</dt>
                  <dd>オンラインが基本。北陸は訪問もご相談ください。</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </section>

      <!-- 料金の目安 -->
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="fare" class="section__content" data-spy-section>
              <div data-readable>
                <h2 class="section__heading">Fare</h2>
                <p class="section__subHeading">料金の目安</p>
              </div>
<?php renderCopilotFares(); ?>
              <div data-readable>
                <p class="copilotNote">※ <?= $value('price_as_of') ?>現在の目安です。詳しくはお見積もりでご案内します。
                  <br>※ 最低受注額 <span class="copilotValue">1件 <?= $value('min_order') ?>円〜</span> ／ 特急 (<?= $value('rush_term') ?>) <span class="copilotValue">+<?= $value('rush_rate') ?>%</span></p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 取引条件 -->
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="terms" class="section__content" data-spy-section>
              <div data-readable>
                <h2 class="section__heading">Terms</h2>
                <p class="section__subHeading">取引条件</p>
              </div>
              <dl class="section__specs is-wide" data-readable>
                <div class="section__spec">
                  <dt>インボイス</dt>
                  <dd>適格請求書（インボイス）を発行できます。</dd>
                </div>
                <div class="section__spec">
                  <dt>秘密保持契約</dt>
                  <dd>締結できます。</dd>
                </div>
                <div class="section__spec">
                  <dt>名前を出さない制作</dt>
                  <dd>対応します。ただし、ソースコード内のライセンス表記 (MITなど) は残します。</dd>
                </div>
                <div class="section__spec">
                  <dt>実績の掲載</dt>
                  <dd>ご依頼ごとに可否を確認し、許可のない案件は名前を出しません。</dd>
                </div>
                <div class="section__spec">
                  <dt>契約の形</dt>
                  <dd>請負 (成果物の納品) が基本。チームでの支援は準委任も可。</dd>
                </div>
                <div class="section__spec">
                  <dt>お支払い</dt>
                  <dd>納品月末締め・翌月末払いを基本とします。</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </section>

      <!-- 稼働状況 -->
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="status" class="section__content">
              <div data-readable>
                <h2 class="section__heading">Flight Status</h2>
                <p class="section__subHeading">稼働状況</p>
              </div>
              <div data-readable>
                <!-- 状態は content/options.json の flight_status. 受付中の年月は js/flight-status.js が添える -->
                <p class="copilotStatus is-panel<?= $status['class'] ?>" data-flight-status="<?= e($status['state']) ?>"><?= e($status['text']) ?></p>
                <p class="copilotNote">同時にお受けする件数 <span class="copilotValue"><?= $value('concurrent') ?>件まで</span>。少し先の時期からのご相談も歓迎します。</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- プロフィール -->
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="pilot" class="section__content">
              <div data-readable>
                <h2 class="section__heading">Pilot</h2>
                <p class="section__subHeading">プロフィール</p>
              </div>
              <div data-readable>
                <p>QWEL.DESIGN 代表の<strong>伊藤大悟</strong>です。2017年に開業し、広告代理店や制作会社からの委託を中心に制作を続けてきました。
                  <br>2019年から福井市の越前海岸を拠点に、リモートで全国のご依頼をお受けしています。</p>
                <p><a href="/#profile">詳しいプロフィールと経歴</a></p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ご相談 -->
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="contact" class="section__content" data-spy-section>
              <div data-readable>
                <h2 class="section__heading">Contact</h2>
                <p class="section__subHeading">ご相談・お見積もり</p>
              </div>
              <div data-readable>
                <p><?= e(fillOptions('ご相談内容はひとことでも構いません。{reply_days}営業日以内にお返事します。')) ?></p>
              </div>
              <!-- フォームは /contact/ と共通 (項目は templates/config.php の SITE_CONTACT_FIELDS. 確認・完了は /contact/confirm/, /contact/thanks/) -->
<?php renderContactForm(); ?>
            </div>
          </div>
        </div>
      </section>
    </main>
<?php renderFooter(); ?>
