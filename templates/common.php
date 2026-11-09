<?php
/**
 * 本家 (QWEL in Action) と, ちいさなサイト工房で共通の設定
 * 区画の設定 (templates/config.php など) の最後に読む (お問い合わせの送信元に, 区画の SITE_NAME を使うため)
 * 区画によって変わらない値だけをここに書く. 区画のルート, 固定ページ, 投稿タイプは templates/sections.php
 */

date_default_timezone_set('Asia/Tokyo');

// サイトの URL (末尾のスラッシュなし. OGP, canonical の絶対 URL に使う)
const SITE_URL = 'https://qwel.design';
// リポジトリのルートの URL のパス (assets/, content/ など, 区画をまたいで共通のファイルの置き場所)
const SITE_BASE = '/';
const SITE_LANG = 'ja';
const SITE_OG_LOCALE = 'ja_JP';
const SITE_GA_ID = 'G-28NN8JBME5';
const SITE_PRETTY_URLS = PHP_SAPI !== 'cli-server';
// 構造化データの著者と, 著作権表示 (開始年 - 今年 運営者)
const SITE_AUTHOR = [
  '@type' => 'Person',
  'name'  => '伊藤 大悟',
  'url'   => 'https://qwel.design/',
];
const SITE_OWNER = 'QWEL.DESIGN';
const SITE_START_YEAR = 2019;

/**
 * お問い合わせフォーム (/contact/. Co-Pilot の #contact, ちいさなサイト工房にも同じ項目のフォームを置く)
 * SMTP のパスワードなど秘密の値は inc/.env に書く (inc/.env.example を参照)
 *
 * path: フォームの URL (同じ名前のディレクトリに index.php, confirm/, thanks/ を置く. 送信は api/send.php)
 * formUrl: 入力画面を別の場所に置く場合の URL (このサイトでは使わない)
 * title: ページのタイトル (<title> とパンくず)
 * heading, subheading: 入力画面の見出し (欧文) と, その下の和文 (このサイトだけのキー)
 * lead: 入力画面の見出しの下の説明文 (HTML 可. {reply_days} などは content/options.json の値に置き換える)
 * interval: 同じ人 (セッション) が続けて送信できるまでの秒数 (0 で制限しない)
 * replyFooter: 自動返信メールの末尾に付ける文 (本家と工房で共通)
 * env: .env の場所 (公開ディレクトリの外に置く場合は, このパスを書き換える)
 */
const SITE_CONTACT = [
  'path'        => 'contact/',
  'formUrl'     => '',
  'title'       => 'お問い合わせ',
  'heading'     => 'Contact',
  'subheading'  => 'ご相談・お問い合わせ',
  'lead'        => 'ご相談、お見積もり、ご感想など、お気軽にお寄せください。{reply_days}営業日以内にお返事します。',
  'interval'    => 60,
  'replyFooter' => "
内容を確認のうえ、あらためてご連絡いたします。今しばらくお待ちください。

────────────────────────────────────────────────
QWEL.DESIGN (クヴェル・デザイン)
Web制作・UI/UX開発 / ちいさなサイト工房
https://qwel.design/

伊藤 大悟 (代表)
────────────────────────────────────────────────
",
  'env'         => __DIR__ . '/../inc/.env',
];

/**
 * お問い合わせフォームの項目 (キーが name 属性. 並び順に表示する. 本家と工房で共通)
 * label: 項目名 (確認画面とメール本文にも使う)
 * type: text, email, tel, url, textarea, select, radio, checkbox (email の最初の項目を, 自動返信の宛先と管理者宛メールの返信先にする)
 * required: 必須か
 * options: select, radio, checkbox の選択肢
 * placeholder, autocomplete: 入力欄の属性 (select の placeholder は, 値が空の最初の選択肢になる)
 * hint, wide: 項目名に添える補足と, 入力欄を横いっぱいにするか (このサイトだけのキー. templates/contact.php)
 * value: hidden の値 (このサイトだけのキー. hidden は画面と確認画面に出さず, メールにだけ出る)
 * 項目の初期値は, フォームを置くページで渡せる (renderContactForm(['kind' => ...]))
 */
const SITE_CONTACT_FIELDS = [
  'name' => [
    'label'        => 'お名前',
    'type'         => 'text',
    'required'     => true,
    'autocomplete' => 'name',
  ],
  'company' => [
    'label'        => '会社名・屋号',
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
    'label'       => 'ご相談の種類',
    'type'        => 'text',
    'placeholder' => '例: サイトを新しく作りたい',
    'wide'        => true,
  ],
  'url' => [
    'label'       => 'サイトや資料の URL',
    'type'        => 'url',
    'hint'        => '今のサイト、Figma のリンクなど',
    'placeholder' => 'https://',
    'wide'        => true,
  ],
  'message' => [
    'label'       => 'ご相談内容',
    'type'        => 'textarea',
    'required'    => true,
    'placeholder' => 'ご予算や時期があればお書きください',
  ],
  'source' => [
    'label'       => 'どちらでお知りになりましたか',
    'type'        => 'text',
    'placeholder' => '例: ○○さんの紹介、検索、SNSなど',
    'wide'        => true,
  ],
  // 送信元の区画 (隠し項目. 区画の SITE_NAME が入り, メールの本文で本家と工房を見分ける)
  'section' => [
    'label' => '送信元',
    'type'  => 'hidden',
    'value' => SITE_NAME,
  ],
];
