/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Экспорт ручек палитры для песочницы docs/palette-knobs.html.
 *
 *   npm run theme        →  docs/theme-defaults.js
 *
 * Берёт ВСЕ переменные src/scss/imports/_theme.scss через meta.module-variables —
 * новая ручка попадёт в экспорт сама, перечислять нечего. Плюс реальные ступени
 * --adm-<семейство>-<N>, посчитанные Sass, чтобы песочница в покое показывала
 * ровно то, что уйдёт в adm.css, а не свою JS-копию формул.
 *
 * Результат подключается в песочнице через <script src>: в отличие от fetch это
 * работает и при открытии страницы двойным кликом (file://).
 *
 * Все пути — от __dirname, поэтому запуск не зависит от текущей директории.
 */

import * as sass from 'sass';
import * as fs from 'fs';
import * as path from 'path';
import {fileURLToPath} from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const loadPaths = [path.join(__dirname, 'src/scss'), path.join(__dirname, 'node_modules')];
const outFile = path.join(__dirname, 'docs/theme-defaults.js');

/**
 * Sass-значение → обычный JS: цвета в hex, карты в объекты, списки в массивы
 */
const toJs = (v) => {
  if (v instanceof sass.SassColor) {
    const hex = (ch) => Math.round(v.channel(ch, {space: 'rgb'})).toString(16).padStart(2, '0');
    return `#${hex('red')}${hex('green')}${hex('blue')}`;
  }
  if (v instanceof sass.SassNumber) return v.hasUnits ? `${v.value}${v.numeratorUnits.join('')}` : v.value;
  if (v instanceof sass.SassString) return v.text;
  if (v instanceof sass.SassBoolean) return v.value;
  if (v instanceof sass.SassMap) {
    const o = {};
    for (const [k, val] of v.contents) o[toJs(k)] = toJs(val);
    return o;
  }
  if (v instanceof sass.SassList) return v.asList.toArray().map(toJs);
  return null;
};

/**
 * Все переменные модуля _theme.scss одной картой
 */
const readTheme = () => {
  let theme = null;
  sass.compileString(
    '@use "sass:meta"; @use "imports/theme"; :root { --adm-export: #{adm-export(meta.module-variables("theme"))}; }',
    {
      loadPaths,
      functions: {
        'adm-export($vars)': (args) => {
          theme = toJs(args[0]);
          return new sass.SassString('ok');
        },
      },
    },
  );
  return theme;
};

/**
 * Ступени --adm-<семейство>-<N>, как их посчитал Sass.
 * Компилируется только палитра, без Bootstrap — быстро и не зависит от dist/.
 */
const readPalette = () => {
  const css = sass.compileString('@use "imports/palette";', {loadPaths, style: 'compressed'}).css;
  const palette = {};
  // #abc → #aabbcc: compressed-вывод укорачивает hex, где может
  for (const m of css.matchAll(/--adm-([a-z]+)-(\d+):\s*(#[0-9a-f]{3,8})\b/gi)) {
    const h = m[3].toLowerCase();
    (palette[m[1]] ??= {})[m[2]] = h.length === 4 ? '#' + [...h.slice(1)].map((c) => c + c).join('') : h;
  }
  return palette;
};

export const exportTheme = () => {
  const data = {builtAt: new Date().toISOString(), theme: readTheme(), palette: readPalette()};
  fs.mkdirSync(path.dirname(outFile), {recursive: true});
  fs.writeFileSync(
    outFile,
    '// Генерируется командой `npm run theme` (export-theme.js). Руками не править.\n' +
    `window.ADM_THEME = ${JSON.stringify(data, null, 2)};\n`,
  );
  console.log(`Theme exported: ${path.relative(__dirname, outFile)}`);
};

// Запущен напрямую (npm run theme / кнопка в IDE), а не импортирован
if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  try {
    exportTheme();
  } catch (error) {
    console.error('Theme export failed:', error.message);
    process.exit(1);
  }
}
