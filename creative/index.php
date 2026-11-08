<?php
/**
 * Creative Crafts (/creative/) の一覧
 * 作品は content/creative/*.md (1 作品 = 1 ファイル). 作品の中身は, それぞれのリポジトリから /creative/xxx/ に置く
 * ※ このディレクトリには .htaccess (リライト) を置かない (作品のディレクトリを横取りしないように)
 */

require_once dirname(__DIR__) . '/inc/ContentPage.php';

ContentPage::render('creative');
