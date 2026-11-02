<?php
/**
 * contact.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * お問い合わせフォームの3画面 (入力 → 確認 → 完了)
 * 入力内容は sessionStorage で確認画面へ渡し, 確認画面から api/send.php へ送る (js/contact-form.js)
 *
 * 使い方 (config.php の SITE_CONTACT の path と同じ名前のディレクトリ):
 *   index.php:         render_contact('form');
 *   confirm/index.php: render_contact('confirm');
 *   thanks/index.php:  render_contact('thanks');
 */

require_once dirname(__DIR__) . '/partials/site.php';
require_once dirname(__DIR__) . '/ContactForm.php';

// お問い合わせフォームの各画面の URL ($step: '' (入力), 'confirm/', 'thanks/')
function contact_url(string $step = ''): string {
  return path(SITE_CONTACT['path'] . $step);
}

/**
 * $step: form (入力), confirm (確認), thanks (完了)
 */
function render_contact(string $step): void {
  $title = SITE_CONTACT['title'];
  $stepTitles = ['confirm' => '入力内容の確認', 'thanks' => '送信完了'];
  $stepTitle = $stepTitles[$step] ?? null;

  render_header([
    'title'       => $stepTitle ? "{$stepTitle} | {$title}" : $title,
    'description' => strip_tags(SITE_CONTACT['lead'] ?? ''),
    'path'        => SITE_CONTACT['path'] . ($stepTitle ? $step . '/' : ''),
    'noindex'     => $stepTitle !== null,
  ]);

  // パンくず (… > お問い合わせ > 確認・完了)
  $breadcrumb = new ContentNavigation([
    'parents'     => array_map(fn($crumb) => ['label' => $crumb['label'], 'path' => path($crumb['path'])], SITE_BREADCRUMB),
    'subdirLabel' => $title,
    'subdirPath'  => contact_url(),
  ]);
?>
    <main id="main" class="main">
      <?= $stepTitle ? $breadcrumb->breadcrumb(['title' => $stepTitle]) : $breadcrumb->breadcrumb() ?>
      <h1 class="main__title"><?= e($stepTitle ?? $title) ?></h1>
<?php
  match ($step) {
    'confirm' => render_contact_confirm(),
    'thanks'  => render_contact_thanks(),
    default   => render_contact_form(),
  };
?>
    </main>
<?php
  render_footer();
}

// 入力画面
function render_contact_form(): void {
?>
<?php if (!empty(SITE_CONTACT['lead'])) { ?>
      <p class="main__lead"><?= SITE_CONTACT['lead'] ?></p>
<?php } ?>
      <form class="form" data-contact-form data-confirm-url="<?= e(contact_url('confirm/')) ?>">
        <table>
<?php foreach (SITE_CONTACT_FIELDS as $name => $field) { ?>
<?php render_contact_field($name, $field); ?>
<?php } ?>
        </table>
        <div class="form__honeypot" aria-hidden="true">
          <label for="contact-<?= ContactForm::HONEYPOT ?>">この欄は空のままにしてください</label>
          <input id="contact-<?= ContactForm::HONEYPOT ?>" type="text" name="<?= ContactForm::HONEYPOT ?>" tabindex="-1" autocomplete="off">
        </div>
        <input type="submit" value="確認">
      </form>
<?php
}

// 入力画面の1項目 (表の1行)
function render_contact_field(string $name, array $field): void {
  $type     = $field['type'] ?? 'text';
  $id       = 'contact-' . $name;
  $label    = $field['label'] ?? $name;
  $required = !empty($field['required']);
  $mark     = $required ? '<span class="is-required">*必須</span>' : '';
  // どれか1つの入力が必要な項目 (required_group)
  if (!$required && !empty($field['required_group'])) $mark = '<span class="is-required">*いずれか必須</span>';
  // 入力欄に付ける属性
  $attrs = ' name="' . e($name) . '"';
  if ($required && $type !== 'checkbox') $attrs .= ' required';
  if (!empty($field['autocomplete'])) $attrs .= ' autocomplete="' . e($field['autocomplete']) . '"';
  if (!empty($field['placeholder'])) $attrs .= ' placeholder="' . e($field['placeholder']) . '"';
?>
          <tr>
<?php if ($type === 'radio' || $type === 'checkbox') { ?>
            <th><span id="<?= e($id) ?>-label"><?= e($label) ?><?= $mark ?></span></th>
            <td>
              <ul class="form__choices" role="group" aria-labelledby="<?= e($id) ?>-label">
<?php foreach ($field['options'] ?? [] as $i => $option) { ?>
                <li><label><input type="<?= e($type) ?>"<?= $type === 'checkbox' ? ' name="' . e($name) . '"' : $attrs ?> value="<?= e($option) ?>"<?= $type === 'radio' && $i === 0 && $required ? ' checked' : '' ?>> <?= e($option) ?></label></li>
<?php } ?>
              </ul>
            </td>
<?php } else { ?>
            <th><label for="<?= e($id) ?>"><?= e($label) ?><?= $mark ?></label></th>
            <td>
<?php if ($type === 'textarea') { ?>
              <textarea id="<?= e($id) ?>"<?= $attrs ?> rows="8"></textarea>
<?php } elseif ($type === 'select') { ?>
              <select id="<?= e($id) ?>"<?= $attrs ?>>
<?php foreach ($field['options'] ?? [] as $option) { ?>
                <option value="<?= e($option) ?>"><?= e($option) ?></option>
<?php } ?>
              </select>
<?php } else { ?>
              <input id="<?= e($id) ?>" type="<?= e($type) ?>"<?= $attrs ?>>
<?php } ?>
            </td>
<?php } ?>
          </tr>
<?php
}

// 確認画面 (値は js/contact-form.js が sessionStorage から入れる)
function render_contact_confirm(): void {
?>
      <div class="confirm" data-contact-confirm data-form-url="<?= e(contact_url()) ?>" data-thanks-url="<?= e(contact_url('thanks/')) ?>" data-send-url="<?= e(path('api/send.php')) ?>">
        <div data-contact-filled hidden>
          <p class="main__lead">以下の内容で送信します。よろしければ「送信する」を押してください。</p>
          <table>
<?php foreach (SITE_CONTACT_FIELDS as $name => $field) { ?>
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
          <p><a href="<?= e(contact_url()) ?>"><?= e(SITE_CONTACT['title']) ?>へ</a></p>
        </div>
        <noscript><p>お問い合わせフォームのご利用には JavaScript を有効にしてください。</p></noscript>
      </div>
<?php
}

// 完了画面
function render_contact_thanks(): void {
?>
      <div class="thanks">
        <p>お問い合わせありがとうございました。
          <br>ご入力いただいたメールアドレスに、確認のメールをお送りしました。
          <br>内容を確認のうえ、3営業日以内にお返事します。
        </p>
        <p>確認のメールが届かない場合は、迷惑メールのフォルダもご確認ください。</p>
        <p>&nbsp;
        <p><a href="<?= e(path()) ?>">「ちいさなサイト工房」のトップへ戻る</a></p>
      </div>
<?php
}
