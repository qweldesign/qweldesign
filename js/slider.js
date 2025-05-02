/**
 * Slider
 * このファイルは QWEL Project の一部です。
 * Part of the QWEL Project © QWEL.DESIGN 2025
 * Licensed under GPL v3 – see https://qwel.design/
 */

export default class Slider {
  // data属性によるパラメータ管理:
  // data-is-header: headerに設置 = ドラグ、ホイール操作に対応しない
  // data-aspect-ratio: アスペクト比 (SCSSも修正が必要)
  // data-gap: アイテム間隔(px) SCSSで指定不可
  // data-interval: スライドアニメーション時間間隔
  // data-duration: スライドアニメーション所要時間
  constructor(elem) {
    // Sliderの各要素
    this.elem = elem || document.querySelector('.slider');
    if (!this.elem) return;
    this.inner = this.elem.querySelector('.slider__inner');
    if (!this.inner) return;
    this.items = this.inner.children;
    if (!this.items.length) return;

    // 各オプション (data属性から取得)
    this.isHeader = this.elem.dataset.isHeader || false; // headerに設置する場合はドラグ、ホイール操作に対応しない
    this.aspectRatio = this.elem.dataset.aspectRatio || 8 / 5;
    this.gap = this.elem.dataset.gap - 0 || 0; // アイテム間隔(px)
    this.interval = this.elem.dataset.interval || 3000; // 1000未満を指定すると自動再生しない
    this.duration = this.elem.dataset.duration || 500;

    // innerにスタイル適用
    this.inner.style.flexWrap = 'nowrap'; // .wp-block-galleryのリセット
    this.inner.style.gap = `${this.gap}px`;

    // 各状態管理
    this.currentIndex = 0;
    this.itemsCount = this.items.length;
    this.distance = 0; // インラインでtranslateXに適用する値
    this.dragDistance = []; // ドラグ操作の軌跡を保持
    this.isAnimated = false;

    // 各セットアップ
    this.setupNavs();
    this.setupItems();
    this.readyMove(-3, true); // 左に3つアイテムを足しておく
    this.setActiveTarget();
    this.handleEvents();

    // リサイズ
    this.windowResizeHandler();

    // 自動再生
    if (this.interval >= 1000) this.startInterval();
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
      this.move(1);
    }
  }

  // sizeを指定して、スライダーを動かす
  move(size, duration = this.duration) {
    // 予め引っ張られてくるアイテムを移動しておく
    this.readyMove(size);

    // 状態の更新
    const len = this.items.length;
    this.currentIndex += size;
    if (this.currentIndex < 0) this.currentIndex += len;
    if (this.currentIndex >= len) this.currentIndex -= len;

    // アニメーション開始
    this.isAnimated = true;
    this.start = this.distance;

    if (size < 0) {
      for (let i = 0; i > size; i--) {
        this.start -= this.items[(this.currentIndex - i - 3 + len) % len].clientWidth;
        this.start -= this.gap;
      }
    } else {
      for (let i = 0; i < size; i++) {
        this.start += this.items[(this.currentIndex - i - 4 + len) % len].clientWidth;
        this.start += this.gap;
      }
    }

    this.stop = this.getAdjustedDistance(this.currentIndex);
    this.flickDistance = this.stop - this.start;
    this.currentDuration = duration;
    this.timeStart = false;
    this.moving(performance.now());
  }

  readyMove(size, init = false) {
    const len = this.items.length;

    if (size < 0) {
      for (let i = 0; i > size; i--) {
        let j = (this.currentIndex + i - 4 + len) % len;
        if (init) j += 3;
        const order = window.getComputedStyle(this.items[j]).order;
        this.items[j].style.order = parseInt(order) - 1;
      }
    } else {
      for (let i = 0; i < size; i++) {
        let j = (this.currentIndex + i - 3 + len) % len;
        if (init) j += 3;
        const order = window.getComputedStyle(this.items[j]).order;
        this.items[j].style.order = parseInt(order) + 1;
      }
    }
  }

  // ナビゲーション(.slider__prev, .slider__next, .slider__nav)を設置
  setupNavs() {
    // .slider__prev
    this.prev = document.createElement('a');
    this.prev.classList.add('slider__prev');
    this.prev.setAttribute('href', '#');
    let icon = document.createElement('div');
    icon.classList.add('icon', 'icon--chevron-left', 'icon--md');
    icon.innerHTML = '<span class="icon__span"></span>';
    this.prev.appendChild(icon);

    // .slider__next
    this.next = document.createElement('a');
    this.next.classList.add('slider__next');
    this.next.setAttribute('href', '#');
    icon = document.createElement('div');
    icon.classList.add('icon', 'icon--chevron-right', 'icon--md');
    icon.innerHTML = '<span class="icon__span"></span>';
    this.next.appendChild(icon);

    // .slider__nav
    this.nav = document.createElement('ul');
    this.nav.classList.add('slider__nav');

    // .slider__navItem
    for (let i = 0; i < this.itemsCount; i++) {
      const li = document.createElement('li');
      li.classList.add('slider__navItem');
      li.dataset.targetIndex = i; // data-target-indexを挿入
      this.nav.appendChild(li);
    }

    this.elem.appendChild(this.prev);
    this.elem.appendChild(this.next);
    this.elem.after(this.nav);
  }

  // アイテムが7個未満の場合に予備を連ねておく
  setupItems() {
    while (this.items.length < 7) {
      for (let i = 0; i < this.itemsCount; i++) {
        const clone = this.items[i].cloneNode(true);
        this.inner.appendChild(clone);
      }
    }
  }

  // アイテムのアクティブ状態を管理
  setActiveTarget() {
    // スライダー内アイテム
    if (this.inner.querySelector('.slider__item--current')) {
      this.inner.querySelector('.slider__item--current').classList.remove('slider__item--current');
    }
    this.items[this.currentIndex].classList.add('slider__item--current');
    // ナビゲーション
    if (this.nav.querySelector('.slider__navItem--current')) {
      this.nav.querySelector('.slider__navItem--current').classList.remove('slider__navItem--current');
    }
    this.navItems = this.nav.children;
    this.navItems[this.currentIndex % this.itemsCount].classList.add('slider__navItem--current');
  }

  handleEvents() {
    // タッチデバイスの判定
    const touchSupported = 'ontouchstart' in document.documentElement || navigator.maxTouchPoints > 0;
    const myTouch = touchSupported ? 'touchend' : 'click';

    // 状態
    this.x = 0;
    this.y = 0;
    this.isDragging = false;
    this.delta = 0;

    // ドラグおよびホイール操作
    if (!this.isHeader) {
      if (touchSupported) {
        this.inner.addEventListener('touchstart', (event) => {
          this.x = event.touches[0].clientX;
          this.y = event.touches[0].clientY;
          this.isDragging = true;
          this.touchStartHandler();
        });

        this.inner.addEventListener('touchmove', (event) => {
          this.x = event.touches[0].clientX;
          this.y = event.touches[0].clientY;
          this.touchMoveHandler();
        });

        this.inner.addEventListener('touchend', () => {
          this.touchEndHandler();
          this.isDragging = false;
        });

        // touchcancelは、touchEnd扱い
        this.inner.addEventListener('touchcancel', () => {
          this.touchEndHandler();
          this.isDragging = false;
        });
      }

      this.inner.addEventListener('mousedown', (event) => {
        this.x = event.clientX;
        this.y = event.clientY;
        this.isDragging = true;
        this.touchStartHandler();
        event.preventDefault();
      });

      this.inner.addEventListener('mousemove', (event) => {
        this.x = event.clientX;
        this.y = event.clientY;
        this.touchMoveHandler();
        event.preventDefault();
      });

      this.inner.addEventListener('mouseup', () => {
        this.touchEndHandler();
        this.isDragging = false;
      });

      // ポインターが外れたときは、touchEnd扱い
      this.inner.addEventListener('mouseleave', () => {
        this.touchEndHandler();
        this.isDragging = false;
      });

      // ホイール操作
      this.inner.addEventListener('wheel', (event) => {
        this.delta = event.deltaY;
        this.myWheelHandler();
        event.preventDefault();
      });
    }

    // img > a リンク無効化
    this.inner.querySelectorAll('.post__image > a').forEach((elem) => {
      elem.addEventListener(myTouch, (event) => {
        event.preventDefault();
      });
    });

    // ナビゲーション操作
    this.nav.addEventListener(myTouch, (event) => {
      const target = event.target;
      if (target.dataset.targetIndex) {
        this.move(target.dataset.targetIndex - this.currentIndex % this.itemsCount);
        this.stopInterval();
      }
    });

    // 前ボタン
    this.prev.addEventListener(myTouch, (event) => {
      if (!this.isAnimated) this.move(-1);
      this.stopInterval();
      event.preventDefault();
    });

    // 次ボタン
    this.next.addEventListener(myTouch, (event) => {
      if (!this.isAnimated) this.move(1);
      this.stopInterval();
      event.preventDefault();
    });

    // リサイズ
    window.addEventListener('resize', () => {
      this.windowResizeHandler();
    });
  }

  touchStartHandler() {
    // 配列をリセット
    this.dragDistance = [this.x];
    // 自動再生を止める
    this.stopInterval();
  }

  touchMoveHandler() {
    if (this.isDragging && !this.isAnimated) {
      // 配列にx座標をpushする
      this.dragDistance.push(this.x);
      const len = this.dragDistance.length;
      let distance = 0;
      // インラインスタイルを書き換える
      for (let i = 0; i < len; i++) {
        if (i > 0) {
          distance = this.dragDistance[i] - this.dragDistance[i - 1];
          this.inner.style.transform = `translateX(${this.distance + distance}px)`;
        }
      }
      this.distance += distance;
    }
  }

  touchEndHandler() {
    // フリック操作
    if (this.isDragging) {
      // 移動距離
      const distance = this.dragDistance[0] - this.dragDistance[this.dragDistance.length - 1];
      if (Math.abs(distance) > 10) { // 僅かな移動距離で、move()を頻発させない
        const len = this.items.length;
        let size = 0;
        // 移動距離とアイテムの幅から、どれだけmove()させるか計測
        if (distance < 0) {
          const w1 = this.items[(this.currentIndex - 3 + len) % len].clientWidth;
          const w2 = this.items[(this.currentIndex - 2 + len) % len].clientWidth;
          if (w1 / 3 < Math.abs(distance)) size--;
          if ((w1 * 2 + w2) / 3 < Math.abs(distance)) size--;
        } else {
          const w1 = this.items[(this.currentIndex - 4 + len) % len].clientWidth;
          const w2 = this.items[(this.currentIndex - 5 + len) % len].clientWidth;
          if (w1 / 3 < Math.abs(distance)) size++;
          if ((w1 * 2 + w2) / 3 < Math.abs(distance)) size++;
        }
        this.move(size, this.duration / 2); // 既に引っ張ってきているので、半分の時間
      }
    }
  }

  myWheelHandler() {
    const delta = this.delta;
    if (delta < 0 && !this.isAnimated) this.move(-1);
    if (delta > 0 && !this.isAnimated) this.move(1);
    this.stopInterval();
  }

  windowResizeHandler() {
    // 再計算
    this.inner.style.width = `${this.getInnerWidth()}px`;
    this.distance = this.getAdjustedDistance(this.currentIndex);
    this.inner.style.transform = `translateX(${this.distance}px)`;
  }

  getInnerWidth() {
    const len = this.items.length;
    return this.elem.clientHeight * this.aspectRatio * len + this.gap * (len - 1);
  }

  getAdjustedDistance(index) {
    const len = this.items.length;
    let result = window.innerWidth / 2;
    result -= this.items[index % len].clientWidth / 2;
    for (let i = 0; i > -3; i--) {
      let j = (index + i - 1 + len) % len;
      result -= this.items[j].clientWidth;
      result -= this.gap;
    }
    return result;
  }

  moving(timeCurrent) {
    if (!this.timeStart) {
      this.timeStart = timeCurrent;
    }

    const timeElapsed = timeCurrent - this.timeStart;
    const next = this.easing(timeElapsed, this.start, this.flickDistance, this.currentDuration);
    this.inner.style.transform = `translateX(${next}px)`;

    timeElapsed < this.currentDuration
      ? window.requestAnimationFrame(this.moving.bind(this))
      : this.moved();
  }


  moved() {
    this.inner.style.transform = `translateX(${this.start + this.flickDistance}px)`;
    this.timeStart = false;
    this.isAnimated = false;
    this.setActiveTarget();
    this.windowResizeHandler();
  }


  easing(t, b, c, d) {
    t /= d / 2;
    if (t < 1) return c / 2 * t * t + b;
    t--;
    return -c / 2 * (t * (t - 2) - 1) + b;
  }
}
