<?php

namespace Subugoe\OafwmGamification\Task;

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
use TYPO3\CMS\Core\Mail\MailMessage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;








/**
 * A worker downloading all comunity authors actions.
 *
 * @author Sibylle Nägle <naegle@typo3.org>
 */
class LogCommunityAuthorActionsTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask implements LoggerAwareInterface
{

    use LoggerAwareTrait;

    protected $logger = null;

    protected function getLogins ($uid)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_log');
        $tstamps = $queryBuilder
            ->select('tstamp')
            ->from('sys_log')
            ->where(
                $queryBuilder->expr()->eq('userid', $queryBuilder->createNamedParameter($uid)),
                $queryBuilder->expr()->like('details',  $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards('logged in') . '%'))
            )
            ->execute()
            ->fetchAll(0);
        return $tstamps;
    }

    protected function getLogouts ($uid, $start, $end)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_log');
        $tstamps = $queryBuilder
            ->select('tstamp')
            ->from('sys_log')
            ->where(
                $queryBuilder->expr()->eq('userid', $queryBuilder->createNamedParameter($uid)),
                $queryBuilder->expr()->like('details',  $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards('logged out from TYPO3 Backend') . '%'))
            )
            ->execute()
            ->fetchAll(0);
        return $tstamps;
    }

    protected function getChangedSettings ($uid, $start, $end)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_log');
        $tstamps = $queryBuilder
            ->select('tstamp')
            ->from('sys_log')
            ->where(
                $queryBuilder->expr()->eq('userid', $queryBuilder->createNamedParameter($uid)),
                $queryBuilder->expr()->like('details',  $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards('logged out from TYPO3 Backend') . '%'))
            )
            ->execute()
            ->fetchAll(0);
        return $tstamps;
    }

    /**
     * SELECT * FROM `sys_log` WHERE userid = 3 AND details LIKE "%Record%was updated%" AND
     * tablename = "tt_content" AND tstamp < 1572908399 AND tstamp > 1572822000
     *
     * @param $uid
     * @param $begin
     * @param $end
     * @return mixed
     */
    protected function getNrOfActions ($uid, $begin, $end)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_log');
        $actions = $queryBuilder
            ->count('tstamp')
            ->from('sys_log')
            ->where(
                $queryBuilder->expr()->eq('userid', $queryBuilder->createNamedParameter($uid)),
                $queryBuilder->expr()->like('details',  $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards('was updated') . '%')),
                $queryBuilder->expr()->eq('tablename', $queryBuilder->createNamedParameter('tt_content')),
                $queryBuilder->expr()->gt('tstamp', $queryBuilder->createNamedParameter($begin, \PDO::PARAM_INT)),
                $queryBuilder->expr()->lt('tstamp', $queryBuilder->createNamedParameter($end, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchColumn(0);
        return $actions;
    }

    protected function getCommunityAuthors()
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('be_users');
        $authors = $queryBuilder
            ->select('realName', 'uid', 'username')
            ->from('be_users')
            ->where(
                $queryBuilder->expr()->eq('usergroup', $queryBuilder->createNamedParameter('8'))
            )
            ->orderBy('uid', 'DESC')
            ->execute()
            ->fetchAll(0);
        return $authors;
    }


    protected function getCSV()
    {
        $authors = $this->getCommunityAuthors();
        $csv = '"Date", "Login", "Logout", "Number of activities inbetween"';
        foreach ($authors as $key => $author) {

        }
        return $csv;
    }



    /**
     * SELECT * FROM `be_users` WHERE usergroup = 8
     *
     * @return mixed
     */
    protected function getEmailMessage()
    {
        $authors = $this->getCommunityAuthors();

        $message = "";
        foreach ($authors as $key => $author) {
                $message .= LF ;
                $message .= 'Author: ' . $author['realName'] .', Username: '. $author['username'] .', UID: '. $author['uid'] . LF ;
                $message .= TAB . 'Date' . TAB . TAB .'LoginTime'. TAB .'NumberOfChanges '. LF ;

                $tstamps = $this->getLogins($author['uid']);
                // produce on line for each day
                foreach ($tstamps as $tkey => $tstamp) {
                    $time = getdate($tstamp['tstamp']);
                    $date = $time['mday'] .'.'. $time['mon'] .'.'. $time['year'];

                    // produce entries for each day
                    $startOfDay = strtotime("today", $tstamp['tstamp']);
                    $endOfDay = strtotime("tomorrow", $tstamp['tstamp']) - 1;
                    $nrOfchanges = $this->getNrOfActions($author['uid'], $startOfDay, $endOfDay);

                    // date
                    $message .= TAB .$date;
                    // login time
                    $message .= TAB . $time['hours'] .':'. $time['minutes'];
                    // number of changes
                    $message .= TAB . TAB . $nrOfchanges ;
                    $message .= LF ;
                }
        }

        return $message;
    }

    /**
     * @return bool
     */
    public function execute() {
        $authors = $this->getEmailMessage();

        // Prepare mailer and send the mail
        try {
            /** @var \TYPO3\CMS\Core\Mail\MailMessage $mailer */
        $mail = GeneralUtility::makeInstance(MailMessage::class);

        // Create the message
        $mail->setSubject('Zusammenfassung der Aktivitaeten der Community Autoren');
        $mail->setBody($authors);
        $mail->setFrom(array('sibylle@naegle.info' => 'IPOA'));
        $mail->setTo($this->email);
        $mail->setBcc('naegle@sub.uni-goettingen.de');
        $mailsSend = $mail->send();
        $success = $mailsSend > 0;
        } catch (\Exception $e) {
            throw new \TYPO3\CMS\Core\Exception($e->getMessage(), 1476048416);
        }
        return true;
    }
}
