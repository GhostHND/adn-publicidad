<?php

return [
    'subject' =>
        env(
            'VAPID_SUBJECT',
            'https://www.adnpublicidad.site'
        ),

    'public_key' =>
        env(
            'VAPID_PUBLIC_KEY'
        ),

    'private_key' =>
        env(
            'VAPID_PRIVATE_KEY'
        ),
];