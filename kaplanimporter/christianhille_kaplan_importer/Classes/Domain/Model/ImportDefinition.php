<?php
namespace CHRISTIANHILLE\KaplanImporter\Domain\Model;

use \GeorgRinger\News\Domain\Model\Category;

class ImportDefinition extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var string
     */
    protected $url;
    
    /**
     * 
     * @var string
     */
    protected $arbeitsgruppe;
    /**
     * 
     * @var string
     */
    protected $code;
    
    /**
     * @var bool
     */
    protected $roomsAndPlacesAsCategories;
   
    /**
     * @var bool
     */
    protected $categoryAsCategory;
    
    /**
     * @var bool
     */
    protected $createMissingCategories;
    
    
    /**
     * @var int
     */
    protected $targetFolder;
    
    /**
     * @var int
     */
    protected $days;
    
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\GeorgRinger\News\Domain\Model\Category>
     * @lazy
     */
    protected $categories;


    /**
     * @return void
     */
    public function initializeObject()
    {
        $this->categories = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * @param string $feedUrl
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
    
    /**
     * @return boolean
     */
    public function getRoomsAndPlacesAsCategories() {
        return $this->roomsAndPlacesAsCategories;
    }
    
    /**
     * @param boolean $roomsAndPlacesAsCategories
     * @return void
     */
    public function setRoomsAndPlacesAsCategories($roomsAndPlacesAsCategories) {
        $this->roomsAndPlacesAsCategories = $roomsAndPlacesAsCategories;
    }
    
    /**
     * Returns the boolean state of roomsAndPlacesAsCategories
     *
     * @return bool
     */
    public function isRoomsAndPlacesAsCategories() {
        return $this->roomsAndPlacesAsCategories;
    }
    
    /**
     * @return boolean
     */
    public function getCategoryAsCategory() {
        return $this->categoryAsCategory;
    }
    
    /**
     * 
     * @param boolean $categoryAsCategory
     */
    public function setCategoryAsCategory($categoryAsCategory) {
        $this->categoryAsCategory = $categoryAsCategory;
    }
    
    /**
     * @return boolean 
     */
    public function isCategoryAsCategory() {
        return $this->categoryAsCategory;
    }
    
    /**
     * @return boolean
     */
    public function getCreateMissingCategories() {
        return $this->createMissingCategories;
    }
    
    /**
     *
     * @param boolean $createMissingCategories
     */
    public function setCreateMissingCategories($createMissingCategories) {
        $this->createMissingCategories = $createMissingCategories;
    }
    
    /**
     * @return boolean
     */
    public function isCreateMissingCategories() {
        return $this->createMissingCategories;
    }
    
    /**
     * @return int
     */
    public function getTargetFolder()
    {
        return $this->targetFolder;
    }
    
    /**
     * @param int $targetFolder
     * @return void
     */
    public function setTargetFolder($targetFolder)
    {
        $this->targetFolder = $targetFolder;
    }
    
    /**
     * @return number
     */
    public function getDays() {
        return $this->days;
    }
    
    /**
     * 
     * @param int $days
     */
    public function setDays($days) {
        $this->days = $days;
    }
    
    
    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\GeorgRinger\News\Domain\Model\Category> $categories
     * @return void
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }
    
    /**
     * @param \GeorgRinger\News\Domain\Model\Category $category
     * @return void
     */
    public function addCategory(Category $category)
    {
        $this->categories->attach($category);
    }
    
    /**
     * Remove category from the list
     *
     * @param \GeorgRinger\News\Domain\Model\Category $category
     * @return void
     */
    public function removeCategory(Category $category)
    {
        $this->categories->detach($category);
    }
    

    /**
     * 
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\GeorgRinger\News\Domain\Model\Category>
     */
    public function getCategories() {
        return $this->categories;
    }

    /**
     * @return string
     */
    public function getArbeitsgruppe()
    {
        return $this->arbeitsgruppe;
    }
    
    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
    
    /**
     * @param string $arbeitsgruppe
     */
    public function setArbeitsgruppe($arbeitsgruppe)
    {
        $this->arbeitsgruppe = $arbeitsgruppe;
    }
    
    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }
    
}

