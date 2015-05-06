<?php
class Flickr_Model {
    private $id;
    private $title;
    private $siteTitle;
    private $description;
    private $caption;
    private $published;
    private $thumbnailUrl;
    private $smallSquareUrl;
    private $largeSquareUrl;
    private $originalUrl;
    private $medium640Url;
    private $mediumUrl;
    private $ordinal;
    private $credit;
    
    public function setCredit($credit) {
        $this->credit = $credit;
    }

    public function getCredit() {
        return $this->credit;
    }
    
    public function setMediumUrl($mediumUrl) {
        $this->mediumUrl = $mediumUrl;
    }

    public function getMediumUrl() {
        return $this->mediumUrl;
    }
    
    public function setMedium640Url($medium640Url) {
        $this->mediumUrl = $medium540Url;
    }

    public function getMedium640Url() {
        return $this->medium640Url;
    }
    
    public function setId($id) {
        $this->id = $id;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setSiteTitle($siteTitle) {
        $this->siteTitle = $siteTitle;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setCaption($caption) {
        $this->caption = $caption;
    }

    public function setPublished($published) {
        $this->published = $published;
    }

    public function setThumbnailUrl($thumbnailUrl) {
        $this->thumbnailUrl = $thumbnailUrl;
    }

    public function setSmallSquareUrl($smallSquareUrl) {
        $this->smallSquareUrl = $smallSquareUrl;
    }

    public function setLargeSquareUrl($largeSquareUrl) {
        $this->largeSquareUrl = $largeSquareUrl;
    }

    public function setOriginalUrl($originalUrl) {
        $this->originalUrl = $originalUrl;
    }

    public function setOrdinal($ordinal) {
        $this->ordinal = $ordinal;
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        $str = strval($this->title['_content']);
        $str = strip_tags($str);
        
        return $str;
    }

    public function getSiteTitle() {
        return $this->siteTitle;
    }

    public function getDescription() {
        $str = strval($this->description['_content']);
        $str = strip_tags($str);
        
        return $str;
    }

    public function getCaption() {
        return $this->caption;
    }

    public function getPublished() {
        return $this->published;
    }

    public function getThumbnailUrl() {
        return $this->thumbnailUrl;
    }

    public function getSmallSquareUrl() {
        return $this->smallSquareUrl;
    }

    public function getLargeSquareUrl() {
        return $this->largeSquareUrl;
    }

    public function getOriginalUrl() {
        return $this->originalUrl;
    }

    public function getOrdinal() {
        return $this->ordinal;
    }
    
    public static function build($data) {
        $len = count($data);
        
        if($len > 1) {
            $ary = array();
            foreach($data as $datum) {
                $flickr = new Flickr_Model();
                $flickr->setId($datum['id']);
                $flickr->setLargeSquareUrl($datum['url_q']);
                $flickr->setOriginalUrl($datum['url_o']);
                $flickr->setPublished($datum['dateupload']);
                $flickr->setSmallSquareUrl($datum['url_sq']);
                $flickr->setThumbnailUrl($datum['url_t']);
                $flickr->setTitle($datum['description']);
                $ary[] = $flickr;
            }
            
            return $ary;
        } else if($len == 1) {
            $flickr = new Flickr_Model();
            $flickr->setId($data['id']);
            $flickr->setLargeSquareUrl($data['url_q']);
            $flickr->setOriginalUrl($data['url_o']);
            $flickr->setPublished($data['dateupload']);
            $flickr->setSmallSquareUrl($data['url_sq']);
            $flickr->setThumbnailUrl($data['url_t']);
            $flickr->setTitle($data['description']);
            
            return $flickr;
        } else {}
        
        return false;
    }
}
?>