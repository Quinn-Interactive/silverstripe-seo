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
    use Injectable;
    use Configurable;

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
            $tags[] = sprintf('<meta property="og:title" content="%s"/>', htmlentities((string) $this->getTitle()));
        }

        if ($this->getDescription()) {
            $tags[] = sprintf('<meta property="og:description" content="%s"/>', htmlentities((string) $this->getDescription()));
        }

        if ($this->getType()) {
            $tags[] = sprintf('<meta property="og:type" content="%s"/>', $this->getType());
        }

        if ($this->getUrl()) {
            $tags[] = sprintf('<meta property="og:url" content="%s"/>', $this->getUrl());
        }

        if ($this->getImageUrl()) {
            $tags[] = sprintf('<meta property="og:image" content="%s"/>', $this->getImageUrl());
        }

        if ($this->imageWidth && $this->imageHeight) {
            $tags[] = sprintf('<meta property="og:image:width" content="%s" />', $this->imageWidth);
            $tags[] = sprintf('<meta property="og:image:height" content="%s" />', $this->imageHeight);
        }

        $tags[] = sprintf('<meta property="og:locale" content="%s" />', i18n::get_locale());
        $tags[] = sprintf('<meta property="og:site_name" content="%s" />', SiteConfig::current_site_config()->Title);

        return $tags;
    }

    /**
     * @return FacebookMetaGenerator
     */
    public function setDescription(mixed $description)
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
     * @return FacebookMetaGenerator
     */
    public function setImageUrl(mixed $imageUrl)
    {
        if ($imageUrl && (str_starts_with((string) $imageUrl, '/') || !str_starts_with((string) $imageUrl, 'http'))) {
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
     * @return FacebookMetaGenerator
     */
    public function setTitle(mixed $title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     *
     * @return FacebookMetaGenerator
     * @throws \Exception
     */
    public function setType(mixed $type)
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
     * @return FacebookMetaGenerator
     */
    public function setUrl(mixed $url)
    {
        if ($url && (str_starts_with((string) $url, '/') || !str_starts_with((string) $url, 'http'))) {
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
