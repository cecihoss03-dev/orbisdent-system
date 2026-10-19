<?php

return [
    'currency_symbol' => env('ORBIS_CURRENCY_SYMBOL', 'Bs.'),

    'subscriptions' => [
        // Avisar cuando falten esta cantidad de días (o menos) para el fin.
        'alert_days_before' => (int) env('ORBIS_ALERT_DAYS', 2),
    ],

    'whatsapp' => [
        // callmebot | twilio | log   (log = solo escribe en storage/logs, para probar)
        'driver' => env('ORBIS_WHATSAPP_DRIVER', 'log'),

        // Tu número en formato internacional sin '+': 5917XXXXXXXX
        'admin_phone' => env('ORBIS_ADMIN_WHATSAPP'),

        'callmebot' => [
            'apikey' => env('CALLMEBOT_APIKEY'),
        ],

        'twilio' => [
            'sid'   => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from'  => env('TWILIO_WHATSAPP_FROM', '+14155238886'), // sandbox por defecto
        ],
    ],
];
