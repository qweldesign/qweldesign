<?php
/**
 * copilot.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * Co-Pilot (/co-pilot/) の, content/ から作る部品 (実績, 料金の目安)
 * 本文は co-pilot/index.php. 数値 (返信の日数, 最低受注額など) は content/options.json (fill_options で差し込む)
 *
 * 原稿:
 *   content/co-pilot/records.json  匿名の実績 (title: 業種・案件の種類, role: 担当した範囲・使った技術)
 *   content/works/*.md             公開している実績 (title, website, img, order. ちいさなサイト工房と同じ書き方)
 *   content/co-pilot/fares.json    料金の目安 (工程ごとの項目と金額)
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/ContentLoader.php';

// 匿名の実績 (業種・案件の種類 / 担当した範囲・使った技術 の2列)
function render_copilot_records(): void {
  $records = content_json('co-pilot/records')['records'] ?? [];
?>
              <ul class="copilotRecords" data-readable>
<?php foreach ($records as $record) { ?>
                <li>
                  <span><?= e($record['title'] ?? '') ?></span>
                  <span><?= e($record['role'] ?? '') ?></span>
                </li>
<?php } ?>
              </ul>
<?php
}

// 公開している実績の画面 (content/works/*.md を order の小さい順に)
function render_copilot_shots(): void {
  $works = (new ContentLoader(site_file('content/works')))->load();
  usort($works, fn($a, $b) => (int)($a['order'] ?? PHP_INT_MAX) <=> (int)($b['order'] ?? PHP_INT_MAX));
?>
              <ul class="copilotShots" data-readable>
<?php foreach ($works as $work) { ?>
<?php $domain = preg_replace('#^https?://(www\.)?|/$#', '', $work['website'] ?? ''); ?>
                <li><a href="<?= e($work['website'] ?? '') ?>" target="_blank" rel="noopener"><img src="<?= e(path($work['img'] ?? '')) ?>" alt="<?= e(($work['title'] ?? '') . 'のサイト画面') ?>" width="1200" height="750" loading="lazy"><?= e($domain) ?></a></li>
<?php } ?>
              </ul>
<?php
}

/**
 * 料金の目安の金額の表示
 * min だけ: 30,000円〜 / min と max: 15,000〜30,000円 / price: 4,500円 / text: 金額の代わりの文
 * unit があれば金額の前に添える (例: 時間単価 4,500円, 人日 30,000〜36,000円)
 */
function copilot_fare_price(array $item): string {
  if (isset($item['text'])) return $item['text'];
  $yen = fn($amount) => number_format((int)$amount);
  if (isset($item['price'])) {
    $price = $yen($item['price']) . '円';
  } elseif (isset($item['min'], $item['max'])) {
    $price = $yen($item['min']) . '〜' . $yen($item['max']) . '円';
  } elseif (isset($item['min'])) {
    $price = $yen($item['min']) . '円〜';
  } else {
    $price = '';
  }
  return trim(($item['unit'] ?? '') . ' ' . $price);
}

// 料金の目安の表 (工程ごとに, 工程名のセルを縦につなぐ)
function render_copilot_fares(): void {
  $fares = content_json('co-pilot/fares');
?>
              <div class="copilotFare" data-readable>
                <table>
                  <thead>
                    <tr><th scope="col">工程</th><th scope="col">項目</th><th scope="col" class="copilotFare__price">目安 (<?= e($fares['tax'] ?? '税別') ?>)</th></tr>
                  </thead>
                  <tbody>
<?php foreach ($fares['groups'] ?? [] as $group) { ?>
<?php foreach ($group['items'] ?? [] as $i => $item) { ?>
                    <tr><?php if ($i === 0) { ?><td class="copilotFare__group"<?= count($group['items']) > 1 ? ' rowspan="' . count($group['items']) . '"' : '' ?>><?= e($group['group'] ?? '') ?></td><?php } ?><td><?= e($item['name'] ?? '') ?></td><td class="copilotFare__price<?= isset($item['text']) ? ' is-text' : '' ?>"><?= e(copilot_fare_price($item)) ?></td></tr>
<?php } ?>
<?php } ?>
                  </tbody>
                </table>
              </div>
<?php
}
