<?php
/**
 * ちいさなサイト工房 (/small-site-studio/) の設定
 * サイト名, 料金, お問い合わせの項目など, 工房の区画だけの値をここにまとめる (ルート・投稿タイプは config-common.php)
 * 本家とちいさなサイト工房で共通の値は config-common.php に書く
 *
 * 工房のページ (small-site-studio/ 以下) は, 先頭でこの設定を読んでから inc/ のテンプレートを読む
 * (inc/bootstrap.php は, 設定がなければ本家の config.php を読むため)
 */

require_once __DIR__ . '/config-common.php';

// この区画のヘッダー・フッターの部品 (inc/partials/{SITE_LAYOUT}.php) と, スタイルシート (リポジトリのルートからのパス. scss/studio.scss から書き出す)
const SITE_LAYOUT = 'studio-site';
const SITE_STYLESHEET = 'studio.css';

// サイト名 (<title> の「記事タイトル | サイト名」と og:site_name に使う)
const SITE_NAME = 'ちいさなサイト工房';
// トップページ (紹介ページ) の <title>
const SITE_TOP_TITLE = 'ちいさなサイト工房｜福井の小さな事業のためのウェブサイト制作・更新代行';
// サイトの説明 (description の既定値)
const SITE_DESCRIPTION = '更新を頼める、壊れにくい。福井・越前海岸の QWEL.DESIGN が、小さな事業のためのウェブサイトを作り、お知らせの掲載や小さな直しまで引き受けます。';
// OGP 画像の既定値 (content/images/banners/ は本家のバナーと共用. 1200 × 630. 原稿は tools/ogp/small-site-studio.html)
// 画像を作り直したときは, ファイル名を変えると SNS や LINE の古いキャッシュを避けられる
const SITE_OGP_IMAGE = 'content/images/banners/small-site-studio.jpg';
// OGP 画像の代替テキスト
const SITE_OGP_IMAGE_ALT = 'ちいさなサイト工房 — 更新を頼める、壊れにくい。小さな事業のためのウェブサイト';
// 記事一覧のサムネイルの既定値 (記事にアイキャッチがない場合)
const SITE_THUMBNAIL_IMAGE = 'content/images/banners/small-site-studio.jpg';
// Google Fonts などの読み込み (不要なら空文字)
const SITE_FONTS_URL = 'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Noto+Sans+JP:wght@400;700&family=Shippori+Mincho:wght@500;700&display=swap';

// ヘッダーのロゴ (画像のパスとリンク先. 画像がなければ SITE_NAME を文字で出す)
const SITE_LOGO = 'assets/logo.svg';
const SITE_LOGO_LINK = '';
// ロゴの横に添える文字 (任意)
const SITE_LOGO_TEXT = 'ちいさなサイト工房';
const SITE_LOGO_SUBTEXT = 'by QWEL.DESIGN 福井・越前海岸';

// ヘッダーのナビ (サイトのルートからのパス => 表示名. 不要なら空の配列)
const SITE_NAV = [];

// パンくずの先頭の階層 (path はサイトのルートからのパス, または http から始まる URL)
// 本家 (QWEL in Action) から始め, 本家へ戻る道にする. 最後の階層が, このサイトのトップ
const SITE_BREADCRUMB = [
  ['label' => 'QWEL in Action', 'path' => 'https://qwel.design/'],
  ['label' => 'ちいさなサイト工房', 'path' => ''],
];

// フッターのリンク (サイトのルートからのパス => 表示名)
const SITE_FOOTER_LINKS = [
  'https://qwel.design/' => 'QWEL in Action',
];

// フッターの SNS など (url は http から始まる URL, またはサイトのルートからのパス. icon は icons.svg の symbol の id)
// 本家・Co-Pilot と同じ並び (note, GitHub, Contact). Contact は, このサイトの相談フォームへ
const SITE_SNS = [
  ['label' => 'note', 'url' => 'https://note.com/qweldesign', 'icon' => 'si-journal'],
  ['label' => 'GitHub', 'url' => 'https://github.com/qweldesign', 'icon' => 'si-github'],
  ['label' => 'Contact Me', 'url' => 'https://qwel.design/contact/', 'icon' => 'si-mail'],
];

// 著作権表示 (開始年 - 今年 運営者)
const SITE_START_YEAR = 2019;

// 区画のルート・投稿タイプ・ページは, config-common.php の SITE_SECTIONS['studio'] に書く
// (サイトマップが全区画の URL を1つにまとめるため)
const SITE_SECTION = 'studio';
const SITE_ROOT = SITE_SECTIONS[SITE_SECTION]['root'];
const SITE_POST_TYPES = SITE_SECTIONS[SITE_SECTION]['post_types'];
const SITE_PAGES = SITE_SECTIONS[SITE_SECTION]['pages'];

// 数値などの設定 (content/small-site-studio/options.json. 料金, 返事の日数, 制作期間など)
// site_option() で読み, 文中の {reply_days} などは fill_options() で差し込む
// よくある不安は content/small-site-studio/faq.json
const SITE_OPTIONS = 'small-site-studio/options';

/**
 * お問い合わせフォーム (/contact/)
 * SMTP のパスワードなど秘密の値は inc/.env に書く (inc/.env.example を参照)
 *
 * path: フォームの URL (サイトのルートからのパス. 同じ名前のディレクトリに index.php, confirm/, thanks/ を置く. 送信は api/send.php)
 * title: ページのタイトル
 * lead: フォームの上の説明文 (HTML 可. {reply_days} などは content/small-site-studio/options.json の値に置き換える)
 * interval: 同じ人 (セッション) が続けて送信できるまでの秒数 (0 で制限しない)
 * reply_footer: 自動返信メールの末尾に付ける文 (lead と同じく {reply_days} などを置き換える)
 * env: .env の場所 (公開ディレクトリの外に置く場合は, このパスを書き換える)
 * ※ 返事の日数は options.json の reply_days から入れる (ここには直接書かない)
 */
const SITE_CONTACT = [
  'path'         => 'contact/',
  'title'        => 'ご相談',
  'lead'         => 'ご相談は無料です。{reply_days}営業日以内にお返事します。<br>お電話での連絡を希望される方は、電話番号もご記入ください。',
  'interval'     => 60,
  'reply_footer' => "
{reply_days}営業日以内にお返事しますので、今しばらくお待ちください。

────────────────────────────────────────────────
ちいさなサイト工房 by QWEL.DESIGN
福井・越前海岸 https://qwel.design/
────────────────────────────────────────────────
",
  'env'          => __DIR__ . '/inc/.env',
];

/**
 * お問い合わせフォームの項目 (キーが name 属性. 並び順に表示する)
 * label: 項目名 (確認画面とメール本文にも使う)
 * type: text, email, tel, url, textarea, select, radio, checkbox
 *       (email の最初の項目を, 自動返信の宛先と管理者宛メールの返信先にする)
 * required: 必須か
 * required_group: 同じ名前を書いた項目のうち, どれか1つの入力を必須にする
 * options: select, radio, checkbox の選択肢
 * placeholder, autocomplete: 入力欄の属性 (任意)
 */
const SITE_CONTACT_FIELDS = [
  'name' => [
    'label'        => 'お名前',
    'type'         => 'text',
    'required'     => true,
    'autocomplete' => 'name',
  ],
  'business' => [
    'label'        => '屋号・団体名',
    'type'         => 'text',
    'autocomplete' => 'organization',
  ],
  'email' => [
    'label'        => 'メールアドレス',
    'type'         => 'email',
    'required'     => true,
    'autocomplete' => 'email',
  ],
  'tel' => [
    'label'        => '電話番号',
    'type'         => 'tel',
    'autocomplete' => 'tel',
    'placeholder'  => 'お電話での連絡を希望される方',
  ],
  'kind' => [
    'label'   => 'ご相談の種類',
    'type'    => 'select',
    'options' => ['新しく作りたい', '作り直したい', '今のサイトの困りごと', 'その他'],
  ],
  'message' => [
    'label' => 'ご相談内容',
    'type'  => 'textarea',
  ],
  'source' => [
    'label'       => 'どちらでお知りになりましたか',
    'type'        => 'text',
    'placeholder' => '例: ○○さんの紹介、検索、SNSなど',
  ],
];
