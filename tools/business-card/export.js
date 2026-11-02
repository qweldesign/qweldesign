// 名刺の書き出し
// 書き出し: node export.js [dpi]
// out/front.png, out/back.png (塗り足し込み 97x61mm) と out/business-card.pdf を出力する
const path = require('path');
const fs = require('fs');
const { chromium } = require('playwright');

(async () => {
  const dpi = parseInt(process.argv[2] || '350', 10);
  const scale = dpi / 96;
  const out = path.join(__dirname, 'out');
  fs.mkdirSync(out, { recursive: true });

  const browser = await chromium.launch(process.env.CHROMIUM ? { executablePath: process.env.CHROMIUM } : {});
  const page = await browser.newPage({ viewport: { width: 1200, height: 900 }, deviceScaleFactor: scale });

  // 印刷用の表示 (仕上がり線・影・余白を消す) で開く
  await page.emulateMedia({ media: 'print' });
  await page.goto('file://' + path.join(__dirname, 'index.html'));
  await page.evaluate(() => document.fonts.ready);
  // 雲の配置を待つ
  await page.waitForFunction(() => document.documentElement.dataset.cloudsReady === 'true');
  await page.waitForTimeout(300);

  // 1面ずつ表示して書き出す (隣の面が端に写り込まないように)
  const names = ['front', 'back'];
  const count = await page.$$eval('.card', (els) => els.length);
  for (let i = 0; i < count; i++) {
    await page.$$eval('.card', (els, i) => els.forEach((el, j) => { el.style.display = i === j ? '' : 'none'; }), i);
    const card = (await page.$$('.card'))[i];
    const box = await card.boundingBox();
    // 用紙の外 (端数の1px) が写らないよう, 端数を切り捨てて切り抜く
    const clip = {
      x: Math.ceil(box.x * scale) / scale,
      y: Math.ceil(box.y * scale) / scale,
      width: Math.floor(box.width * scale) / scale,
      height: Math.floor(box.height * scale) / scale,
    };
    await page.screenshot({ path: path.join(out, (names[i] || 'card-' + (i + 1)) + '.png'), clip });
  }
  await page.$$eval('.card', (els) => els.forEach((el) => { el.style.display = ''; }));

  // PDF: 1面 1ページ (塗り足し込み 97x61mm)
  await page.pdf({ path: path.join(out, 'business-card.pdf'), width: '97mm', height: '61mm', printBackground: true });

  await browser.close();
  console.log('done', dpi + 'dpi');
})();
