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
 * Class GetSlugByEmailUserViewHelper
 * @package Subugoe\OafwmGamification\ViewHelpers
 */
class GetSlugByEmailViewHelper extends AbstractViewHelper
{

    /**
     * email
     *
     * @var string
     */
    protected $email;
    /**
     * @return mixed
     */
    public function getSlug()
    {

        $email = $this->arguments['email'];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_address');
        $slug = $queryBuilder
            ->select('slug')
            ->from('tt_address')
            ->where(
                $queryBuilder->expr()->eq('email', $queryBuilder->createNamedParameter($email))
            )
            ->execute()
            ->fetchColumn(0);
        return $slug;
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
        $this->registerArgument('email', 'string', 'Email of user', true);
    }

    /**
     * Return array with uid and groupname of backend user
     *
     * @return array
     */
    public function render()
    {
        return $this->getSlug();
    }
}
