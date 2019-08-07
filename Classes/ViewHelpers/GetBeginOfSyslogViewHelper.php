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
 * Class GetBeginOfSyslogViewHelper
 * @package Subugoe\OafwmGamification\ViewHelpers
 */
class GetBeginOfSyslogViewHelper extends AbstractViewHelper
{
    // SELECT MIN( tstamp ) FROM `sys_log`
    protected function getBeginOfSyslog()
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_log');
        $tstamps = $queryBuilder
            ->select('tstamp')
            ->from('sys_log')
            ->execute()
        ->fetch();

        // only use unique ids
        $lowest = "15554301560";
        foreach ($tstamps as $tstamp) {
            if ($tstamp < $lowest) {
                $lowest = $tstamp;
            }
        };
        return $lowest;
    }

    /**
     * @return string
     */
    public function render()
    {
        $begin = $this->getBeginOfSyslog();
        return $begin;
    }
}

?>
