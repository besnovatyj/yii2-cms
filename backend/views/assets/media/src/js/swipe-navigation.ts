/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

// Только типы — импорт стирается при компиляции и не создаёт runtime-require.
// Bootstrap внешний (external), берётся из window.bootstrap, который выставляет bootstrap.ts.
import type * as Bootstrap from 'bootstrap';

const bs = (window as any).bootstrap as typeof Bootstrap;

/** Зона у края экрана (px), из которой начинается свайп-открытие. */
const EDGE_ZONE = 32;
/** Минимальная горизонтальная дистанция свайпа (px). */
const THRESHOLD = 60;
/** Во сколько раз горизонтальная составляющая должна превышать вертикальную. */
const HORIZONTAL_RATIO = 1.5;
/** Bootstrap-брейкпоинт md: левый сайдбар — offcanvas только ниже него. */
const MD_BREAKPOINT = 768;

interface TouchPoint {
  x: number;
  y: number;
}

/**
 * Есть ли среди предков элемента горизонтально прокручиваемый контейнер
 * (например, мобильная карточная таблица GridView). В таком случае свайп
 * трактуется как прокрутка контента, а не как жест навигации.
 */
function isHorizontallyScrollable(target: EventTarget | null): boolean {
  let el = target as HTMLElement | null;
  while (el && el !== document.body) {
    if (el.scrollWidth > el.clientWidth && /(auto|scroll)/.test(getComputedStyle(el).overflowX)) {
      return true;
    }
    el = el.parentElement;
  }
  return false;
}

/**
 * Открытие/закрытие сайдбаров горизонтальными свайпами на тач-устройствах.
 *
 * Жесты (только один палец):
 *  - свайп вправо от левого края      → открыть левый сайдбар  (#sidebarMenu, ниже md);
 *  - свайп влево  от правого края     → открыть правый сайдбар (#rightSidebarMenu);
 *  - свайп в обратную сторону         → закрыть открытый сайдбар.
 *
 * Управление идёт через штатный Bootstrap Offcanvas API, поэтому бэкдроп,
 * блокировка скролла body и доступность работают как при клике по кнопке.
 * Левый сайдбар — responsive-offcanvas (offcanvas-md), поэтому открывается
 * свайпом только когда реально ведёт себя как offcanvas (ширина < md).
 */
export default function initSwipeNavigation(): void {
  const left = document.getElementById('sidebarMenu');
  const right = document.getElementById('rightSidebarMenu');
  if (!left && !right) {
    return;
  }

  let start: TouchPoint | null = null;
  let blocked = false;

  document.addEventListener('touchstart', (evt) => {
    if (evt.touches.length !== 1) {
      start = null;
      return;
    }
    const touch = evt.touches[0];
    start = { x: touch.clientX, y: touch.clientY };
    blocked = isHorizontallyScrollable(evt.target);
  }, { passive: true });

  document.addEventListener('touchend', (evt) => {
    const from = start;
    start = null;
    if (!from || blocked) {
      return;
    }

    const touch = evt.changedTouches[0];
    const dx = touch.clientX - from.x;
    const dy = touch.clientY - from.y;

    // Жест засчитывается только как выраженно горизонтальный.
    if (Math.abs(dx) < THRESHOLD || Math.abs(dx) < Math.abs(dy) * HORIZONTAL_RATIO) {
      return;
    }

    const width = window.innerWidth;
    const isMobile = width < MD_BREAKPOINT;
    const leftOpen = left?.classList.contains('show') ?? false;
    const rightOpen = right?.classList.contains('show') ?? false;

    if (dx > 0) {
      // Свайп вправо: закрыть правый либо открыть левый от левого края.
      if (rightOpen && right) {
        bs.Offcanvas.getOrCreateInstance(right).hide();
      } else if (left && isMobile && !leftOpen && from.x <= EDGE_ZONE) {
        bs.Offcanvas.getOrCreateInstance(left).show();
      }
    } else {
      // Свайп влево: закрыть левый либо открыть правый от правого края.
      if (leftOpen && left) {
        bs.Offcanvas.getOrCreateInstance(left).hide();
      } else if (right && !rightOpen && from.x >= width - EDGE_ZONE) {
        bs.Offcanvas.getOrCreateInstance(right).show();
      }
    }
  }, { passive: true });
}
