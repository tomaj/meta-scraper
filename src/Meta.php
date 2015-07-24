<?php

namespace Tomaj\Scraper;

class Meta
{
    private $title;

    private $description;

    private $author;

    private $keywords;

    private $ogType;

    private $ogTitle;

    private $ogDescription;

    private $ogUrl;

    private $ogSiteName;

    private $ogImage;

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
        return $this;
    }

    public function getKeywords()
    {
        return $this->keywords;
    }

    public function setOgType($ogType)
    {
        $this->ogType = $ogType;
        return $this;
    }

    public function getOgType()
    {
        return $this->ogType;
    }

    public function setOgTitle($ogTitle)
    {
        $this->ogTitle = $ogTitle;
        return $this;
    }

    public function getOgTitle()
    {
        return $this->ogTitle;
    }

    public function setOgDescription($ogDescription)
    {
        $this->ogDescription = $ogDescription;
        return $this;
    }

    public function getOgDescription()
    {
        return $this->ogDescription;
    }

    public function setOgUrl($ogUrl)
    {
        $this->ogUrl = $ogUrl;
        return $this;
    }

    public function getOgUrl()
    {
        return $this->ogUrl;
    }

    public function setOgSiteName($ogSiteName)
    {
        $this->ogSiteName = $ogSiteName;
        return $this;
    }

    public function getOgSiteName()
    {
        return $this->ogSiteName;
    }

    public function setOgImage($ogImage)
    {
        $this->ogImage = $ogImage;
        return $this;
    }

    public function getOgImage()
    {
        return $this->ogImage;
    }
}
