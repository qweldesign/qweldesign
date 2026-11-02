<?php
/**
 * サイトの設定 (qwel.design / QWEL in Action)
 * サイト名や URL, 投稿タイプ, お問い合わせの項目など, サイトごとに変わる値はここにまとめる
 * (inc/ 以下のエンジン・テンプレートには, サイト固有の値を書かない)
 *
 * このリポジトリが受け持つ URL: /, /co-pilot/, /contact/, /creative/ (一覧), /tools/ (一覧)
 * /small-site-studio/ (qwel-small-site-studio) と, /creative/ /tools/ 以下の各作品 (それぞれのリポジトリ) は別に置く
 */

// 日付の判定・表示のタイムゾーン
date_default_timezone_set('Asia/Tokyo');

// サイト名 (<title> の「ページ名 | サイト名」と og:site_name に使う)
const SITE_NAME = 'QWEL in Action';
// トップページの <title> (サイト名だけでなく, 何のサイトかまで伝える)
const SITE_TOP_TITLE = 'QWEL in Action | 福井・越前海岸から、フルスクラッチのWeb開発';
// サイトの URL (末尾のスラッシュなし. OGP, canonical の絶対URLに使う)
const SITE_URL = 'https://qwel.design';
// サイトのルートのパス (ドメイン直下なので '/')
const SITE_ROOT = '/';
// サイトの説明 (description の既定値)
const SITE_DESCRIPTION = '福井市の越前海岸を拠点にする QWEL.DESIGN (伊藤大悟) のポートフォリオです。地域の活動に加わりながら、WordPress のオリジナルテーマ開発や、JavaScript によるフルスクラッチの UI/UX 実装を手掛けています。';
// OGP 画像の既定値 (サイトのルートからのパス. 原稿は tools/ogp/)
const SITE_OGP_IMAGE = 'assets/ogp.jpg';
const SITE_OGP_IMAGE_ALT = 'QWEL in Action ― 福井・越前海岸から、フルスクラッチのWeb開発';
const SITE_OG_LOCALE = 'ja_JP';
// 記事一覧のサムネイルの既定値 (このサイトでは使わないが, エンジンが参照する)
const SITE_THUMBNAIL_IMAGE = 'assets/ogp.jpg';
// Google Fonts (欧文の見出し, 等幅, 明朝)
const SITE_FONTS_URL = 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;1,500&family=IBM+Plex+Mono&family=Noto+Sans:wght@100..900&family=Noto+Serif:wght@100..900&family=Shippori+Mincho:wght@500&display=swap';
// 言語
const SITE_LANG = 'ja';
// Google アナリティクス (不要なら空文字)
const SITE_GA_ID = 'G-28NN8JBME5';

// ヘッダーのロゴ (昼の便と夜の便. リンク先は空ならサイトのトップ)
const SITE_LOGO = 'assets/logo.svg';
const SITE_LOGO_NIGHT = 'assets/logo_night.svg';
const SITE_LOGO_LINK = '';

// ヘッダーのメニュー (サイトのルートからのパス => 表示名)
// トップの各セクションへ. トップと Co-Pilot は, ページの中のセクションへのメニューを各テンプレートで渡す
const SITE_NAV = [
  '#about'    => 'About QWEL',
  '#works'    => 'Works & Community',
  '#profile'  => 'Profile',
  '#creative' => 'Tools & Crafts',
  '#story'    => 'A Little Story',
];

// パンくずの先頭の階層 (path はサイトのルートからのパス, または http から始まる URL)
const SITE_BREADCRUMB = [
  ['label' => 'QWEL in Action', 'path' => ''],
];

// フッターの文字のリンク (サイトのルートからのパス => 表示名)
// 下層ページは本家へ戻る道. トップは2つのサービスへのリンクを index.php で渡す
const SITE_FOOTER_LINKS = [
  '' => 'QWEL in Action',
];

// フッターのアイコン (url は http から始まる URL, またはサイトのルートからのパス. icon は icons.svg の symbol の id)
// ちいさなサイト工房 (/small-site-studio/) と同じ並び (note, GitHub, Contact)
const SITE_SNS = [
  ['label' => 'note', 'url' => 'https://note.com/qweldesign', 'icon' => 'si-journal'],
  ['label' => 'GitHub', 'url' => 'https://github.com/qweldesign', 'icon' => 'si-github'],
  ['label' => 'Contact Me', 'url' => 'contact/', 'icon' => 'si-mail'],
];

// 著作権表示 (開始年 - 今年 運営者)
const SITE_OWNER = 'QWEL.DESIGN';
const SITE_START_YEAR = 2019;

/**
 * 投稿タイプ
 * キー: 投稿タイプの名前
 * label: 表示名 (一覧の見出し, パンくず)
 * path: 一覧の URL (サイトのルートからのパス)
 * dir: Markdown を置くディレクトリ (サイトのルートからのパス)
 * list: 一覧ページをサイトマップに載せるか (規定で true)
 * items: 個別のページを作るか (規定で true. false なら一覧から各作品の url へ直接リンクし, サイトマップにも載せない)
 * count: 一覧の1ページあたりの件数 (null で全件)
 * show_date: 日付を表示するか
 * lead: 一覧の見出しの下の文
 *
 * creative, tools は一覧だけ (items: false). 各作品の中身は, それぞれのリポジトリから /creative/xxx/, /tools/xxx/ に置く
 * そのため creative/, tools/ には投稿タイプ用の .htaccess (リライト) を置かない (作品のディレクトリを横取りしないように)
 */
const SITE_POST_TYPES = [
  'creative' => [
    'label'     => 'Creative Crafts',
    'path'      => 'creative/',
    'dir'       => 'content/creative',
    'items'     => false,
    'count'     => null,
    'show_date' => false,
    'lead'      => 'Vanilla JS でスクラッチした、アニメーションやインタラクティブな表現作品の一覧です。',
  ],
  'tools' => [
    'label'     => 'Creative Tools',
    'path'      => 'tools/',
    'dir'       => 'content/tools',
    'items'     => false,
    'count'     => null,
    'show_date' => false,
    'lead'      => 'PHP / JS でスクラッチした、実用的な機能モジュールの一覧です。',
  ],
];

// URL の形式 (false: ?slug=xxx, ?page=2 / true: xxx/, page/2/ ※ .htaccess のリライトが必要)
const SITE_PRETTY_URLS = true;

// 投稿タイプ以外のページ (サイトのルートからのパス. サイトマップに載せる)
const SITE_PAGES = [
  'co-pilot/',
  'contact/',
];

/**
 * お問い合わせフォーム (/contact/. Co-Pilot の #contact にも同じフォームを置く)
 * SMTP のパスワードなど秘密の値は inc/.env に書く (inc/.env.example を参照)
 *
 * path: フォームの URL (サイトのルートからのパス. 同じ名前のディレクトリに index.php, confirm/, thanks/ を置く. 送信は api/send.php)
 * title: ページのタイトル (<title> とパンくず)
 * heading, subheading: ページの見出し (欧文) と, その下の和文
 * lead: フォームの上の説明文 (HTML 可. {reply_days} などは content/options.json の値に置き換える)
 * interval: 同じ人 (セッション) が続けて送信できるまでの秒数 (0 で制限しない)
 * reply_footer: 自動返信メールの末尾に付ける文
 * env: .env の場所 (公開ディレクトリの外に置く場合は, このパスを書き換える)
 */
const SITE_CONTACT = [
  'path'         => 'contact/',
  'title'        => 'お問い合わせ',
  'heading'      => 'Contact',
  'subheading'   => 'ご相談・お見積もり',
  'lead'         => '資料の URL だけでも構いません。{reply_days}営業日以内にお返事します。',
  'interval'     => 60,
  'reply_footer' => "
内容を確認のうえ、あらためてご連絡いたします。今しばらくお待ちください。

────────────────────────────────────────────────
QWEL.DESIGN (クヴェル・デザイン)
WordPress・フロントエンド制作の委託
https://qwel.design/

伊藤 大悟 (代表)
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
 * hint: 項目名に添える補足 (任意. 例: Figma のリンクなど)
 * wide: 入力欄を横いっぱいにする (textarea は常に横いっぱい)
 */
const SITE_CONTACT_FIELDS = [
  'company' => [
    'label'        => '会社名',
    'type'         => 'text',
    'required'     => true,
    'autocomplete' => 'organization',
  ],
  'name' => [
    'label'        => 'ご担当者名',
    'type'         => 'text',
    'required'     => true,
    'autocomplete' => 'name',
  ],
  'email' => [
    'label'        => 'メールアドレス',
    'type'         => 'email',
    'required'     => true,
    'autocomplete' => 'email',
  ],
  'kind' => [
    'label'   => 'ご依頼の種類',
    'type'    => 'select',
    'options' => ['コーディング', 'WordPress', 'JavaScript・UI', '改修・保守', 'チームでの支援', 'その他'],
  ],
  'budget' => [
    'label' => 'ご予算の目安',
    'type'  => 'text',
  ],
  'deadline' => [
    'label' => 'ご希望の納期',
    'type'  => 'text',
  ],
  'url' => [
    'label'       => '資料の URL',
    'type'        => 'url',
    'hint'        => 'Figma のリンクなど',
    'placeholder' => 'https://',
    'wide'        => true,
  ],
  'message' => [
    'label'    => 'ご相談内容',
    'type'     => 'textarea',
    'required' => true,
  ],
];
