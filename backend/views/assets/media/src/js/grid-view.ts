/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

import * as bootstrap from 'bootstrap';

/**
 * Показывает прелоадер на всю страницу и скрывает его при завершении навигации.
 * Работает как с pjax (yii2), так и с полной перезагрузкой страницы.
 */
function showPagePreloader(): void {
  const el = document.createElement('div');
  el.className = 'grid-filter-preloader';
  el.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Загрузка...</span></div>';
  document.body.appendChild(el);

  // Скрываем при завершении pjax-навигации (Yii2 использует jQuery pjax,
  // который пробрасывает события как нативные DOM-события на контейнере)
  document.addEventListener('pjax:complete', () => el.remove(), { once: true });

  // Защитный таймаут: если ни pjax, ни перезагрузка не убрали прелоадер
  const fallback = window.setTimeout(() => el.remove(), 30_000);

  // При полной перезагрузке страницы прелоадер и так исчезнет,
  // но на случай history.back и т.п. — чистим таймер при выгрузке
  window.addEventListener('pagehide', () => window.clearTimeout(fallback), { once: true });
}

/**
 * Основная логика адаптации GridView под мобильные устройства.
 * Фильтры в оффканвас рендерятся на сервере через $this->params['mobileFiltersForm'],
 * поэтому здесь остаётся только синхронизация data-label и прелоадер.
 */
export default function initMobileGridView(): void {
  const table = document.querySelector<HTMLTableElement>('.table-mobile-cards');
  if (table) {
    syncTableLabels(table);
  }

  // Прелоадер при сабмите серверно-рендеренной формы фильтров в оффканвас
  const mobileForm = document.querySelector<HTMLFormElement>('#offcanvasFilters form');
  mobileForm?.addEventListener('submit', () => {
    showPagePreloader();
  });
}

/**
 * Копирует заголовки таблицы в атрибуты data-label ячеек для CSS-карточек на мобилке
 */
function syncTableLabels(table: HTMLTableElement): void {
  const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent?.trim() || '');
  const rows = table.querySelectorAll('tbody tr:not(.empty)');

  rows.forEach(row => {
    const cells = row.querySelectorAll('td');
    cells.forEach((td, index) => {
      if (headers[index]) {
        td.setAttribute('data-label', headers[index]);
      }
    });
  });
}
