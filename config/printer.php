<?php

return [
    // usb | network | windows | cups
    'connector' => env('PRINTER_CONNECTOR', 'usb'),

    // usb -> device path (e.g. /dev/usb/lp0)
    // network -> IP/hostname
    // windows -> printer share/name
    // cups -> CUPS queue name
    'address' => env('PRINTER_ADDRESS', '/dev/usb/lp0'),

    // only used by the network connector
    'port' => env('PRINTER_PORT', 9100),
];
