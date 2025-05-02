/**
 * Auto Copyright
 * このファイルは QWEL Project の一部です。
 * Part of the QWEL Project © QWEL.DESIGN 2025
 * Licensed under GPL v3 – see https://qwel.design/
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
