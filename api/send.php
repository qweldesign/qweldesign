<?php
/**
 * お問い合わせフォームの送信の入口 (本家: /contact/ と /co-pilot/#contact)
 * 本家の設定を読んでから, 共通の処理 (inc/send.php) へ
 */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/inc/send.php';
