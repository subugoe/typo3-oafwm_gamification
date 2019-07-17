<?php
defined('TYPO3_MODE') or die();



$temporaryColumns = array (
    'rewards' => [
        'exclude' => true,
        'label' => 'rewards',
        'config' => [
            'type' => 'text',
            'cols' => '30',
            'rows' => '5',
            'enableRichtext' => true,
        ]
    ]
);



\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tt_address',
    $temporaryColumns
);

