<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * The manifest of files that are local to specific environment.
 * This file returns a list of environments that the application
 * may be installed under. The returned data must be in the following
 * format:
 *
 * ```php
 * return [
 *     'environment name' => [
 *         'path' => 'directory storing the local files',
 *         'skipFiles'  => [
 *             // list of files that should only be copied once and skipped if they already exist
 *         ],
 *         'setWritable' => [
 *             // list of directories that should be set writable
 *         ],
 *         'setExecutable' => [
 *             // list of files that should be set executable
 *         ],
 *         'setCookieValidationKey' => [
 *             // list of config files that need to be inserted with automatically generated cookie validation keys
 *         ],
 *         'createSymlink' => [
 *             // list of symlinks to be created. Keys are symlinks, and values are the targets.
 *         ],
 *     ],
 * ];
 * ```
 */
return [
    'Development' => [
        'path' => 'dev',
        'clearDirectories' => [
            'backend/pub/assets',
            'frontend/pub/assets',
        ],
        'setWritable' => [
            'var/runtime',
            'backend/pub/assets',
            'frontend/pub/assets',
        ],
        'setExecutable' => [
            'yii',
        ],
        'setCookieValidationKey' => [
            'common/config/params-local.php',
        ],
    ],
    'Production' => [
        'path' => 'prod',
        'clearDirectories' => [
            'backend/pub/assets',
            'frontend/pub/assets',
        ],
        'setWritable' => [
            'var/runtime',
            'backend/pub/assets',
            'frontend/pub/assets',
        ],
        'setExecutable' => [
            'yii',
        ],
        'setCookieValidationKey' => [
            'common/config/params-local.php',
        ],
    ],
];
