<?php

/**
 * Returns the importmap for this application.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@symfony/ux-disclose' => [
        'path' => './vendor/symfony/ux-disclose/assets/dist/controller.js',
    ],
];
