<?php
namespace CHRISTIANHILLE\KaplanImporter\Domain\Repository;

class ImportDefinitionRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @return void
     */
    public function initializeObject()
    {
        /* @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
        $querySettings = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        
        $this->setDefaultQuerySettings($querySettings);
    }
}

