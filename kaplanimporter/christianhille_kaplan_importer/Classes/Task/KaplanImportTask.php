<?php

namespace CHRISTIANHILLE\KaplanImporter\Task;

use CHRISTIANHILLE\KaplanImporter\Service\Import\ImportDefinitionWrapper;

class KaplanImportTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{
    /**
     * @var int
     */
    public $importDefinitionUid;
	
    /**
     * @return bool
     */
    public function execute()
    {
        /* @var \TYPO3\CMS\Core\Log\Logger $logger */
        $logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		/* @var $repository \CHRISTIANHILLE\KaplanImporter\Domain\Repository\ImportDefinitionRepository */
		$repository = $objectManager->get(\CHRISTIANHILLE\KaplanImporter\Domain\Repository\ImportDefinitionRepository::class);		
		/* @var $importJob \CHRISTIANHILLE\KaplanImporter\Service\Import\ImportJob */
		$importJob = $objectManager->get(\CHRISTIANHILLE\KaplanImporter\Service\ImportService::class);
		if ($this->importDefinitionUid > 0) {
		    $importDefinition = $repository->findByUid($this->importDefinitionUid);
		    if (!is_null($importDefinition)) {
		        try {
		          $importJob->import(new ImportDefinitionWrapper($importDefinition));
		        }
		        catch(\Exception $ex) {
		            $logger->error($ex->getMessage());
		            $logger->error($ex->getTraceAsString());
		            throw $ex;
		        }
		        return true;
		    }
		}
		$importDefinitions = $repository->findAll();
		foreach ($importDefinitions as $importDefinition) {
		    try {
		        $importJob->import(new ImportDefinitionWrapper($importDefinition));
		    }
		    catch(\Exception $ex) {
		        $logger->error($ex->getMessage());
		        $logger->error($ex->getTraceAsString());
		    }
		}
		return true;
    }
	
    /**
     * @return string
     */
    public function __toString()
    {
        return 'KaplanImportTask';
    }
}
