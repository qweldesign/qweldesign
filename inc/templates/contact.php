<?php
/**
 * contact.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * お問い合わせフォームの3画面 (入力 → 確認 → 完了). 夜間飛行のテーマ (scss/_contact.scss) で組む
 * 入力内容は sessionStorage で確認画面へ渡し, 確認画面から api/send.php へ送る (js/contact-form.js)
 *
 * 使い方 (config.php の SITE_CONTACT の path と同じ名前のディレクトリ):
 *   index.php:         render_contact('form');
 *   confirm/index.php: render_contact('confirm');
 *   thanks/index.php:  render_contact('thanks');
 * ほかのページにフォームだけを置く場合 (Co-Pilot の #contact など):
 *   require_once .../inc/templates/contact.php; のうえで render_contact_form();
 *   (確認・完了は /contact/confirm/, /contact/thanks/ を共通で使う. 確認画面から戻ると, 入力を始めたページへ戻る)
 */

require_once dirname(__DIR__) . '/bootstrap.php';
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
    'description' => strip_tags(fill_options(SITE_CONTACT['lead'] ?? '')),
    'path'        => SITE_CONTACT['path'] . ($stepTitle ? $step . '/' : ''),
    'noindex'     => $stepTitle !== null,
  ]);
?>
    <main>
<?php
  match ($step) {
    'confirm' => render_contact_confirm(),
    'thanks'  => render_contact_thanks(),
    default   => render_contact_page(),
  };
?>
    </main>
<?php
  render_footer();
}

// 入力画面 (ファーストビュー + フォーム)
function render_contact_page(): void {
  $breadcrumb = new ContentNavigation([
    'parents'     => array_map(fn($crumb) => ['label' => $crumb['label'], 'path' => path($crumb['path'])], SITE_BREADCRUMB),
    'subdirLabel' => SITE_CONTACT['title'],
    'subdirPath'  => contact_url(),
  ]);
?>
      <div class="flightHero is-sub">
        <?= $breadcrumb->breadcrumb() ?>

        <h1 class="flightHero__title"><?= e(SITE_CONTACT['heading']) ?><span class="flightHero__mark" aria-hidden="true">✶</span></h1>
        <p class="flightHero__audience"><?= e(SITE_CONTACT['subheading']) ?></p>
<?php if (!empty(SITE_CONTACT['lead'])) { ?>
        <p class="flightHero__text"><?= fill_options(SITE_CONTACT['lead']) ?></p>
<?php } ?>
      </div>

      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="contact" class="section__content">
<?php render_contact_form(); ?>
            </div>
          </div>
        </div>
      </section>
<?php
}

// 入力のフォーム (/contact/ と, ほかのページの #contact で共通)
// name 属性 (SITE_CONTACT_FIELDS のキー) がそのまま送信の項目になり, label が確認画面とメールの項目名になる
function render_contact_form(): void {
?>
              <form class="contactForm" data-contact-form data-confirm-url="<?= e(contact_url('confirm/')) ?>">
<?php foreach (SITE_CONTACT_FIELDS as $name => $field) { ?>
<?php render_contact_field($name, $field); ?>
<?php } ?>
                <!-- ボット対策の見えない入力欄 (人は入力しないので, 値があれば送信を破棄する) -->
                <div class="contactForm__honeypot" aria-hidden="true">
                  <label for="contact-<?= ContactForm::HONEYPOT ?>">この欄は空のままにしてください</label>
                  <input id="contact-<?= ContactForm::HONEYPOT ?>" type="text" name="<?= ContactForm::HONEYPOT ?>" tabindex="-1" autocomplete="off">
                </div>
                <div class="contactForm__actions">
                  <button class="button" type="submit">入力内容を確認</button>
                </div>
              </form>
<?php
}

// 入力のフォームの1項目
// 必須の項目には「必須」, 任意の入力欄には「任意」(と補足) を添える. 選択肢の項目には印を付けない
function render_contact_field(string $name, array $field): void {
  $type     = $field['type'] ?? 'text';
  $id       = 'contact-' . $name;
  $label    = $field['label'] ?? $name;
  $required = !empty($field['required']);
  $isChoice = in_array($type, ['select', 'radio', 'checkbox'], true);
  $hint     = $field['hint'] ?? '';

  if ($required) {
    $mark = '<span class="contactForm__required">必須</span>';
  } elseif (!empty($field['required_group'])) {
    $mark = '<span class="contactForm__required">いずれか必須</span>';
  } elseif (!$isChoice) {
    $mark = '<span class="contactForm__optional">任意' . ($hint !== '' ? ' (' . e($hint) . ')' : '') . '</span>';
  } else {
    $mark = $hint !== '' ? '<span class="contactForm__optional">' . e($hint) . '</span>' : '';
  }
  $wide = $type === 'textarea' || !empty($field['wide']) ? ' is-wide' : '';

  // 入力欄に付ける属性
  $attrs = ' name="' . e($name) . '"';
  if ($required && $type !== 'checkbox') $attrs .= ' required';
  if (!empty($field['autocomplete'])) $attrs .= ' autocomplete="' . e($field['autocomplete']) . '"';
  if (!empty($field['placeholder'])) $attrs .= ' placeholder="' . e($field['placeholder']) . '"';
?>
<?php if ($type === 'radio' || $type === 'checkbox') { ?>
                <div class="contactForm__field<?= $wide ?>" role="group" aria-labelledby="<?= e($id) ?>-label">
                  <span id="<?= e($id) ?>-label" class="contactForm__label"><?= e($label) ?><?= $mark ?></span>
                  <ul class="contactForm__choices">
<?php foreach ($field['options'] ?? [] as $i => $option) { ?>
                    <li><label><input type="<?= e($type) ?>"<?= $type === 'checkbox' ? ' name="' . e($name) . '"' : $attrs ?> value="<?= e($option) ?>"<?= $type === 'radio' && $i === 0 && $required ? ' checked' : '' ?>> <?= e($option) ?></label></li>
<?php } ?>
                  </ul>
                </div>
<?php } else { ?>
                <label class="contactForm__field<?= $wide ?>">
                  <span class="contactForm__label"><?= e($label) ?><?= $mark ?></span>
<?php if ($type === 'textarea') { ?>
                  <textarea<?= $attrs ?> rows="6"></textarea>
<?php } elseif ($type === 'select') { ?>
                  <select<?= $attrs ?>>
<?php foreach ($field['options'] ?? [] as $option) { ?>
                    <option value="<?= e($option) ?>"><?= e($option) ?></option>
<?php } ?>
                  </select>
<?php } else { ?>
                  <input type="<?= e($type) ?>"<?= $attrs ?>>
<?php } ?>
                </label>
<?php } ?>
<?php
}

// 確認画面 (値は js/contact-form.js が sessionStorage から入れる)
function render_contact_confirm(): void {
?>
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="confirm" class="section__content" data-contact-confirm data-form-url="<?= e(contact_url()) ?>" data-thanks-url="<?= e(contact_url('thanks/')) ?>" data-send-url="<?= e(path('api/send.php')) ?>">
              <h1 class="section__heading">Confirm</h1>
              <p class="section__subHeading">入力内容の確認</p>
              <div data-contact-filled hidden>
                <p>以下の内容で送信します。よろしければ「送信する」を押してください。</p>
                <table class="contactConfirm">
<?php foreach (SITE_CONTACT_FIELDS as $name => $field) { ?>
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
                <p><a href="<?= e(contact_url()) ?>"><?= e(SITE_CONTACT['title']) ?>のフォームへ</a></p>
              </div>
              <noscript><p>お問い合わせフォームのご利用には JavaScript を有効にしてください。</p></noscript>
            </div>
          </div>
        </div>
      </section>
<?php
}

// 完了画面
function render_contact_thanks(): void {
?>
      <section class="section">
        <div class="section__inner">
          <div class="section__container">
            <div id="thanks" class="section__content">
              <h1 class="section__heading">Thank You</h1>
              <p class="section__subHeading">送信しました</p>
              <p>ご相談ありがとうございます。ご入力いただいたメールアドレスに、確認のメールをお送りしました。
                <br>内容を確認のうえ、<?= e(fill_options('{reply_days}')) ?>営業日以内にお返事します。</p>
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
