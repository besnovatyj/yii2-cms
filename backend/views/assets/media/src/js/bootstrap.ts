/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Bootstrap 5 + Popper.js — глобальный экспорт.
 *
 * Собирается ESBuild в IIFE и выставляет window.bootstrap,
 * чтобы сторонние скрипты модулей (не входящие в этот бандл)
 * могли использовать Bootstrap JS API (Modal, Toast, Tooltip и т.д.).
 */
import * as bootstrap from 'bootstrap';

(window as any).bootstrap = bootstrap;
