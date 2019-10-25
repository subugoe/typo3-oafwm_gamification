<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'OAFWM Settings',
    'description' => 'Extension with settings for IPOA community editors',
    'category' => 'plugin',
    'version' => '1.0.0',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'author' => 'Sibylle Naegle',
    'author_email' => 'naegle@sub.uni-goettingen.de',
    'author_company' => 'SUB Uni-Goettingen',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.9.99',
            'tt_address' => '4.3',
            'blog' => '9.1'
        ],
    ],
];
