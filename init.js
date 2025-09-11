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

// Reveal On Scroll
import RevealOnScroll from './js/revealOnScroll.js';
const revealOnScroll = new RevealOnScroll();

// Posts
(async () => {
  // 記事データを取得
  const res = await fetch('./blog/posts.json');
  const content = await res.json();

  const list = document.getElementById('post');
  const template = document.getElementById('postTemplate');
  content.forEach((post) => {
    const item = template.content.cloneNode(true);
    const a = item.querySelector('a');
    const img = a.querySelector('.post__image');
    const date = a.querySelector('.post__date');
    const title = a.querySelector('.post__title');
    a.setAttribute('href', `./blog/?post=${post.slug}`);
    img.setAttribute('src', `./blog/images${post.imgUrl}`)
    date.textContent = post.date;
    title.textContent = post.title;
    list.appendChild(item);
  });

  revealOnScroll.refresh();
})();