/**
 * Reveal On Scroll
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 */

export default class RevealOnScroll {
  constructor(options = {}) {
    this.selector = options.selector || '[data-reveal]';
    const threshold = options.threshold ?? 0.15;
    const rootMargin = options.rootMargin || '0px 0px -12% 0px';
    this.inviewClass = options.inviewClass || 'is-inview';
    this.onceDefault = options.onceDefault ?? true; // 一度きりを既定にする
    const waitReady = options.waitReady ?? true; // DOM未準備なら待ってから起動
    this._observed = new Set();

    this.io = new IntersectionObserver(this._onIntersect.bind(this), {
      threshold, rootMargin
    });

    // DOM未準備なら待ってから発火
    if (waitReady && document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.init(), { once: true });
    } else {
      this.init();
    }
  }

  // 要素収集・監視開始
  init() {
    this.refresh(); // 一度スキャンして監視へ
  }

  // DOMを再スキャンして未監視の要素だけ追加観測
  refresh() {
    const nodes = document.querySelectorAll(this.selector);
    nodes.forEach(elem => {
      if (this._observed.has(elem)) return;

      // data-reveal-delay を inline style へ反映
      const delay = elem.getAttribute('data-reveal-delay');
      if (delay) elem.style.transitionDelay = `${parseInt(delay, 10)}ms`;

      this.io.observe(elem);
      this._observed.add(elem);
    });
  }

  _onIntersect(entries) {
    for (const entry of entries) {
      const elem = entry.target;
      const onceAttr = elem.getAttribute('data-reveal-once');
      const once = onceAttr != null ? onceAttr !== 'false' : this.onceDefault;

      if (entry.isIntersecting) {
        elem.classList.add(this.inviewClass);
        if (once) {
          this.io.unobserve(elem);
          this._observed.delete(elem);
        }
      } else if (!once) {
        // 画面外に出た時に戻したい場合
        elem.classList.remove(this.inviewClass);
      }
    }
  }

  // 監視解除・破棄
  destroy() {
    this.io.disconnect();
    this._observed.clear();
  }
}
