<?php

return [

    /*
    |--------------------------------------------------------------------------
    | EMPRESA
    |--------------------------------------------------------------------------
    */

    'business_name' =>
        env(
            'ADN_BUSINESS_NAME',
            'ADN Publicidad'
        ),

    'business_location' =>
        env(
            'ADN_BUSINESS_LOCATION',
            'Danlí, El Paraíso, Honduras'
        ),

    'phone' =>
        env(
            'ADN_BUSINESS_PHONE',
            ''
        ),

    'email' =>
        env(
            'ADN_BUSINESS_EMAIL',
            ''
        ),

    'website' =>
        env(
            'ADN_BUSINESS_WEBSITE',
            'adnpublicidad.site'
        ),

    /*
    |--------------------------------------------------------------------------
    | LOGO
    |--------------------------------------------------------------------------
    */

    'logo_path' =>
        env(
            'ADN_LOGO_PATH',
            ''
        ),

    /*
    |--------------------------------------------------------------------------
    | MONEDA
    |--------------------------------------------------------------------------
    */

    'currency_symbol' =>
        'L',

    /*
    |--------------------------------------------------------------------------
    | COMPARTIR DOCUMENTOS
    |--------------------------------------------------------------------------
    |
    | Estos textos posteriormente pasarán al módulo Configuración
    | para que puedan editarse desde la interfaz.
    |
    */

    'share' => [

        'quotation_message' =>
            'Hola {{cliente}}, le comparto la cotización {{numero}} de ADN Publicidad por un total de {{total}}. Quedo atento a cualquier consulta.',

        'receipt_message' =>
            'Hola {{cliente}}, gracias por su pago. Le comparto el recibo {{numero}} por {{monto}}. Saldo pendiente: {{saldo}}. Gracias por confiar en ADN Publicidad.',

    ],

];