/**
 * Preloader
 * このファイルは QWEL Project の一部です。
 * Part of the QWEL Project © QWEL.DESIGN 2025
 * Licensed under GPL v3 – see https://qwel.design/
 */

// body要素に限らず、任意の要素に定義可能 (Faderで駆動することを想定)
export default class Preloader {
  // 引数:isLoadManually true: 自動読み込みしない false: window.onloadで自動読み込み
  // data属性によるパラメータ管理:
  // data-background-color: preloader背景色
  // data-img-src: preloader画像 (指定無しの場合はスピナーを生成)
  // data-terminateTime: 最大読み込み時間
  // data-spinner-off: true指定で、スピナーを生成しない
  // data-spinner-bars-count: スピナーバー本数 (SCSSも修正が必要)
  // data-spinner-interval: スピナー回転間隔
  constructor(elem, isLoadManually) {
    // 要素を取得
    this.elem = elem || document.querySelector('.preloader');
    if (!this.elem) return;

    // 各オプション (data属性から取得)
    const backgroundColor = this.elem.dataset.backgroundColor || '';
    const imgSrc = this.elem.dataset.imgSrc || '';
    const terminateTime = this.elem.dataset.terminateTime || 3000;
    const spinnerOff = this.elem.dataset.spinnerOff || false;
    const spinnerBarsCount = this.elem.dataset.spinnerBarsCount || 12; // Sassも修正が必要
    const spinnerInterval = this.elem.dataset.spinnerInterval || 1000;

    // 背景を覆う
    this.preloader = document.createElement('div');
    this.preloader.classList.add('preloader__overlay');
    this.preloader.style.backgroundColor = backgroundColor;
    this.elem.appendChild(this.preloader);

    // 画像またはスピナーを表示
    if (imgSrc) {
      const img = new Image();
      img.classList.add('preloader__image');
      img.src = imgSrc;
      this.preloader.appendChild(img);
    } else if (!spinnerOff) {
      this.spinner = new Spinner({
        barsCount: spinnerBarsCount,
        interval: spinnerInterval
      });
      this.spinner.spin();
      this.preloader.appendChild(this.spinner.spinner);
    }

    // 3000ミリ秒でロード
    this.terminateTime = terminateTime;
    this.terminateTimmerId = setTimeout(() => {
      this.load()
    }, this.terminateTime);

    // ページ読み込みが完了したらロード
    if (!isLoadManually) window.onload = () => this.load();
  }

  load() {
    clearTimeout(this.terminateTimmerId);

    let callback;
    const promise = new Promise((resolve, reject) => {
      callback = () => resolve(this.preloader);
      this.preloader.addEventListener('transitionend', callback);
    });

    // エフェクト終了をbody要素に伝え、各要素をtransitionさせる
    document.body.classList.add('loaded');
    
    this.preloader.classList.add('is-hidden');
    promise.then(() => {
      this.preloader.removeEventListener('transitionend', callback);
      this.terminate();
    });
  }

  terminate() {
    if (this.spinner) {
      this.spinner.stop();
    }
    this.preloader.remove();
  }
}


// CSSでスピナーを作成
class Spinner {
  constructor(options = {}) {
    this.spinner = document.createElement('div');
    this.spinner.classList.add('preloader__spinner');
    this.barsCount = options.barsCount || 12;
    const bars = [];

    for (let i = 0; i < this.barsCount; i++) {
      bars[i] = document.createElement('span');
      this.spinner.appendChild(bars[i]);
    }

    this.interval = options.interval || 1000;
    this.interval /= this.barsCount;
  }

  spin() {
    this.isSpin = true;

    setTimeout(() => {
      this.loop(0);
    }, this.interval);
  }

  stop() {
    this.isSpin = false;
  }

  loop(rotateCount = 0) {
    if (!this.isSpin) return;

    if (this.barsCount === rotateCount) {
      rotateCount = 0;
    } else {
      rotateCount++;
    }

    const deg = rotateCount * 360 / this.barsCount;
    this.spinner.style.transform = `rotate(${deg}deg)`;

    setTimeout(() => {
      this.loop(rotateCount);
    }, this.interval);
  }
}
