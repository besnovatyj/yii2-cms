/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

// Только типы — импорт стирается при компиляции и не создаёт runtime-require.
// Bootstrap в этом бандле внешний (external), берётся из window.bootstrap,
// который выставляет bootstrap.ts.
import type * as Bootstrap from 'bootstrap';

const bs = (window as any).bootstrap as typeof Bootstrap;

/**
 * Открытие правого сайдбара сразу на нужной вкладке.
 *
 * Разметка декларативная: кнопка штатно открывает offcanvas через
 * data-bs-toggle="offcanvas", а дополнительный атрибут data-sidebar-tab="settings"
 * указывает, какую вкладку активировать. Контент вкладок присутствует в DOM
 * независимо от видимости offcanvas, поэтому таб можно переключить в тот же клик.
 */
export default function initSidebarRightTabs(): void {
  document.addEventListener('click', (evt) => {
    const trigger = (evt.target as HTMLElement)?.closest<HTMLElement>('[data-sidebar-tab]');
    if (!trigger) {
      return;
    }

    const tabId = trigger.dataset.sidebarTab;
    if (!tabId) {
      return;
    }

    // Ссылка-переключатель вкладки в самом сайдбаре (id вида "settings-tab").
    const tabLink = document.getElementById(`${tabId}-tab`);
    if (tabLink) {
      bs.Tab.getOrCreateInstance(tabLink).show();
    }
  });
}
