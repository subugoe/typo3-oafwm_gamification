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
class LogAuthorActionsTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask implements LoggerAwareInterface
{

    use LoggerAwareTrait;

    protected $logger = null;

    protected function getData ()
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');
        $data = $queryBuilder
            ->select('tt_address.oafwm_uid', 'tt_address.oafwm_groupname', 'log.tstamp', 'log.userid', 'log.IP', 'log.type', 'log.action', 'log.recpid', 'log.recuid', 'log.tablename', 'log.log_data')
            ->from('tt_address')
            ->join(
                'tt_address',
                'sys_log',
                'log',
                $queryBuilder->expr()->eq('log.userid', $queryBuilder->quoteIdentifier('tt_address.oafwm_uid'))
            )
            ->where(
                $queryBuilder->expr()->notLike('log.details',  $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards('Exception handler') . '%'))
            )
            ->orderBy('log.tstamp', 'DESC')
            ->execute()
            ->fetchAll(0);
        return $data;
    }

    protected function getComments()
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_blog_domain_model_comment');
        $data = $queryBuilder
            ->select('tstamp', 'comment', 'email')
            ->from('tx_blog_domain_model_comment')
            ->orderBy('tx_blog_domain_model_comment.tstamp', 'DESC')
            ->execute()
            ->fetchAll(0);
        return $data;
    }

    protected function getUidByEmail($email) {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_address');
        $uid = $queryBuilder
            ->select('oafwm_uid')
            ->from('tt_address')
            ->where(
                $queryBuilder->expr()->eq('email', $queryBuilder->createNamedParameter($email))
            )
            ->execute()
            ->fetchColumn(0);
        return $uid;
    }

    protected function getGroupByEmail($email) {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_address');
        $group = $queryBuilder
            ->select('oafwm_groupname')
            ->from('tt_address')
            ->where(
                $queryBuilder->expr()->eq('email', $queryBuilder->createNamedParameter($email))
            )
            ->execute()
            ->fetchColumn(0);
        return $group;
    }


    protected function getEmailMessage() {
        $message = "Timestamp; Datum; Uhrzeit; NutzerID; NutzerGruppe; IP; Login/Logout; ArtDerAenderung; GeaenderteSeite; GeaendertesElement; AnzahlGeaenderterZeichen; Kommentar";
        $message .= LF;
        $tstamps = array_merge($this->getComments(), $this->getData());
        array_multisort(array_column($tstamps, 'tstamp'), SORT_DESC, $tstamps);

        foreach ($tstamps as $tkey => $tstamp) {
            // time and date
            $message .= $tstamp['tstamp'] . '; ';
            $time = getdate($tstamp['tstamp']);

            $message .= $time['mday'] .'.'. $time['mon'] .'.'. $time['year'] . '; ';
            $message .= $time['hours'] .':'. $time['minutes'] .':'. $time['seconds'] . '; ';
            // id and group
            if ($tstamp['oafwm_uid']) {
                $message .= $tstamp['userid'] . '; ';
            } else {
                //  if there is no oafwm_uid, there is email
                $message .= $this->getUidByEmail($tstamp['email']) . '; ';
            }
            if ($tstamp['oafwm_groupname']) {
                switch ($tstamp['oafwm_groupname']) {
                    case 'Badges':
                        $message .= '1;';
                        break;
                    case 'Level':
                        $message .= '2;';
                        break;
                    case 'Controlgroup':
                        $message .= '3;';
                        break;
                }
            } else {
                switch ($this->getGroupByEmail($tstamp['email'])) {
                    case 'Badges':
                        $message .= '1;';
                        break;
                    case 'Level':
                        $message .= '2;';
                        break;
                    case 'Controlgroup':
                        $message .= '3;';
                        break;
                }
            }
            // IP
            if ($tstamp['IP']) {
                $message .= $tstamp['IP'] . '; ';
            } else {
                $message .= '0;';
            }
            // login or logout
            if ($tstamp['type']) {
                if ($tstamp['type'] == '255') {
                    switch ($tstamp['action']) {
                        case '1':
                            $message .= '1;';
                            break;
                        case '2':
                            $message .= '2;';
                            break;
                        case '3':
                            $message .= '3;';
                            break;
                    }
                } else {
                    $message .= '0;';
                }
                // type of change
                if ($tstamp['type'] == 1 && $tstamp['tablename'] == 'tt_content') {
                    $message .= '1;';
                } elseif ($tstamp['type'] == 1 && $tstamp['tablename'] == 'pages') {
                    $message .= '2;';
                } elseif ($tstamp['type'] == 2) {
                    $message .= '3;';
                } elseif ($tstamp['type'] == 3) {
                    $message .= '4;';
                } else {
                    $message .= '0;';
                }
            } else {
                $message .= '0;';
            }
            // page of change
            if ($tstamp['recpid']) {
                $message .= $tstamp['recpid'] . '; ';
            } else {
                $message .= '0;';
            }
            // element of change
            if ($tstamp['recuid']) {
                $message .= $tstamp['recuid'] . '; ';
            } else {
                $message .= '0;';
            }
            // number of changed letters
            if ($tstamp['tablename']) {
                if ($tstamp['tablename'] == 'tt_content') {
                    $logdata = $tstamp['log_data'];
                    $start = strpos($logdata, ':"') + 2;
                    $end = strpos($logdata, '";');
                    $length = $end - $start;
                    $substring = substr($logdata, $start, $length);
                    $message .= strlen($substring) . '; ';
                } else {
                    $message .= '0;';
                }
            } else {
                $message .= '0;';
            }
            // comment
            if ($tstamp['comment']) {
                $message .= '1;';
            } else {
                $message .= '0;';
            }
            $message .= LF;
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
            $mail->setSubject('Aktivitaeten der Community Autoren');
            $mail->setBody($authors);
            $mail->setFrom(array('naegle@sub.uni-goettingen.de' => 'IPOA'));
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
