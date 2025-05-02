/**
 * Back To Top
 * このファイルは QWEL Project の一部です。
 * Part of the QWEL Project © QWEL.DESIGN 2025
 * Licensed under GPL v3 – see https://qwel.design/
 */

export default class BackToTop {
  // options
  // size: ボタンが出現する位置 (window.innerHeightの何倍か) を設定可能
  constructor(options = {}) {
    // ボタン生成
    this.btn = document.createElement('div');
    this.btn.classList.add('backToTop');
    const icon = document.createElement('div');
    icon.classList.add('icon', 'icon--chevron-up', 'icon--lg');
    icon.innerHTML = '<span class="icon__span"></span>';
    this.btn.appendChild(icon);

    // ボタン設置
    const body = document.body;
    body.appendChild(this.btn);

    // 状態管理
    this.isShown = false;
    this.size = options.size || 0;

    // イベント登録
    this.handleEvents();
  }

  backToTop() {
    window.scroll({ top: 0, behavior: 'smooth' });
  }

  handleEvents() {
    const myTouch = 'ontouchend' in document && window.innerWidth < 1024 ? 'touchend' : 'click';

    window.addEventListener('scroll', () => {
      if (!this.isShown && window.innerHeight * this.size < window.scrollY) {
        this.isShown = true;
        this.btn.classList.add('backToTop--active');
      } else if (this.isShown && window.innerHeight * this.size >= window.scrollY) {
        this.isShown = false;
        this.btn.classList.remove('backToTop--active');
      }
    });

    this.btn.addEventListener(myTouch, (event) => {
      event.preventDefault();
      this.backToTop();
    });
  }
}
