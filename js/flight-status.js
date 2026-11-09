/**
 * Flight Status
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 */

/**
 * Flight Status
 * 稼働状況 (受付状況) に, 受付中の年月を添える
 *
 * 状態と文言は HTML に書いておく (content/options.json の flight_status から, co-pilot/index.php が出力する)
 *   data-flight-status="open"    : 受付中. 年月を添える (「2026年11月現在、新規のご相談を受け付けています」)
 *   data-flight-status="consult" : 応相談. 何もしない (計器灯を消す .is-consult も HTML で付ける)
 * 頻繁に更新しなくても古びて見えないよう, 年月は「ページを開いた年月」を使う
 * .is-panel の要素 (稼働状況のセクション) では, 年月を文頭ではなく札 (AS OF 2026.11) にして添える
 * JavaScript が動かない環境では, HTML に書いた文言がそのまま表示される
 *
 * 使い方:
 * import FlightStatus from './js/flight-status.js';
 * new FlightStatus();
 *
 * オプション:
 * selector: 対象のセレクタ (規定で [data-flight-status])
 * date: 基準にする日付 (規定で new Date(). 表示の確認用)
 */

export default class FlightStatus {
  constructor(options = {}) {
    this.selector = options.selector || '[data-flight-status]';
    this.date = options.date || new Date();

    document.querySelectorAll(this.selector).forEach((elem) => this.render(elem));
  }

  render(elem) {
    if (elem.dataset.flightStatus !== 'open') return;

    const year = this.date.getFullYear();
    const month = this.date.getMonth() + 1;

    if (elem.classList.contains('is-panel')) {
      // 稼働状況のセクションの札には, 年月を添える
      const updated = document.createElement('span');
      updated.className = 'copilotStatus__updated';
      updated.textContent = `AS OF ${year}.${String(month).padStart(2, '0')}`;
      elem.append(updated);
    } else {
      elem.textContent = `${year}年${month}月現在、${elem.textContent.trim()}`;
    }
  }
}
