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

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
        admPanel {
                enable.edit = 1
                module.edit.forceNoPopup = 1
                module.edit.forceDisplayFieldIcons = 1
                module.edit.forceDisplayIcons = 0
                hide = 1
        }
        options.enableBookmarks = 1
');
