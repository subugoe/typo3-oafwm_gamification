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

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Scheduler\Task\Enumeration\Action;



/**
 * Additional field provider for the log community author actions task
 *
 * @author Sibylle Nägle <naegle@sub.uni-goettingen.de>
 */
class LogCommunityAuthorActionsAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    /**
     * @param array $taskInfo
     * @param AbstractTask $task
     * @param SchedulerModuleController $schedulerModule
     * @return array
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $currentSchedulerModuleAction = $schedulerModule->getCurrentAction();

        // Initialize extra field value
        if (empty($taskInfo['email'])) {
            if ($currentSchedulerModuleAction->equals(Action::ADD)) {
                // In case of new task and if field is empty, set default email address
                $taskInfo['email'] = $GLOBALS['BE_USER']->user['email'];
            } elseif ($currentSchedulerModuleAction->equals(Action::EDIT)) {
                // In case of edit, and editing a test task, set to internal value if not data was submitted already
                $taskInfo['email'] = $task->email;
            } else {
                // Otherwise set an empty value, as it will not be used anyway
                $taskInfo['email'] = '';
            }
        }
        // Write the code for the field
        $fieldID = 'task_email';
        $fieldCode = '<input type="text" class="form-control" name="tx_scheduler[email]" id="' . $fieldID . '" value="' . htmlspecialchars($taskInfo['email']) . '" size="30">';
        $additionalFields = [];
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => 'LLL:EXT:scheduler/Resources/Private/Language/locallang.xlf:label.email',
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
        return $additionalFields;

    }

    protected function getNumberOfDaysAdditionalField(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $fieldId = 'tx_oafwmGamification_logCommunityAuthorActions_numberOfDays';
        if (empty($taskInfo[$fieldId])) {
            $taskInfo[$fieldId] = $task->numberOfDays ?? 180;
        }
        $fieldName = 'tx_oafwmGamification_logCommunityAuthorActions_numberOfDays';
        $fieldHtml = '<input class="form-control" type="text" ' . 'name="' . $fieldName . '" ' . 'id="' . $fieldId . '" ' . 'value="' . (int)$taskInfo[$fieldId] . '" ' . 'size="4">';
        $fieldConfiguration = [
            'code' => $fieldHtml,
            'label' => 'Use entries older than given number of days',
            'cshLabel' => $fieldId
        ];
        return $fieldConfiguration;
    }

    public function validateNumberOfDaysAdditionalField(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        $submittedData['email'] = trim($submittedData['email']);
        if (empty($submittedData['email'])) {
            $this->addMessage(
                $GLOBALS['LANG']->sL('LLL:EXT:scheduler/Resources/Private/Language/locallang.xlf:msg.noEmail'),
                FlashMessage::ERROR
            );
            $result = false;
        } else {
            $result = true;
        }
        return $result;

    }



    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule) {
        $validData = $this->validateNumberOfDaysAdditionalField($submittedData, $schedulerModule);
        return $validData;
    }
    /**
     * Save additional field in task
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $task->email = $submittedData['email'];
    }
};
