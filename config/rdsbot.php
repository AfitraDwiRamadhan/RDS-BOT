<?php

return [
    'version' => 'RDS-BOT V1.0 Rev 1',
    'node_server_url' => env('WA_NODE_URL', 'http://localhost:3000'),
    'webhook_secret' => env('WA_WEBHOOK_SECRET', 'RdsBotSecret2026'),
    
    'available_plugins' => [
        'order' => [
            'name' => 'Order Management System',
            'description' => 'Mengelola pesanan otomatis menggunakan formulir !form',
            'commands' => ['!form', 'ps', 'dn', 'cn', 'rf', '!history']
        ],
        'broadcast' => [
            'name' => 'Broadcast Message',
            'description' => 'Mengirim pesan massal ke grup atau kontak',
            'commands' => ['!bc', '!bcgrup']
        ],
        'monitoring' => [
            'name' => 'System Monitoring',
            'description' => 'Mengecek performa internal engine bot',
            'commands' => ['!status', '!help']
        ]
    ]
];