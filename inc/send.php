<?php
/**
 * お問い合わせフォームの送信 (各区画の contact/confirm/ の確認画面から JSON で POST される)
 * 入口は区画ごとの api/send.php (その区画の設定を読んでから, これを読む)
 *   本家: /api/send.php → config.php の SITE_CONTACT, SITE_CONTACT_FIELDS
 * 成功: 204 / 入力の不備: 400 (errors) / 不正な送信元: 403 / 送信間隔の制限: 429 / 設定・送信の失敗: 500
 * 設定の不備や送信の失敗の内容は, サーバーのエラーログにだけ残す
 */
// PHP の部品と Composer のライブラリは inc/ に置く (inc/.htaccess で Web から読めない)
if (!defined('SITE_LAYOUT')) {
  require_once dirname(__DIR__) . '/config.php';
}
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ContactForm.php';

mb_language('Japanese');
mb_internal_encoding('UTF-8');
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

// レスポンスを返して終了する (入力の不備などは JSON で返す)
function respond(int $status, array $body = []): void {
  http_response_code($status);
  if ($body) echo json_encode($body, JSON_UNESCAPED_UNICODE);
  exit;
}

// URL の scheme://host[:port] (Origin, Referer の照合用)
function origin_of(string $url): string {
  $parts = parse_url($url);
  if (empty($parts['scheme']) || empty($parts['host'])) return '';
  return strtolower($parts['scheme'] . '://' . $parts['host']) . (isset($parts['port']) ? ':' . $parts['port'] : '');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Allow: POST');
  respond(405);
}

// 設定の読み込み (宛先・SMTP は inc/.env, それ以外は config.php の SITE_CONTACT, SITE_CONTACT_FIELDS)
// .env を公開ディレクトリの外に置く場合は、config.php の SITE_CONTACT の env を書き換える
$env_path      = SITE_CONTACT['env'];
$site_title    = SITE_NAME;
$site_url      = SITE_URL;
$mailFooter    = fill_options(SITE_CONTACT['reply_footer'] ?? '');
$send_interval = (int) (SITE_CONTACT['interval'] ?? 0);
$form          = new ContactForm(SITE_CONTACT_FIELDS);

// Originチェック & Refererチェック (CSRF対策)
// scheme://host[:port] が SITE_URL と完全に一致しない送信元は弾く
// (先頭一致だと https://qwel.design.example.com なども通ってしまうため)
$site_origin = origin_of($site_url);
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
// Originが不正 OR Refererが不正 → 弾く
if (
  ($origin !== '' && strtolower($origin) !== $site_origin) ||
  ($referer !== '' && origin_of($referer) !== $site_origin)
) {
  respond(403);
}

// データ取得
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
  respond(400);
}

// ボット対策: 見えない入力欄 (ハニーポット) に値があれば, 送ったふりをして破棄する
if ($form->is_bot($data)) {
  respond(204);
}

// 送信間隔の制限 (同じセッションから続けて送信できないようにする. 0 で制限しない)
if ($send_interval > 0) {
  session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure'   => strpos($site_url, 'https://') === 0,
  ]);
  $wait = $send_interval - (time() - (int) ($_SESSION['contact_sent_at'] ?? 0));
  if ($wait > 0) {
    respond(429, ['retryAfter' => $wait]);
  }
}

// バリデーション (項目は config.php の SITE_CONTACT_FIELDS)
['values' => $values, 'errors' => $errors] = $form->validate($data);
if ($errors) {
  respond(400, ['errors' => $errors]);
}

// メール送信 (管理者宛と自動返信. 自動返信の失敗はログだけ残して続行する)
try {
  $env = load_env($env_path);
  $form->send($values, $env, $site_title, $mailFooter);
} catch (Throwable $e) {
  // 設定ミスや管理者宛の送信失敗の内容はサーバーのログにだけ残す
  error_log('お問い合わせフォームの送信に失敗しました: ' . $e->getMessage());
  respond(500);
}

// 送信間隔の制限のため, 送信した時刻を記録する
if ($send_interval > 0) {
  $_SESSION['contact_sent_at'] = time();
}

respond(204);
