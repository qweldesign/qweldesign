// G Nav Shrink
window.addEventListener('scroll', () => {
  const gNav = document.getElementById('gNav');
  if (0 < window.scrollY) {
    gNav.classList.add('gNav--shrink');
  } else {
    gNav.classList.remove('gNav--shrink');
  }
});

// Auto Copyright
import AutoCopyright from './js/autoCopyright.js';
new AutoCopyright(2019, 'QWEL.DESIGN');

// Back To Top
import BackToTop from './js/backToTop.js';
new BackToTop();

// Drawer Menu
import DrawerMenu from './js/drawerMenu.js';
new DrawerMenu();

// Embed
//import Embed from './js/embed.js';
//new Embed();

// Fader
//import Fader from './js/fader.js';
//new Fader();

// Modal
//import Modal from './js/modal.js';
//new Modal();

// Preloader
//import Preloader from './js/preloader.js';
//new Preloader();

// Responsive Color
//import ResponsiveColor from './js/responsiveColor.js';
//new ResponsiveColor();

// Reveal On Scroll
import RevealOnScroll from './js/revealOnScroll.js';
new RevealOnScroll();

// Slider
//import Slider from './js/slider.js';
//new Slider();
