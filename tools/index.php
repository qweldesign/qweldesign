<?php
/**
 * Creative Tools (/tools/) の一覧
 * ツールは content/tools/*.md (1 ツール = 1 ファイル). ツールの中身は, それぞれのリポジトリから /tools/xxx/ に置く
 * ※ このディレクトリには .htaccess (リライト) を置かない (ツールのディレクトリを横取りしないように)
 */

require_once dirname(__DIR__) . '/inc/ContentPage.php';

ContentPage::render('tools');
