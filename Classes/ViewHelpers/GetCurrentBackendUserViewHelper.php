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

use \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * Class GetCurrentBackendUserViewHelper
 * @package Subugoe\OafwmGamification\ViewHelpers
 */
class GetCurrentBackendUserViewHelper extends AbstractViewHelper
{

    /**
     * uid
     *
     * @var string
     */
    protected $uid;

    /**
     * groupName
     *
     * @var string
     */
    protected $groupName;

    /**
     * userName
     *
     * @var string
     */
    protected $userName;

    /**
     * @return mixed
     */
    public function getUid()
    {
        return $GLOBALS['BE_USER']->user['uid'];
    }

    /**
     * @return mixed
     */
    public function getGroupName()
    {
        $userUid = $this->getUid();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_address');
        $groupName = $queryBuilder
            ->select('oafwm_groupname')
            ->from('tt_address')
            ->where(
                $queryBuilder->expr()->eq('oafwm_uid', $queryBuilder->createNamedParameter($userUid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchColumn(0);
        return $groupName;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        $userUid = $this->getUid();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_address');
        $userName = $queryBuilder
            ->select('name')
            ->from('tt_address')
            ->where(
                $queryBuilder->expr()->eq('oafwm_uid', $queryBuilder->createNamedParameter($userUid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchColumn(0);
        return $userName;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        $userUid = $this->getUid();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_address');
        $userEmail = $queryBuilder
            ->select('email')
            ->from('tt_address')
            ->where(
                $queryBuilder->expr()->eq('oafwm_uid', $queryBuilder->createNamedParameter($userUid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchColumn(0);
        return $userEmail;
    }

    /**
     * Return array with uid and groupname of backend user
     *
     * @return array
     */
    public function render()
    {
        $user = array(
            'uid' => $this->getUid(),
            'username' => $this->getUsername(),
            'groupname' => $this->getGroupName(),
            'email' => $this->getEmail()
        );
        return $user;
    }
}
