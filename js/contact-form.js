/**
 * Contact Form
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 */

/**
 * お問い合わせフォーム (入力 → 確認 → 完了)
 * 入力内容は sessionStorage で確認画面へ渡し, 確認画面から送信する
 *
 * 入力画面: form[data-contact-form][data-confirm-url] (1つのサイトの中の複数のページに置ける)
 *   確認画面の「入力画面に戻る」は, 送信を始めたページ (のフォームの位置) へ戻る
 * 確認画面: [data-contact-confirm][data-form-url][data-thanks-url][data-send-url]
 *   値を入れる欄: [data-contact-value="name 属性"]
 *   ボタン: [data-contact-send], [data-contact-back]
 *   メッセージ: [data-contact-message]
 *   入力内容があるとき / ないときに表示する要素: [data-contact-filled], [data-contact-empty]
 */
export default class ContactForm {
  constructor() {
    this.storageKey = 'contact-form';
    // 入力を始めたページ (確認画面から戻る先. フォームを複数のページに置くため)
    this.originKey = 'contact-form-origin';

    this.form = document.querySelector('[data-contact-form]');
    if (this.form) this.formInit();

    this.confirm = document.querySelector('[data-contact-confirm]');
    if (this.confirm) this.confirmInit();
  }

  // sessionStorage の入力内容 (なければ null)
  load() {
    try {
      const data = sessionStorage.getItem(this.storageKey);
      return data ? JSON.parse(data) : null;
    } catch {
      return null;
    }
  }

  // 入力画面
  formInit() {
    // 確認画面から戻ってきたときは, 入力内容を戻す
    const values = this.load();
    if (values) this.restore(values);

    this.form.addEventListener('submit', (event) => {
      event.preventDefault();

      // 同じ name の値が複数ある (チェックボックス) 場合は配列にする
      const formData = new FormData(this.form);
      const data = {};
      for (const key of new Set(formData.keys())) {
        const items = formData.getAll(key);
        const isMultiple = this.form.querySelectorAll(`[type="checkbox"][name="${CSS.escape(key)}"]`).length > 0;
        data[key] = isMultiple ? items : items[0];
      }

      sessionStorage.setItem(this.storageKey, JSON.stringify(data));
      const anchor = this.form.closest('[id]')?.id;
      sessionStorage.setItem(this.originKey, location.pathname + (anchor ? '#' + anchor : ''));
      location.href = this.form.dataset.confirmUrl;
    });
  }

  restore(values) {
    for (const elem of this.form.elements) {
      if (!elem.name || !(elem.name in values)) continue;
      const value = values[elem.name];
      if (elem.type === 'checkbox') {
        elem.checked = [].concat(value).includes(elem.value);
      } else if (elem.type === 'radio') {
        elem.checked = elem.value === value;
      } else if (elem.type !== 'submit') {
        elem.value = value;
      }
    }
  }

  // 確認画面
  confirmInit() {
    const values = this.load();
    const filled = this.confirm.querySelector('[data-contact-filled]');
    const empty = this.confirm.querySelector('[data-contact-empty]');
    this.message = this.confirm.querySelector('[data-contact-message]');
    this.sendButton = this.confirm.querySelector('[data-contact-send]');

    if (!values) {
      if (empty) empty.hidden = false;
      return;
    }
    if (filled) filled.hidden = false;

    this.confirm.querySelectorAll('[data-contact-value]').forEach((cell) => {
      const value = values[cell.dataset.contactValue] ?? '';
      cell.textContent = Array.isArray(value) ? value.join(', ') : value;
    });

    // 戻るボタン (入力画面で sessionStorage から入力内容を戻す)
    const backButton = this.confirm.querySelector('[data-contact-back]');
    if (backButton) {
      backButton.addEventListener('click', () => {
        location.href = sessionStorage.getItem(this.originKey) || this.confirm.dataset.formUrl;
      });
    }

    if (this.sendButton) {
      this.sendButton.addEventListener('click', () => this.send(values));
    }
  }

  showMessage(text) {
    if (!this.message) return;
    this.message.textContent = text;
    this.message.hidden = false;
  }

  async send(values) {
    // 二重送信防止
    if (this.sendButton) this.sendButton.disabled = true;
    if (this.message) this.message.hidden = true;

    try {
      const res = await fetch(this.confirm.dataset.sendUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(values),
      });

      if (res.ok) {
        sessionStorage.removeItem(this.storageKey);
        location.href = this.confirm.dataset.thanksUrl;
        return;
      }

      const body = await res.json().catch(() => ({}));
      if (res.status === 400 && body.errors) {
        this.showMessage(Object.values(body.errors).join('\n') + '\n入力画面に戻って修正してください。');
      } else if (res.status === 429) {
        this.showMessage(`続けて送信することはできません。${body.retryAfter ?? 60}秒ほど待ってから、もう一度お試しください。`);
      } else {
        this.showMessage('送信に失敗しました。時間をおいて、もう一度お試しください。');
      }
    } catch (err) {
      console.error('Network error:', err);
      this.showMessage('通信に失敗しました。通信環境をご確認のうえ、もう一度お試しください。');
    }

    if (this.sendButton) this.sendButton.disabled = false;
  }
}
