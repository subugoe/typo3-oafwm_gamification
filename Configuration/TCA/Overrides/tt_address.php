<?php
defined('TYPO3_MODE') or die();

$columns = [
    'oafwm_uid' => [
        'label' => 'Backend user id of user',
        'config' => [
            'default' => '',
            'type' => 'input',
        ]
    ],
    'oafwm_groupname' => [
        'label' => 'Group of user',
        'config' => [
            'type' => 'input',
            'size' => 20,
            'eval' => 'trim',
            'valuePicker' => [
                'items' => [
                    [ 'Badges', 'Badges', ],
                    [ 'Level', 'Level', ],
                    [ 'Controlgroup', 'Controlgroup', ],
                ],
            ],
        ],
    ],
    'oafwm_orcid' => [
        'label' => 'Orcid number',
        'config' => [
            'default' => '',
            'type' => 'input',
        ]
    ],
    'oafwm_twitter' => [
        'label' => 'Twitter account',
        'config' => [
            'default' => '',
            'type' => 'input',
        ]
    ],
    'oafwm_xing' => [
        'label' => 'Xing account',
        'config' => [
            'default' => '',
            'type' => 'input',
        ],
    ],
    'oafwm_linkedin' => [
        'label' => 'LinkedIn account',
        'config' => [
            'default' => '',
            'type' => 'input',
        ],
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $columns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'oafwm_uid', '', 'after:name');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'oafwm_groupname', '', 'after:oafwm_uid');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'oafwm_orcid', '', 'after:www');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'oafwm_twitter', '', 'after:oafwm_orcid');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'oafwm_xing', '', 'after:oafwm_twitter');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'oafwm_linkedin', '', 'after:oafwm_xing');

