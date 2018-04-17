<?php

namespace Vulcan\Seo\Extensions;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use Vulcan\Seo\Builders\FacebookMetaGenerator;
use Vulcan\Seo\Builders\TwitterMetaGenerator;

/**
 * Class PageSeoExtension
 * @package Vulcan\Seo\Extensions
 *
 * @property string FacebookPageType
 * @property string FacebookPageTitle
 * @property string FacebookPageDescription
 * @property int    FacebookPageImageID
 * @property int    CreatorID
 *
 * @method Image FacebookPageImage()
 * @method Member|MemberExtension Creator()
 */
class PageSeoExtension extends DataExtension
{
    use Configurable;

    private static $db = [
        'FacebookPageType'        => 'Varchar(50)',
        'FacebookPageTitle'       => 'Varchar(255)',
        'FacebookPageDescription' => 'Text',
        'TwitterPageTitle'        => 'Varchar(255)',
        'TwitterPageDescription'  => 'Text'
    ];

    private static $has_one = [
        'FacebookPageImage' => Image::class,
        'TwitterPageImage'  => Image::class,
        'Creator'           => Member::class
    ];

    private static $owns = [
        'FacebookPageImage',
        'TwitterPageImage'
    ];

    private static $cascade_deletes = [
        'FacebookPageImage',
        'TwitterPageImage'
    ];

    /**
     * The "creator tag" is the meta tag for Twitter to specify the creators Twitter account. Enabled by default
     *
     * @config
     * @var bool
     */
    private static $enable_creator_tag = true;

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->getOwner()->Creator()->exists()) {
            $this->getOwner()->CreatorID = Security::getCurrentUser()->ID;
        }
    }

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        parent::updateCMSFields($fields);

        $fields->addFieldsToTab('Root.Main', [
            ToggleCompositeField::create(null, 'Facebook SEO', [
                DropdownField::create('FacebookPageType', 'Type', FacebookMetaGenerator::getValidTypes()),
                TextField::create('FacebookPageTitle', 'Title')->setAttribute('placeholder', $this->getOwner()->Title)->setRightTitle('If blank, inherits default page title')->setTargetLength(45, 25, 70),
                UploadField::create('FacebookPageImage', 'Image')->setRightTitle('Facebook recommends images to be 1200 x 630 pixels. If no image is provided, facebook will choose the first image that appears on the page which usually has bad results')->setFolderName('seo'),
                TextareaField::create('FacebookPageDescription', 'Description')->setAttribute('placeholder', $this->getOwner()->MetaDescription ?: $this->getOwner()->dbObject('Content')->LimitCharacters(297))->setRightTitle('If blank, inherits meta description if it exists or gets the first 297 characters from content')->setTargetLength(200, 160, 320),
            ]),
            ToggleCompositeField::create(null, 'Twitter SEO', [
                TextField::create('TwitterPageTitle', 'Title')->setAttribute('placeholder', $this->getOwner()->Title)->setRightTitle('If blank, inherits default page title')->setTargetLength(45, 25, 70),
                UploadField::create('TwitterPageImage', 'Image')->setRightTitle('Must be at least 280x150 pixels')->setFolderName('seo'),
                TextareaField::create('TwitterPageDescription', 'Description')->setAttribute('placeholder', $this->getOwner()->MetaDescription ?: $this->getOwner()->dbObject('Content')->LimitCharacters(297))->setRightTitle('If blank, inherits meta description if it exists or gets the first 297 characters from content')->setTargetLength(200, 160, 320),
            ])
        ], 'Metadata');
    }

    /**
     * Extension point for SiteTree to merge all tags with the standard meta tags
     *
     * @param $tags
     */
    public function MetaTags(&$tags)
    {
        $tags = explode(PHP_EOL, $tags);
        $tags = array_merge($tags, $this->getCanonicalUrlLink(), $this->getFacebookMetaTags(), $this->getTwitterMetaTags(), $this->getArticleTags());
        $tags = implode(PHP_EOL, $tags);
    }

    /**
     * Creates the Facebook/OpenGraph meta tags
     *
     * @return array
     */
    public function getFacebookMetaTags()
    {
        $owner = $this->getOwner();
        $imageWidth = $owner->FacebookPageImage()->exists() ? $owner->FacebookPageImage()->getWidth() : null;
        $imageHeight = $owner->FacebookPageImage()->exists() ? $owner->FacebookPageImage()->getHeight() : null;

        $generator = FacebookMetaGenerator::create();
        $generator->setTitle($owner->FacebookPageTitle ?: $owner->Title);
        $generator->setDescription($owner->FacebookPageDescription ?: $owner->MetaDescription ?: $owner->Content);
        $generator->setImageUrl(($owner->FacebookPageImage()->exists()) ? $owner->FacebookPageImage()->AbsoluteLink() : null);
        $generator->setImageDimensions($imageWidth, $imageHeight);
        $generator->setType($owner->FacebookPageType ?: 'website');
        $generator->setUrl($owner->AbsoluteLink());

        return $generator->process();
    }

    /**
     * Creates the twitter meta tags
     *
     * @return array
     */
    public function getTwitterMetaTags()
    {
        $owner = $this->getOwner();
        $generator = TwitterMetaGenerator::create();
        $generator->setTitle($owner->FacebookPageTitle ?: $owner->Title);
        $generator->setDescription($owner->FacebookPageDescription ?: $owner->MetaDescription ?: $owner->Content);
        $generator->setImageUrl(($owner->FacebookPageImage()->exists()) ? $owner->FacebookPageImage()->AbsoluteLink() : null);

        if ($this->config()->get('enable_creator_tag') && $owner->Creator()->exists() && $owner->Creator()->TwitterAccountName) {
            $generator->setCreator($owner->Creator()->TwitterAccountName);
        }

        return $generator->process();
    }

    /**
     * Creates article:published_time and article:modified_time tags
     *
     * @return array
     */
    public function getArticleTags()
    {
        /** @var DBDatetime $published */
        $published = $this->getOwner()->dbObject('Created');

        /** @var DBDatetime $modified */
        $modified = $this->getOwner()->dbObject('LastEdited');

        return [
            sprintf('<meta property="article:published_time" content="%s" />', $published->Rfc3339()),
            sprintf('<meta property="article:modified_time" content="%s" />', $modified->Rfc3339()),
        ];
    }

    /**
     * Creates the canonical url link
     *
     * @return array
     */
    public function getCanonicalUrlLink()
    {
        return [
            sprintf('<link rel="canonical" href="%s"/>', $this->getOwner()->AbsoluteLink())
        ];
    }

    /**
     * @return \Page|static
     */
    public function getOwner()
    {
        /** @var \Page $owner */
        $owner = parent::getOwner();
        return $owner;
    }
}
