/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

import initMobileGridView from './grid-view';
import initSidebarRightTabs from './sidebar-right';
import initSwipeNavigation from './swipe-navigation';
import 'htmx.org';


document.addEventListener('DOMContentLoaded', () => {

  // --- Инициализация htmx ---
  window.htmx = require('htmx.org');

  // Добавляет CSRF-токен Yii2 ко всем htmx POST-запросам.
  // Yii2 регистрирует <meta name="csrf-token"> через layout автоматически.
  document.addEventListener('htmx:configRequest', function (evt) {
    let meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
      evt.detail.headers['X-CSRF-Token'] = meta.getAttribute('content') || '';
    }
  });

  // --- Инициализация Tooltips ---
  // bootstrap загружен глобально через bootstrap.ts → window.bootstrap
  const bs = (window as any).bootstrap;

  // https://getbootstrap.com/docs/5.3/components/tooltips/
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bs.Tooltip(tooltipTriggerEl));

  // https://getbootstrap.com/docs/5.0/components/offcanvas/#methods
  // const sidebarMenu = document.getElementById('sidebarMenu');
  // const sidebarMenuOffCanvas = new bootstrap.Offcanvas(sidebarMenu);
  //
  // // sidebarMenuOffCanvas.show();
  // // sidebarMenuOffCanvas.toggle();
  // sidebarMenuOffCanvas.hide();

  // --- Инициализация мобильного GridView ---
  initMobileGridView();

  // --- Открытие правого сайдбара на нужной вкладке ---
  initSidebarRightTabs();

  // --- Свайп-навигация по сайдбарам на тач-устройствах ---
  initSwipeNavigation();

});


