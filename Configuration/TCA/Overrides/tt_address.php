<?php
defined('TYPO3_MODE') or die();

$columns = [
    'user_uid' => [
        'label' => 'Backend user id of user',
        'config' => [
            'default' => '',
            'type' => 'input',
        ]
    ],
    'user_groupname' => [
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
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $columns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'user_uid', '', 'after:name');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'user_groupname', '', 'after:user_uid');

