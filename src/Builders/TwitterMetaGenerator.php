<?php

namespace QuinnInteractive\Seo\Builders;

use QuinnInteractive\Seo\Extensions\SiteConfigSettingsExtension;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class TwitterMetaGenerator
 * @package QuinnInteractive\Seo\Builders
 */
class TwitterMetaGenerator
{
    use Injectable, Configurable;

    /**
     * @var string|null
     */
    protected $creator;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var string|null
     */
    protected $imageHeight;

    /**
     * @var string|null
     */
    protected $imageUrl;

    /**
     * @var string|null
     */
    protected $imageWidth;

    /**
     * @var string|null
     */
    protected $title;

    /**
     * @return mixed
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        $obj = DBHTMLText::create();

        if (!$this->description) {
            return null;
        }

        return $obj->setValue($this->description)->LimitCharacters(297);
    }

    /**
     * @return mixed
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function process()
    {
        /** @var SiteConfig|SiteConfigSettingsExtension $siteConfig */
        $siteConfig = SiteConfig::current_site_config();
        $tags       = [];

        $tags['twitter:card'] = 'summary';
        if ($this->getTitle()) {
            $tags['twitter:title'] = htmlentities($this->getTitle());
        }

        if ($this->getDescription()) {
            $tags['twitter:description'] = htmlentities($this->getDescription());
        }

        if ($this->getImageUrl()) {
            $tags['twitter:image'] = $this->getImageUrl();
        }
        if ($this->getCreator()) {
            $tags['twitter:creator'] = "@{$this->getCreator()}";
        }

        if ($siteConfig->TwitterAccountName) {
            $tags['twitter:site'] = "@$siteConfig->TwitterAccountName";
        }

        return $tags;
    }

    /**
     * @param string $creator
     *
     * @return TwitterMetaGenerator
     */
    public function setCreator($creator)
    {
        $this->creator = str_replace('@', '', $creator);

        return $this;
    }

    /**
     * @param mixed $description
     *
     * @return TwitterMetaGenerator
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param mixed $imageUrl
     *
     * @return TwitterMetaGenerator
     */
    public function setImageUrl($imageUrl)
    {
        if ($imageUrl && (substr($imageUrl, 0, 1) === '/' || substr($imageUrl, 0, 4) !== 'http')) {
            throw new \InvalidArgumentException(
                'A relative or invalid URL was detected, your must provide the full absolute URL'
            );
        }
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @param mixed $title
     *
     * @return TwitterMetaGenerator
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}
