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

    // SELECT userid FROM `sys_log` where tablename = "tt_content" and event_pid = "150"
    protected function getAuthorOfPage($pid)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_log');
        $authorUids = $queryBuilder
            ->select('userid')
            ->from('sys_log')
            ->where(
                $queryBuilder->expr()->eq('tablename', $queryBuilder->createNamedParameter("tt_content", \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('event_pid', $queryBuilder->createNamedParameter($pid, \PDO::PARAM_INT))
            )
            ->execute();
        // only use unique ids
        $authorArray = [];
        foreach ($authorUids as $author) {
            if (in_array($author, $authorArray)) {} else {
                array_push($authorArray, $author);
            }
        };
        return array_merge($authorArray);
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
        $authors = $this->getAuthorOfPage($pid);
        return $authors;
    }
}

?>
