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
        // query more specific than needed to avoid Sibylles admin stuff to show up
        $data = $queryBuilder
            ->select('tt_address.oafwm_uid', 'tt_address.oafwm_groupname', 'log.tstamp', 'log.userid', 'log.details', 'log.IP', 'log.type', 'log.action', 'log.event_pid', 'log.recpid', 'log.recuid', 'log.tablename', 'log.log_data')
            ->from('tt_address')
            ->join(
                'tt_address',
                'sys_log',
                'log',
                $queryBuilder->expr()->eq('log.userid', $queryBuilder->quoteIdentifier('tt_address.oafwm_uid'))
            )
            ->where(
                $queryBuilder->expr()->notLike('log.details',  $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards('Exception handler') . '%')),
                $queryBuilder->expr()->neq('tablename', $queryBuilder->createNamedParameter('be_users')),
                $queryBuilder->expr()->neq('tablename', $queryBuilder->createNamedParameter('be_groups')),
                $queryBuilder->expr()->neq('tablename', $queryBuilder->createNamedParameter('tt_address')),
                $queryBuilder->expr()->neq('tablename', $queryBuilder->createNamedParameter('sys_file_reference')),
                $queryBuilder->expr()->neq('tablename', $queryBuilder->createNamedParameter('tx_blog_domain_model_comment')),
                $queryBuilder->expr()->neq('tablename', $queryBuilder->createNamedParameter('sys_template')),
                $queryBuilder->expr()->neq('tablename', $queryBuilder->createNamedParameter('tx_wsflexslider_domain_model_image')),
                $queryBuilder->expr()->notLike('log.details',  $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards('Personal settings changed') . '%')),
                $queryBuilder->expr()->notLike('log.details',  $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards('Scheduler task') . '%')),
                $queryBuilder->expr()->notLike('log.details',  $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards('cleared the cache') . '%')),
                $queryBuilder->expr()->notLike('log.details',  $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards('pb_social') . '%')),
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
            ->select('tstamp', 'comment', 'email', 'parentid')
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

    protected function getIPByEmail($email) {
        $uid = $this->getUidByEmail($email);
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_log');
        $IP = $queryBuilder
            ->select('IP')
            ->from('sys_log')
            ->where(
                $queryBuilder->expr()->eq('userid', $queryBuilder->createNamedParameter($uid))
            )
            ->execute()
            ->fetchColumn(0);
        return $IP;
    }


    protected function getEmailMessage() {
        $message = "Timestamp;\tDatum;\t\tZeit;\t\tID;\tGruppe;\tIP;\t\tLogin/-out;\tArt;\tSeite;\tElement;\tAnzahlZeichen;\tKommentar";
        $message .= "\r\n";
        $tstamps = array_merge($this->getComments(), $this->getData());
        array_multisort(array_column($tstamps, 'tstamp'), SORT_DESC, $tstamps);

        foreach ($tstamps as $tkey => $tstamp) {
            // time and date
            $message .= $tstamp['tstamp'] . ";\t";
            $day = gmdate("d.m.Y", $tstamp['tstamp']);
            $time = gmdate("H:i:s", $tstamp['tstamp']);

            $message .= $day . ";\t";
            $message .= $time . ";\t";
            // id and group
            if ($tstamp['oafwm_uid']) {
                $message .= $tstamp['userid'] . ";\t";
            } else {
                //  if there is no oafwm_uid, there is email
                $message .= $this->getUidByEmail($tstamp['email']) . ";\t";
            }
            if ($tstamp['oafwm_groupname']) {
                switch ($tstamp['oafwm_groupname']) {
                    case 'Badges':
                        $message .= "1;\t";
                        break;
                    case 'Level':
                        $message .= "2;\t";
                        break;
                    case 'Controlgroup':
                        $message .= "3;\t";
                        break;
                }
            } else {
                switch ($this->getGroupByEmail($tstamp['email'])) {
                    case 'Badges':
                        $message .= "1;\t";
                        break;
                    case 'Level':
                        $message .= "2;\t";
                        break;
                    case 'Controlgroup':
                        $message .= "3;\t";
                        break;
                }
            }
            // IP
            if ($tstamp['IP']) {
                $message .= $tstamp['IP'] . ";\t";
            } else {
                $message .= $this->getIPByEmail($tstamp['email']) . ";\t";
            }
            // login or logout
            if ($tstamp['type']) {
                if ($tstamp['type'] == '255') {
                    switch ($tstamp['action']) {
                        case '1':
                            $message .= "1;\t\t";
                            break;
                        case '2':
                            $message .= "2;\t\t";
                            break;
                        case '3':
                            $message .= "3;\t\t";
                            break;
                        default:
                            $message .= "error;\t";
                    }
                } else {
                    $message .= "0;\t\t";
                }
                // type of change
                if ($tstamp['type'] == 1 && $tstamp['tablename'] == 'tt_content') {
                    if (strpos($tstamp['details'], 'Moved')) {
                        $message .= "CM;\t"; // content element moved
                    } elseif (strpos($tstamp['details'], 'deleted')) {
                        $message .= "CD;\t"; // content element deleted
                    } elseif (strpos($tstamp['details'], 'updated')) {
                        $message .= "CU;\t"; // content element updated
                    } elseif (strpos($tstamp['details'], 'inserted')) {
                        $message .= "CI;\t"; // content element inserted
                    } else {
                        $message .= "error" . $tstamp['details'];
                    }
                } elseif ($tstamp['type'] == 1 && $tstamp['tablename'] == 'pages') {
                    if (strpos($tstamp['details'], 'oved record')) {
                        // "Moved" is first word and would result in 0, therefor "oved"
                        $message .= "PM;\t"; // element on page moved
                    } elseif (strpos($tstamp['details'], 'deleted')) {
                        $message .= "PD;\t"; // element on page deleted
                    } elseif (strpos($tstamp['details'], 'updated')) {
                        $message .= "PU;\t"; // page updated
                    } elseif (strpos($tstamp['details'], 'inserted on')) {
                        $message .= "PI;\t"; // inserted element on page
                    } else {
                        $message .= "Error" . $tstamp['details'];
                    }
                } elseif ($tstamp['type'] == 2) {
                    if (strpos($tstamp['details'], 'ploading file')) {
                        // "Uploaded" is first word and would result in 0, therefor "ploaded"
                        $message .= "FU;\t"; // file uploaded
                    } elseif (strpos($tstamp['details'], 'ile renamed')) {
                        // "File renamed" is first word and would result in 0, therefor "ile renamed"
                        $message .= "FR;\t"; // file renamed
                    } elseif (strpos($tstamp['details'], 'irectory')) {
                        // "Directory" is first word and would result in 0, therefor "irectory"
                        $message .= "FD;\t"; // directory created
                    } else {
                        $message .= "error" . $tstamp['details'];
                    }
                } else {
                    $message .= "0;\t";
                }
            } else {
                $message .= "0;\t\t0;\t";
            }
            // page of change
            if ($tstamp['parentid']) {
                $message .= $tstamp['parentid'] . ";\t";
            } elseif  ($tstamp['event_pid'] != -1) {
                $message .= $tstamp['event_pid'] . ";\t";
            } else {
                $message .= "0;\t";
            }
            // element of change
            if ($tstamp['recuid']) {
                $message .= $tstamp['recuid'] . ";\t";
            } else {
                $message .= "0;\t";
            }
            $message .= "\t";
            // number of changed letters
            if ($tstamp['tablename']) {
                if ($tstamp['tablename'] == 'tt_content') {
                    $logdata = $tstamp['log_data'];
                    $start = strpos($logdata, ':"') + 2;
                    $end = strpos($logdata, '";');
                    $length = $end - $start;
                    $substring = substr($logdata, $start, $length);
                    $message .= strlen($substring) . ";\t";
                } else {
                    $message .= "0;\t";
                }
            } else {
                $message .= "0;\t";
            }
            $message .= "\t";
            // comment
            if ($tstamp['comment']) {
                $message .= "1;\t";
            } else {
                $message .= "0;\t";
            }
            $message .= "\r\n";
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
