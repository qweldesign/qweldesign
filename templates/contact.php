<?php
/**
 * contact.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * お問い合わせフォームの 3 画面 (入力 → 確認 → 完了). 夜間飛行のテーマ (scss/_contact.scss) で組む
 * 項目は config.php の SITE_CONTACT_FIELDS から作る (本家と工房で共通の項目)
 * 入力内容は sessionStorage で確認画面へ渡し, 確認画面から api/send.php へ送る (js/contact-form.js)
 * このファイルはサイトが持つもの. デザインに合わせて書き換えてよい
 *
 * 使い方 (SITE_CONTACT の path と同じディレクトリ):
 *   index.php:         renderContact('form');
 *   confirm/index.php: renderContact('confirm');
 *   thanks/index.php:  renderContact('thanks');
 * ほかのページにフォームだけを置く場合 (Co-Pilot の #contact など):
 *   このファイルを読み込み, renderContactForm() を呼ぶ (引数で項目の初期値を渡せる)
 *   確認・完了は /contact/confirm/, /contact/thanks/ を共通で使う. 確認画面から戻ると, 送信を始めたページへ戻る
 */

require_once dirname(__DIR__) . '/inc/ContactForm.php';
require_once dirname(__DIR__) . '/inc/ContentNavigation.php';
require_once __DIR__ . '/navigation.php';

// 各画面の URL ($step: '' (入力), 'confirm/', 'thanks/'). 入力画面は, formUrl があればその URL
function contactUrl(string $step = ''): string {
  if ($step === '' && SITE_CONTACT['formUrl']) return path(SITE_CONTACT['formUrl']);
  return path(SITE_CONTACT['path'] . $step);
}

// $step: form (入力), confirm (確認), thanks (完了)
function renderContact(string $step): void {
  // 入力画面を別の場所に置いている場合は, そこへ転送する
  if ($step === 'form' && SITE_CONTACT['formUrl']) {
    header('Location: ' . contactUrl(), true, 301);
    exit;
  }

  $title = SITE_CONTACT['title'];
  $stepTitle = ['confirm' => '入力内容の確認', 'thanks' => '送信完了'][$step] ?? null;

  renderHeader([
    'title'       => $stepTitle ? "{$stepTitle} | {$title}" : $title,
    'description' => strip_tags(fillOptions(SITE_CONTACT['lead'])),
    'noindex'     => $stepTitle !== null,
    'path'        => SITE_CONTACT['path'] . ($stepTitle ? $step . '/' : ''),
  ]);
?>
    <main>
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

// 入力画面 (ファーストビュー + フォーム)
function renderContactPage(): void {
  $crumbs = ContentNavigation::breadcrumbs([...SITE_BREADCRUMB, ['label' => SITE_CONTACT['title']]]);
?>
      <div class="flightHero is-sub">
<?php renderBreadcrumb($crumbs); ?>
        <h1 class="flightHero__title"><?= e(SITE_CONTACT['heading']) ?><span class="flightHero__mark" aria-hidden="true">✶</span></h1>
        <p class="flightHero__audience"><?= e(SITE_CONTACT['subheading']) ?></p>
<?php if (SITE_CONTACT['lead']) { ?>
        <p class="flightHero__text"><?= fillOptions(SITE_CONTACT['lead']) ?></p>
<?php } ?>
      </div>

      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="contact" class="section__content">
<?php renderContactForm(); ?>
            </div>
          </div>
        </div>
      </section>
<?php
}

/**
 * 入力のフォーム (/contact/ と, ほかのページの #contact で共通)
 * name 属性 (SITE_CONTACT_FIELDS のキー) がそのまま送信の項目になり, label が確認画面とメールの項目名になる
 * $defaults: 項目の初期値 ([name => 値])
 */
function renderContactForm(array $defaults = []): void {
  $honeypot = ContactForm::HONEYPOT;
?>
              <form class="contactForm" data-contact-form data-confirm-url="<?= e(contactUrl('confirm/')) ?>">
<?php foreach (SITE_CONTACT_FIELDS as $name => $field) { ?>
<?php renderContactField($name, $field, $defaults[$name] ?? null); ?>
<?php } ?>
                <!-- ボット対策の見えない入力欄 (人は入力しないので, 値があれば送信を破棄する) -->
                <div class="contactForm__honeypot" aria-hidden="true">
                  <label for="contact-<?= e($honeypot) ?>">この欄は空のままにしてください</label>
                  <input id="contact-<?= e($honeypot) ?>" type="text" name="<?= e($honeypot) ?>" tabindex="-1" autocomplete="off" data-contact-honeypot>
                </div>
                <div class="contactForm__actions">
                  <button class="button" type="submit">入力内容を確認</button>
                </div>
              </form>
<?php
}

/**
 * 入力のフォームの 1 項目
 * 必須の項目には「必須」, 任意の入力欄には「任意」(と hint の補足) を添える. 選択肢の項目には印を付けない
 * このサイトだけのキー: hint (項目名に添える補足), wide (入力欄を横いっぱいにする. textarea は常に横いっぱい)
 * select の placeholder は, 値が空の最初の選択肢 (「選択してください」など) にする
 * hidden は value (このサイトだけのキー) か $default を送るだけで, 画面と確認画面には出さない (メールには出る)
 */
function renderContactField(string $name, array $field, ?string $default = null): void {
  $type = $field['type'] ?? 'text';
  if ($type === 'hidden') {
?>
                <input type="hidden" name="<?= e($name) ?>" value="<?= e($default ?? (string)($field['value'] ?? '')) ?>">
<?php
    return;
  }
  $id = 'contact-' . $name;
  $label = $field['label'] ?? $name;
  $required = !empty($field['required']);
  $isChoice = in_array($type, ['select', 'radio', 'checkbox'], true);
  $hint = $field['hint'] ?? '';

  if ($required) {
    $mark = '<span class="contactForm__required">必須</span>';
  } elseif (!$isChoice) {
    $mark = '<span class="contactForm__optional">任意' . ($hint !== '' ? ' (' . e($hint) . ')' : '') . '</span>';
  } else {
    $mark = $hint !== '' ? '<span class="contactForm__optional">' . e($hint) . '</span>' : '';
  }
  $wide = $type === 'textarea' || !empty($field['wide']) ? ' is-wide' : '';

  // 入力欄に付ける属性
  $attrs = ' id="' . e($id) . '" name="' . e($name) . '"';
  if ($required && $type !== 'checkbox') $attrs .= ' required';
  if (!empty($field['autocomplete'])) $attrs .= ' autocomplete="' . e($field['autocomplete']) . '"';
  if (!empty($field['placeholder']) && $type !== 'select') $attrs .= ' placeholder="' . e($field['placeholder']) . '"';
?>
<?php if ($type === 'radio' || $type === 'checkbox') { ?>
                <div class="contactForm__field<?= $wide ?>" role="group" aria-labelledby="<?= e($id) ?>-label">
                  <span id="<?= e($id) ?>-label" class="contactForm__label"><?= e($label) ?><?= $mark ?></span>
                  <ul class="contactForm__choices">
<?php foreach ($field['options'] ?? [] as $index => $option) { ?>
<?php $checked = $default !== null ? $option === $default : $type === 'radio' && $index === 0 && $required; ?>
                    <li><label><input type="<?= e($type) ?>" name="<?= e($name) ?>"<?= $required && $type === 'radio' ? ' required' : '' ?> value="<?= e($option) ?>"<?= $checked ? ' checked' : '' ?>> <?= e($option) ?></label></li>
<?php } ?>
                  </ul>
                </div>
<?php } else { ?>
                <label class="contactForm__field<?= $wide ?>" for="<?= e($id) ?>">
                  <span class="contactForm__label"><?= e($label) ?><?= $mark ?></span>
<?php if ($type === 'textarea') { ?>
                  <textarea<?= $attrs ?> rows="<?= (int)($field['rows'] ?? 6) ?>"><?= e($default ?? '') ?></textarea>
<?php } elseif ($type === 'select') { ?>
                  <select<?= $attrs ?>>
<?php if (!empty($field['placeholder'])) { ?>
                    <option value=""<?= $default === null ? ' selected' : '' ?>><?= e($field['placeholder']) ?></option>
<?php } ?>
<?php foreach ($field['options'] ?? [] as $option) { ?>
                    <option value="<?= e($option) ?>"<?= $option === $default ? ' selected' : '' ?>><?= e($option) ?></option>
<?php } ?>
                  </select>
<?php } else { ?>
                  <input type="<?= e($type) ?>"<?= $attrs ?><?= $default !== null ? ' value="' . e($default) . '"' : '' ?>>
<?php } ?>
                </label>
<?php } ?>
<?php
}

// 確認画面 (値は js/contact-form.js が sessionStorage から入れる)
function renderContactConfirm(): void {
?>
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="confirm" class="section__content" data-contact-confirm data-form-url="<?= e(contactUrl()) ?>" data-thanks-url="<?= e(contactUrl('thanks/')) ?>" data-send-url="<?= e(path('api/send.php')) ?>">
              <h1 class="section__heading">Confirm</h1>
              <p class="section__subHeading">入力内容の確認</p>
              <div data-contact-filled hidden>
                <p>以下の内容で送信します。よろしければ「送信する」を押してください。</p>
                <table class="contactConfirm">
<?php foreach (SITE_CONTACT_FIELDS as $name => $field) { ?>
<?php if (($field['type'] ?? 'text') === 'hidden') continue; ?>
                  <tr>
                    <th><?= e($field['label'] ?? $name) ?></th>
                    <td data-contact-value="<?= e($name) ?>"></td>
                  </tr>
<?php } ?>
                </table>
                <p class="contactMessage" data-contact-message role="alert" hidden></p>
                <div class="contactForm__actions">
                  <button class="button" type="button" data-contact-back>入力画面に戻る</button>
                  <button class="button" type="button" data-contact-send>送信する</button>
                </div>
              </div>
              <div data-contact-empty hidden>
                <p>入力内容が見つかりませんでした。お手数ですが、もう一度ご入力ください。</p>
                <p><a href="<?= e(contactUrl()) ?>"><?= e(SITE_CONTACT['title']) ?>のフォームへ</a></p>
              </div>
              <noscript><p>お問い合わせフォームのご利用には JavaScript を有効にしてください。</p></noscript>
            </div>
          </div>
        </div>
      </section>
<?php
}

// 完了画面
function renderContactThanks(): void {
?>
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="thanks" class="section__content">
              <h1 class="section__heading">Thank You</h1>
              <p class="section__subHeading">送信しました</p>
              <p>ご相談ありがとうございます。ご入力いただいたメールアドレスに、確認のメールをお送りしました。
                <br>内容を確認のうえ、<?= e(fillOptions('{reply_days}')) ?>営業日以内にお返事します。</p>
              <p class="contactNote">確認のメールが届かない場合は、迷惑メールのフォルダもご確認ください。</p>
              <div class="section__row py-medium">
                <div class="section__col">
                  <a class="button" href="<?= e(path()) ?>">QWEL in Action のトップへ</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
<?php
}
