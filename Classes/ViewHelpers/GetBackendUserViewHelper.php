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
 * Class GetBackendUserViewHelper
 * @package Subugoe\OafwmGamification\ViewHelpers
 */
class GetBackendUserViewHelper extends AbstractViewHelper
{
    /**
     * @return string
     */
    public function getBackendUser($uid)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('be_users');
        $groupName = $queryBuilder
            ->select('realname')
            ->from('be_users')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchColumn(0);
        return $groupName;
    }

    /**
     * @return array
     */
    public function getSocial($uid)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_address');
        $statement = $queryBuilder
            ->select('*')
            ->from('tt_address')
            ->where(
                $queryBuilder->expr()->eq('oafwm_uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            )
            ->execute();

        $result = [];
        while ($record = $statement->fetch()) {
            $result['oafwm_orcid'] = $record['oafwm_orcid'];
            $result['oafwm_twitter'] = $record['oafwm_twitter'];
        };
        return $result;
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
        $this->registerArgument('uid', 'integer', 'Id of user', true);
    }

    /**
     * Return array with uid and groupname of backend user
     *
     * @return array
     */
    public function render()
    {
        $uid = $this->arguments['uid'];
        $user = array(
            'username' => $this->getBackendUser($uid),
            'social' => $this->getSocial($uid)
        );
        return $user;
    }
}
