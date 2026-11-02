/**
 * Flight Status
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 */

/**
 * Flight Status
 * 稼働状況 (受付状況) の文言を, 状態に合わせて書き込む
 *
 * 頻繁に更新しなくても古びて見えないよう, 受付中の年月は「ページを開いた年月」を使う
 * 状態は init.js の1行 (new FlightStatus('open') の引数) で切り替える
 *   'open'    : 「2026年11月現在、新規のご相談を受け付けています」 (年月はページを開いた日から)
 *   'consult' : 「稼働は応相談です。時期も含めてご相談ください」 (計器灯を消し, 年月は出さない)
 *
 * 書き込む先は [data-flight-status] の要素
 * .is-panel の要素 (稼働状況のセクション) では, 受付中の年月を文頭ではなく札 (AS OF 2026.11) にして添える
 * JavaScript が動かない環境では, HTML に書いた文言がそのまま表示される
 *
 * 使い方:
 * import FlightStatus from './js/flight-status.js';
 * new FlightStatus('open');
 *
 * オプション (第2引数):
 * selector: 書き込む先のセレクタ (規定で [data-flight-status])
 * date: 基準にする日付 (規定で new Date(). 表示の確認用)
 */

export default class FlightStatus {
  static messages = {
    open: '新規のご相談を受け付けています',
    consult: '稼働は応相談です。時期も含めてご相談ください'
  };

  constructor(state = 'open', options = {}) {
    this.state = state in FlightStatus.messages ? state : 'open';
    this.selector = options.selector || '[data-flight-status]';
    this.date = options.date || new Date();

    document.querySelectorAll(this.selector).forEach((elem) => this.render(elem));
  }

  render(elem) {
    const year = this.date.getFullYear();
    const month = this.date.getMonth() + 1;

    const isOpen = this.state === 'open';
    const isPanel = elem.classList.contains('is-panel');
    const prefix = isOpen && !isPanel ? `${year}年${month}月現在、` : '';

    elem.textContent = prefix + FlightStatus.messages[this.state];
    elem.classList.toggle('is-consult', !isOpen);

    // 稼働状況のセクションの札には, 受付中のときだけ年月を添える
    if (isOpen && isPanel) {
      const updated = document.createElement('span');
      updated.className = 'copilotStatus__updated';
      updated.textContent = `AS OF ${year}.${String(month).padStart(2, '0')}`;
      elem.append(updated);
    }
  }
}
