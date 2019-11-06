<?php


if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

/**
 * TypoScript for backend configuration
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:oafwm_gamification/Configuration/TSConfig/Page.t3s">'
);
// Also include TCEMAIN:
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:oafwm_gamification/Configuration/TSConfig/Page.t3s">'
);
// and TSconfig for Copas
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:tmpl_ipoa/Configuration/TSConfig/COP.t3s">'
);

// Add download log task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Subugoe\OafwmGamification\Task\LogCommunityAuthorActionsTask::class] = array(
    'extension' => 'oafwm_gamification',
    'title' => 'LLL:EXT:oafwm_gamification/Resources/Private/Language/locallang.xlf:log_community_authors_actions',
    'description' => 'LLL:EXT:oafwm_gamification/Resources/Private/Language/locallang.xlf:log_community_authors_actions',
    'additionalFields' => \Subugoe\OafwmGamification\Task\LogCommunityAuthorActionsAdditionalFieldProvider::class
);

$GLOBALS['TYPO3_CONF_VARS']['LOG']['OafwmGamification']['generateApacheHtaccess'];

$GLOBALS['TYPO3_CONF_VARS']['LOG']['OafwmGamification']['writerConfiguration'] = array(
    // configuration for ERROR level log entries
    \TYPO3\CMS\Core\Log\LogLevel::INFO => array(
        // add a FileWriter
        'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
            // configuration for the writer
            'logFile' => 'typo3temp/var/log/oafwm.log'
        )
    )
);
