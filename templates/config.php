<?php
/**
 * サイトの設定
 * サイトごとに変わる値は, ここにまとめる
 */

// 日付の判定と表示のタイムゾーン
date_default_timezone_set('Asia/Tokyo');

// サイト名 (下層ページの title, ロゴの代わりの文字)
const SITE_NAME = 'Simple CMS';
// トップページの title (空なら SITE_NAME)
const SITE_TITLE = 'Simple CMS | Markdown で作る軽量なサイト';
// サイトの URL (末尾のスラッシュなし. canonical, OGP の絶対 URL と, お問い合わせフォームの送信元の確認に使う)
const SITE_URL = 'https://qwel.design';
// 区画のルートの URL (ドメイン直下なら /, サブディレクトリ配置の区画なら /small-site-studio/ のように)
const SITE_ROOT = '/';
// 共有ファイルの URL の基点 (サイト全体をサブディレクトリに置く場合は /tools/simple-cms/ のように)
const SITE_BASE = '/';
const SITE_LANG = 'ja';
// description の既定値
const SITE_DESCRIPTION = 'Markdown の原稿から、データベースなしでサイトを作るエンジンです。';
// Google Fonts などの CSS の URL (不要なら空文字. display=swap を付ける. 書体と太さを絞ると転送量が減る)
const SITE_FONTS_URL = 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500&family=Noto+Sans:wght@100..900&family=Noto+Serif:wght@100..900&display=swap';
// Google Analytics の測定 ID (不要なら空文字)
const SITE_GA_ID = '';
// OGP 画像の既定値 (サイトのルートからのパス. 不要なら空文字) と代替テキスト
const SITE_OGP_IMAGE = '';
const SITE_OGP_IMAGE_ALT = '';
// og:locale (不要なら空文字)
const SITE_OG_LOCALE = 'ja_JP';
// 一覧の画像の既定値 (記事に img がない場合. 不要なら空文字)
const SITE_THUMBNAIL_IMAGE = '';
// URL の形式 (true: /blog/xxx/, false: /blog/?slug=xxx). .htaccess が効かない php -S では false になる
const SITE_PRETTY_URLS = PHP_SAPI !== 'cli-server';

// ヘッダーのロゴ (空ならサイト名を文字で出す)
const SITE_LOGO = 'assets/logo.svg';
// ロゴのリンク先 (空ならトップへ)
const SITE_LOGO_LINK = '';
// ヘッダーのナビ (リンク先 => 表示名. リンク先はサイトのルートからのパス, #section, http から始まる URL)
// 例: ['blog/' => 'Blog', '#about' => 'About']
const SITE_NAV = [];
// フッターのリンク (形は SITE_NAV と同じ)
const SITE_FOOTER_LINKS = [];
// パンくずの先頭の階層 (path はサイトのルートからのパス, または http から始まる URL)
const SITE_BREADCRUMB = [
  ['label' => 'Top', 'path' => ''],
];
// フッターの SNS など (icon は assets/icons.svg の symbol の id. 不要なら空の配列)
const SITE_SNS = [
  ['label' => 'GitHub', 'url' => 'https://github.com/qweldesign', 'icon' => 'si-github'],
  ['label' => 'Contact Me', 'url' => 'contact/', 'icon' => 'si-mail']
];
// 投稿タイプ以外のページ (サイトのルートからのパス. サイトマップに載せる)
const SITE_PAGES = [
  'contact/',
];
// robots.txt でクロールを拒否するパス (サイトのルートからのパス. 例: 'private/'. 不要なら空の配列)
const SITE_ROBOTS_DISALLOW = [];
// 記事の著者 (構造化データの author. 不要なら空の配列. 例: ['@type' => 'Organization', 'name' => 'QWEL.DESIGN'])
const SITE_AUTHOR = [];
// 著作権表示 (開始年 - 今年 運営者)
const SITE_OWNER = 'QWEL.DESIGN';
const SITE_START_YEAR = 2019;

/**
 * 投稿タイプ
 * キー: 投稿タイプの名前. 入口のディレクトリ (投稿タイプの名前/index.php) に使う
 * label: 表示名 (一覧の見出し, パンくず)
 * path: 一覧の URL (サイトのルートからのパス. 省略するとキー + /. '' でルートに置く)
 * dir: 原稿のディレクトリ (共有 content/ からのパス. 省略すると content/キー)
 * count: 1 ページの件数 (既定で 10. null で全件)
 * showDate: 日付を出すか (既定で true)
 * lead: 一覧の見出しの下の文. 一覧の description にも使う
 * empty: 記事が 0 件のときの文 (既定で「まだ記事はありません。」)
 * layout: post (記事. 前後の記事を出す) または page (固定ページ. 既定で post)
 * items: false で個別のページを作らず, 一覧から原稿の url へ直接リンクする (既定で true)
 * sort: date (日付の新しい順) または order (原稿の order の小さい順. 既定で date)
 * schema: 個別記事の構造化データの種類 (schema.org の @type. 既定で BlogPosting. null で出さない)
 * list: false で一覧をサイトマップに載せない (既定で true)
 * breadcrumb: false でパンくずに投稿タイプの階層を出さない (一覧ページを持たないサイトなど. 既定で true)
 */
const SITE_POST_TYPES = [
  'blog' => [
    'label' => 'Blog',
  ],
  'page' => [
    'label'    => 'Pages',
    'path'     => '',
    'layout'   => 'page',
    'count'    => null,
    'showDate' => false,
    'schema'   => 'WebPage',
  ],
];

/**
 * お問い合わせフォーム
 * 宛先と SMTP の秘密の値は inc/.env に書く (inc/.env.example を参照)
 *
 * path: フォームの URL (サイトのルートからのパス. 同じディレクトリに index.php, confirm/, thanks/ を置く)
 * formUrl: 入力画面を別の場所に置くときの URL (例: #contact. 使わないなら空文字)
 * title: ページのタイトル
 * lead: 入力画面の上の文 (HTML を書ける)
 * interval: 同じ人 (セッション) が続けて送信できるまでの秒数 (0 で制限しない)
 * replyFooter: 自動返信メールの末尾に付ける文
 * env: .env の場所 (公開ディレクトリの外に置く場合は, このパスを書き換える)
 */
const SITE_CONTACT = [
  'path'        => 'contact/',
  'formUrl'     => '',
  'title'       => 'お問い合わせ',
  'lead'        => 'お気軽にお問い合わせください。',
  'interval'    => 60,
  'replyFooter' => '
後日ご返信いたしますので今しばらくお待ちください。

────────────────────────────────────────────────
福井の物作りのためのWeb制作&プログラミング教室
QWEL.DESIGN (クヴェル・デザイン)
────────────────────────────────────────────────
',
  'env'         => __DIR__ . '/../inc/.env',
];

/**
 * お問い合わせフォームの項目 (キーが name 属性. 並び順に表示する)
 * label: 項目名 (確認画面とメール本文にも使う)
 * type: text, email, tel, url, textarea, select, radio, checkbox, date (既定で text)
 *       (最初の email の項目を, 自動返信の宛先と管理者宛メールの返信先にする)
 * required: 必須か
 * autocomplete, placeholder: 入力欄の属性
 * options: select, radio, checkbox の選択肢
 * rows: textarea の高さ (既定で 8)
 */
const SITE_CONTACT_FIELDS = [
  'name' => [
    'label'        => 'お名前',
    'required'     => true,
    'autocomplete' => 'name',
  ],
  'email' => [
    'label'        => 'メールアドレス',
    'type'         => 'email',
    'required'     => true,
    'autocomplete' => 'email',
  ],
  'subject' => [
    'label'   => '件名',
    'type'    => 'select',
    'options' => ['ご質問・お問い合わせ', 'その他'],
  ],
  'message' => [
    'label'    => 'メッセージ本文',
    'type'     => 'textarea',
    'required' => true,
    'rows'     => 6,
  ],
];
