import { createApp } from 'https://cdnjs.cloudflare.com/ajax/libs/vue/3.0.0/vue.esm-browser.prod.js';

const data = {
  data() {
    return {
      sections: {
        feature: {
          id: 'feature',
          name: 'feature',
          items: [
            {
              heading: 'feature-1',
              src: './assets/sampleImg00.jpg'
            },
            {
              heading: 'feature-2',
              src: './assets/sampleImg01.jpg'
            },
            {
              heading: 'feature-3',
              src: './assets/sampleImg02.jpg'
            },
            {
              heading: 'feature-4',
              src: './assets/sampleImg03.jpg'
            }
          ]
        },
        about: {
          id: 'about',
          name: 'about',
          heading: 'Mission',
          excerpt: 'Our mission is ...',
          src: './assets/sampleImg01.jpg',
          more: 'read more'
        },
        service: {
          id: 'service',
          name: 'service',
          more: 'read more',
          items: [
            {
              date: '2025-04-01',
              heading: 'service-1',
              tags: ['news', 'info'],
              excerpt: 'Today is ...',
              src: './assets/sampleImg02.jpg'
            },
            {
              date: '2025-04-02',
              heading: 'service-2',
              tags: ['news'],
              excerpt: 'Today is ...',
              src: './assets/sampleImg03.jpg'
            },
            {
              date: '2025-04-03',
              heading: 'service-3',
              tags: ['info'],
              excerpt: 'Today is ...',
              src: './assets/sampleImg04.jpg'
            }
          ]
        },
        news: {
          id: 'news',
          name: 'news',
          more: 'read more',
          items: [
          {
              date: '2025-04-01',
              heading: 'news-1',
              tags: ['news', 'info'],
              excerpt: 'Today is ...',
              src: './assets/sampleImg03.jpg'
            },
            {
              date: '2025-04-02',
              heading: 'news-2',
              tags: ['news'],
              excerpt: 'Today is ...',
              src: './assets/sampleImg04.jpg'
            },
            {
              date: '2025-04-03',
              heading: 'news-3',
              tags: ['info'],
              excerpt: 'Today is ...',
              src: './assets/sampleImg05.jpg'
            }
          ]
        },
        team: {
          id: 'team',
          name: 'team',
          heading: 'Mission',
          excerpt: 'Our mission is ...',
          src: './assets/sampleImg04.jpg',
          more: 'read more'
        },
        access: {
          id: 'access',
          name: 'access',
          src: 'https://www.google.com/maps/embed'
        },
        contact: {
          id: 'contact',
          name: 'contact',
          description: 'サンプルデモであるため、このフォームは機能しません。'
        }
      },
      social: {
        x: true,
        facebook: true,
        instagram: true,
        youtube: false
      }
    };
  }
}

createApp(data).mount('#app');

// Header toggle
window.addEventListener('scroll', () => {
  const header = document.getElementById('header');
  if (0 < window.scrollY) {
    header.classList.add('header--active');
  } else {
    header.classList.remove('header--active');
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
import Embed from './js/embed.js';
new Embed();

// Fader
import Fader from './js/fader.js';
new Fader();

// Preloader
import Preloader from './js/preloader.js';
new Preloader();

// Responsive Color
import ResponsiveColor from './js/responsiveColor.js';
new ResponsiveColor();

// Reveal On Scroll
import RevealOnScroll from './js/revealOnScroll.js';
new RevealOnScroll();

// Slider
import Slider from './js/slider.js';
new Slider();
