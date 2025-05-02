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
