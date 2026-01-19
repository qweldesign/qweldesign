/**
 * Auto Copyright
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 */

export default class AutoCopyright {
  constructor(startYear, companyName, elem) {
    elem ||= document.querySelector('.footer__copyright');
    if (elem) elem.innerHTML = this.generate(startYear, companyName);
  }

  generate(startYear, companyName) {
    const currentYear = new Date().getFullYear();
    if (startYear == currentYear) {
      return `&copy; ${startYear} ${companyName}`
    } else {
      return `&copy; ${startYear} - ${currentYear} ${companyName}`;
    }
  }
}
