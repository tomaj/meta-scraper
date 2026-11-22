<?php

declare(strict_types=1);

namespace Tomaj\Scraper;

use DateTime;

final class Meta
{
    private ?string $title = null;

    private ?string $description = null;

    /** @var list<Author> */
    private array $authors = [];

    private ?string $keywords = null;

    private ?string $ogType = null;

    private ?string $ogTitle = null;

    private ?string $ogDescription = null;

    private ?string $ogUrl = null;

    private ?string $ogSiteName = null;

    private ?string $ogImage = null;

    /** @var list<Section> */
    private array $sections = [];

    /** @var list<Tag> */
    private array $tags = [];

    private ?DateTime $publishedTime = null;

    private ?DateTime $modifiedTime = null;

    public function setTitle(string $title): Meta
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setDescription(string $description): Meta
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function addAuthor(Author $author): Meta
    {
        $this->authors[] = $author;
        return $this;
    }

    public function addAuthorByName(string $name): Meta
    {
        return $this->addAuthor(new Author(null, $name));
    }

    /**
     * @return list<Author>
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function addTag(Tag $tag): Meta
    {
        $this->tags[] = $tag;
        return $this;
    }

    /**
     * @return list<Tag>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function setKeywords(string $keywords): Meta
    {
        $this->keywords = $keywords;
        return $this;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setOgType(string $ogType): Meta
    {
        $this->ogType = $ogType;
        return $this;
    }

    public function getOgType(): ?string
    {
        return $this->ogType;
    }

    public function setOgTitle(string $ogTitle): Meta
    {
        $this->ogTitle = $ogTitle;
        return $this;
    }

    public function getOgTitle(): ?string
    {
        return $this->ogTitle;
    }

    public function setOgDescription(string $ogDescription): Meta
    {
        $this->ogDescription = $ogDescription;
        return $this;
    }

    public function getOgDescription(): ?string
    {
        return $this->ogDescription;
    }

    public function setOgUrl(string $ogUrl): Meta
    {
        $this->ogUrl = $ogUrl;
        return $this;
    }

    public function getOgUrl(): ?string
    {
        return $this->ogUrl;
    }

    public function setOgSiteName(string $ogSiteName): Meta
    {
        $this->ogSiteName = $ogSiteName;
        return $this;
    }

    public function getOgSiteName(): ?string
    {
        return $this->ogSiteName;
    }

    public function setOgImage(string $ogImage): Meta
    {
        $this->ogImage = $ogImage;
        return $this;
    }

    public function getOgImage(): ?string
    {
        return $this->ogImage;
    }

    public function addSection(Section $section): Meta
    {
        $this->sections[] = $section;
        return $this;
    }

    public function addSectionByName(string $name): Meta
    {
        return $this->addSection(new Section(null, $name));
    }

    /**
     * @return list<Section>
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    public function setPublishedTime(DateTime|string $publishedTime): Meta
    {
        if ($publishedTime instanceof DateTime) {
            $this->publishedTime = $publishedTime;
        } else {
            $time = strtotime($publishedTime);
            if ($time !== false) {
                $this->publishedTime = new DateTime('@' . $time);
            }
        }
        return $this;
    }

    public function getPublishedTime(): ?DateTime
    {
        return $this->publishedTime;
    }

    public function setModifiedTime(DateTime|string $modifiedTime): Meta
    {
        if ($modifiedTime instanceof DateTime) {
            $this->modifiedTime = $modifiedTime;
        } else {
            $time = strtotime($modifiedTime);
            if ($time !== false) {
                $this->modifiedTime = new DateTime('@' . $time);
            }
        }
        return $this;
    }

    public function getModifiedTime(): ?DateTime
    {
        return $this->modifiedTime;
    }

    public function merge(Meta $meta): Meta
    {
        $value = $meta->getTitle();
        if ($this->isEmpty($this->getTitle()) && !$this->isEmpty($value)) {
            $this->setTitle($value);
        }

        $value = $meta->getDescription();
        if ($this->isEmpty($this->getDescription()) && !$this->isEmpty($value)) {
            $this->setDescription($value);
        }

        if ($this->getAuthors() === [] && $meta->getAuthors() !== []) {
            foreach ($meta->getAuthors() as $author) {
                $this->addAuthor($author);
            }
        }

        if ($this->getSections() === [] && $meta->getSections() !== []) {
            foreach ($meta->getSections() as $section) {
                $this->addSection($section);
            }
        }

        if ($this->getTags() === [] && $meta->getTags() !== []) {
            foreach ($meta->getTags() as $tag) {
                $this->addTag($tag);
            }
        }

        $value = $meta->getKeywords();
        if ($this->isEmpty($this->getKeywords()) && !$this->isEmpty($value)) {
            $this->setKeywords($value);
        }

        $value = $meta->getOgType();
        if ($this->isEmpty($this->getOgType()) && !$this->isEmpty($value)) {
            $this->setOgType($value);
        }

        $value = $meta->getOgTitle();
        if ($this->isEmpty($this->getOgTitle()) && !$this->isEmpty($value)) {
            $this->setOgTitle($value);
        }

        $value = $meta->getOgDescription();
        if ($this->isEmpty($this->getOgDescription()) && !$this->isEmpty($value)) {
            $this->setOgDescription($value);
        }

        $value = $meta->getOgUrl();
        if ($this->isEmpty($this->getOgUrl()) && !$this->isEmpty($value)) {
            $this->setOgUrl($value);
        }

        $value = $meta->getOgSiteName();
        if ($this->isEmpty($this->getOgSiteName()) && !$this->isEmpty($value)) {
            $this->setOgSiteName($value);
        }

        $value = $meta->getOgImage();
        if ($this->isEmpty($this->getOgImage()) && !$this->isEmpty($value)) {
            $this->setOgImage($value);
        }

        $value = $meta->getPublishedTime();
        if ($this->isEmpty($this->getPublishedTime()) && !$this->isEmpty($value)) {
            $this->setPublishedTime($value);
        }

        $value = $meta->getModifiedTime();
        if ($this->isEmpty($this->getModifiedTime()) && !$this->isEmpty($value)) {
            $this->setModifiedTime($value);
        }

        return $this;
    }

    /**
     * Mirrors the original loose emptiness check, so '' and '0' count as missing values.
     *
     * @phpstan-assert-if-false !null $value
     */
    private function isEmpty(string|DateTime|null $value): bool
    {
        return in_array($value, [null, '', '0'], true);
    }
}
