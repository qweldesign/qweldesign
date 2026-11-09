<?php
/**
 * ちいさなサイト工房のお問い合わせフォーム (入力画面: /small-site-studio/contact/)
 * 項目は本家と共通 (templates/common.php の SITE_CONTACT_FIELDS). 画面は templates/studio/contact.php
 */
require_once dirname(__DIR__, 2) . '/inc/bootstrap.php';
bootstrapSection('studio');
require_once sectionTemplate('contact.php');

renderContact('form');
