<?php
namespace Subugoe\OafwmGamification\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Sibylle Naegle <naegle@sub.uni-goettingen.de>, SUB Goettingen
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
 ***************************************************************/


class Address extends \FriendsOfTYPO3\TtAddress\Domain\Model\Address
{
    /**
     * @var integer
     */
    protected $userUid;

    /**
     * @var string
     */
    protected $userGroupname;

    /**
     * @return integer
     */
    public function getUserUid()
    {
        return $this->userUid;
    }
    /**
     * @param integer $userUid
     */
    public function setUserUid($userUid)
    {
        $this->userUid = $userUid;
    }

    /**
     * @return integer
     */
    public function getUserGroupname()
    {
        return $this->userGroupname;
    }
    /**
     * @param integer $userGroupname
     */
    public function setUserGroupname($userGroupname)
    {
        $this->userGroupname = $userGroupname;
    }
}
