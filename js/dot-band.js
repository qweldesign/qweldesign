/**
 * Dot Band
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 */

/**
 * Dot Band
 * 帯状に分布した水玉 (正円) を, 帯に沿って流す
 * 帯の端に行き着いた点は, 反対側の端から再登場する
 *
 * 夜 (ダークモード) は対角線を流れる天の川, 昼 (ライトモード) は画面の下を流れる雲海になる
 * 点の並びは昼夜で共有し, プリセットを補間して形を変える
 * (OS の設定を切り替えると, 天の川が倒れて沈み, 星がふくらんで雲になる)
 * html の [data-scene] 属性 ("day" | "night") があれば, OS の設定よりそちらを優先する (js/scene-switch.js)
 *
 * 使い方:
 * _flight.scss をバンドルした css を読み込み,
 * body直下に [data-dot-band] 属性を持つ canvas 要素を配置して, インスタンスを生成する
 * import DotBand from './js/dot-band.js';
 * new DotBand();
 *
 * オプション:
 * canvasSelector: 描画先の canvas セレクタ (規定で [data-dot-band])
 * night: 夜のプリセットの上書き (下記 PRESETS.night のキー)
 * day: 昼のプリセットの上書き (下記 PRESETS.day のキー)
 * duration: 昼夜の移り変わりにかける秒数
 *
 * プリセット:
 * angle: 帯の角度 (度, 右上がりが正). null で画面の左下から右上の対角線
 * offsetY: 帯の中心の縦位置 (画面高さに対する割合. 0 で中央, 正で下)
 * density: 帯の長さ 100rem あたりの点の数
 * bandWidth: 帯の太さ (画面の短辺に対する割合. 小さいほど帯がくっきりする)
 * edgeFade: 帯の外側ほど淡くする強さ (0 で一様)
 * speed: 流れる速さ (px / 秒)
 * floatRange: 流れと直交する方向の揺れ幅 (px)
 * color: 水玉の色 [H, S (%), L (%)] (HSL)
 * opacity: 全体の不透明度
 */

const PRESETS = {
  // 夜: 天の川 (_flightColors.scss の star と同値)
  night: {
    angle: null,
    offsetY: 0,
    density: 400,
    bandWidth: 0.2,
    edgeFade: 1.0,
    speed: 10,
    floatRange: 4,
    color: [220.0, 37.5, 77.5],
    opacity: 0.3
  },
  // 昼: 雲海 (primary #209fdf). 水平に, 右から左へ (ロゴの機体が右へ進む)
  day: {
    angle: 180,
    offsetY: 0.3,
    density: 150,
    bandWidth: 0.08,
    edgeFade: 0.5,
    speed: 15,
    floatRange: 2,
    color: [200.0, 75.0, 50.0],
    opacity: 0.15
  }
};

const TAU = Math.PI * 2;
const lerp = (a, b, t) => a + (b - a) * t;
const easeInOut = (t) => (t < 0.5 ? 4 * t ** 3 : 1 - (-2 * t + 2) ** 3 / 2);

// 標準正規分布の乱数 (Box-Muller法)
function gaussian() {
  const u = 1 - Math.random();
  const v = Math.random();
  return Math.sqrt(-2 * Math.log(u)) * Math.cos(TAU * v);
}

export default class DotBand {
  constructor(options = {}) {
    // オプション
    const canvasSelector = options.canvasSelector || '[data-dot-band]';
    this.canvas = document.querySelector(canvasSelector);
    if (!this.canvas) return;

    this.night = { ...PRESETS.night, ...options.night };
    this.day = { ...PRESETS.day, ...options.day };
    this.duration = options.duration ?? 1.6;

    this.ctx = this.canvas.getContext('2d');
    this.reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    this.darkQuery = window.matchMedia('(prefers-color-scheme: dark)');
    this.elapsed = 0;

    // 0: 夜, 1: 昼
    this.mix = this.getScene() === 'night' ? 0 : 1;
    this.target = this.mix;

    this.createDots();

    // bind
    this.resize = this.resize.bind(this);
    this.loop = this.loop.bind(this);
    this.onSchemeChange = this.onSchemeChange.bind(this);

    // イベント登録
    this.resize();
    window.addEventListener('resize', this.resize);
    this.darkQuery.addEventListener('change', this.onSchemeChange);
    // 切り替えボタンで選んだ便 (html の [data-scene] 属性) の変化も監視する
    this.observer = new MutationObserver(this.onSchemeChange);
    this.observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-scene'] });

    // 動きを減らす設定の場合は, 静止画として描画する (昼夜の切り替え時にだけ描き直す)
    if (!this.reduceMotion) {
      this.last = performance.now();
      requestAnimationFrame(this.loop);
    }
  }

  // 帯状に分布する点を生成する
  // 座標は帯に沿った位置 pos (0〜1) と, 帯からの距離 v (帯の太さに対する倍率) で持ち,
  // 画面サイズや昼夜が変わっても同じ並びを保つ
  createDots() {
    // 画面が大きい場合にも足りるよう多めに用意し, 描画時に数を絞る
    const pool = Math.ceil(Math.max(this.night.density, this.day.density) * 3);
    this.dots = Array.from({ length: pool }, () => {
      const seed = Math.random();
      const size = seed ** 3; // 小さい星ほど多く, 大きい星は少なく
      const v = gaussian() * 0.5;
      return {
        pos: Math.random(),
        v,
        // 夜の星: 小さい星ほど淡く・遅く (奥にあるように見せる). 帯の外側ほど淡く
        night: {
          r: 1.5 + size * 9.5, // 16px 基準の半径 (1.5〜11px)
          alpha: (0.35 + size * 0.65) * Math.exp(-(v ** 2) * this.night.edgeFade),
          speedRatio: 0.6 + size * 0.8
        },
        // 昼の雲: 大きさを均等に散らし, 円が重なり合って雲の塊に見えるように
        day: {
          r: 8 + seed * 26, // 16px 基準の半径 (8〜34px)
          alpha: (0.5 + seed * 0.5) * Math.exp(-(v ** 2) * this.day.edgeFade),
          speedRatio: 0.7 + seed * 0.6
        },
        phase: Math.random() * TAU,
        period: 4 + Math.random() * 4
      };
    });
  }

  // 現在の便: 選んだ便があればそれを, 無ければ OS の設定に従う
  getScene() {
    const scene = document.documentElement.dataset.scene;
    if (scene === 'day' || scene === 'night') return scene;
    return this.darkQuery.matches ? 'night' : 'day';
  }

  onSchemeChange() {
    this.target = this.getScene() === 'night' ? 0 : 1;
    if (this.reduceMotion) {
      this.mix = this.target;
      this.draw();
    }
  }

  resize() {
    const dpr = window.devicePixelRatio || 1;
    const width = (this.width = this.canvas.clientWidth);
    const height = (this.height = this.canvas.clientHeight);
    this.canvas.width = Math.round(width * dpr);
    this.canvas.height = Math.round(height * dpr);
    this.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    this.rem = parseFloat(getComputedStyle(document.documentElement).fontSize);
    // 画面の左下から右上への対角線の角度 (度)
    this.diagonal = (Math.atan2(height, width) * 180) / Math.PI;

    this.draw();
  }

  // 夜と昼のプリセットを t で補間した, 現在の帯の形
  getBand(t) {
    const { night, day } = this;
    const angleDeg = lerp(night.angle ?? this.diagonal, day.angle ?? this.diagonal, t);
    const angle = (angleDeg * Math.PI) / 180;
    // 帯の向き (画面座標は y が下向きなので, 右上がりは負の sin)
    const dirX = Math.cos(angle);
    const dirY = -Math.sin(angle);
    // 帯の長さ: 画面を帯の向きに投影した長さ + はみ出し分の余白
    const margin = 12 * (this.rem / 16) * 2;
    const length = this.width * Math.abs(dirX) + this.height * Math.abs(dirY) + margin * 2;
    // 帯の長さに応じて描く数を決める (密度を一定に保つ)
    const count = (density) => Math.min(this.dots.length, Math.round((density * length) / (100 * this.rem)));

    return {
      dirX,
      dirY,
      length,
      centerX: this.width / 2,
      centerY: this.height / 2 + lerp(night.offsetY, day.offsetY, t) * this.height,
      sigma: Math.min(this.width, this.height) * lerp(night.bandWidth, day.bandWidth, t),
      speed: lerp(night.speed, day.speed, t),
      floatRange: lerp(night.floatRange, day.floatRange, t),
      opacity: lerp(night.opacity, day.opacity, t),
      color: night.color.map((value, i) => lerp(value, day.color[i], t)),
      nightCount: count(night.density),
      dayCount: count(day.density)
    };
  }

  loop(time) {
    // タブが裏にあった後に飛ばないよう, 1フレームの経過時間に上限を付ける
    const delta = Math.min((time - this.last) / 1000, 0.1);
    this.last = time;
    this.elapsed += delta;

    // 昼夜の移り変わり
    if (this.mix !== this.target) {
      const step = delta / this.duration;
      this.mix = this.target > this.mix ? Math.min(this.target, this.mix + step) : Math.max(this.target, this.mix - step);
    }

    // 帯に沿って進み, 終端を越えたら始端から再登場
    // (速さが昼夜で変わっても位置が飛ばないよう, 経過時間ではなく位置を積み上げる)
    const t = easeInOut(this.mix);
    const { speed, length } = this.getBand(t);
    for (const dot of this.dots) {
      const speedRatio = lerp(dot.night.speedRatio, dot.day.speedRatio, t);
      dot.pos = (dot.pos + (speed * speedRatio * delta) / length) % 1;
    }

    this.draw();
    requestAnimationFrame(this.loop);
  }

  draw() {
    const { ctx } = this;
    const t = easeInOut(this.mix);
    const band = this.getBand(t);
    const unit = this.rem / 16;
    const [h, s, l] = band.color;

    ctx.clearRect(0, 0, this.width, this.height);
    ctx.fillStyle = `hsl(${h}, ${s}%, ${l}%)`;

    const total = Math.max(band.nightCount, band.dayCount);
    for (let i = 0; i < total; i++) {
      const dot = this.dots[i];
      // 夜と昼で描く数が違うので, 片方にしか無い点はフェードで出し入れする
      const presence = (i < band.nightCount ? 1 - t : 0) + (i < band.dayCount ? t : 0);
      if (presence <= 0) continue;

      const along = (dot.pos - 0.5) * band.length;
      // 流れと直交する方向の揺れ
      const float = Math.sin((this.elapsed / dot.period) * TAU + dot.phase) * band.floatRange;
      const across = dot.v * band.sigma + float;
      // 帯の向き (dirX, dirY) と, それに直交する向き (-dirY, dirX) で配置
      const x = band.centerX + band.dirX * along - band.dirY * across;
      const y = band.centerY + band.dirY * along + band.dirX * across;

      ctx.globalAlpha = lerp(dot.night.alpha, dot.day.alpha, t) * band.opacity * presence;
      ctx.beginPath();
      ctx.arc(x, y, lerp(dot.night.r, dot.day.r, t) * unit, 0, TAU);
      ctx.fill();
    }
    ctx.globalAlpha = 1;
  }
}
