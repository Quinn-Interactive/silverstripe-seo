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

        $tags[] = '<meta name="twitter:card" content="summary"/>';
        if ($this->getTitle()) {
            $tags[] = sprintf('<meta name="twitter:title" content="%s"/>', htmlentities((string) $this->getTitle()));
        }

        if ($this->getDescription()) {
            $tags[] = sprintf('<meta name="twitter:description" content="%s"/>', htmlentities((string) $this->getDescription()));
        }

        if ($this->getImageUrl()) {
            $tags[] = sprintf('<meta name="twitter:image" content="%s"/>', $this->getImageUrl());
        }
        if ($this->getCreator()) {
            $tags[] = sprintf('<meta name="twitter:creator" content="@%s"/>', $this->getCreator());
        }

        if ($siteConfig->TwitterAccountName) {
            $tags[] = sprintf('<meta name="twitter:site" content="@%s" />', $siteConfig->TwitterAccountName);
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
     * @return TwitterMetaGenerator
     */
    public function setDescription(mixed $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return TwitterMetaGenerator
     */
    public function setImageUrl(mixed $imageUrl)
    {
        if ($imageUrl && (str_starts_with((string) $imageUrl, '/') || !str_starts_with((string) $imageUrl, 'http'))) {
            throw new \InvalidArgumentException(
                'A relative or invalid URL was detected, your must provide the full absolute URL'
            );
        }
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @return TwitterMetaGenerator
     */
    public function setTitle(mixed $title)
    {
        $this->title = $title;

        return $this;
    }
}
