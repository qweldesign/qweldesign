// Action Core
import ActionCore from './js/action-core.js';
if (document.querySelector('[data-primary-menu]')) {
  // 本家: ヘッダー・ドロワーメニュー・スクロール位置に合わせたメニューの点灯・BackToTop など一式
  new ActionCore.Preset();
} else {
  // 工房: BackToTop だけ (ヘッダーにメニューがないので, ドロワーメニューは作らない)
  new ActionCore.BackToTop();
}

// Scene Switch (昼の便・夜の便の切り替えボタン)
import SceneSwitch from './js/scene-switch.js';
new SceneSwitch();

// Dot Band (背景の天の川・雲海)
import DotBand from './js/dot-band.js';
new DotBand();

// Contact Form (お問い合わせ. 本家: /contact/ と Co-Pilot の #contact, 工房: #contact と /small-site-studio/contact/)
// 各画面の URL (確認・完了・送信先) は, フォームと確認画面の data 属性 (各区画の contact テンプレートが出力する) から読む
import ContactForm from './js/contact-form.js';
new ContactForm();
