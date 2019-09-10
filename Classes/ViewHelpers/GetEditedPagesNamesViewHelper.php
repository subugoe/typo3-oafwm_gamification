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
 * Class GetEditedPagesNamesViewHelper
 *
 * Returns associative array with page titles and uids as their value
 * e.g: Anleitungen -> 1291
 * @package Subugoe\OafwmGamification\ViewHelpers
 */
class GetEditedPagesNamesViewHelper extends AbstractViewHelper
{

    // SELECT pages.title FROM `pages`
    //    INNER JOIN `sys_log` ON `pages`.`uid` = `sys_log`.`event_pid`
    //    WHERE `sys_log`.`userid` = 3 AND `sys_log`.`tablename`= "tt_content"
    //    AND `pages`.`hidden` = 0 AND `pages`.`doktype` = 1 AND `pages`.`sys_language_uid` = 0 AND `pages`.`deleted` = 0
    //    GROUP BY `pages`.`slug`
    //    ORDER BY `sys_log`.`tstamp` DESC

    protected function getPagesNames($id)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');

        $pages = $queryBuilder
            ->select('pages.title','pages.slug','log.tstamp')
            ->from('pages')
            ->join(
                'pages',
                'sys_log',
                'log',
                $queryBuilder->expr()->eq('log.event_pid', $queryBuilder->quoteIdentifier('pages.uid'))
            )
            ->where(
                $queryBuilder->expr()->eq('log.userid', $queryBuilder->createNamedParameter($id, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('log.tablename', $queryBuilder->createNamedParameter('tt_content')),
                $queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter('0')),
                $queryBuilder->expr()->eq('pages.deleted', $queryBuilder->createNamedParameter('0')),
                $queryBuilder->expr()->eq('pages.doktype', $queryBuilder->createNamedParameter('1')),
                $queryBuilder->expr()->eq('pages.sys_language_uid', $queryBuilder->createNamedParameter('0'))
            )
            ->orderBy('log.tstamp', 'DESC')
            ->groupBy('pages.slug')
            ->execute()
            ->fetchAll();
        return $pages;
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
        $pages = $this->getPagesNames($uid);
        return $pages;
    }
}

?>
