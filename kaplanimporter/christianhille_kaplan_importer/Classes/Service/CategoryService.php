<?php
namespace CHRISTIANHILLE\KaplanImporter\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class CategoryService
{
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;
    
    /**
     * @var \TYPO3\CMS\Core\Database\ConnectionPool 
     */
    protected $connectionPool;
    
    /**
     * @var \TYPO3\CMS\Extbase\Service\CacheService
     */
    protected $cacheService;
    
    /**
     *  @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;
    
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $this->connectionPool = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class);
        $this->cacheService = $this->objectManager->get(\TYPO3\CMS\Extbase\Service\CacheService::class);
        $this->categories = [];
        $this->logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
    }
    
    static protected $categoryTable = "sys_category";
    
    /**
     * List of categorys
     * 
     * @var array
     */
    protected $categories;
    protected $rootPid;
    protected $rootUid;
    
    /**
     * Return loaded categories with there names and uids
     * @return array
     */
    public function getCategories() {
        return $this->categories;
    }
    
    /**
     * Load all categories starting with categroy given as parameter
     * 
     * @param int $pid
     * @return array 
     */
    public function loadCategories(int $pid) {
        $this->categories = [];
        $this->loadCategoriesRoot($pid);
        $this->loadCategoriesInternal($pid, $this->categories, TRUE);
        return $this->categories;
    }
    
    public function loadCategoriesFromImportDefinition(\CHRISTIANHILLE\KaplanImporter\Domain\Model\ImportDefinition $importDefinition) {
        $this->categories = [];
        $categories = $importDefinition->getCategories();
        $categories->rewind();
        if ($categories->valid()) {
            $this->logger->debug(sprintf("Load categories from %s (%d)*...", $categories->current()->getTitle(), $categories->current()->getUid()));
            $this->loadCategoriesRoot($categories->current()->getUid());
            $this->loadCategoriesInternal($categories->current()->getUid(), $this->categories, TRUE);
            $categories->next();
            while($categories->valid()) {
                $this->logger->debug(sprintf("Load categories from %s (%d)...", $categories->current()->getTitle(), $categories->current()->getUid()));
                $this->loadCategoriesInternal($categories->current()->getUid(), $this->categories, TRUE);
                $categories->next();
            }
        }
    }
    
    protected function loadCategoriesRoot(int $pid) {
        /* @var TYPO3\CMS\Core\Database\Query\QueryBuilder */
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(CategoryService::$categoryTable);
        $queryBuilder->select('uid', 'title', 'pid');
        $queryBuilder->from(CategoryService::$categoryTable);
        $queryBuilder->where(
            $queryBuilder->expr()->eq("uid", $pid)
            );
        /* @var \Doctrine\DBAL\Driver\Statement */
        $statement = $queryBuilder->execute();
        $result = $statement->fetch();
        if (!is_null($result) && is_array($result)) {
            $this->rootPid = $result['pid'];
            $this->rootUid = $pid;
        }
    }
    
    protected function loadCategoriesInternal(int $pid, array &$categories, bool $recursive) {
        /* @var TYPO3\CMS\Core\Database\Query\QueryBuilder */
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(CategoryService::$categoryTable);
        $queryBuilder->select('uid', 'title', 'pid');
        $queryBuilder->from(CategoryService::$categoryTable);
        $queryBuilder->where(
            $queryBuilder->expr()->eq("parent", $pid)
        );
        /* @var \Doctrine\DBAL\Driver\Statement */
        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll();
        if (!is_null($result) && is_array($result) && count($result) > 0) {
            foreach ($result as $category) {
                $this->logger->debug(' ' . $category['title'] . " " . $category['uid']);
				if (!isset($categories[$category['title']])) {
                    $categories[$category['title']] = $category['uid'];
                    if ($recursive) $this->loadCategoriesInternal($category['uid'], $categories, $recursive);
                }
            }
        }
        return $categories;
    }
    
    public function lookupCategory(string $category, bool $create = TRUE) {
        if (is_null($category) || ''.$category == '') {
            return FALSE;
        }
        if (isset($this->categories[$category])) {
            return $this->categories[$category];
        }
        else if ($create && isset($this->rootPid) && isset($this->rootUid)) {
            /* @var $dataHandler \TYPO3\CMS\Core\DataHandling\DataHandler */
            $dataHandler = GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
            
            $data = [];
            $data[CategoryService::$categoryTable]['NEW'] = array(
                'title' => $category,
                'pid' => $this->rootPid,
                'parent' => $this->rootUid
            );
                
            $dataHandler->start($data, []);
            $dataHandler->process_datamap();
            $uid = $dataHandler->substNEWwithIDs['NEW'];
            $this->categories[$category] = $uid;
            $this->logger->debug(sprintf("Create category %s (%d) with parent %d in folder %d", $category, $uid, $this->rootUid, $this->rootPid));
            return $uid;
        }
        else {
            return FALSE;
        }
    }
    
    
    
    
}

