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
 * Class GetFirstEditVieHelper
 * @package Subugoe\OafwmGamification\ViewHelpers
 */
class GetFirstEditViewHelper extends AbstractViewHelper
{
    // SELECT MIN( tstamp ) FROM `sys_log` WHERE tablename = "tt_content"
    protected function getFirstGroupEdit($gid)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_log');
        $tstamps = $queryBuilder
            ->select('sys_log.tstamp')
            ->from('sys_log')
            ->join(
                'sys_log',
                'be_users',
                'be_users',
                $queryBuilder->expr()->eq('be_users.uid', $queryBuilder->quoteIdentifier('sys_log.userid'))
            )
            ->where(
                $queryBuilder->expr()->eq('be_users.usergroup', $queryBuilder->createNamedParameter($gid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('sys_log.tablename', $queryBuilder->createNamedParameter('tt_content'))
            )
            ->execute()
            ->fetchAll();
        $times = array_column($tstamps, 'tstamp');
        $first = min($times);
        return $first;
    }

    // SELECT MIN( tstamp ) FROM `sys_log`
    protected function getFirstUserEdit($uid)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_log');
        $tstamps = $queryBuilder
            ->select('tstamp')
            ->from('sys_log')
            ->where(
                $queryBuilder->expr()->eq('tablename', $queryBuilder->createNamedParameter('tt_content')),
                $queryBuilder->expr()->eq('userid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAll();
        $times = array_column($tstamps, 'tstamp');
        $first = min($times);
        return $first;
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
        $this->registerArgument('uid', 'integer', 'Uid of user to compare timestamp for', true);
        $this->registerArgument('gid', 'integer', 'Id of group to compare to', true);
    }

    /**
     * @return string
     */
    public function render()
    {
        $uid = $this->arguments['uid'];
        $firstOfUser = $this->getFirstUserEdit($uid);
        $firstOfGroup = $this->getFirstGroupEdit(8);
        $result = ($firstOfUser <= $firstOfGroup ? true : false);
        return $result;
    }
}

?>
