<?php
namespace CHRISTIANHILLE\KaplanImporter\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;

class NewsService
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
    
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $this->connectionPool = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class);
        $this->cacheService = $this->objectManager->get(\TYPO3\CMS\Extbase\Service\CacheService::class);
    }
    
    static protected $newsTable = "tx_news_domain_model_news";
    
    
    public function findNewsByImportIdAndImportSource(string $importId, string $importSource, bool $showdisabled = TRUE) {
        /* @var TYPO3\CMS\Core\Database\Query\QueryBuilder */
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(NewsService::$newsTable);
        if ($showdisabled) $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        $queryBuilder->select('*');
        $queryBuilder->from(NewsService::$newsTable);
        $queryBuilder->where(
            $queryBuilder->expr()->eq("import_source", $queryBuilder->createNamedParameter($importSource)),
            $queryBuilder->expr()->eq("import_id", $queryBuilder->createNamedParameter($importId))
        );
        /* @var \Doctrine\DBAL\Driver\Statement */
        $statement = $queryBuilder->execute();
        $result = $statement->fetch();
        return is_null($result) || !is_array($result) ? [] : $result;
    }
    
    public function findNewsByImportSourceAndDay(string $importSource, \DateTime $from, \DateTime $to) {
        /* @var TYPO3\CMS\Core\Database\Query\QueryBuilder */
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(NewsService::$newsTable);
        $queryBuilder->select('uid');
        $queryBuilder->from(NewsService::$newsTable);
        $queryBuilder->where(
            $queryBuilder->expr()->eq("import_source", $queryBuilder->createNamedParameter($importSource)),
            $queryBuilder->expr()->gte("datetime", $from->getTimestamp()),
            $queryBuilder->expr()->lt("datetime", $to->getTimestamp())
        );
        /* @var \Doctrine\DBAL\Driver\Statement */
        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll();
        return is_null($result) || !is_array($result) ? [] : $result;
    }
    
    public function convertToUidsArray($dataset) {
        $result = [];
        foreach ($dataset as $row) {
            if (isset($row['uid'])) $result[] = $row['uid'];
        }
        return $result;
    }

    public function findNewsByImportSourceAndDayWithoutUids(string $importSource, \DateTime $day, array $news) {
        /* @var TYPO3\CMS\Core\Database\Query\QueryBuilder */
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(NewsService::$newsTable);
        $queryBuilder->select('*');
        $queryBuilder->from(NewsService::$newsTable);
        $queryBuilder->where(
            $queryBuilder->expr()->eq("import_source", $queryBuilder->createNamedParameter($importSource)),
            $queryBuilder->expr()->gte("datetime", $day->getTimestamp()),
            $queryBuilder->expr()->lt("datetime", $day->add(new \DateInterval("1 day"))->getTimestamp())
        );
        if (!is_null($news) && is_array($news) && count($news) > 0) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->notIn("uid", $news)
            );
        }
        /* @var \Doctrine\DBAL\Driver\Statement */
        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll();
        return is_null($result) || !is_array($result) ? [] : $result;
    }
    
    public function removeNewsByImportSourceAndDayWithoutUids(string $importSource, \DateTime $day, array $news) {
        /* @var TYPO3\CMS\Core\Database\Query\QueryBuilder */
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(NewsService::$newsTable);
        $queryBuilder->delete(NewsService::$newsTable);
        $queryBuilder->where(
            $queryBuilder->expr()->eq("import_source", $queryBuilder->createNamedParameter($importSource)),
            $queryBuilder->expr()->gte("datetime", $day->getTimestamp()),
            $queryBuilder->expr()->lt("datetime", $day->add(new \DateInterval("1 day"))->getTimestamp())
            );
        if (!is_null($news) && is_array($news) && count($news) > 0) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->notIn("uid", $news)
                );
        }
        /* @var \Doctrine\DBAL\Driver\Statement */
        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll();
        return is_null($result) || !is_array($result) ? [] : $result;
    }
    
    public function insertUpdateNews(array &$newsItem) {
        /* @var $dataHandler \TYPO3\CMS\Core\DataHandling\DataHandler */
        $dataHandler = GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
        
        $data = [];
        
        if (isset($newsItem['categories'])) {
            if (is_array($newsItem['categories'])) {
                // Remap categories from array to comma delimited list
                $newcategories = $newsItem['categories'];
                $newsItem['categories'] = implode(',', $newcategories);
            }
        }
        
        if (!isset($newsItem['uid'])) {
            $data[NewsService::$newsTable]['NEW'] = $newsItem;
            
            $dataHandler->start($data, []);
            $dataHandler->process_datamap();
            
            $newsItem['uid'] = $dataHandler->substNEWwithIDs['NEW'];
        } 
        else {
            $data[NewsService::$newsTable][$newsItem['uid']] = $newsItem;
            
            $dataHandler->start($data, []);
            $dataHandler->process_datamap();
        }
    }
    
    public function deleteNews(array $uids) {
        if (is_set($uids) && is_array($uids) && count($uids) > 0) {
            /* @var $dataHandler \TYPO3\CMS\Core\DataHandling\DataHandler */
            $dataHandler = GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
            
            $cmd = [];
            foreach ($uids as $uid) {
                $cmd[NewsService::$newsTable][$uid]['delete'] = 1;
            }
            $dataHandler->start([], $cmd);
            $dataHandler->process_cmdmap();
        }
    }
    
}

