<?php

namespace Subugoe\OafwmGamification\ViewHelpers;

/*******************************************************************************
 * Copyright notice
 *
 *  (c) 2019 Sibylle Naegle <naegle@sub-goettingen.de>
 *      Goettingen State Library
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ******************************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;



/**
 * Class GetEditedPagesViewHelper
 *
 * Returns associative array with page titles and uids as their value
 * e.g: Anleitungen -> 1291
 * @package Subugoe\OafwmGamification\ViewHelpers
 */
class GetEditedPagesViewHelper extends AbstractViewHelper
{

    // SELECT pid FROM `sys_log` WHERE userid = 3 AND tablename = "tt_content"
    protected function getPagesID($id)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_log');
        $pageIds = $queryBuilder
            ->select('event_pid')
            ->from('sys_log')
            ->where(
                $queryBuilder->expr()->eq('userid', $queryBuilder->createNamedParameter($id, \PDO::PARAM_INT)),
                $queryBuilder->expr()->neq('event_pid', $queryBuilder->createNamedParameter('-1'))
            )
            ->execute()
            ->fetchAll();
        return $pageIds;
    }

    protected function getPageName($pageId)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');
        $pageName = $queryBuilder
            ->select('title','uid','tstamp')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pageId, \PDO::PARAM_INT))
            )
            ->execute()->fetch(0);
        return $pageName;
    }

    /**
     * Initialize all arguments. You need to override this method and call
     * $this->registerArgument(...) inside this method, to register all your arguments.
     *
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('uid', 'integer', 'Uid of user to show the edited pages for', true);
    }

    /**
     * @return string
     */
    public function render()
    {
        $uid = $this->arguments['uid'];
        $pageNames = [];
        $pages = $this->getPagesID($uid);
        foreach ($pages as $pageId)
        {
            $name = $this->getPageName($pageId['event_pid']);
            if ($name) {
                     array_push($pageNames, $name);

            }
        }
        sort($pageNames);
        $uniqPages = [];
        foreach ($pageNames as $page) {
            if (in_array($page, $pages)) {} else {
                $uniqPages[$page['title']] = $page['uid'];
            }
        }
        return $uniqPages;
    }
}

?>
