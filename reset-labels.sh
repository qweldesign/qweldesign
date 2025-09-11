#!/bin/bash

set -e #エラーが出たら止める

echo "既存ラベルを削除中…"
gh label list --json name -q '.[].name' | while IFS= read -r label; do
  gh label delete "$label" --confirm
done

echo "新しいラベルを作成中…"
gh label create "バグ" \
  --color ffa2ca \
  --description "レイアウト・動作等の不具合" || true

gh label create "要望" \
  --color ffbc50 \
  --description "追加・修正の要望" || true

gh label create "レビュー待ち" \
  --color 95e594 \
  --description "ディレクター・デザイナーの確認待ち" || true

gh label create "保留" \
  --color 60e1ff \
  --description "クライアントの承認・確認待ち" || true

gh label create "提案" \
  --color c9bfff \
  --description "改善案の提案・相談等" || true

echo "完了！"
