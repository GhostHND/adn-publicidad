<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SECRETO COMPARTIDO
    |--------------------------------------------------------------------------
    */

    'web_secret' =>
        env(
            'ADN_WEB_INTEGRATION_SECRET'
        ),

    /*
    |--------------------------------------------------------------------------
    | ADN WEB
    |--------------------------------------------------------------------------
    */

    'web_url' =>
        env(
            'ADN_WEB_URL',
            'http://127.0.0.1:8001'
        ),

    /*
    |--------------------------------------------------------------------------
    | EDITOR ADN WEB
    |--------------------------------------------------------------------------
    |
    | URL base del Editor.
    |
    | LOCAL:
    | http://127.0.0.1:8001
    |
    | PRODUCCIÓN:
    | https://editor.adnpublicidad.site
    |
    | En local la APP agrega automáticamente /editor-preview
    | únicamente para el botón del sidebar.
    |
    */

    'editor_url' =>
        env(
            'ADN_WEB_EDITOR_URL',
            'http://127.0.0.1:8001'
        ),

    /*
    |--------------------------------------------------------------------------
    | ACCESO FIRMADO AL EDITOR
    |--------------------------------------------------------------------------
    |
    | Se conserva para el mecanismo existente de acceso a cotizaciones
    | específicas provenientes de ADN Web.
    |
    */

    'editor_access_ttl' =>
        (int) env(
            'ADN_WEB_EDITOR_ACCESS_TTL',
            90
        ),

    /*
    |--------------------------------------------------------------------------
    | HTTP APP -> WEB
    |--------------------------------------------------------------------------
    */

    'web_timeout' =>
        (int) env(
            'ADN_WEB_REQUEST_TIMEOUT',
            10
        ),

    'web_connect_timeout' =>
        (int) env(
            'ADN_WEB_REQUEST_CONNECT_TIMEOUT',
            3
        ),

    /*
    |--------------------------------------------------------------------------
    | SEGURIDAD
    |--------------------------------------------------------------------------
    */

    'max_clock_skew' =>
        (int) env(
            'ADN_WEB_INTEGRATION_MAX_SKEW',
            300
        ),
];