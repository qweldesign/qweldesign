/**
 * Modal
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 */

export default class Modal {
  constructor(options = {}) {
    // 特定のBP未満のビューポートでは発火しない
    const breakpoint = options.breakpoint || 900;
    if (window.innerWidth < breakpoint) return;

    // ギャラリー要素を格納
    this.gallery = options.gallery || document.querySelector('[data-gallery]');
    if (!this.gallery) return;

    // 全てのアイテムを含むNodeListを配列として格納
    const items = options.items || this.gallery.querySelectorAll('[data-gallery-item] img');
    if (!items.length) return;
    this.items = Array.from(items);
    this.index = 0;

    // .modal: body末尾に挿入されるラッパー要素
    this.elem = document.createElement('div');
    this.elem.classList.add('modal', 'is-hidden'); // opacity: 0
    this.elem.setAttribute('aria-hidden', 'true'); // visibility: hidden
    document.body.appendChild(this.elem);

    // .modal__main: モーダル本体
    this.modal = document.createElement('div');
    this.modal.classList.add('modal__main');
    this.elem.appendChild(this.modal);

    // .modal__image: モーダル内の拡大表示する画像
    this.image = document.createElement('img');
    this.image.classList.add('modal__image', 'is-loaded');
    this.modal.appendChild(this.image);

    // .modal__overlay: オーバーレイ (クリック操作で閉じる)
    this.overlay = document.createElement('div');
    this.overlay.classList.add('modal__overlay');
    this.elem.appendChild(this.overlay);

    // .modal__close: 閉じるボタン
    this.close = document.createElement('div');
    this.close.classList.add('modal__close');
    const closeIcon = document.createElement('div');
    closeIcon.classList.add('icon', 'icon--close', 'icon--lg');
    const closeSpan = document.createElement('span');
    closeSpan.classList.add('icon__span');
    closeIcon.appendChild(closeSpan);
    this.close.appendChild(closeIcon);
    this.elem.appendChild(this.close);

    // .modal__prev: 前へボタン
    this.prev = document.createElement('div');
    this.prev.classList.add('modal__prev');
    const prevIcon = document.createElement('div');
    prevIcon.classList.add('icon', 'icon--chevron-left', 'icon--lg');
    const prevSpan = document.createElement('span');
    prevSpan.classList.add('icon__span');
    prevIcon.appendChild(prevSpan);
    this.prev.appendChild(prevIcon);
    this.elem.appendChild(this.prev);

    // .modal__next: 次へボタン
    this.next = document.createElement('div');
    this.next.classList.add('modal__next');
    const nextIcon = document.createElement('div');
    nextIcon.classList.add('icon', 'icon--chevron-right', 'icon--lg');
    const nextSpan = document.createElement('span');
    nextSpan.classList.add('icon__span');
    nextIcon.appendChild(nextSpan);
    this.next.appendChild(nextIcon);
    this.elem.appendChild(this.next);

    // 発火
    this.handleEvents();
  }

  handleEvents() {
    this.items.forEach((item, i) => {
      item.addEventListener('click', () => {
        this.show(i);
      });
    });

    this.overlay.addEventListener('click', () => this.hide());
    this.close.addEventListener('click', () => this.hide());
    this.prev.addEventListener('click', () => this.show(this.index - 1));
    this.next.addEventListener('click', () => this.show(this.index + 1));
  }

  show(i) {
    this.index = (i + this.items.length) % this.items.length;
    const item = this.items[this.index];
    const src = item.getAttribute('src');
    
    // モーダルを開く
    this.elem.classList.remove('is-hidden');
    this.elem.setAttribute('aria-hidden', 'false');

    // 画像切り替え
    if (this.image.getAttribute('src')) {
      this.transitionEnd(this.image, () => {
        this.image.classList.remove('is-loaded');
      }).then(() => {
        this.image.setAttribute('src', src);
        this.image.classList.add('is-loaded');
      });
    } else {
      // 初期
      this.image.setAttribute('src', src);
      this.image.classList.add('is-loaded');
    }
  }

  hide() {
    this.transitionEnd(this.elem, () => {
      this.elem.classList.add('is-hidden');
    }).then(() => {
      this.elem.setAttribute('aria-hidden', 'true');
    });
  }

  transitionEnd(elem, func) {
    // CSS遷移の完了を監視
    let callback;
    const promise = new Promise((resolve, reject) => {
      callback = () => resolve(elem);
      elem.addEventListener('transitionend', callback);
    });
    func();
    promise.then((elem) => {
      elem.removeEventListener('transitionend', callback);
    });
    return promise;
  }
}
