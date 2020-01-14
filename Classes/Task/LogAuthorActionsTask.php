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


use function GuzzleHttp\Psr7\str;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Mail\MailMessage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;



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
            ->select('tt_address.oafwm_uid', 'tt_address.oafwm_groupname', 'tt_address.email', 'log.tstamp', 'log.userid', 'log.details', 'log.IP', 'log.type', 'log.action', 'log.event_pid', 'log.recpid', 'log.recuid', 'log.tablename', 'log.log_data')
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
            ->select('tstamp', 'comment', 'email', 'parentid', 'uid')
            ->from('tx_blog_domain_model_comment')
            ->orderBy('tx_blog_domain_model_comment.tstamp', 'DESC')
            ->execute()
            ->fetchAll();
        return $data;
    }

    /**
     * returns first user with specific email address
     * @param $email
     * @return mixed
     */
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

    /**
     * returns id of group of first user with specific email address
     * @param $email
     * @return mixed
     */
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
        return strtoupper( substr( $group, 0, 1) );
    }

    protected function getIPByUid($email) {
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

    protected function createUniqMultidimensionalArray($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();
        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    protected function getHistByTimestamp($tstamp) {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_history');
        $hist = $queryBuilder
            ->select('history_data')
            ->from('sys_history')
            ->where(
                $queryBuilder->expr()->eq('tstamp', $queryBuilder->createNamedParameter($tstamp))
            )
            ->execute()
            ->fetchAll(0);
        return $hist;
    }

    protected function getContentElementTypeByUid($recuid) {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');
        // there might be deleted elements, but their type should still be shown
        // so restrictions must be lifted, otherwise they would be sorted out
        $queryBuilder
            ->getRestrictions()
            ->removeByType(DeletedRestriction::class);
        $type = $queryBuilder
            ->select('CType')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($recuid))
            )
            ->execute()
            ->fetchColumn(0);
        return $type;
    }

    protected function getCommentIdByTimestamp($tstamp) {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_blog_domain_model_comment');
        $uid = $queryBuilder
            ->select('uid')
            ->from('tx_blog_domain_model_comment')
            ->where(
                $queryBuilder->expr()->eq('tstamp', $queryBuilder->createNamedParameter($tstamp))
            )
            ->execute()
            ->fetchColumn(0);
        return $uid;
    }

    protected function getLengthOfComment($tstamp) {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_blog_domain_model_comment');
        $comment = $queryBuilder
            ->select('comment')
            ->from('tx_blog_domain_model_comment')
            ->where(
                $queryBuilder->expr()->eq('tstamp', $queryBuilder->createNamedParameter($tstamp))
            )
            ->execute()
            ->fetchColumn(0);
        return strlen($comment);
    }

    protected function getRecordAndDiff($tstamp, $recuid)
    {
        $data_a = $this->getHistByTimestamp($tstamp);
        $type = $this->getContentElementTypeByUid($recuid);

        if ($type != 'textmedia') {
            // other types are shorter and need two tabs
            if ($type == 'bullets' || $type == 'shortcut') {
                return $result = $type . ";\t0;\t";
            } else {
                return $result = $type . ";\t\t0;\t";
            }
        }

        $diffbody = $diffhead = $diffhidd = 0;
        foreach ($data_a as $dk => $data) {
            foreach ($data as $dat) {

                preg_match('/oldRecord":{"bodytext":"(.*)(","|"},|"}})/U', $dat, $oldbody);
                preg_match('/newRecord":{"bodytext":"(.*)(","|"},|"}})/U', $dat, $newbody);

                preg_match('/oldRecord":{"header":"(.*)(","|"},|"}})/U', $dat, $oldhead);
                preg_match('/newRecord":{"header":"(.*)(","|"},|"}})/U', $dat, $newhead);

                preg_match('/oldRecord":{"hidden":["]?(.?)(,|}|"})/U', $dat, $oldhidd);
                preg_match('/newRecord":{"hidden":["]?(.?)(,|}|"})/U', $dat, $newhidd);

                preg_match('/uid":[^bodytext].*bodytext":"(.*)(","|"},|"}})/U', $dat, $insbody);
                preg_match('/uid":[^header].*header":"(.*)(","|"},|"}})/U', $dat, $inshead);
                preg_match('/uid":[^hidden":].*hidden":["]?(.?)(,|}|"})/U', $dat, $inshidd);

                $diffbody = $diffbody + ((int)strlen($newbody[1]) - (int)strlen($oldbody[1]));
                $diffhead = $diffhead + ((int)strlen($newhead[1]) - (int)strlen($oldhead[1]));
                $diffhidd = $diffhidd + ((int)$newhidd[1] - (int)$oldhidd[1]);
            }
        }
        $result = "textmedia;\t";
        $result .= ($diffbody+$diffhead) . ";\t";
        return $result;
    }

    protected function getEmailMessage() {
        $message = "Timestamp;\tDatum;\t\tZeit;\t\tID;\tGruppe;\tIP;\t\tLogin/-out;\tArt;\tSeite;\tElement;\tTyp;\t\tZahl geänderter Zeichen;";
        $message .= "\r\n";
        $tstampsm = array_merge($this->getComments(), $this->getData());
        $tstamps = $this->createUniqMultidimensionalArray($tstampsm, 'tstamp');
        array_multisort(array_column($tstamps, 'tstamp'), SORT_DESC, $tstamps);

        foreach ($tstamps as $tkey => $tstamp) {
            //
            // time and date
            //
            $message .= $tstamp['tstamp'] . ";\t";
            $day = gmdate("d.m.Y", $tstamp['tstamp']);
            $time = gmdate("H:i:s", $tstamp['tstamp']);

            $message .= $day . ";\t";
            $message .= $time . ";\t";

            //
            // id and group
            //
            if ($tstamp['oafwm_uid']) {
                $message .= $tstamp['userid'] . ";\t";
            } else {
                // if event is a comment, tx_blog has no idea of users id, but it stores email
                $message .= $this->getUidByEmail($tstamp['email']) . ";\t";
            }
            if ($tstamp['oafwm_groupname']) {
                switch ($tstamp['oafwm_groupname']) {
                    case 'Badges':
                        $message .= "B;\t";
                        break;
                    case 'Level':
                        $message .= "L;\t";
                        break;
                    case 'Controlgroup':
                        $message .= "C;\t";
                        break;
                    default:
                        $message .= "0;\t";
                }
            } else {
                // if event is a comment, tx_blog has no idea of users id, but it stores email
                $message .= $this->getGroupByEmail($tstamp['email']) . ";\t";
            }

            //
            // IP
            //
            if ($tstamp['IP']) {
                $message .= $tstamp['IP'] . ";\t";
            } else {
                // if event is comment, tx_blog has no IP and IP via email doesn't make sense
                $message .= "0;\t\t";
            }

            //
            // login or logout
            //
            if ($tstamp['type']) {
                if ($tstamp['type'] == '255') {
                    switch ($tstamp['action']) {
                        case '1': // login
                            $message .= "1;\t\t";
                            break;
                        case '2': // logout
                            $message .= "2;\t\t";
                            break;
                        case '3': //failure
                            $message .= "3;\t\t";
                            break;
                        default: // something went wrong
                            $message .= "error on type;\t";
                    }
                } else {
                    $message .= "0;\t\t";
                }

                //
                // type of change
                //
                if ($tstamp['type'] == 1 && $tstamp['tablename'] == 'tt_content') {
                    // content element stuff
                    if (strpos($tstamp['details'], 'oved')) {
                        // "Moved" is first word and would result in 0, therefor "oved"
                        $message .= "CM;\t"; // content element moved
                    } elseif (strpos($tstamp['details'], 'deleted')) {
                        $message .= "CD;\t"; // content element deleted
                    } elseif (strpos($tstamp['details'], 'updated')) {
                        $message .= "CU;\t"; // content element updated
                    } elseif (strpos($tstamp['details'], 'inserted')) {
                        $message .= "CI;\t"; // content element inserted
                    } else {
                        $message .= "Error on element type" . $tstamp['details'];
                    }
                } elseif ($tstamp['type'] == 1 && $tstamp['tablename'] == 'pages') {
                    // page stuff
                    if (strpos($tstamp['details'], 'oved record')) {
                        // "Moved" is first word and would result in 0, therefor "oved"
                        $message .= "PM;\t"; // page moved
                    } elseif (strpos($tstamp['details'], 'deleted')) {
                        $message .= "PD;\t"; // element on page deleted
                    } elseif (strpos($tstamp['details'], 'updated')) {
                        $message .= "PU;\t"; // page updated
                    } elseif (strpos($tstamp['details'], 'inserted on')) {
                        $message .= "PI;\t"; // inserted element on page
                    } else {
                        $message .= "Error on page" . $tstamp['details'];
                    }
                } elseif ($tstamp['type'] == 1 && $tstamp['tablename'] == 'sys_file_metadata') {
                    // metadata changed
                    $message .= "MD;\t";
                } elseif ($tstamp['type'] == 2) {
                    // file stuff
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
                        $message .= "error on file" . $tstamp['details'];
                    }
                    // login stuff
                } elseif ($tstamp['type'] == 255) {
                    $message .= "LL;\t";
                } else {
                    $message .= "error on ?;\t";
                }
            } else {
                // blog comment
                $message .= $tstamp['type'] . "0;\t\tBC;\t";
            }

            //
            // page of change
            //
            if ($tstamp['parentid']) {
                $message .= $tstamp['parentid'] . ";\t";
            } elseif ($tstamp['event_pid'] != -1) {
                $message .= $tstamp['event_pid'] . ";\t";
            } else { // login/-out
                $message .= "0;\t";
            }

            //
            // id of changed element
            //
            if ($tstamp['recuid']) {
                // content element
                $message .= $tstamp['recuid'] . ";\t";
            } elseif ($tstamp['uid']) {
                // comment
                $message .= $tstamp['uid'] . ";\t";
            } else {
                // login
                $message .= "0;\t";
            }
            $message .= "\t";

            //
            // number of changed letters
            //
            if ($tstamp['tablename']) {
                if ($tstamp['tablename'] == 'tt_content') {
                    $message .= $this->getRecordAndDiff($tstamp['tstamp'], $tstamp['recuid']);
                } else {
                    $message .= "page;\t\t0;\t";
                }
            } elseif ($tstamp['comment']) {
                $length = $this->getLengthOfComment($tstamp['tstamp']);
                $message .= "comment;\t" . $length . ";\t";
            } else {
                $message .= "0;\t\t0;\t";
            }
            $message .= "\t";
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
