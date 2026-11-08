/**
 * Scene Switch
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 */

/**
 * Scene Switch
 * 昼の便 (ライトモード) と夜の便 (ダークモード) を切り替えるボタンを自動生成し, 制御する
 *
 * 普段は OS の設定 (prefers-color-scheme) に従い, ボタンで選んだ便はタブを開いている間だけ保つ
 * 選んだ便は html の [data-scene] 属性 ("day" | "night") で示し, sessionStorage にも保存する
 * (トップから下層ページ (co-pilot/ など) へ移っても便が変わらない. タブを閉じれば OS の設定に戻る)
 * OS の設定と同じ便を選び直すと属性と保存を消し, 再び OS の設定に従う
 *
 * 遷移直後に OS の設定の便が一瞬見えないよう, 各ページの <head> で描画の前に属性を戻しておく
 * <script>try{const s=sessionStorage.getItem('qwel-flight-scene');if(s==='day'||s==='night')document.documentElement.dataset.scene=s}catch(e){}</script>
 * (このスクリプトが無いページでも, インスタンスの生成時に保存した便を戻す)
 * 属性が無い間は, CSS・ロゴ・背景のアニメーションとも OS の設定に従う
 *
 * ボタンの横には, 切り替え先を示すラベル (例: 夜の便 · DARK) を添え, ホバー・フォーカス時に表示する
 * ボタンの存在を知らせるため, ページを開くたびに読み込み後しばらくラベルを表示する
 *
 * 使い方:
 * _flight.scss をバンドルした css を読み込み, インスタンスを生成する
 * import SceneSwitch from './js/scene-switch.js';
 * new SceneSwitch();
 *
 * オプション:
 * hintDuration: 読み込み後にラベルを表示しておく秒数 (規定で 6. 0 で表示しない)
 * sourceSelector: 夜の便のロゴを指す source 要素のセレクタ (規定で [data-night-source])
 * storageKey: sessionStorage のキー (規定で qwel-flight-scene. <head> のスクリプトと合わせる)
 */

export default class SceneSwitch {
  constructor(options = {}) {
    if (!document.querySelector('.is-flight')) return;
    
    // オプション
    this.hintDuration = options.hintDuration ?? 6;
    this.sourceSelector = options.sourceSelector || '[data-night-source]';
    this.storageKey = options.storageKey || 'qwel-flight-scene';

    this.root = document.documentElement;
    this.darkQuery = window.matchMedia('(prefers-color-scheme: dark)');

    // bind
    this.onClick = this.onClick.bind(this);
    this.update = this.update.bind(this);

    // ボタン生成
    this.createButton();

    // イベント登録
    this.btn.addEventListener('click', this.onClick);
    this.darkQuery.addEventListener('change', this.update);

    // 初期表示 (前のページで選んだ便を戻す)
    this.restore();
    this.update();
    this.showHint();
  }

  // OS の設定による便
  get systemScene() {
    return this.darkQuery.matches ? 'night' : 'day';
  }

  // 現在の便
  get scene() {
    return this.root.dataset.scene || this.systemScene;
  }

  createButton() {
    // button要素にすることで, キーボード (Enter/Space) でも操作可能にする
    this.btn = document.createElement('button');
    this.btn.setAttribute('type', 'button');
    this.btn.classList.add('sceneSwitch');

    // アイコン要素 (切り替え先の便を表す: 昼は月, 夜は太陽)
    this.icon = document.createElement('div');
    this.icon.classList.add('icon', 'is-lg');

    // span要素
    const span = document.createElement('span');
    span.classList.add('icon__span');

    // ラベル要素 (切り替え先の便とモード名)
    this.label = document.createElement('span');
    this.label.classList.add('sceneSwitch__label');
    this.label.setAttribute('aria-hidden', 'true'); // 読み上げは aria-label に任せる

    // bodyに挿入
    this.icon.appendChild(span);
    this.btn.appendChild(this.icon);
    this.btn.appendChild(this.label);
    document.body.appendChild(this.btn);
  }

  // 読み込み後しばらくラベルを表示する
  showHint() {
    if (this.hintDuration <= 0) return;
    this.btn.classList.add('is-hinted');
    this.hintTimer = setTimeout(() => this.hideHint(), this.hintDuration * 1000);
  }

  hideHint() {
    clearTimeout(this.hintTimer);
    this.btn.classList.remove('is-hinted');
  }

  onClick(event) {
    event.preventDefault();
    // 押したら役目を終えるので, ヒントを消す (ラベルはホバー・フォーカスで引き続き表示される)
    this.hideHint();
    const next = this.scene === 'night' ? 'day' : 'night';
    // OS の設定と同じ便に戻したときは, 属性を外して OS の設定に従う
    if (next === this.systemScene) {
      delete this.root.dataset.scene;
    } else {
      this.root.dataset.scene = next;
    }
    this.save();
    this.update();
  }

  // 保存した便を戻す (OS の設定と同じになっていれば, 属性と保存を消して OS の設定に従う)
  restore() {
    const stored = this.load();
    if (stored && stored !== this.systemScene) {
      this.root.dataset.scene = stored;
    } else {
      delete this.root.dataset.scene;
      if (stored) this.save();
    }
  }

  // sessionStorage の読み書き (使えない環境では, 保存せずにそのページの中だけで切り替える)
  load() {
    try {
      const value = sessionStorage.getItem(this.storageKey);
      return value === 'day' || value === 'night' ? value : null;
    } catch {
      return null;
    }
  }

  save() {
    try {
      const scene = this.root.dataset.scene;
      if (scene) sessionStorage.setItem(this.storageKey, scene);
      else sessionStorage.removeItem(this.storageKey);
    } catch {
      // 保存できなくても, 切り替えそのものは続ける
    }
  }

  update() {
    const scene = this.scene;
    const isForced = Boolean(this.root.dataset.scene);

    // ロゴの出し分け (DrawerMenu に複製されたロゴも含む)
    // 便を選んでいる間は source の media を上書きし, それ以外は OS の設定に戻す
    document.querySelectorAll(this.sourceSelector).forEach((source) => {
      source.media = isForced ? (scene === 'night' ? 'all' : 'not all') : '(prefers-color-scheme: dark)';
    });

    // ボタンの表示
    const toNight = scene === 'day';
    this.icon.classList.toggle('is-moon', toNight);
    this.icon.classList.toggle('is-sun', !toNight);
    this.btn.setAttribute('aria-label', toNight ? '夜の便 (ダークモード) に切り替える' : '昼の便 (ライトモード) に切り替える');
    this.label.textContent = toNight ? '夜の便 · DARK' : '昼の便 · LIGHT';
  }

  destroy() {
    clearTimeout(this.hintTimer);
    this.btn?.removeEventListener('click', this.onClick);
    this.btn?.remove();
    this.darkQuery.removeEventListener('change', this.update);
  }
}
