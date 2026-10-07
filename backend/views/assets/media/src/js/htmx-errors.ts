/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Глобальный показ ошибок htmx-запросов админки.
 *
 * По умолчанию htmx на ответ 4xx/5xx ничего не меняет на странице, и действие выглядит так,
 * будто просто не сработало. Здесь ошибка показывается тостом `showAlert` (виджет алертов,
 * подключается layout'ом админки).
 *
 * Каждый htmx-запрос помечается заголовком `X-Requested-With: XMLHttpRequest` — тогда Yii
 * (`yii\web\ErrorHandler::renderException()` → `shouldRenderSimpleHtml()`) отвечает на ошибку
 * коротким `<pre>Имя: текст</pre>` вместо целой страницы ошибки. Текст `HttpException` и прочих
 * `UserException` приходит как есть; непредвиденная ошибка в проде — «An internal server error
 * occurred.», подробности только в YII_DEBUG и логах.
 *
 * Свой показ ошибок: атрибут `data-htmx-errors="local"` на элементе или его предке — глобальный
 * обработчик такие запросы пропускает.
 */

interface AlertConfig {
  message: string;
  type?: string;
  duration?: number;
}

/** Длинный текст (трассировка в YII_DEBUG) в тост не помещается — полный есть в логах и debug. */
const MAX_LENGTH = 600;

/**
 * Обработка этого запроса оставлена странице.
 */
function isLocal(element: Element | null): boolean {
  return element?.closest('[data-htmx-errors="local"]') != null;
}

/**
 * Текст ошибки из ответа. DOMParser не исполняет разметку ответа, в отличие от innerHTML.
 */
function errorText(xhr: XMLHttpRequest): string {
  const text = new DOMParser()
    .parseFromString(xhr.responseText || '', 'text/html')
    .body.textContent?.trim() ?? '';

  if (text === '') {
    return `${xhr.status} ${xhr.statusText}`.trim();
  }
  return text.length > MAX_LENGTH ? text.slice(0, MAX_LENGTH) + '…' : text;
}

/**
 * `showAlert` вставляет сообщение как HTML — текст ответа нужно экранировать.
 */
function escapeHtml(text: string): string {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Показывает ошибку тостом; без виджета алертов — системным окном, но не молча.
 */
function showError(text: string): void {
  const showAlert = (window as unknown as { showAlert?: (config: AlertConfig) => void }).showAlert;
  if (typeof showAlert === 'function') {
    showAlert({ message: escapeHtml(text), type: 'error', duration: 0 });
  } else {
    window.alert(text);
  }
}

export default function initHtmxErrors(): void {
  document.addEventListener('htmx:configRequest', (evt: Event) => {
    const detail = (evt as CustomEvent<{ headers: Record<string, string> }>).detail;
    detail.headers['X-Requested-With'] = 'XMLHttpRequest';
  });

  document.addEventListener('htmx:responseError', (evt: Event) => {
    const detail = (evt as CustomEvent<{ xhr: XMLHttpRequest; elt: Element }>).detail;
    if (isLocal(detail.elt)) {
      return;
    }
    showError(errorText(detail.xhr));
  });

  document.addEventListener('htmx:sendError', (evt: Event) => {
    const detail = (evt as CustomEvent<{ elt: Element }>).detail;
    if (isLocal(detail.elt)) {
      return;
    }
    showError('Сервер не ответил: проверьте соединение и повторите действие.');
  });
}
