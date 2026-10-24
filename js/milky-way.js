/**
 * Milky Way
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 */

/**
 * Milky Way
 * 天の川のように, 左下から右上へ帯状に分布した水玉 (正円) を, 帯に沿って流す
 * 帯の端に行き着いた点は, 反対側の端から再登場する
 *
 * 使い方:
 * _nightFlight.scss をバンドルした css を読み込み,
 * body直下に [data-milky-way] 属性を持つ canvas 要素を配置して, インスタンスを生成する
 * import MilkyWay from './js/milky-way.js';
 * new MilkyWay();
 *
 * オプション:
 * canvasSelector: 描画先の canvas セレクタ (規定で [data-milky-way])
 * density: 帯の長さ 100rem あたりの点の数
 * bandWidth: 帯の太さ (画面の短辺に対する割合. 小さいほど帯がくっきりする)
 * angle: 帯の角度 (度, 右上がりが正). null で画面の左下から右上の対角線
 * edgeFade: 帯の外側ほど淡くする強さ (0 で一様)
 * speed: 流れる速さ (px / 秒)
 * floatRange: 流れと直交する方向の揺れ幅 (px)
 * color: 水玉の色
 * opacity: 全体の不透明度
 */

const TAU = Math.PI * 2;

// 標準正規分布の乱数 (Box-Muller法)
function gaussian() {
  const u = 1 - Math.random();
  const v = Math.random();
  return Math.sqrt(-2 * Math.log(u)) * Math.cos(TAU * v);
}

export default class MilkyWay {
  constructor(options = {}) {
    // オプション
    const canvasSelector = options.canvasSelector || '[data-milky-way]';
    this.canvas = document.querySelector(canvasSelector);
    if (!this.canvas) return;

    this.params = {
      density: options.density ?? 400,
      bandWidth: options.bandWidth ?? 0.2,
      angle: options.angle ?? null,
      edgeFade: options.edgeFade ?? 1.0,
      speed: options.speed ?? 10,
      floatRange: options.floatRange ?? 4,
      color: options.color || 'oklch(80% 0.045 265)', // _nightFlight.scss の star と同値
      opacity: options.opacity ?? 0.3
    };

    this.ctx = this.canvas.getContext('2d');
    this.reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    this.elapsed = 0;
    this.createDots();

    // bind
    this.resize = this.resize.bind(this);
    this.loop = this.loop.bind(this);

    // イベント登録
    this.resize();
    window.addEventListener('resize', this.resize);

    // 動きを減らす設定の場合は, 静止画として1回だけ描画する
    if (!this.reduceMotion) {
      this.last = performance.now();
      requestAnimationFrame(this.loop);
    }
  }

  // 帯状に分布する点を生成する
  // 座標は帯に沿った位置 u (0〜1) と, 帯からの距離 v (帯の太さに対する倍率) で持ち,
  // 画面サイズが変わっても同じ並びを保つ
  createDots() {
    const { density, edgeFade } = this.params;
    // 画面が大きい場合にも足りるよう多めに用意し, 描画時に数を絞る
    const pool = Math.ceil(density * 3);
    this.dots = Array.from({ length: pool }, () => {
      // 小さい点ほど多く, 大きい点は少なく
      const size = Math.random() ** 3;
      const v = gaussian() * 0.5;
      return {
        u: Math.random(),
        v,
        r: 1.5 + size * 9.5, // 16px 基準の半径 (1.5〜11px)
        // 小さい点ほど淡く・遅く (奥にあるように見せる). 帯の外側ほど淡くして, 帯の芯を際立たせる
        alpha: (0.35 + size * 0.65) * Math.exp(-(v ** 2) * edgeFade),
        speedRatio: 0.6 + size * 0.8,
        phase: Math.random() * TAU,
        period: 4 + Math.random() * 4
      };
    });
  }

  resize() {
    const { angle: angleDeg, bandWidth, density } = this.params;
    const dpr = window.devicePixelRatio || 1;
    const width = (this.width = this.canvas.clientWidth);
    const height = (this.height = this.canvas.clientHeight);
    this.canvas.width = Math.round(width * dpr);
    this.canvas.height = Math.round(height * dpr);
    this.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    this.rem = parseFloat(getComputedStyle(document.documentElement).fontSize);

    // 帯の向き (画面座標は y が下向きなので, 右上がりは負の sin)
    const angle = angleDeg === null ? Math.atan2(height, width) : (angleDeg * Math.PI) / 180;
    this.dirX = Math.cos(angle);
    this.dirY = -Math.sin(angle);
    // 帯の長さ: 画面を帯の向きに投影した長さ + はみ出し分の余白
    const margin = 12 * (this.rem / 16) * 2;
    this.bandLength = width * Math.abs(this.dirX) + height * Math.abs(this.dirY) + margin * 2;
    this.bandSigma = Math.min(width, height) * bandWidth;
    // 帯の長さに応じて描く数を決める (密度を一定に保つ)
    this.visibleCount = Math.min(this.dots.length, Math.round((density * this.bandLength) / (100 * this.rem)));

    this.draw();
  }

  loop(time) {
    // タブが裏にあった後に飛ばないよう, 1フレームの経過時間に上限を付ける
    this.elapsed += Math.min((time - this.last) / 1000, 0.1);
    this.last = time;
    this.draw();
    requestAnimationFrame(this.loop);
  }

  draw() {
    const { ctx, dirX, dirY, bandLength, bandSigma } = this;
    const { speed, floatRange, color, opacity } = this.params;
    const centerX = this.width / 2;
    const centerY = this.height / 2;
    const unit = this.rem / 16;

    ctx.clearRect(0, 0, this.width, this.height);
    ctx.fillStyle = color;

    for (let i = 0; i < this.visibleCount; i++) {
      const dot = this.dots[i];
      // 帯に沿って進み, 終端を越えたら始端から再登場
      const travel = (speed * dot.speedRatio * this.elapsed) / bandLength;
      const along = (((dot.u + travel) % 1) - 0.5) * bandLength;
      // 流れと直交する方向の揺れ
      const float = Math.sin((this.elapsed / dot.period) * TAU + dot.phase) * floatRange;
      const across = dot.v * bandSigma + float;
      // 帯の向き (dirX, dirY) と, それに直交する向き (-dirY, dirX) で配置
      const x = centerX + dirX * along - dirY * across;
      const y = centerY + dirY * along + dirX * across;

      ctx.globalAlpha = dot.alpha * opacity;
      ctx.beginPath();
      ctx.arc(x, y, dot.r * unit, 0, TAU);
      ctx.fill();
    }
    ctx.globalAlpha = 1;
  }
}
