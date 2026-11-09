<?php
/**
 * お問い合わせフォームの送信 (確認画面から JSON で POST される)
 * 成功: 204 / 入力の不備: 400 (errors) / 不正な送信元: 403 / 送信間隔の制限: 429 / 設定・送信の失敗: 500
 * 設定の不備や送信の失敗の内容は, サーバーのエラーログにだけ残す
 */

require_once dirname(__DIR__) . '/inc/bootstrap.php';
bootstrapSection();
require_once dirname(__DIR__) . '/inc/vendor/autoload.php';
require_once dirname(__DIR__) . '/inc/env.php';
require_once dirname(__DIR__) . '/inc/ContactForm.php';

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
function originOf(string $url): string {
  $parts = parse_url($url);
  if (empty($parts['scheme']) || empty($parts['host'])) return '';
  return strtolower($parts['scheme'] . '://' . $parts['host']) . (isset($parts['port']) ? ':' . $parts['port'] : '');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Allow: POST');
  respond(405);
}

// scheme://host[:port] が SITE_URL と完全に一致しない送信元は弾く
// (先頭一致だと https://example.com.evil.test なども通ってしまうため)
$siteOrigin = originOf(SITE_URL);
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (($origin !== '' && strtolower($origin) !== $siteOrigin) || ($referer !== '' && originOf($referer) !== $siteOrigin)) {
  respond(403);
}

$data = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($data)) respond(400);

$form = new ContactForm(SITE_CONTACT_FIELDS);

// 見えない入力欄に値があれば, 送ったふりをして破棄する
if ($form->isBot($data)) respond(204);

// 同じセッションから続けて送信できないようにする (0 で制限しない)
$interval = (int)SITE_CONTACT['interval'];
if ($interval > 0) {
  session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure'   => str_starts_with(SITE_URL, 'https://'),
  ]);
  $wait = $interval - (time() - (int)($_SESSION['contactSentAt'] ?? 0));
  if ($wait > 0) respond(429, ['retryAfter' => $wait]);
}

['values' => $values, 'errors' => $errors] = $form->validate($data);
if ($errors) respond(400, ['errors' => $errors]);

try {
  $form->send($values, loadEnv(SITE_CONTACT['env']), SITE_NAME, SITE_CONTACT['replyFooter']);
} catch (Throwable $error) {
  error_log('お問い合わせフォームの送信に失敗しました: ' . $error->getMessage());
  respond(500);
}

if ($interval > 0) $_SESSION['contactSentAt'] = time();

respond(204);
