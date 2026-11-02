// チラシの書き出し: cd tools/flyer && npm install && node export.js [dpi]
//
// tools/flyer/out/ に次のファイルを出力する
//   front.png, back.png   入稿用の面ごとの画像 (塗り足し込み 216 × 303 mm. 既定 350dpi)
//   flyer.pdf             入稿用 (塗り足し込み 216 × 303 mm. TrimBox に仕上がり 210 × 297 mm を入れる)
//   flyer-a4.pdf          家庭・コンビニのプリンター用 (A4 ちょうど)
//
// 画面の表示 (tools/flyer/index.php) と書き出しのサイズを切り分けるため, 寸法はページの data-sheet-* から読み,
// PDF の用紙の範囲は pdf-lib で mm から正確に設定する
// (Chrome は PDF の用紙の大きさを 0.96pt 刻みに丸めるため, 少し大きく書き出してから, 面の範囲に切り直す)
//
// チラシは PHP なので, 書き出しの間だけ php -S を立ち上げる (php コマンドが必要)
// Chromium の場所を指定する場合は CHROMIUM=/path/to/chrome node export.js
const path = require('path');
const fs = require('fs');
const { spawn } = require('child_process');
const { chromium } = require('playwright');
const { PDFDocument } = require('pdf-lib');

const root = path.join(__dirname, '..', '..');
const out = path.join(__dirname, 'out');
const dpi = parseInt(process.argv[2] || '350', 10);
const port = parseInt(process.env.PORT || '8765', 10);
const base = `http://127.0.0.1:${port}/tools/flyer/`;

// mm → pt (PDF の単位)
const mmToPt = (mm) => (mm * 72) / 25.4;

// PHP の簡易サーバーを立ち上げ, 応答するまで待つ
async function startServer() {
  const server = spawn('php', ['-S', `127.0.0.1:${port}`, '-t', root], { stdio: 'ignore' });
  for (let i = 0; i < 50; i++) {
    try {
      const res = await fetch(base);
      if (res.ok) return server;
    } catch {
      // まだ起動していない
    }
    await new Promise((resolve) => setTimeout(resolve, 100));
  }
  server.kill();
  throw new Error(`php -S が ${base} で応答しません`);
}

// ページを開き, 書体の読み込みを待って, 面の寸法 (mm) を返す
async function open(page, query) {
  await page.goto(base + '?print' + (query ? '&' + query : ''), { waitUntil: 'networkidle' });
  await page.evaluate(() => document.fonts.ready);
  await page.waitForTimeout(300);
  return page.evaluate(() => ({
    width: Number(document.body.dataset.sheetWidth),
    height: Number(document.body.dataset.sheetHeight),
    bleed: Number(document.body.dataset.bleed),
  }));
}

// 面ごとに PNG を書き出す (隣の面が端に写り込まないよう, 1面ずつ表示する)
async function exportPng(page, scale, names) {
  const count = await page.$$eval('.sheet', (els) => els.length);
  for (let i = 0; i < count; i++) {
    await page.$$eval('.sheet', (els, i) => els.forEach((el, j) => { el.style.display = i === j ? '' : 'none'; }), i);
    const box = await (await page.$$('.sheet'))[i].boundingBox();
    // 用紙の外 (端数の1px) が写らないよう, 端数を切り捨てて切り抜く
    const clip = {
      x: Math.ceil(box.x * scale) / scale,
      y: Math.ceil(box.y * scale) / scale,
      width: Math.floor(box.width * scale) / scale,
      height: Math.floor(box.height * scale) / scale,
    };
    await page.screenshot({ path: path.join(out, names[i] + '.png'), clip });
  }
  await page.$$eval('.sheet', (els) => els.forEach((el) => { el.style.display = ''; }));
}

// PDF を書き出し, 用紙の範囲を面の寸法 (mm) に正確に合わせる
async function exportPdf(page, size, file) {
  // 丸めで面より小さくならないよう, 1mm 大きい用紙に書き出す (面は左上に寄せて置かれる)
  const raw = await page.pdf({
    width: `${size.width + 1}mm`,
    height: `${size.height + 1}mm`,
    printBackground: true,
    preferCSSPageSize: false,
  });
  const doc = await PDFDocument.load(raw);
  const w = mmToPt(size.width);
  const h = mmToPt(size.height);
  const b = mmToPt(size.bleed);
  for (const p of doc.getPages()) {
    // PDF の座標は左下が原点. 面は用紙の左上にあるので, 上端から面の高さぶんを残す
    const y = p.getHeight() - h;
    p.setMediaBox(0, y, w, h);
    p.setCropBox(0, y, w, h);
    p.setBleedBox(0, y, w, h);
    // 仕上がり (塗り足しの内側)
    p.setTrimBox(b, y + b, w - b * 2, h - b * 2);
  }
  fs.writeFileSync(path.join(out, file), await doc.save());
}

(async () => {
  fs.mkdirSync(out, { recursive: true });
  const server = await startServer();
  const browser = await chromium.launch(process.env.CHROMIUM ? { executablePath: process.env.CHROMIUM } : {});
  try {
    const scale = dpi / 96;
    const page = await browser.newPage({ viewport: { width: 1200, height: 900 }, deviceScaleFactor: scale });

    // 入稿用 (塗り足しつき)
    const size = await open(page);
    // 面が画面に収まるようにする (はみ出した部分はスクリーンショットに写らないため)
    await page.setViewportSize({ width: 1200, height: Math.ceil((size.height * 96) / 25.4) + 100 });
    await exportPng(page, scale, ['front', 'back']);
    await exportPdf(page, size, 'flyer.pdf');

    // 家庭用 (A4)
    const sizeA4 = await open(page, 'bleed=0');
    await exportPdf(page, sizeA4, 'flyer-a4.pdf');

    console.log(`done ${dpi}dpi → ${path.relative(process.cwd(), out)}/ (front.png, back.png, flyer.pdf, flyer-a4.pdf)`);
  } finally {
    await browser.close();
    server.kill();
  }
})().catch((error) => {
  console.error(error);
  process.exit(1);
});
