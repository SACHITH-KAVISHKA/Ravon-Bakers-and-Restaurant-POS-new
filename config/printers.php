<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Printer Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your KOT and BOT printers here. Supports Network (IP), USB, and Bluetooth.
    | For network printers, use IP:PORT format.
    |
    */

    'enabled' => env('PRINTER_ENABLED', true),

    'default_timeout' => env('PRINTER_TIMEOUT', 3), // seconds

    'printers' => [
        'kot' => [
            'enabled' => env('KOT_PRINTER_ENABLED', true),
            'type' => env('KOT_PRINTER_TYPE', 'network'), // network, usb, bluetooth
            'connector' => env('KOT_PRINTER_CONNECTOR', '192.168.1.100:9100'), // IP:PORT for network
            'name' => 'Kitchen Printer',
        ],

        'bot' => [
            'enabled' => env('BOT_PRINTER_ENABLED', true),
            'type' => env('BOT_PRINTER_TYPE', 'network'), // network, usb, bluetooth
            'connector' => env('BOT_PRINTER_CONNECTOR', '192.168.1.101:9100'), // IP:PORT for network
            'name' => 'Bar Printer',
        ],

        'pos' => [
            'enabled' => env('POS_PRINTER_ENABLED', true),
            'type' => env('POS_PRINTER_TYPE', 'network'), // network, usb, bluetooth
            'connector' => env('POS_PRINTER_CONNECTOR', '192.168.1.102:9100'), // IP:PORT for network
            'name' => 'POS Printer',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Print Settings
    |--------------------------------------------------------------------------
    */

    'settings' => [
        'paper_width' => 48, // characters per line (80mm = 48, 58mm = 32)
        'font_size' => 'normal', // normal, wide, tall, double
        'copies' => 1, // number of copies to print
        'cut_paper' => true, // auto-cut after print
        'open_drawer' => false, // open cash drawer
        'beep' => false, // beep after print
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Print Settings
    |--------------------------------------------------------------------------
    */

    'auto_print' => [
        'kot' => env('AUTO_PRINT_KOT', true),
        'bot' => env('AUTO_PRINT_BOT', true),
        'receipt' => env('AUTO_PRINT_RECEIPT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Options
    |--------------------------------------------------------------------------
    */

    'fallback' => [
        'save_to_file' => env('PRINTER_FALLBACK_SAVE', true), // save print jobs to file if printer fails
        'file_path' => storage_path('app/print_jobs'),
        'log_errors' => true,
    ],
];
