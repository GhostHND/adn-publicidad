<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SITIO PÚBLICO
    |--------------------------------------------------------------------------
    */

    'public_url' =>
        env(
            'ADN_PUBLIC_URL',
            'https://www.adnpublicidad.site'
        ),

    /*
    |--------------------------------------------------------------------------
    | SISTEMA ADMINISTRATIVO / PWA
    |--------------------------------------------------------------------------
    */

    'admin_url' =>
        env(
            'ADN_ADMIN_URL',
            'https://app.adnpublicidad.site'
        ),

    /*
    |--------------------------------------------------------------------------
    | APLICAR SEPARACIÓN DE DOMINIOS
    |--------------------------------------------------------------------------
    |
    | En desarrollo local permanecerá ignorado automáticamente.
    |
    */

    'enforce' =>
        env(
            'ADN_ENFORCE_DOMAINS',
            false
        ),

];