<?php
/**
 * ContactForm.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * お問い合わせフォームの項目の検証と, メールの送信
 * 項目は config.php の SITE_CONTACT_FIELDS, SMTP などの秘密の値は inc/.env から読む
 * メールの送信には PHPMailer を使う (inc/ で composer install を実行して導入する)
 */

require_once __DIR__ . '/env.php';

use PHPMailer\PHPMailer\PHPMailer;

class ContactForm {
  // ボット対策の見えない入力欄の name 属性 (人は入力しないので, 値があれば破棄する)
  public const HONEYPOT = 'website_url';

  // 1項目あたりの最大文字数 (textarea とそれ以外)
  protected const MAX_LENGTH_TEXTAREA = 5000;
  protected const MAX_LENGTH = 200;

  protected array $fields;

  public function __construct(array $fields) {
    $this->fields = $fields;
  }

  // 返信先 (最初の email 項目) の name 属性
  public function email_field(): ?string {
    foreach ($this->fields as $name => $field) {
      if (($field['type'] ?? 'text') === 'email') return $name;
    }
    return null;
  }

  // ボットの送信か (見えない入力欄に値がある)
  public function is_bot(array $data): bool {
    return trim((string)($data[self::HONEYPOT] ?? '')) !== '';
  }

  /**
   * 送信された値を検証し, 定義済みの項目だけを取り出す
   * 戻り値: ['values' => [name => 値], 'errors' => [name => メッセージ]]
   */
  public function validate(array $data): array {
    $values = [];
    $errors = [];

    foreach ($this->fields as $name => $field) {
      $type     = $field['type'] ?? 'text';
      $label    = $field['label'] ?? $name;
      $options  = $field['options'] ?? [];
      $required = !empty($field['required']);
      $raw      = $data[$name] ?? ($type === 'checkbox' ? [] : '');

      // チェックボックスは複数の値 (配列), それ以外は文字列
      if ($type === 'checkbox') {
        $value = array_values(array_filter(array_map('strval', (array)$raw), 'strlen'));
        if ($required && !$value) {
          $errors[$name] = "{$label}を選択してください";
        } elseif (array_diff($value, $options)) {
          $errors[$name] = "{$label}の選択肢が正しくありません";
        }
        $values[$name] = $value;
        continue;
      }

      if (!is_string($raw)) {
        $errors[$name] = "{$label}の値が正しくありません";
        continue;
      }
      $value = trim(str_replace(["\r\n", "\r"], "\n", $raw));
      // 1行の項目は改行を許さない (ヘッダインジェクション対策)
      if ($type !== 'textarea') $value = preg_replace('/[\n\t]+/', ' ', $value);

      if ($value === '') {
        if ($required) $errors[$name] = "{$label}を入力してください";
      } elseif (mb_strlen($value) > ($type === 'textarea' ? self::MAX_LENGTH_TEXTAREA : self::MAX_LENGTH)) {
        $errors[$name] = "{$label}が長すぎます";
      } elseif ($type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
        $errors[$name] = "{$label}の形式が正しくありません";
      } elseif ($type === 'url' && !preg_match('#^https?://#', $value)) {
        $errors[$name] = "{$label}の形式が正しくありません";
      } elseif (in_array($type, ['select', 'radio'], true) && !in_array($value, $options, true)) {
        $errors[$name] = "{$label}の選択肢が正しくありません";
      }
      $values[$name] = $value;
    }

    // どれか1つの入力が必要な項目のまとまり (required_group に同じ名前を書いた項目. 例: メールか電話番号)
    $groups = [];
    foreach ($this->fields as $name => $field) {
      if (!empty($field['required_group'])) $groups[$field['required_group']][] = $name;
    }
    foreach ($groups as $names) {
      // 形式の誤りなどで既にエラーがある場合は, そちらを優先する
      if (array_intersect_key($errors, array_flip($names))) continue;
      $filled = array_filter($names, fn($name) => ($values[$name] ?? '') !== '' && ($values[$name] ?? []) !== []);
      if ($filled) continue;
      $labels = array_map(fn($name) => $this->fields[$name]['label'] ?? $name, $names);
      $errors[$names[0]] = implode('か', $labels) . (count($names) > 2 ? 'のいずれか' : 'のどちらか') . 'を入力してください';
    }

    return ['values' => $values, 'errors' => $errors];
  }

  // メール本文 (項目名: 値)
  public function mail_body(array $values): string {
    $lines = [];
    foreach ($this->fields as $name => $field) {
      $value = $values[$name] ?? '';
      if (is_array($value)) $value = implode(', ', $value);
      $label = $field['label'] ?? $name;
      // 複数行の値は, 項目名の次の行から書く
      $lines[] = str_contains($value, "\n") ? "【{$label}】\n{$value}" : "【{$label}】{$value}";
    }
    return implode("\n\n", $lines) . "\n";
  }

  /**
   * 管理者宛と自動返信の2通を送る
   * 管理者宛に失敗したら例外を投げる. 自動返信の失敗はログに残すだけにする
   * $env: inc/.env の設定 (ADMIN_EMAIL, SMTP_HOST, SMTP_USER, SMTP_PASS, SMTP_PORT, SMTP_SECURE)
   */
  public function send(array $values, array $env, string $siteName, string $replyFooter = ''): void {
    $adminEmail = env_required($env, 'ADMIN_EMAIL');
    $smtp = [
      'host'   => env_required($env, 'SMTP_HOST'),
      'user'   => env_required($env, 'SMTP_USER'),
      'pass'   => env_required($env, 'SMTP_PASS'),
      'port'   => (int)($env['SMTP_PORT'] ?? 587),
      'secure' => strtolower($env['SMTP_SECURE'] ?? 'tls'),
    ];
    $emailField = $this->email_field();
    $userEmail  = $emailField ? ($values[$emailField] ?? '') : '';
    $body       = "以下の内容で受け付けました。\n\n" . $this->mail_body($values);

    // 管理者宛 (返信先はお問い合わせした人)
    $admin = $this->mailer($smtp);
    $admin->setFrom($adminEmail, $siteName);
    $admin->addAddress($adminEmail);
    if ($userEmail !== '') $admin->addReplyTo($userEmail);
    $admin->Subject = "{$siteName} からのお問い合わせ";
    $admin->Body    = $body;
    $admin->send();

    // 自動返信 (失敗してもお問い合わせ自体は受け付けているので, エラーにしない)
    if ($userEmail === '') return;
    try {
      $reply = $this->mailer($smtp);
      $reply->setFrom($adminEmail, $siteName);
      $reply->addAddress($userEmail);
      $reply->Subject = "{$siteName} へのお問い合わせありがとうございます";
      $reply->Body    = $body . $replyFooter;
      $reply->send();
    } catch (Throwable $e) {
      error_log('お問い合わせフォームの自動返信に失敗しました: ' . $e->getMessage() . ' 宛先: ' . $userEmail);
    }
  }

  // SMTP の設定を済ませた PHPMailer を作る
  // PHPMailer (Composer の inc/vendor/) は送信するときだけ読み込む (入力画面などは inc/vendor/ がなくても表示できる)
  protected function mailer(array $smtp): PHPMailer {
    $autoload = __DIR__ . '/vendor/autoload.php';
    if (!is_file($autoload)) {
      throw new RuntimeException('PHPMailer がありません. inc/ で composer install を実行してください');
    }
    require_once $autoload;

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host     = $smtp['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp['user'];
    $mail->Password = $smtp['pass'];
    if ($smtp['secure'] === 'none') {
      // 暗号化なし (ローカルのテスト用メールサーバー向け)
      $mail->SMTPSecure  = '';
      $mail->SMTPAutoTLS = false;
    } else {
      $mail->SMTPSecure = $smtp['secure'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    }
    $mail->Port    = $smtp['port'];
    $mail->CharSet = PHPMailer::CHARSET_UTF8;
    return $mail;
  }
}
