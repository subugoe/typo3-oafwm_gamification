<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'OAFWM Gamification Extension',
    'description' => 'Extension to facilitate gamification elements for OAFWM',
    'category' => 'plugin',
    'version' => '0.0.1',
    'state' => 'alpha',
    'clearCacheOnLoad' => true,
    'author' => 'Sibylle Naegle',
    'author_email' => 'naegle@sub.uni-goettingen.de',
    'author_company' => 'SUB Uni-Goettingen',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.9.99',
            'tt_address' => '4.3-'
        ],
    ]
];
