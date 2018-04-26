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
use Vulcan\Seo\Models\RenderedHtml;
use Vulcan\Seo\Seo;

/**
 * Class PageSeoExtension
 * @package Vulcan\Seo\Extensions
 *
 * @property string FacebookPageType
 * @property string FacebookPageTitle
 * @property string FacebookPageDescription
 *
 * @property int    FacebookPageImageID
 * @property int    CreatorID
 * @property int    RenderedHtmlID
 *
 * @method RenderedHtml RenderedHtml()
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
        'TwitterPageDescription'  => 'Text',
        'RenderedHtml'            => 'Text'
    ];

    private static $has_one = [
        'RenderedHtml'      => RenderedHtml::class,
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

        if (!$this->getOwner()->ID && !$this->getOwner()->Creator()->exists() && $member = Security::getCurrentUser()) {
            $this->getOwner()->CreatorID = $member->ID;
        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if ($this->getOwner()->RenderedHtml()->exists()) {
            $this->getOwner()->RenderedHtml()->refresh();
        } else {
            $this->getOwner()->RenderedHtmlID = RenderedHtml::findOrMake($this->getOwner())->ID;
            $this->getOwner()->write();
            $this->getOwner()->publishRecursive();
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
        $tags = array_merge(
            $tags,
            Seo::getCanonicalUrlLink($this->getOwner()),
            Seo::getFacebookMetaTags($this->getOwner()),
            Seo::getTwitterMetaTags($this->getOwner()),
            Seo::getArticleTags($this->getOwner()),
            Seo::getGoogleAnalytics(),
            Seo::getPixels()
        );

        $tags = implode(PHP_EOL, $tags);
    }
}
