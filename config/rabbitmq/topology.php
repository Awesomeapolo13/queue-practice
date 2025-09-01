<?php

declare(strict_types=1);

return [
    'exchanges' => [
        'direct_orders' => [
            'type' => 'direct',
            'durable' => true,
            'auto_delete' => false,
        ],
        'topic_notifications' => [
            'type' => 'topic',
            'durable' => true,
            'auto_delete' => false,
        ],
        'headers_analytics' => [
            'type' => 'headers',
            'durable' => true,
            'auto_delete' => false,
        ],
        'fanout_broadcasts' => [
            'type' => 'fanout',
            'durable' => true,
            'auto_delete' => false,
        ],
    ],
    'queues' => [
        // Direct exchange queue
        'orders' => [
            'durable' => true,
            'exclusive' => false,
            'auto_delete' => false,
        ],
        // Topic exchange queues
        'email_notifications' => [
            'durable' => true,
            'exclusive' => false,
            'auto_delete' => false,
        ],
        'sms_notifications' => [
            'durable' => true,
            'exclusive' => false,
            'auto_delete' => false,
        ],
        // Headers exchange queues
        'analytics_critical' => [
            'durable' => true,
            'exclusive' => false,
            'auto_delete' => false,
        ],
        'analytics_normal' => [
            'durable' => true,
            'exclusive' => false,
            'auto_delete' => false,
        ],
        // Fanout exchange queues
        'audit' => [
            'durable' => true,
            'exclusive' => false,
            'auto_delete' => false,
        ],
        'monitoring' => [
            'durable' => true,
            'exclusive' => false,
            'auto_delete' => false,
        ],
        'backup' => [
            'durable' => true,
            'exclusive' => false,
            'auto_delete' => false,
        ],
    ],
    'bindings' => [
        // 1. Direct Exchange Bindings
        [
            'queue' => 'orders',
            'exchange' => 'direct_orders',
            'routing_key' => 'order.create',
        ],
        // 2. Topic Exchange Bindings
        [
            'queue' => 'email_notifications',
            'exchange' => 'topic_notifications',
            'routing_key' => 'notification.email.*',
        ],
        [
            'queue' => 'sms_notifications',
            'exchange' => 'topic_notifications',
            'routing_key' => 'notification.sms.*',
        ],
        // 3. Headers Exchange Bindings
        [
            'queue' => 'analytics_critical',
            'exchange' => 'headers_analytics',
            'routing_key' => '',
            'headers' => [
                'priority' => 'high',
                'x-match' => 'any',
            ],
        ],
        [
            'queue' => 'analytics_normal',
            'exchange' => 'headers_analytics',
            'routing_key' => '',
            'headers' => [
                'priority' => 'normal',
                'x-match' => 'any',
            ],
        ],
        // 4. Fanout Exchange Bindings (routing_key игнорируется)
        [
            'queue' => 'audit',
            'exchange' => 'fanout_broadcasts',
            'routing_key' => '',
        ],
        [
            'queue' => 'monitoring',
            'exchange' => 'fanout_broadcasts',
            'routing_key' => '',
        ],
        [
            'queue' => 'backup',
            'exchange' => 'fanout_broadcasts',
            'routing_key' => '',
        ],
    ],
];

