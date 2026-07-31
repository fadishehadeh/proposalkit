<?php
declare(strict_types=1);

return [
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'database' => 'g2ratecard',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],
    'app' => [
        'name'   => 'ProposalKit',
        'agency' => 'G2Mena',
        'base'   => '/g2ratecard',
    ],
    'currencies'  => ['AED', 'USD', 'QAR'],
    'multipliers' => [1.0, 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 2.0],
];
