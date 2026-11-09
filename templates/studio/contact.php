<?php
/**
 * contact.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * ちいさなサイト工房のお問い合わせフォームの 3 画面 (入力 → 確認 → 完了). 本家のものは templates/contact.php
 * 項目は本家と共通 (templates/common.php の SITE_CONTACT_FIELDS). 送信は本家の api/send.php へ (送信元は隠し項目で分かる)
 * 入力内容は sessionStorage で確認画面へ渡す (js/contact-form.js)
 * templates/ より 1 段深いので, リポジトリのルートは dirname(__DIR__, 2)
 *
 * 使い方 (工房の区画を選んでから. SITE_CONTACT の path と同じ, small-site-studio/ 以下のディレクトリ):
 *   index.php:         renderContact('form');
 *   confirm/index.php: renderContact('confirm');
 *   thanks/index.php:  renderContact('thanks');
 * トップの #contact は renderContactForm() (確認・完了は /small-site-studio/contact/confirm/, thanks/ を共通で使う)
 */

require_once dirname(__DIR__, 2) . '/inc/bootstrap.php';
bootstrapSection('studio');
require_once dirname(__DIR__, 2) . '/inc/ContactForm.php';
require_once dirname(__DIR__, 2) . '/inc/ContentNavigation.php';
require_once sectionTemplate('navigation.php');

// 工房でのフォームの呼び名と, 入力画面の説明文 (本家の SITE_CONTACT の title, lead の代わり)
const STUDIO_CONTACT_TITLE = 'ご相談';
const STUDIO_CONTACT_LEAD = 'ご相談は無料です。{reply_days}営業日以内にお返事します。<br>お電話での連絡を希望される方は、電話番号もご記入ください。';

// 各画面の URL ($step: '' (入力), 'confirm/', 'thanks/')
function contactUrl(string $step = ''): string {
  return path(SITE_CONTACT['path'] . $step);
}

// 送信の入口 (本家と共通の api/send.php. 区画のルートではなく, リポジトリのルートから)
function contactSendUrl(): string {
  return rtrim(siteBase(), '/') . '/api/send.php';
}

// $step: form (入力), confirm (確認), thanks (完了)
function renderContact(string $step): void {
  $title = STUDIO_CONTACT_TITLE;
  $stepTitle = ['confirm' => '入力内容の確認', 'thanks' => '送信完了'][$step] ?? null;

  renderHeader([
    'title'       => $stepTitle ? "{$stepTitle} | {$title}" : $title,
    'description' => strip_tags(fillOptions(STUDIO_CONTACT_LEAD)),
    'path'        => SITE_CONTACT['path'] . ($stepTitle ? $step . '/' : ''),
    'noindex'     => $stepTitle !== null,
  ]);

  // パンくず (… > ご相談 > 確認・完了)
  $crumbs = [...SITE_BREADCRUMB, ['label' => $title, 'path' => SITE_CONTACT['path']]];
  if ($stepTitle) $crumbs[] = ['label' => $stepTitle];
?>
    <main id="main" class="main">
<?php renderBreadcrumb(ContentNavigation::breadcrumbs($crumbs)); ?>
      <h1 class="main__title"><?= e($stepTitle ?? $title) ?></h1>
<?php
  match ($step) {
    'confirm' => renderContactConfirm(),
    'thanks'  => renderContactThanks(),
    default   => renderContactPage(),
  };
?>
    </main>
<?php
  renderFooter();
}

// 入力画面
function renderContactPage(): void {
  renderContactForm();
}

/**
 * 説明文と入力のフォーム (/small-site-studio/contact/ と, トップの #contact で共通)
 * $defaults: 項目の初期値 ([name => 値])
 */
function renderContactForm(array $defaults = []): void {
  $honeypot = ContactForm::HONEYPOT;
  $hidden = array_filter(SITE_CONTACT_FIELDS, fn(array $field): bool => ($field['type'] ?? 'text') === 'hidden');
?>
      <p class="main__lead"><?= fillOptions(STUDIO_CONTACT_LEAD) ?></p>
      <form class="form" data-contact-form data-confirm-url="<?= e(contactUrl('confirm/')) ?>">
        <table>
<?php foreach (array_diff_key(SITE_CONTACT_FIELDS, $hidden) as $name => $field) { ?>
<?php renderContactField($name, $field, $defaults[$name] ?? null); ?>
<?php } ?>
        </table>
<?php foreach ($hidden as $name => $field) { ?>
        <input type="hidden" name="<?= e($name) ?>" value="<?= e($defaults[$name] ?? (string)($field['value'] ?? '')) ?>">
<?php } ?>
        <div class="form__honeypot" aria-hidden="true">
          <label for="contact-<?= e($honeypot) ?>">この欄は空のままにしてください</label>
          <input id="contact-<?= e($honeypot) ?>" type="text" name="<?= e($honeypot) ?>" tabindex="-1" autocomplete="off" data-contact-honeypot>
        </div>
        <input type="submit" value="確認">
      </form>
<?php
}

// 入力画面の 1 項目 (表の 1 行)
// 補足 (hint) は項目名に添えず, 入力欄の placeholder を「例: 補足」にする (ほかの項目の「例: …」とそろえ, 項目名の列を短く保つ)
function renderContactField(string $name, array $field, ?string $default = null): void {
  $type = $field['type'] ?? 'text';
  $id = 'contact-' . $name;
  $label = $field['label'] ?? $name;
  $required = !empty($field['required']);
  $mark = $required ? '<span class="is-required">*必須</span>' : '';
  $placeholder = !empty($field['hint']) ? '例: ' . $field['hint'] : ($field['placeholder'] ?? '');
  // 入力欄に付ける属性
  $attrs = ' name="' . e($name) . '"';
  if ($required && $type !== 'checkbox') $attrs .= ' required';
  if (!empty($field['autocomplete'])) $attrs .= ' autocomplete="' . e($field['autocomplete']) . '"';
  if ($placeholder !== '' && $type !== 'select') $attrs .= ' placeholder="' . e($placeholder) . '"';
?>
          <tr>
<?php if ($type === 'radio' || $type === 'checkbox') { ?>
            <th><span id="<?= e($id) ?>-label"><?= e($label) ?><?= $mark ?></span></th>
            <td>
              <ul class="form__choices" role="group" aria-labelledby="<?= e($id) ?>-label">
<?php foreach ($field['options'] ?? [] as $i => $option) { ?>
<?php $checked = $default !== null ? $option === $default : $type === 'radio' && $i === 0 && $required; ?>
                <li><label><input type="<?= e($type) ?>"<?= $type === 'checkbox' ? ' name="' . e($name) . '"' : $attrs ?> value="<?= e($option) ?>"<?= $checked ? ' checked' : '' ?>> <?= e($option) ?></label></li>
<?php } ?>
              </ul>
            </td>
<?php } else { ?>
            <th><label for="<?= e($id) ?>"><?= e($label) ?><?= $mark ?></label></th>
            <td>
<?php if ($type === 'textarea') { ?>
              <textarea id="<?= e($id) ?>"<?= $attrs ?> rows="8"><?= e($default ?? '') ?></textarea>
<?php } elseif ($type === 'select') { ?>
              <select id="<?= e($id) ?>"<?= $attrs ?>>
<?php if (!empty($field['placeholder'])) { ?>
                <option value=""<?= $default === null ? ' selected' : '' ?>><?= e($field['placeholder']) ?></option>
<?php } ?>
<?php foreach ($field['options'] ?? [] as $option) { ?>
                <option value="<?= e($option) ?>"<?= $option === $default ? ' selected' : '' ?>><?= e($option) ?></option>
<?php } ?>
              </select>
<?php } else { ?>
              <input id="<?= e($id) ?>" type="<?= e($type) ?>"<?= $attrs ?><?= $default !== null ? ' value="' . e($default) . '"' : '' ?>>
<?php } ?>
            </td>
<?php } ?>
          </tr>
<?php
}

// 確認画面 (値は js/contact-form.js が sessionStorage から入れる. 隠し項目は出さない)
function renderContactConfirm(): void {
?>
      <div class="confirm" data-contact-confirm data-form-url="<?= e(contactUrl()) ?>" data-thanks-url="<?= e(contactUrl('thanks/')) ?>" data-send-url="<?= e(contactSendUrl()) ?>">
        <div data-contact-filled hidden>
          <p class="main__lead">以下の内容で送信します。よろしければ「送信する」を押してください。</p>
          <table>
<?php foreach (SITE_CONTACT_FIELDS as $name => $field) { ?>
<?php if (($field['type'] ?? 'text') === 'hidden') continue; ?>
            <tr>
              <th><?= e($field['label'] ?? $name) ?></th>
              <td data-contact-value="<?= e($name) ?>"></td>
            </tr>
<?php } ?>
          </table>
          <p class="confirm__message" data-contact-message role="alert" hidden></p>
          <input type="submit" value="送信する" data-contact-send>
          <input type="button" value="入力画面に戻る" data-contact-back>
        </div>
        <div data-contact-empty hidden>
          <p>入力内容が見つかりませんでした。お手数ですが、もう一度ご入力ください。</p>
          <p><a href="<?= e(contactUrl()) ?>"><?= e(STUDIO_CONTACT_TITLE) ?>へ</a></p>
        </div>
        <noscript><p>お問い合わせフォームのご利用には JavaScript を有効にしてください。</p></noscript>
      </div>
<?php
}

// 完了画面
function renderContactThanks(): void {
?>
      <div class="thanks">
        <p>お問い合わせありがとうございました。
          <br>ご入力いただいたメールアドレスに、確認のメールをお送りしました。
          <br>内容を確認のうえ、<?= e(fillOptions('{reply_days}')) ?>営業日以内にお返事します。
        </p>
        <p>確認のメールが届かない場合は、迷惑メールのフォルダもご確認ください。</p>
        <p>&nbsp;</p>
        <p><a href="<?= e(path()) ?>">「ちいさなサイト工房」のトップへ戻る</a></p>
      </div>
<?php
}
