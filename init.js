// Action Core
import ActionCore from './js/action-core.js';
new ActionCore.Preset();

// Milky Way (背景の天の川)
import MilkyWay from './js/milky-way.js';
new MilkyWay();

/**
 * Auto Copyright
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 */

class AutoCopyright {
  constructor(startYear, companyName, elem) {
    elem ||= document.querySelector('.footer__copyright');
    if (elem) elem.innerHTML = this.generate(startYear, companyName);
  }

  generate(startYear, companyName) {
    const currentYear = new Date().getFullYear();
    return `&copy; ${startYear} - ${currentYear} ${companyName}`;
  }
}

new AutoCopyright(2019, 'QWEL.DESIGN');
