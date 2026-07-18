/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

// Только типы — импорт стирается при компиляции и не создаёт runtime-require.
// Bootstrap внешний (external), берётся из window.bootstrap, который выставляет bootstrap.ts.
import type * as Bootstrap from 'bootstrap';

const bs = (window as any).bootstrap as typeof Bootstrap;

/** Зона у края экрана (px), из которой начинается свайп-открытие. */
const EDGE_ZONE = 100;
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

type PeekSide = 'left' | 'right';

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

  // --- Визуальная подсказка: «ручка», выезжающая от края за пальцем ---
  const peeks: Record<PeekSide, HTMLElement | null> = { left: null, right: null };
  let activeSide: PeekSide | null = null;

  const getPeek = (side: PeekSide): HTMLElement => {
    let el = peeks[side];
    if (!el) {
      el = document.createElement('div');
      el.className = `swipe-peek swipe-peek--${side}`;
      el.setAttribute('aria-hidden', 'true');
      const icon = document.createElement('i');
      icon.className = side === 'left' ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
      el.appendChild(icon);
      document.body.appendChild(el);
      peeks[side] = el;
    }
    return el;
  };

  // Показать/подвинуть «ручку»: progress 0..1 — насколько свайп близок к порогу.
  const showPeek = (side: PeekSide, progress: number): void => {
    if (activeSide && activeSide !== side) {
      hidePeek();
    }
    const el = getPeek(side);
    activeSide = side;
    el.classList.remove('swipe-peek--resetting');
    const fraction = Math.max(0, Math.min(1, progress));
    const offset = side === 'left' ? (fraction - 1) * 100 : (1 - fraction) * 100;
    el.style.transform = `translateX(${offset}%)`;
    el.style.opacity = String(fraction);
  };

  // Убрать «ручку» с плавным возвратом к краю (инлайн-стили → CSS-дефолт).
  function hidePeek(): void {
    if (!activeSide) {
      return;
    }
    const el = peeks[activeSide];
    activeSide = null;
    if (!el) {
      return;
    }
    el.classList.add('swipe-peek--resetting');
    el.style.transform = '';
    el.style.opacity = '';
    el.addEventListener(
      'transitionend',
      () => el.classList.remove('swipe-peek--resetting'),
      { once: true },
    );
  }

  document.addEventListener('touchstart', (evt) => {
    if (evt.touches.length !== 1) {
      start = null;
      return;
    }
    const touch = evt.touches[0];
    start = { x: touch.clientX, y: touch.clientY };
    blocked = isHorizontallyScrollable(evt.target);
  }, { passive: true });

  document.addEventListener('touchmove', (evt) => {
    if (!start || blocked || evt.touches.length !== 1) {
      return;
    }
    const touch = evt.touches[0];
    const dx = touch.clientX - start.x;
    const dy = touch.clientY - start.y;

    // Пока жест не стал выраженно горизонтальным — подсказку не показываем.
    if (Math.abs(dx) <= Math.abs(dy) * HORIZONTAL_RATIO) {
      hidePeek();
      return;
    }

    const width = window.innerWidth;
    const isMobile = width < MD_BREAKPOINT;
    const leftOpen = left?.classList.contains('show') ?? false;
    const rightOpen = right?.classList.contains('show') ?? false;

    // Подсказка нужна только для жеста ОТКРЫТИЯ от соответствующего края.
    if (dx > 0 && left && isMobile && !leftOpen && start.x <= EDGE_ZONE) {
      showPeek('left', dx / THRESHOLD);
    } else if (dx < 0 && right && !rightOpen && start.x >= width - EDGE_ZONE) {
      showPeek('right', -dx / THRESHOLD);
    } else {
      hidePeek();
    }
  }, { passive: true });

  document.addEventListener('touchcancel', () => {
    start = null;
    hidePeek();
  }, { passive: true });

  document.addEventListener('touchend', (evt) => {
    const from = start;
    start = null;
    hidePeek();
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
