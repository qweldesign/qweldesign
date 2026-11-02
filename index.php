<?php
/**
 * トップページ (QWEL in Action)
 * 本文はこのテンプレートに書く. ヘッダー・フッター・<head> は inc/partials/site.php (設定は config.php)
 * ヘッダーのメニューは, このページの各セクションへ (表示中のセクションに合わせて灯る)
 * フッターの文字のリンクは, 2つのサービス (ちいさなサイト工房, Co-Pilot) へ
 */
require_once __DIR__ . '/inc/partials/site.php';

// 構造化データ: サイトと運営者 (検索エンジンに, 名前・拠点・活動を正しく伝える)
ob_start();
?>
    <!-- 構造化データ: サイトと運営者 (検索エンジンに, 名前・拠点・活動を正しく伝える) -->
    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@graph": [
          {
            "@type": "WebSite",
            "@id": "https://qwel.design/#website",
            "url": "https://qwel.design/",
            "name": "QWEL in Action",
            "inLanguage": "ja",
            "publisher": { "@id": "https://qwel.design/#person" }
          },
          {
            "@type": "Person",
            "@id": "https://qwel.design/#person",
            "name": "伊藤 大悟",
            "alternateName": ["Taigo Ito", "QWEL.DESIGN"],
            "url": "https://qwel.design/",
            "jobTitle": "Web 開発者 (フロントエンド)",
            "description": "福井市の越前海岸を拠点に、WordPress のオリジナルテーマ開発や、JavaScript によるフルスクラッチの UI/UX 実装を手掛ける。",
            "address": {
              "@type": "PostalAddress",
              "addressLocality": "福井市",
              "addressRegion": "福井県",
              "addressCountry": "JP"
            },
            "knowsAbout": ["WordPress", "JavaScript", "UI/UX", "PHP", "TypeScript", "React", "Vue.js"],
            "sameAs": [
              "https://github.com/qweldesign",
              "https://note.com/qweldesign",
              "https://zenn.dev/qweldesign"
            ]
          }
        ]
      }
    </script>
<?php
$structuredData = ob_get_clean();

render_header([
  'full_title' => SITE_TOP_TITLE,
  'path'       => '',
  'type'       => 'website',
  'brand_tag'  => 'h1',
  'nav'        => [
    '#about'    => 'About QWEL',
    '#works'    => 'Works & Community',
    '#profile'  => 'Profile',
    '#creative' => 'Tools & Crafts',
    '#story'    => 'A Little Story',
  ],
  'footer_links' => [
    'small-site-studio/' => 'ちいさなサイト工房',
    'co-pilot/'          => '制作会社・広告代理店の方へ',
  ],
  'head' => $structuredData,
]);
?>
    <main>
      <div class="flightHero">
        <p class="flightHero__title">QWEL<br><em>in Action</em></p>
        <p class="flightHero__lead">福井・越前海岸から ― フルスクラッチのWeb制作とUI/UXクラフト</p>
      </div>
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="about" class="section__content" data-spy-section>
              <div data-readable>
                <h2 class="section__heading">About QWEL</h2>
                <p class="section__subHeading">QWELについて</p>
              </div>
              <div data-readable>
                <p>Web開発・プログラミング教室を営む QWEL.DESIGN (クヴェル・デザイン) のクラフト作品集 <strong>"QWEL in Action"</strong> へようこそ。</p>
                <p>これまで、UI/UX開発を数多く手掛けてきましたが、中でも<em>インタラクション設計を得意とし、多くを JavaScript によるフルスクラッチ (Vanilla JS) で実装</em>しています。必要に応じて PHP や API 連携にも対応しています。</p>
                <p>JavaScript は、「<strong>見える</strong>」「<strong>触れる</strong>」「<strong>反応する</strong>」世界をつくることができる言語です。<br>つまり、<em>ユーザー体験そのものを設計し、演出する</em>ことができます。</p>
                <p>さらに、柔軟で即興的な性質を持つこの言語の扱いは、まるで<em>音楽やドローイングのように自由で、創造の喜びを味わえる</em>営みだと感じています。</p>
                <p><em>憧れの模倣や、誰かのためを想ってつくる体験</em>が、<em>人の感性を育てていく</em>─<br>それが、<strong>QWEL の哲学</strong>です。</p>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="works" class="section__content" data-spy-section>
              <div data-readable>
                <h2 class="section__heading">Works &amp; Community</h2>
                <p class="section__subHeading">越前海岸でつくっているもの</p>
              </div>
              <div data-readable>
                <p>2019年末に福井市の越前海岸へ移住してから、地域の活動に加わりながら、その活動を届けるWebサイトを手掛けてきました。
                  <br>ご依頼を受けて作ったものもあれば、<em>当事者として作り、いまも一緒に運営しているもの</em>もあります。</p>
                <p>ご依頼の多くは機密保持のため、ここでご紹介するのはその一部です。</p>
              </div>
              <ul class="section__list">
                <li class="section__listItem" data-readable>
                  <h3>福井市越前海岸盛り上げ隊</h3>
                  <ul class="section__tags" aria-label="関わり方">
                    <li class="section__tag">制作</li>
                    <li class="section__tag">運営</li>
                  </ul>
                  <p>福井市越前海岸エリアで活動する地域団体のサイトです。私自身もメンバーとして、活動に積極的に参加しています。</p>
                  <a class="section__link" href="https://discoverechizen.com" target="_blank" rel="noopener">discoverechizen.com</a>
                </li>
                <li class="section__listItem" data-readable>
                  <h3>版画ゆうびん舎</h3>
                  <ul class="section__tags" aria-label="関わり方">
                    <li class="section__tag">制作</li>
                    <li class="section__tag">運用</li>
                  </ul>
                  <p>版画家 おさのなおこ のポートフォリオサイトです。原画をスキャンしたデータをふんだんに使い、優しく温かい表情の木版画作品を、Webでもお楽しみいただけるようにしました。</p>
                  <a class="section__link" href="https://hangakobo.com" target="_blank" rel="noopener">hangakobo.com</a>
                </li>
                <li class="section__listItem" data-readable>
                  <h3>海辺の古民家はりいしゃ</h3>
                  <ul class="section__tags" aria-label="関わり方">
                    <li class="section__tag">制作</li>
                    <li class="section__tag">運営</li>
                  </ul>
                  <p>福井市越前海岸に佇む古民家を、ゲストハウスとギャラリーとして運営しています。</p>
                  <a class="section__link" href="https://hariisha.jp" target="_blank" rel="noopener">hariisha.jp</a>
                </li>
                <li class="section__listItem" data-readable>
                  <h3>国見 海・人・暮らし相談室</h3>
                  <ul class="section__tags" aria-label="関わり方">
                    <li class="section__tag">制作</li>
                    <li class="section__tag">運営</li>
                  </ul>
                  <p>越前海岸の町「国見地区」で、空き家マッチングツアーを開催している団体のサイトです。ツアーの申込みの仕組みまで含めて制作し、運営にも携わっています。</p>
                  <a class="section__link" href="https://kunimi.life" target="_blank" rel="noopener">kunimi.life</a>
                </li>
              </ul>
            </div>
            <div class="section__bannerArea">
              <a class="section__banner" href="./small-site-studio/">
                <img src="./content/images/banners/small-site-studio.jpg" alt="ちいさなサイト工房">
              </a>
              <a class="section__banner" href="./co-pilot/">
                <img src="./content/images/banners/co-pilot.jpg" alt="Co-Pilot ― 制作会社・広告代理店の方へ">
              </a>
            </div>
          </div>
        </div>
      </section>
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="profile" class="section__content" data-spy-section>
              <div data-readable>
                <h2 class="section__heading">Profile</h2>
                <p class="section__subHeading">プロフィール</p>
              </div>
              <div data-readable>
                <p>はじめまして。QWEL.DESIGN 代表の<strong>伊藤大悟</strong>です。
                  <br>福井市の越前海岸に暮らしながら、Webサイトの制作と、UI/UXの設計・実装を手掛けています。</p>
                <p>広告代理店や制作会社からの委託を中心に、<em>WordPressのオリジナルテーマ開発</em>から、<em>インタラクションにこだわったフロントエンドの実装</em>まで、既製品に頼らず一から形にしています。</p>
              </div>
              <dl class="section__specs" data-readable>
                <div class="section__spec">
                  <dt>屋号</dt>
                  <dd>QWEL.DESIGN (クヴェル・デザイン)</dd>
                </div>
                <div class="section__spec">
                  <dt>代表</dt>
                  <dd>伊藤 大悟</dd>
                </div>
                <div class="section__spec">
                  <dt>拠点</dt>
                  <dd>福井県福井市 越前海岸</dd>
                </div>
                <div class="section__spec">
                  <dt>開業</dt>
                  <dd>2017年</dd>
                </div>
                <div class="section__spec">
                  <dt>専門</dt>
                  <dd>Web開発 / UI/UX設計</dd>
                </div>
                <div class="section__spec">
                  <dt>得意</dt>
                  <dd>JavaScript によるフルスクラッチ実装</dd>
                </div>
              </dl>

              <h3 class="section__caption" data-readable>Services ― ご依頼いただけること</h3>
              <ul class="section__list">
                <li class="section__listItem" data-readable>
                  <h3>WordPress / Simple CMS</h3>
                  <p>オリジナルテーマを、設計から実装まで一から制作します。</p>
                  <p>サイトの目的と更新のしやすさに合わせて、必要な機能だけを組み込みます。</p>
                  <p>データベースを使わない markdown によるオリジナルの最小CMSを組むことも可能です。</p>
                </li>
                <li class="section__listItem" data-readable>
                  <h3>UI/UX &amp; Interaction</h3>
                  <p>「見える」「触れる」「反応する」体験を、JavaScript (Vanilla JS) のフルスクラッチで実装します。</p>
                  <p>SPA にも対応しています。</p>
                </li>
                <li class="section__listItem" data-readable>
                  <h3>Web Application</h3>
                  <p>TypeScript / React / Vue を用いたWebアプリケーションの開発に携わっています。</p>
                </li>
                <li class="section__listItem" data-readable>
                  <h3>PHP &amp; API</h3>
                  <p>お問い合わせフォームや営業日カレンダーなど、必要に応じて PHP や API 連携にも対応します。</p>
                </li>
              </ul>

              <h3 class="section__caption" data-readable>Flight Log ― 経歴</h3>
              <ol class="section__timeline" data-readable>
                <li class="section__timelineItem">
                  <span class="section__timelineYear">～2015</span>
                  <p>ピアノ修理の職人として、木工と研磨の仕事に携わる。</p>
                </li>
                <li class="section__timelineItem">
                  <span class="section__timelineYear">2015</span>
                  <p>プログラミングに興味を抱き、2年間で独習する。</p>
                </li>
                <li class="section__timelineItem">
                  <span class="section__timelineYear">2017</span>
                  <p>Web制作会社 TG WEBCODING を設立。</p>
                </li>
                <li class="section__timelineItem">
                  <span class="section__timelineYear">2018</span>
                  <p>子ども向けプログラミング教室を開講。</p>
                </li>
                <li class="section__timelineItem">
                  <span class="section__timelineYear">2019</span>
                  <p>東京都町田市から福井市の越前海岸へ移住し、屋号を QWEL.DESIGN に改める。</p>
                </li>
                <li class="section__timelineItem">
                  <span class="section__timelineYear">2025〜</span>
                  <p>TypeScript / React / Vue を用いたWebアプリケーション開発にも取り組む。</p>
                </li>
              </ol>

              <h3 class="section__caption" data-readable>Off Duty ― コードの外で</h3>
              <div data-readable>
                <p>サン=テグジュペリの愛読家です。郵便飛行の操縦士だった彼は、飛行機を「農夫の鋤、大工の鉋」にたとえ、大地から学ぶための道具だと語りました。</p>
              </div>
              <div class="section__motto" data-readable>
                <p>鋤と鉋に続くもの、それはプログラミングだと考えています。</p>
              </div>
              <div data-readable>
                <p>ピアノ修理で身についた、手で確かめながら仕上げていく感覚は、いまのコードの書き方にも通じています。越前海岸に移住してからは、楽器演奏や天体観測、焚き火と薪ストーブのある暮らしを楽しみながら、技術で地域社会とより深く関わっていきたいと考えています。</p>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="creative" class="section__content" data-spy-section>
              <div data-readable>
                <h2 class="section__heading">Tools &amp; Crafts</h2>
                <p class="section__subHeading">機能と表現</p>
              </div>
              <div data-readable>
                <p>QWEL.DESIGN の仕事としては、<em>WordPressのオリジナルテーマ開発</em>や、<em>UI/UXの設計・実装</em>が主な業務です。
                  <br>実務的なUI/UXコンポーネント・PHP作品は、CREATIVE TOOLS にまとめています。</p>
                <p>一方で、<em>純粋な好奇心と創作の楽しさ</em>から、インタラクティブなアニメーションやビジュアル表現にも取り組んできました。
                  <br>それらは、<em>機能と表現の間にあるもの</em>として、手を動かしながら少しずつ形にしてきた "クラフト" の記録として、CREATIVE CRAFTS にまとめています。</p>
              </div>
              <div class="section__row py-medium">
                <div class="section__col" data-readable>
                  <a class="button is-primary is-md" href="./tools/">Creative Tools</a>
                </div>
                <div class="section__col" data-readable>
                  <a class="button is-secondary is-md" href="./creative/">Creative Crafts</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="story" class="section__content" data-spy-section>
              <div data-readable>
                <h2 class="section__heading">A Little Story</h2>
                <p class="section__subHeading">ちいさな物語</p>
              </div>
              <div data-readable>
                <p>屋号 QWEL の由来は、子ども向けプログラミング教室を開催しようと思い立ったとき、<em>子どもが入力し易いキーボードの左上の配列 "Q", "W", "E" から</em>決めました。最後の "L" は、総当たりで<em>響きを重視</em>しました。</p>
                <p>だから、「<strong>優しさと共にある技術</strong>」がコンセプトです。</p>
                <p>そして、"QWEL" の文字をプロペラ飛行機に模してデザインしたロゴには、機能と遊び心の両方を込めています。</p>
                <p class="section__motto">「届けたい」と思って作ること。
                  <br>「誰かの役に立ちたい」と願って飛ばすこと。</p>
                <p>それが、QWEL の原点であり、これからも変わらない翼のかたちです。</p>
                <p>QWEL にご興味を持っていただいた方には、<em>noteで日々の活動</em>や、<em>GitHubから制作実績</em>を見ていただいたり、<em>お問い合わせフォームからご相談・ご感想</em>をお寄せいただけますと嬉しいです。</p>
              </div>
              <div class="section__row py-medium">
                <div class="section__col" data-readable>
                  <a class="button is-secondary is-md is-brandName" href="https://note.com/qweldesign" target="_blank" rel="noopener">
                    <svg class="button__icon icon is-si-journal is-md" width="36" height="36" aria-hidden="true">
                      <use href="./assets/icons.svg#si-journal"></use>
                    </svg>
                    <span class="button__text">note</span>
                  </a>
                </div>
                <div class="section__col" data-readable>
                  <a class="button is-darker is-md" href="https://github.com/qweldesign" target="_blank" rel="noopener">
                    <svg class="button__icon icon is-si-github is-md" width="36" height="36" aria-hidden="true">
                      <use href="./assets/icons.svg#si-github"></use>
                    </svg>
                    <span class="button__text">GitHub</span></a>
                </div>
                <div class="section__col" data-readable>
                  <a class="button is-primary is-md" href="/contact/">
                    <svg class="button__icon icon is-si-mail is-md" width="36" height="36" aria-hidden="true">
                      <use href="./assets/icons.svg#si-mail"></use>
                    </svg>
                    <span class="button__text">Contact Me</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
<?php render_footer(); ?>
