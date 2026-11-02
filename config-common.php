<?php
/**
 * 共通の設定 (本家 config.php と, ちいさなサイト工房 studio-config.php の両方が読む)
 * 区画 (本家・工房) によって変わらない値だけをここに書く
 *
 * 区画の設定は「1回のリクエストで1つだけ」読む (PHP の定数は上書きできないため)
 *   本家のページ:  config.php        (inc/bootstrap.php が規定で読む)
 *   工房のページ:  studio-config.php (ページの先頭で読んでから inc/bootstrap.php を読む)
 */

// 日付の判定・表示のタイムゾーン
date_default_timezone_set('Asia/Tokyo');

// サイトの URL (末尾のスラッシュなし. OGP, canonical の絶対URLに使う)
const SITE_URL = 'https://qwel.design';
// リポジトリのルートの URL のパス (assets/, content/ など, 区画をまたいで共通のファイルの置き場所)
// 区画のルート (SITE_ROOT) とは別. assets/, content/ から始まるパスは, path() がここから作る
const SITE_BASE = '/';
// 言語
const SITE_LANG = 'ja';
const SITE_OG_LOCALE = 'ja_JP';
// Google アナリティクス (不要なら空文字)
const SITE_GA_ID = 'G-28NN8JBME5';
// 運営者 (著作権表示)
const SITE_OWNER = 'QWEL.DESIGN';
// URL の形式 (false: ?slug=xxx, ?page=2 / true: xxx/, page/2/ ※ .htaccess のリライトが必要)
const SITE_PRETTY_URLS = true;
