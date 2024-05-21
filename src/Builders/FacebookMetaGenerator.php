<?php

namespace QuinnInteractive\Seo\Builders;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class FacebookMetaGenerator
 * @package QuinnInteractive\Seo\Builders
 */
class FacebookMetaGenerator
{
    use Injectable, Configurable;

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
     * @var string
     */
    protected $type = 'website';

    /**
     * @var string|null
     */
    protected $url;

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
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function process()
    {
        $tags = [];

        if ($this->getTitle()) {
            $tags['og:title'] = htmlentities($this->getTitle());
        }

        if ($this->getDescription()) {
            $tags['og:description'] = htmlentities($this->getDescription());
        }

        if ($this->getType()) {
            $tags['og:type'] = $this->getType();
        }

        if ($this->getUrl()) {
            $tags['og:url'] = $this->getUrl();
        }

        if ($this->getImageUrl()) {
            $tags['og:image'] = $this->getImageUrl();
        }

        if ($this->imageWidth && $this->imageHeight) {
            $tags['og:image:width'] = $this->imageWidth;
            $tags['og:image:height'] =  $this->imageHeight;
        }

        $tags['og:locale'] = i18n::get_locale();
        $tags['og:site_name'] = SiteConfig::current_site_config()->Title;

        return $tags;
    }

    /**
     * @param mixed $description
     *
     * @return FacebookMetaGenerator
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param $width
     * @param $height
     *
     * @return $this
     */
    public function setImageDimensions($width, $height)
    {
        $this->setImageWidth($width);
        $this->setImageHeight($height);

        return $this;
    }

    /**
     * @param int $height
     *
     * @return $this
     */
    public function setImageHeight($height)
    {
        $this->imageHeight = $height;
        return $this;
    }

    /**
     * @param mixed $imageUrl
     *
     * @return FacebookMetaGenerator
     */
    public function setImageUrl($imageUrl)
    {
        if ($imageUrl && (substr($imageUrl, 0, 1) === '/' || substr($imageUrl, 0, 4) !== 'http')) {
            throw new \InvalidArgumentException(
                'A relative or invalid URL was detected; you must provide the full absolute URL'
            );
        }

        $this->imageUrl = $imageUrl;
        return $this;
    }

    /**
     * @param int $width
     *
     * @return $this
     */
    public function setImageWidth($width)
    {
        $this->imageWidth = $width;
        return $this;
    }

    /**
     * @param mixed $title
     *
     * @return FacebookMetaGenerator
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param mixed $type
     *
     * @return FacebookMetaGenerator
     * @throws \Exception
     */
    public function setType($type)
    {
        if (!in_array($type, array_keys(static::getValidTypes()))) {
            throw new \Exception(sprintf(
                'That type [%s] is not a valid type; please see: %s',
                $type,
                'https://developers.facebook.com/docs/reference/opengraph/'
            ));
        }

        $this->type = $type;
        return $this;
    }

    /**
     * @param mixed $url
     *
     * @return FacebookMetaGenerator
     */
    public function setUrl($url)
    {
        if ($url && (substr($url, 0, 1) === '/' || substr($url, 0, 4) !== 'http')) {
            throw new \InvalidArgumentException(
                'A relative URL was detected; you must provide the full absolute URL instead'
            );
        }

        $this->url = $url;
        return $this;
    }

    /**
     * Valid types supported by Open Graph
     *
     * @return array
     */
    public static function getValidTypes()
    {
        return [
            'website'                   => 'website',
            'apps.saves'                => 'apps.saves',
            'books.quotes'              => 'books.quotes',
            'books.rates'               => 'books.rates',
            'books.reads'               => 'books.reads',
            'books.wants_to_read'       => 'books.wants_to_read',
            'fitness.bikes'             => 'fitness.bikes',
            'fitness.runs'              => 'fitness.runs',
            'fitness.walks'             => 'fitness.walks',
            'games.achieves'            => 'games.achieves',
            'games.celebrate'           => 'games.celebrate',
            'games.plays'               => 'games.plays',
            'games.saves'               => 'games.saves',
            'music.listens'             => 'music.listens',
            'music.playlists'           => 'music.playlists',
            'news.publishes'            => 'news.publishes',
            'news.reads'                => 'news.reads',
            'og.follows'                => 'og.follows',
            'og.likes'                  => 'og.likes',
            'pages.saves'               => 'pages.saves',
            'restaurant.visited'        => 'restaurant.visited',
            'restaurant.wants_to_visit' => 'restaurant.wants_to_visit',
            'sellers.rates'             => 'sellers.rates',
            'video.rates'               => 'video.rates',
            'video.wants_to_watch'      => 'video.wants_to_watch',
            'video.watches'             => 'video.watches',
            'article'                   => 'article',
            'book'                      => 'book',
            'books.author'              => 'books.author',
            'books.book'                => 'books.book',
            'books.genre'               => 'books.genre',
            'business.business'         => 'business.business',
            'fitness.course'            => 'fitness.course',
            'game.achievement'          => 'game.achievement',
            'music.album'               => 'music.album',
            'music.playlist'            => 'music.playlist',
            'music.radio_station'       => 'music.radio_station',
            'music.song'                => 'music.song',
            'place'                     => 'place',
            'product'                   => 'product',
            'product.group'             => 'product.group',
            'product.item'              => 'product.item',
            'profile'                   => 'profile',
            'restaurant.menu'           => 'restaurant.menu',
            'restaurant.menu_item'      => 'restaurant.menu_item',
            'restaurant.menu_section'   => 'restaurant.menu_section',
            'restaurant.restaurant'     => 'restaurant.restaurant',
            'video.episode'             => 'video.episode',
            'video.movie'               => 'video.movie',
            'video.other'               => 'video.other',
            'video.tv_show'             => 'video.tv_show',
        ];
    }
}
