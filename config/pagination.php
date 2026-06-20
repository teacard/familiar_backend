<?php

return [
    'perPage' => [
        'default' => 10,
        'options' => [10, 25, 50],
    ],
    'page' => [
        'default' => 1,
    ],
    'request' => [
        'perPage' => ['nullable', 'integer', 'in:10,25,50'],
        'page' => ['nullable', 'integer', 'min:1'],
    ],
];
