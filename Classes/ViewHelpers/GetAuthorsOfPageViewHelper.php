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
 * Class GetAuthorsOfPageViewHelper
 * @package Subugoe\OafwmGamification\ViewHelpers
 */
class GetAuthorsOfPageViewHelper extends AbstractViewHelper
{

    // SELECT be_users.realName FROM `be_users`
    //    INNER JOIN `sys_log` ON `be_users`.`uid` = `sys_log`.`userid`
    //    WHERE `sys_log`.`event_pid` = 200 AND `sys_log`.`tablename`= "tt_content"
    //    ORDER BY `be_users`.`realName` DESC
    protected function getAuthorsOfPage($pid)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('be_users');

        $authors = $queryBuilder
            ->select('be_users.realName','log.tstamp')
            ->from('be_users')
            ->join(
                'be_users',
                'sys_log',
                'log',
                $queryBuilder->expr()->eq('log.userid', $queryBuilder->quoteIdentifier('be_users.uid'))
            )
            ->where(
                $queryBuilder->expr()->eq('log.event_pid', $queryBuilder->createNamedParameter($pid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('log.tablename', $queryBuilder->createNamedParameter('tt_content')),
                $queryBuilder->expr()->neq('be_users.realName', $queryBuilder->createNamedParameter(''))
            )
            ->groupBy('be_users.realName')
            ->execute()
            ->fetchAll();
        return $authors;
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
        $this->registerArgument('pid', 'integer', 'Pid of page', true);
    }

    /**
     * @return string
     */
    public function render()
    {
        $pid = $this->arguments['pid'];
        $authors = $this->getAuthorsOfPage($pid);

        return $authors;
    }
}

?>
