<?php
/**
 * 本家 (QWEL in Action) の区画設定
 */

date_default_timezone_set('Asia/Tokyo');

const SITE_NAME = 'QWEL in Action';
const SITE_TITLE = 'QWEL in Action | 福井・越前海岸から、フルスクラッチのWeb開発';
const SITE_TOP_TITLE = 'QWEL in Action | 福井・越前海岸から、フルスクラッチのWeb開発';
const SITE_URL = 'https://qwel.design';
const SITE_ROOT = '/';
const SITE_BASE = '/';
const SITE_LANG = 'ja';
const SITE_DESCRIPTION = '福井市の越前海岸を拠点にする QWEL.DESIGN (伊藤大悟) のポートフォリオです。地域の活動に加わりながら、WordPress のオリジナルテーマ開発や、JavaScript によるフルスクラッチの UI/UX 実装を手掛けています。';
const SITE_FONTS_URL = 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;1,500&family=Noto+Sans:wght@100..900&family=Noto+Serif:wght@100..900&display=swap';
const SITE_GA_ID = 'G-28NN8JBME5';
const SITE_OGP_IMAGE = 'assets/ogp.jpg';
const SITE_OGP_IMAGE_ALT = 'QWEL in Action ― 福井・越前海岸から、フルスクラッチのWeb開発';
const SITE_OG_LOCALE = 'ja_JP';
const SITE_THUMBNAIL_IMAGE = 'assets/ogp.jpg';
const SITE_PRETTY_URLS = PHP_SAPI !== 'cli-server';
const SITE_STYLESHEET = 'style.css';
const SITE_LOGO = 'assets/logo.svg';
const SITE_LOGO_NIGHT = 'assets/logo_night.svg';
const SITE_LOGO_LINK = '';
const SITE_NAV = [
  '#about'    => 'About QWEL',
  '#works'    => 'Works & Community',
  '#profile'  => 'Profile',
  '#creative' => 'Tools & Crafts',
  '#story'    => 'A Little Story',
];
const SITE_FOOTER_LINKS = [
  'small-site-studio/' => 'ちいさなサイト工房',
  'co-pilot/'          => '制作会社・広告代理店の方へ',
];
const SITE_SNS = [
  ['label' => 'note', 'url' => 'https://note.com/qweldesign', 'icon' => 'si-journal'],
  ['label' => 'GitHub', 'url' => 'https://github.com/qweldesign', 'icon' => 'si-github'],
  ['label' => 'Contact Me', 'url' => 'contact/', 'icon' => 'si-mail'],
];
const SITE_BREADCRUMB = [
  ['label' => 'QWEL in Action', 'path' => ''],
];
const SITE_PAGES = [
  'co-pilot/',
  'contact/',
  'creative/',
  'tools/',
];
const SITE_ROBOTS_DISALLOW = [];
const SITE_AUTHOR = [
  '@type' => 'Person',
  'name'  => '伊藤 大悟',
  'url'   => 'https://qwel.design/',
];
const SITE_OWNER = 'QWEL.DESIGN';
const SITE_START_YEAR = 2019;
const SITE_POST_TYPES = [
  'creative' => [
    'label'    => 'Creative Crafts',
    'path'     => 'creative/',
    'dir'      => 'content/creative',
    'items'    => false,
    'count'    => null,
    'showDate' => false,
    'sort'     => 'order',
  ],
  'tools' => [
    'label'    => 'Creative Tools',
    'path'     => 'tools/',
    'dir'      => 'content/tools',
    'items'    => false,
    'count'    => null,
    'showDate' => false,
    'sort'     => 'order',
  ],
];

const SITE_CONTACT = [
  'path'        => 'contact/',
  'formUrl'     => '',
  'title'       => 'お問い合わせ',
  'lead'        => 'お気軽にお問い合わせください。',
  'interval'    => 60,
  'replyFooter' => '',
  'env'         => __DIR__ . '/../inc/.env',
];
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
