/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * ESBuild configuration for backend admin assets
 *
 * JS:  src/js/bootstrap.ts → dist/js/bootstrap.js (Bootstrap 5 + Popper, экспортирует window.bootstrap)
 *      src/js/index.ts     → dist/js/index.js     (htmx, tooltips, grid-view — использует window.bootstrap)
 * CSS: src/scss/index.scss → dist/css/adm.css     (Bootstrap 5 + Bootstrap Icons bundled)
 * Fonts: node_modules/bootstrap-icons → dist/fonts/
 */

import * as esbuild from 'esbuild';
import * as sass from 'sass';
import * as fs from 'fs';
import * as path from 'path';
import {fileURLToPath} from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const isWatch = process.argv.includes('--watch');

/**
 * Копирует шрифты Bootstrap Icons из node_modules в dist/fonts/
 */
const copyFonts = () => {
  const src = path.join(__dirname, 'node_modules/bootstrap-icons/font/fonts');
  const dest = path.join(__dirname, 'dist/fonts');
  fs.mkdirSync(dest, {recursive: true});
  for (const file of fs.readdirSync(src)) {
    fs.copyFileSync(path.join(src, file), path.join(dest, file));
  }
  console.log('Fonts copied to dist/fonts/');
};

/**
 * Компилирует SCSS → dist/css/adm.css
 * Шрифты Bootstrap Icons ожидаются в dist/fonts/ (относительно CSS — ../fonts)
 */
const buildCSS = () => {
  try {
    const result = sass.compile('src/scss/index.scss', {
      style: 'compressed',
      sourceMap: true,
      loadPaths: [
        path.join(__dirname, 'node_modules'),
      ],
      quietDeps: true, // подавляет deprecation-предупреждения из node_modules
    });

    fs.mkdirSync('dist/css', {recursive: true});
    fs.writeFileSync('dist/css/adm.css', result.css);

    if (result.sourceMap) {
      fs.writeFileSync('dist/css/adm.css.map', JSON.stringify(result.sourceMap));
    }

    console.log('CSS build completed: dist/css/adm.css');
  } catch (error) {
    console.error('CSS build failed:', error.message);
    process.exit(1);
  }
};

/**
 * Компилирует TypeScript → dist/js/
 *
 * bootstrap.ts → dist/js/bootstrap.js  (Bootstrap 5 + Popper, экспортирует window.bootstrap)
 * index.ts     → dist/js/index.js      (htmx, tooltips, grid-view — использует window.bootstrap)
 *
 * bootstrap.js подключается ПЕРЕД index.js в layout.
 */
const buildJS = async () => {
  const commonOptions = {
    bundle: true,
    format: 'iife',
    target: 'es2020',
    sourcemap: true,
    minify: true,
    treeShaking: true,
    platform: 'browser',
    tsconfig: './tsconfig.json',
    logLevel: 'info',
  };

  const entries = [
    {
      ...commonOptions,
      entryPoints: ['src/js/bootstrap.ts'],
      outfile: 'dist/js/bootstrap.js',
    },
    {
      ...commonOptions,
      entryPoints: ['src/js/index.ts'],
      outfile: 'dist/js/index.js',
      // bootstrap уже глобальный, не бандлить повторно
      external: ['bootstrap'],
    },
  ];

  if (isWatch) {
    for (const options of entries) {
      const ctx = await esbuild.context(options);
      await ctx.watch();
    }
    console.log('Watching JS...');
  } else {
    for (const options of entries) {
      await esbuild.build(options);
    }
    console.log('JS build completed: dist/js/bootstrap.js, dist/js/index.js');
  }
};

/**
 * Упрощённый SCSS watcher через fs.watch
 */
const watchCSS = () => {
  console.log('Watching SCSS files...');
  fs.watch('src/scss', {recursive: true}, (eventType, filename) => {
    if (filename && filename.endsWith('.scss')) {
      console.log(`SCSS changed: ${filename}`);
      buildCSS();
    }
  });
};

const build = async () => {
  try {
    console.log('Starting build...');
    copyFonts();
    buildCSS();
    await buildJS();

    if (isWatch) {
      watchCSS();
      console.log('Watch mode active. Waiting for changes...');
    } else {
      console.log('Build completed successfully!');
    }
  } catch (error) {
    console.error('Build failed:', error);
    process.exit(1);
  }
};

build();
