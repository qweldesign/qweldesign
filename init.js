// Action Core (ヘッダー・ドロワーメニュー・スクロール位置に合わせたメニューの点灯・BackToTop など)
import ActionCore from './js/action-core.js';
new ActionCore.Preset();

// Scene Switch (昼の便・夜の便の切り替えボタン)
import SceneSwitch from './js/scene-switch.js';
new SceneSwitch();

// Dot Band (背景の天の川・雲海)
import DotBand from './js/dot-band.js';
new DotBand();

// Flight Status (Co-Pilot の稼働状況: [data-flight-status]. 年月はページを開いた日から)
// 稼働が埋まってきたら 'open' を 'consult' に書き換えると,「稼働は応相談です」の表示になる
import FlightStatus from './js/flight-status.js';
new FlightStatus('open');

// Contact Form (お問い合わせ: /contact/ と Co-Pilot の #contact → /contact/confirm/ → api/send.php → /contact/thanks/)
// 各画面の URL は, フォームと確認画面の data 属性 (inc/templates/contact.php が出力する) から読む
import ContactForm from './js/contact-form.js';
new ContactForm();
