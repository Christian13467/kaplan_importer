<?php
namespace CHRISTIANHILLE\KaplanImporter\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use CHRISTIANHILLE;
use CHRISTIANHILLE\KaplanImporter\Domain\Model\ImportDefinition;
use CHRISTIANHILLE\KaplanImporter\Service\Import\ImportDefinitionWrapper;

class ImportService
{
    /* @var CHRISTIANHILLE\KaplanImporter\Service\NewsService */
    protected $newsService;
    /* @var CHRISTIANHILLE\KaplanImporter\Service\CategoryService */
    protected $categoryService;
    
    
    /* @var TYPO3\CMS\Core\Log\LogManager */
    protected $logger;
    
    public function __construct()
    {
        $logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        $this->logger = $logger;
    }
    
    public function injectNewsService(CHRISTIANHILLE\KaplanImporter\Service\NewsService $newsService) {
        $this->newsService = $newsService;
    }
    
    public function injectCategoryService(CHRISTIANHILLE\KaplanImporter\Service\CategoryService $categoryService) {
        $this->categoryService = $categoryService;
    }
    
    public function import(ImportDefinitionWrapper $importDefinition) {
        $this->logger->info(sprintf("Start loading kaplan appointments from source %s...", $importDefinition->getImportSource()));
        $this->categoryService->loadCategoriesFromImportDefinition($importDefinition->getImportDefinition());

        while($importDefinition->hasNext()) {
            $url = $importDefinition->nextUrl();
            // Load appointments from database
            $this->logger->debug(sprintf("Load appointments from database..."));
            $foundNews = $this->newsService->findNewsByImportSourceAndDay($importDefinition->getImportSource(), $importDefinition->nextFrom(), $importDefinition->nextTo());
            $foundNewsUids = $this->newsService->convertToUidsArray($foundNews);
            $this->logger->debug(sprintf("Load appointments from %s...", $url));
            // Read data from service
            $content = $this->readContent($url);
            // Parse data from xml and load to database
            $importedNewsUids = $this->parseAndLoadContent($content, $importDefinition);
//             // Import data
//             $importedNewsUids = [];
//             foreach ($parsedContent as $item) {
//                 $itemLoaded = [];
//                 if (isset($item['import_id']) && isset($item['import_source'])) {
//                     // Lookup THIS item identified by import_id and import_source
//                     $itemLoaded = $this->newsService->findNewsByImportIdAndImportSource($item['import_id'], $item['import_source']);
//                     if (isset($itemLoaded['uid'])) {
//                         $this->logger->debug(sprintf("Load appointment %s with uid %d...", $item['import_id'], $itemLoaded['uid']));
//                     }
//                     else {
//                         $this->logger->debug(sprintf("Import appointment %s ...", $item['import_id']));
//                     }
//                     $newsItem = array_merge($itemLoaded, $item);
//                     // Write to db
//                     $this->newsService->insertUpdateNews($newsItem);
//                     $this->logger->debug(sprintf("Appointment %s %d %s...", $item['import_id'], $newsItem['uid'], $newsItem['title']));
//                     $importedNewsUids[] = $newsItem['uid'];
//                 }
//             }
            if (count($foundNewsUids) > 0) {
                $deleteNewsUids = array_diff($foundNewsUids, $importedNewsUids);
                if (count($deleteNewsUids) > 0) {
                    $this->newsService->deleteNews($deleteNewsUids);
                    $this->logger->debug(sprintf("%d Appointments %s deleted...", count($deleteNewsUids), implode(', ', $deleteNewsUids)));
                }
            }
            $this->logger->info(sprintf("Loaded %d appointments from %s (%s, %d days) ...", count($importedNewsUids), $importDefinition->getImportSource(), $importDefinition->nextFrom()->format("d.m.Y"), $importDefinition->nextDays() ));
        }
    }
    
    
    protected function readContent($importFromUrl) {
        if (function_exists('curl_version')) {
            $cUrl = curl_init();
            
            curl_setopt($cUrl, CURLOPT_URL, $importFromUrl);
            curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($cUrl, CURLOPT_FOLLOWLOCATION, true);
            $content = curl_exec($cUrl);
            
            curl_close($cUrl);
        } elseif (file_get_contents(__FILE__) && ini_get('allow_url_fopen')) {
            $content = file_get_contents($importFromUrl);
        }
        return $content;
    }
    
    /**
     *
     * @param array[] $content
     * @param \CHRISTIANHILLE\KaplanImporter\Service\Import\ImportDefinitionWrapper $importDefinition
     * @return NULL|string[]|
     */
    protected function parseAndLoadContent($content, ImportDefinitionWrapper $importDefinition) {
        if ($content === false) {
            return [];
        } else {
            $xmlObject = new \SimpleXMLElement($content);
            
            $importedNewsUids = [];
            $row = 0;
            foreach ($xmlObject->record as $item) {
                $row++;
                $dateTime = \DateTime::createFromFormat("d.m.Y H:i", (string) $item->Datum . " " . (string) $item->Uhrzeit);
                if ($dateTime === false) $dateTime = new \DateTime();
                $news = array();
                $news['import_id'] = ''.$item->ID;
                $news['import_source'] = $importDefinition->getImportSource();
                $news['datetime'] = $dateTime->getTimestamp();
                $news['datetime_object'] = $dateTime;
                $news['title'] = (string) $dateTime->format("d.m.Y H:i ") . " " . $item->Anlass;
                $news['teaser'] = (string) $item->Anlass . (!is_null($item->Zusatz) ? " " . $item->Zusatz : "");
                $news['bodytext'] = '<strong>'.$item->Anlass.'</strong>'.(!is_null($item->Zusatz) ? "<br />" . $item->Zusatz : "");
                $news['chh_kaplanimporter_anlass'] = (string) $item->Anlass;
                $news['chh_kaplanimporter_zusatz'] = (string) $item->Zusatz;
                if (is_null($item->in) || $item->in == '') {
                    $news['chh_kaplanimporter_ort'] = (string) $item->Ort;
                }
                else {
                    $news['chh_kaplanimporter_ort'] = (string) $item->in;
                    $news['chh_kaplanimporter_ort2'] = (string) $item->Ort;
                }
                $news['chh_kaplanimporter_kategorie'] = (string) $item->Kategorie;
                $news['pid'] = $importDefinition->getImportDefinition()->getTargetFolder();
                
                $data = array(
                    'news' => $news,
                    'xml' => $item,
                    'datetime' => $dateTime,
                    'config' => $importDefinition->getImportDefinition()
                );
                $this->hydrateRecord($data);
                $news = $data['news'];
//\TYPO3\CMS\Core\Utility\DebugUtility::debug($news);
                
                
                if (isset($news['import_id']) && isset($news['import_source'])) {
                    // Lookup THIS item identified by import_id and import_source
                    $itemLoaded = $this->newsService->findNewsByImportIdAndImportSource($news['import_id'], $news['import_source']);
                    if (isset($itemLoaded['uid'])) {
                        $this->logger->debug(sprintf("Load appointment %s with uid %d from database...", $news['import_id'], $itemLoaded['uid']));
                        $newsItem = array_merge($itemLoaded, $news);
                    }
                    else {
                        $this->logger->debug(sprintf("Import appointment %s ...", $news['import_id']));
                        $newsItem = $news;
                    }
                    // Write to db
                    $this->newsService->insertUpdateNews($newsItem);
                    $this->logger->debug(sprintf("Appointment %s %d %s loaded...", $news['import_id'], $newsItem['uid'], $newsItem['title']));
                    $importedNewsUids[] = $newsItem['uid'];
                }
            }
            return $importedNewsUids;
        }
    }
    
    protected function hydrateRecord(&$data) {
        /** @var array $news */
        $news = (array) $data['news'];
        $item = $data['xml'];
        $dateTime = $data['datetime'];
        $news['title'] = (string) $dateTime->format("d.m.Y H:i ") . " " . $item->Anlass;
        $news['teaser'] = (string) $item->Anlass . (!is_null($item->Zusatz) ? " " . $item->Zusatz : "");
        $news['bodytext'] = '<strong>'.$item->Anlass.'</strong>'.(!is_null($item->Zusatz) ? "<br />" . $item->Zusatz : "");
        $news['hidden'] = FALSE;
        
        $categories = $news['categories'] || [];
        /** @var \CHRISTIANHILLE\KaplanImporter\Domain\Model\ImportDefinition $importDefinition */
        $importDefinition = $data['config'];
        if ($importDefinition->isCategoryAsCategory() && !is_null($item->Kategorie)) {
            $category = $this->categoryService->lookupCategory($item->Kategorie, $importDefinition->isCreateMissingCategories());
            if ($category !== FALSE) $categories[] = $category;
        }
        if ($importDefinition->isRoomsAndPlacesAsCategories()) {
            if (isset($news['chh_kaplanimporter_ort2'])) {
                $category = $this->categoryService->lookupCategory($news['chh_kaplanimporter_ort2'], $importDefinition->isCreateMissingCategories());
                if ($category !== FALSE) $categories[] = $category;
            }
            else if (isset($news['chh_kaplanimporter_ort'])) {
                $category = $this->categoryService->lookupCategory($news['chh_kaplanimporter_ort'], $importDefinition->isCreateMissingCategories());
                if ($category !== FALSE) $categories[] = $category;
            }
        }
        if (count($categories) > 0) {
            $data['news']['categories'] = $categories;
        }
        
        $hydrateRecordHooks = $GLOBALS['TYPO3_CONF_VARS']['EXT']['christianhille_kaplan_importer']['ImportService.php']['hydrateRecord'];
        if (is_array($hydrateRecordHooks)) {
            foreach ($hydrateRecordHooks as $reference) {
                $this->logger->debug(sprintf("Hydrate %s by %s ...", $news['import_id'], $reference));
                GeneralUtility::callUserFunction($reference, $data, $this);
            }
        }
        
    }
    
}

