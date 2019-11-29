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

// Change frontend-editing toolbar: Remove "To Backend"-Button
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\FrontendEditing\\Hook\\FrontendEditingInitializationHook'] = array(
    'className' => 'Subugoe\\OafwmGamification\\XClass\\XClassedFrontendEditingInitializationHook'
);


// Add download log task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Subugoe\OafwmGamification\Task\LogCommunityAuthorActionsTask::class] = array(
    'extension' => 'oafwm_gamification',
    'title' => 'LLL:EXT:oafwm_gamification/Resources/Private/Language/locallang.xlf:log_community_authors_actions',
    'description' => 'LLL:EXT:oafwm_gamification/Resources/Private/Language/locallang.xlf:log_community_authors_actions',
    'additionalFields' => \Subugoe\OafwmGamification\Task\LogCommunityAuthorActionsAdditionalFieldProvider::class
);


// Add download log task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Subugoe\OafwmGamification\Task\LogAuthorActionsTask::class] = array(
    'extension' => 'oafwm_gamification',
    'title' => 'LLL:EXT:oafwm_gamification/Resources/Private/Language/locallang.xlf:log_authors_actions',
    'description' => 'LLL:EXT:oafwm_gamification/Resources/Private/Language/locallang.xlf:log_authors_actions',
    'additionalFields' => \Subugoe\OafwmGamification\Task\LogAuthorActionsAdditionalFieldProvider::class
);
