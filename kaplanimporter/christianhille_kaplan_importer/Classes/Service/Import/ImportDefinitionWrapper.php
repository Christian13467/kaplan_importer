<?php
namespace CHRISTIANHILLE\KaplanImporter\Service\Import;

use \GeorgRinger\News\Domain\Model\Category;

class ImportDefinitionWrapper
{
    protected $importDefinition;
    
    /**
     * Return import definition
     * 
     * @return \CHRISTIANHILLE\KaplanImporter\Domain\Model\ImportDefinition
     */
    public function getImportDefinition()
    {
        return $this->importDefinition;
    }

    public function __construct(\CHRISTIANHILLE\KaplanImporter\Domain\Model\ImportDefinition $importDefinition) {
        $this->importDefinition = $importDefinition;
        $this->days = $importDefinition->getDays();
    }
    
    public static function importSource(\CHRISTIANHILLE\KaplanImporter\Domain\Model\ImportDefinition $importDefinition) {
        $wrapper = new ImportDefinitionWrapper($importDefinition);
        return $wrapper->getImportSource();
    }
    
    /**
     * Returns importSource name specific to this import definition
     *
     * @return string
     */
    public function getImportSource() {
        return "KAPLAN_IMPORTER_" . $this->importDefinition->getUid();
    }
    
    public function setDays(int $days) {
        $this->days = $days;
    }
    
    /**
     * Return url to load from with given date
     * 
     * @param \DateTime $datetime
     * @return string
     */
    public function getUrl(\DateTime $datetime) : string {
        $url = $this->importDefinition->getUrl();
        if (strripos($url, "arbeitsgruppe=") == FALSE) $url .= "&arbeitsgruppe=" . $this->importDefinition->getArbeitsgruppe();
        if (strripos($url, "code=") == FALSE) $url .= "&code=" . $this->importDefinition->getCode();
        return $url . "&type=xml&start=" . $datetime->format("d.M.Y");
    }
    
    protected $datetime;
    protected $currentDay;
    protected $days;
    
    /**
     * Is there a next url and date available
     * 
     * @return boolean
     */
    public function hasNext() {
        if (is_null($this->datetime)) {
            $this->datetime = new \DateTime("midnight");
            $this->currentDay = 0;
            return true;
        }
        else {
            if ($this->currentDay + $this->days < $this->importDefinition->getDays()) {
                $this->currentDay += $this->days;
                $this->datetime = $this->datetime->add(\DateInterval::createFromDateString("". $this->days ." days"));
                return true;
            }
            return false;
        }
        return true;
    }
    
    /**
     * Next starting day to load 
     * 
     * @return \DateTime
     */
    public function nextFrom() {
        return $this->datetime;
    }
    
    /**
     * Next ending day to load
     *
     * @return \DateTime
     */
    public function nextTo() {
        $datetime = new \DateTime();
        $datetime->setTimestamp($this->datetime->getTimestamp());
        return $datetime->add(\DateInterval::createFromDateString("". $this->days ." days"));
    }
    
    public function nextDays() {
        return $this->days;
    }
    
    /**
     * Return next url to load data from 
     * 
     * @return string
     */
    public function nextUrl() {
        return $this->getUrl($this->datetime) . "&days=" . $this->days;
    }
    
    /**
     * Return first category
     * 
     * @return \GeorgRinger\News\Domain\Model\Category
     */
    public function getCategory() : \GeorgRinger\News\Domain\Model\Category {
        $categories = $this->importDefinition->getCategories();
        $categories->rewind();
        return $categories->current();
    }
}

