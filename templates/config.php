<?php
/**
 * 本家 (QWEL in Action) の区画設定
 * 本家と工房で共通の値は templates/common.php (この設定の最後に読む)
 */

const SITE_NAME = 'QWEL in Action';
const SITE_TITLE = 'QWEL in Action | 福井・越前海岸から、フルスクラッチのWeb開発';
const SITE_TOP_TITLE = 'QWEL in Action | 福井・越前海岸から、フルスクラッチのWeb開発';
const SITE_DESCRIPTION = '福井市の越前海岸を拠点にする QWEL.DESIGN (伊藤大悟) のポートフォリオです。地域の活動に加わりながら、WordPress のオリジナルテーマ開発や、JavaScript によるフルスクラッチの UI/UX 実装を手掛けています。';
const SITE_FONTS_URL = 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;1,500&display=swap';
const SITE_TEXT_FONTS = [];
const SITE_OGP_IMAGE = 'assets/ogp.jpg';
const SITE_OGP_IMAGE_ALT = 'QWEL in Action ― 福井・越前海岸から、フルスクラッチのWeb開発';
const SITE_THUMBNAIL_IMAGE = 'assets/ogp.jpg';
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
// フッターの文字のリンク (下層ページ). トップは index.php で 2 つのサービスへのリンクに替える
const SITE_FOOTER_LINKS = [
  '' => 'QWEL in Action',
];
const SITE_SNS = [
  ['label' => 'note', 'url' => 'https://note.com/qweldesign', 'icon' => 'si-journal'],
  ['label' => 'GitHub', 'url' => 'https://github.com/qweldesign', 'icon' => 'si-github'],
  ['label' => 'Contact Me', 'url' => 'contact/', 'icon' => 'si-mail'],
];
const SITE_BREADCRUMB = [
  ['label' => 'QWEL in Action', 'path' => ''],
];
const SITE_ROBOTS_DISALLOW = [];

// 本家と工房で共通の値 (URL, GA, 運営者, お問い合わせなど). フォームの送信元に SITE_NAME を使うので, 最後に読む
require_once __DIR__ . '/common.php';
