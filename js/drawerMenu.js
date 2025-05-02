/**
 * Drawer Menu
 * このファイルは QWEL Project の一部です。
 * Part of the QWEL Project © QWEL.DESIGN 2025
 * Licensed under GPL v3 – see https://qwel.design/
 */

export default class DrawerMenu {
  // options
  // siteBrand, primaryMenu, socialMenu: クローンする対象
  constructor(options = {}) {
    // クローンする対象
    this.siteBrand = options.siteBrand ||
      document.querySelector('.gNav__siteBrand');
    this.primaryMenu = options.primaryMenu ||
      document.querySelector('.gNav__primaryMenu');
    this.socialMenu = options.socialMenu ||
      document.querySelector('.gNav__socialMenu');

    // 各要素生成
    // .drawer
    this.drawer = document.createElement('button');
    this.drawer.classList.add('drawer', 'drawer--ready');

    // .drawer__navicon
    this.navicon = document.createElement('div');
    this.navicon.classList.add('drawer__navicon');
    let icon = document.createElement('div');
    icon.classList.add('icon', 'icon--menu', 'icon--lg');
    icon.innerHTML = '<span class="icon__span"></span>';
    this.navicon.appendChild(icon);
    this.drawer.appendChild(this.navicon);

    // .drawer__close
    this.close = document.createElement('div');
    this.close.classList.add('drawer__close');
    icon = document.createElement('div');
    icon.classList.add('icon', 'icon--close', 'icon--lg');
    icon.innerHTML = '<span class="icon__span"></span>';
    this.close.appendChild(icon);
    this.drawer.appendChild(this.close);

    // .drawerMenu
    this.drawerMenu = document.createElement('div');
    this.drawerMenu.classList.add('drawerMenu');

    // .drawerMenu__inner
    this.menu = document.createElement('div');
    this.menu.classList.add('drawerMenu__inner');
    this.drawerMenu.appendChild(this.menu);
    this.importMenu();

    // .drawerMenuOverlay
    this.overlay = document.createElement('div');
    this.overlay.classList.add('drawerMenuOverlay', 'drawerMenuOverlay--collapse');

    // body要素に挿入
    const body = document.body;
    body.appendChild(this.drawer);
    body.appendChild(this.drawerMenu);
    body.appendChild(this.overlay);

    // 状態
    this.isShown = false;

    // イベント登録
    this.handleEvents();

    // 出現アニメーション
    setTimeout(() => {
      this.drawer.classList.remove('drawer--ready');
    }, 1000);
  }

  toggle() {
    // 状態から判別して、表示/非表示を切り替え
    if (this.isShown) this.hide();
    else this.show();
  }

  show() {
    // 表示
    if (!this.isShown) {
      this.transitionEnd(this.drawerMenu, () => {
        this.drawerMenu.classList.add('drawerMenu--show');
        this.drawer.classList.add('drawer--active');
        this.menu.classList.remove('drawerMenu__inner--collapse');
        this.overlay.classList.remove('drawerMenuOverlay--collapse');
      }).then(() => {
        this.menu.classList.add('drawerMenu__inner--show');
      });
    }
    this.isShown = true;
  }

  hide() {
    // 非表示
    if (this.isShown) {
      this.transitionEnd(this.drawerMenu, () => {
        this.drawerMenu.classList.remove('drawerMenu--show');
        this.drawer.classList.remove('drawer--active');
        this.menu.classList.remove('drawerMenu__inner--show');
      }).then(() => {
        this.menu.classList.add('drawerMenu__inner--collapse');
        this.overlay.classList.add('drawerMenuOverlay--collapse');
      });
    }
    this.isShown = false;
  }

  importMenu() {
    // メニューアイテムをインポート
    if (this.siteBrand) {
      this.importSiteBrand(); // .drawerMenu__siteBrand
    }
    if (this.primaryMenu) {
      this.importPrimaryMenu(); // .drawerMenu__primaryMenu
    }
    if (this.socialMenu) {
      this.importSocialMenu(); // .drawerMenu__socialMenu
    }
  }

  importSiteBrand() {
    // ブランドロゴ・タイトルをインポート
    const siteBrand = document.createElement('div');
    siteBrand.classList.add('drawerMenu__item', 'drawerMenu__item--siteBrand');
    siteBrand.innerHTML = this.siteBrand.innerHTML;
    this.menu.appendChild(siteBrand);
  }

  importPrimaryMenu() {
    // プライマリメニューをインポート
    const primaryMenu = document.createElement('ul');
    primaryMenu.classList.add('drawerMenu__primaryMenu');

    // li要素を順次インポート
    const menuItems = this.primaryMenu.querySelectorAll('li');
    menuItems.forEach((menuItem) => {
      const primaryMenuItem = document.createElement('li');
      primaryMenuItem.classList.add('drawerMenu__item');
      primaryMenuItem.innerHTML = menuItem.innerHTML;
      primaryMenu.appendChild(primaryMenuItem);
    });
    this.menu.appendChild(primaryMenu);
  }

  importSocialMenu() {
    // ソーシャルメニューをインポート
    const socialMenu = document.createElement('ul');
    socialMenu.classList.add('drawerMenu__socialMenu');

    // li要素を順次インポート
    const menuItems = this.socialMenu.querySelectorAll('li');
    menuItems.forEach((menuItem) => {
      const socialMenuItem = document.createElement('li');
      socialMenuItem.classList.add('drawerMenu__item', 'drawerMenu__item--social');
      socialMenuItem.innerHTML = menuItem.innerHTML;
      socialMenu.appendChild(socialMenuItem);
    });
    this.menu.appendChild(socialMenu);
  }

  handleEvents() {
    const myTouch = 'ontouchend' in document && window.innerWidth < 1024 ? 'touchend' : 'click';

    // ドロワーのイベント登録
    this.drawer.addEventListener(myTouch, (event) => {
      event.preventDefault();
      this.toggle();
    });

    // オーバーレイのイベント登録
    this.overlay.addEventListener(myTouch, () => {
      this.hide();
    });

    // スクロール時のイベント登録
    window.addEventListener('scroll', () => {
      this.windowScrollHandler();
    });
  }

  windowScrollHandler() {
    // スクロール時にメニューを非表示
    if (this.isShown) this.hide();
  }

  transitionEnd(elem, func) {
    // CSS遷移の完了を監視
    let callback;
    const promise = new Promise((resolve, reject) => {
      callback = () => resolve(elem);
      elem.addEventListener('transitionend', callback);
    });
    func();
    promise.then((elem) => {
      elem.removeEventListener('transitionend', callback);
    });
    return promise;
  }
}
