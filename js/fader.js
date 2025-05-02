/**
 * Fader
 * このファイルは QWEL Project の一部です。
 * Part of the QWEL Project © QWEL.DESIGN 2025
 * Licensed under GPL v3 – see https://qwel.design/
 */

export default class Fader {
  // data属性によるパラメータ管理:
  // data-interval: スライドアニメーション時間間隔
  constructor(elem) {
    // Faderの各要素
    this.elem = elem || document.querySelector('.fader');
    if (!this.elem) return;
    this.inner = this.elem.querySelector('.cover__inner');
    if (!this.inner) return;
    this.items = this.inner.children;
    if (!this.items.length || this.items.length <= 1) return;

    // 表示間隔
    this.interval = this.elem.dataset.interval || 5000;

    // 各状態管理
    this.currentIndex = 0; // 2枚目から操作
    this.itemsCount = this.items.length;

    // ナビゲーションのセットアップ (暫定機能)
    this.hasNav = false;
    //this.setupNavs();

    // 2番目以降の要素を背面に移動し、フェードさせておく
    for (let i = 1; i < this.itemsCount; i++) {
      this.items[i].style.zIndex--;
      this.items[i].classList.add('cover__image--fade');
    }

    // 開始
    this.startInterval();
  }

  // 再生
  startInterval() {
    this.isPlay = true;
    this.timeStart = null;
    this.loop(performance.now());
  }

  // 停止
  stopInterval() {
    this.isPlay = false;
  }

  setupNavs() {
    this.hasNav = true;

    // .cover__nav
    this.nav = document.createElement('ul');
    this.nav.classList.add('cover__nav');

    // .cover__navItem
    for (let i = 0; i < this.itemsCount; i++) {
      const li = document.createElement('li');
      li.classList.add('cover__navItem');
      li.dataset.targetIndex = i; // data-target-indexを挿入
      this.nav.appendChild(li);
    }

    // 現アイテムに.--currrentを付与
    this.navItems = this.nav.children;
    this.navItems = this.nav.children;
    this.navItems[this.currentIndex].classList.add('cover__navItem--current');

    this.elem.appendChild(this.nav);

    this.handleEvents();
  }

  handleEvents() {
    // ナビゲーション操作
    const myTouch = 'ontouchend' in document && window.innerWidth < 1024 ? 'touchend' : 'click';
    this.nav.addEventListener(myTouch, (event) => {
      const target = event.target;
      if (target.dataset.targetIndex) {
        this.fade(target.dataset.targetIndex - 0); // 数値型へパース
        this.stopInterval();
      }
    });
  }

  loop(timeCurrent) {
    if (!this.timeStart) {
      this.timeStart = timeCurrent;
    }
    const timeElapsed = timeCurrent - this.timeStart;

    timeElapsed < this.interval
      ? window.requestAnimationFrame(this.loop.bind(this))
      : this.done();
  }

  done() {
    if (this.isPlay) {
      this.startInterval();
      this.fade((this.currentIndex + 1) % this.itemsCount);
    }
  }

  fade(index) {
    // prev(前面)とcurrent(背面)の要素をそれぞれ取得
    const prev = this.items[this.currentIndex];
    const current = this.items[index] || this.items[0];

    // 移動
    if (index > this.currentIndex) {
      // 正順の場合、前からindexまでの要素を前面へ移動
      for (let i = index; i > this.currentIndex; i--) this.items[i].style.zIndex++;
    } else {
      // 逆順の場合、後ろからindexまでの要素を背面へ移動
      for (let i = this.currentIndex; i > index; i--) this.items[i].style.zIndex--;
    }

    // アニメーション
    this.transitionEnd(current, () => {
      // フェードイン
      current.classList.remove('cover__image--fade');
    }).then(() => {
      // トランジションが終了したら、前の要素をフェードさせておく
      prev.classList.add('cover__image--fade');
    });

    // インデックスを継承
    this.currentIndex = index;

    // ナビゲーション
    if (this.hasNav) {
      if (this.nav.querySelector('.cover__navItem--current')) {
        this.nav.querySelector('.cover__navItem--current').classList.remove('cover__navItem--current');
      }
      this.navItems = this.nav.children;
      this.navItems[this.currentIndex].classList.add('cover__navItem--current');
    }
  }

  transitionEnd(elem, func) {
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
