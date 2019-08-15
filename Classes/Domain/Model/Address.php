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
    protected $oafwmUid;

    /**
     * @var string
     */
    protected $oafwmGroupname;

    /**
     * @return integer
     */
    public function getOafwmUid()
    {
        return $this->oafwmUid;
    }
    /**
     * @param integer $oafwmUid
     */
    public function setOafwmUid($oafwmUid)
    {
        $this->oafwmUid = $oafwmUid;
    }

    /**
     * @return integer
     */
    public function getOafwmGroupname()
    {
        return $this->oafwmGroupname;
    }
    /**
     * @param integer $oafwmGroupname
     */
    public function setOafwmGroupname($oafwmGroupname)
    {
        $this->oafwmGroupname = $oafwmGroupname;
    }

    /**
     * @return integer
     */
    public function getOafwmOrcid()
    {
        return $this->oafwmOrcid;
    }
    /**
     * @param integer $oafwmOrcid
     */
    public function setOafwmOrcid($oafwmOrcid)
    {
        $this->oafwmOrcid = $oafwmOrcid;
    }

    /**
     * @return integer
     */
    public function getOafwmTwitter()
    {
        return $this->oafwmTwitter;
    }
    /**
     * @param integer $oafwmTwitter
     */
    public function setOafwmTwitter($oafwmTwitter)
    {
        $this->oafwmTwitter = $oafwmTwitter;
    }

    /**
     * @return integer
     */
    public function getOafwmLinkedin()
    {
        return $this->oafwmLinkedin;
    }
    /**
     * @param integer $oafwmLinkedin
     */
    public function setOafwmLinkedin($oafwmLinkedin)
    {
        $this->oafwmLinkedin = $oafwmLinkedin;
    }

    /**
     * @return integer
     */
    public function getOafwmXing()
    {
        return $this->oafwmXing;
    }
    /**
     * @param integer $soafwmXing
     */
    public function setSocialXing($oafwmXing)
    {
        $this->oafwmXing = $oafwmXing;
    }
}
