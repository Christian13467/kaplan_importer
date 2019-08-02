<?php
namespace CHRISTIANHILLE\KaplanImporter\Task;

use \TYPO3\CMS\Scheduler as Scheduler;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Utility\MathUtility;
use \TYPO3\CMS\Core\Messaging\FlashMessage;

class AdditionalFieldProvider implements Scheduler\AdditionalFieldProviderInterface
{
    const ADDITIONAL_FIELD_NAME = 'task_import_definition';
    
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;
    
    /**
     * @var \TYPO3\CMS\Core\Messaging\FlashMessageService
     */
    protected $flashMessageService;
    
    /**
     * @var \CHRISTIANHILLE\KaplanImporter\Domain\Repository\ImportDefinitionRepository
     */
    protected $repository;
    
    protected $logger;
    
    
    public function __construct()
    {
        $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $this->flashMessageService = $this->objectManager->get(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
        $this->repository = $this->objectManager->get(\CHRISTIANHILLE\KaplanImporter\Domain\Repository\ImportDefinitionRepository::class);
    }
    
    /**
     * @param array $taskInfo reference to array with information about provided task
     * @param object $task
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule reference to the Scheduler BE module
     * @return array Array containing information about additional fields
     */
    public function getAdditionalFields(array &$taskInfo, $task, Scheduler\Controller\SchedulerModuleController $schedulerModule) {

            $importDefinitions = $this->repository->findAll();
            $options = [];
            
            for ($importDefinitions->rewind(); $importDefinitions->valid(); $importDefinitions->next()) {
                /* @var $importDefinition \CHRISTIANHILLE\KaplanImporter\Domain\Model\ImportDefinition */
                $importDefinition = $importDefinitions->current();
                
                if (!is_null($task)) {
                    
                    if ($task->importDefinitionUid === $importDefinition->getUid()) {
                        $options[] = '<option value="' . $importDefinition->getUid() . '" selected="selected">' . $importDefinition->getName() . '</option>';
                    } else {
                        $options[] = '<option value="' . $importDefinition->getUid() . '">' . $importDefinition->getName() . '</option>';
                    }
                } else {
                    $options[] = '<option value="' . $importDefinition->getUid() . '">' . $importDefinition->getName() . '</option>';
                }
            }
            $importDefinitions->rewind();
            $lin = '<option value="0"';
            if ($task->importDefinitionUid == 0) $lin .= ' selected="selected"';
            $lin .= '>Alle ' . $importDefinitions->count() . ' Definitionen laden</option>';
            $options[] = $lin;
            
            $fieldName = 'tx_scheduler[' . self::ADDITIONAL_FIELD_NAME . ']';
            $fieldId = self::ADDITIONAL_FIELD_NAME;
            $fieldHtml = '<select name="' . $fieldName . '" id="' . $fieldId . '" class="form-control">' . implode("\n", $options) . '</select>';
            
            $fieldConfiguration = [];
            $fieldConfiguration[self::ADDITIONAL_FIELD_NAME] = [
                'code' => $fieldHtml,
                'label' => 'Kaplan Import Definitionen',
                'cshKey' => '',
                'cshLabel' => '',
            ];
            return $fieldConfiguration;
    }
    
    /**
     * @param array $submittedData reference to array with data provided by the user
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule reference to the Scheduler BE module
     * @return boolean validation result
     */
    public function validateAdditionalFields(
        array &$submittedData, Scheduler\Controller\SchedulerModuleController $schedulerModule) {
        
            if (MathUtility::canBeInterpretedAsInteger($submittedData[self::ADDITIONAL_FIELD_NAME])) {
                
                $uid = $submittedData[self::ADDITIONAL_FIELD_NAME];
                $importDefinition = $this->repository->findByUid($uid);
                
                $messageQueue = $this->flashMessageService->getMessageQueueByIdentifier();
                
                if ($uid > 0 && is_null($importDefinition)) {
                    /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
                    $flashMessage = GeneralUtility::makeInstance(
                        FlashMessage::class,
                        'Ausgewählte Import Definition existiert nicht',
                        '',
                        FlashMessage::ERROR,
                        true
                    );
                    
                    $messageQueue->addMessage($flashMessage);
                    
                    return false;
                } 
                
                return true;
            }
            
            return false;
    }
    
    /**
     * @param array $submittedData array with data provided by the user
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task current task
     * @return void
     */
    public function saveAdditionalFields(array $submittedData, Scheduler\Task\AbstractTask $task)
    {
        $task->importDefinitionUid = (int) $submittedData[self::ADDITIONAL_FIELD_NAME];
    }
    
}

