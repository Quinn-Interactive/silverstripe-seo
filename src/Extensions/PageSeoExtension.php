<?php

namespace QuinnInteractive\Seo\Extensions;

use QuinnInteractive\Seo\Builders\FacebookMetaGenerator;
use QuinnInteractive\Seo\Seo;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\VersionedAdmin\Controllers\HistoryViewerController;

/**
 * Class PageSeoExtension
 * @package QuinnInteractive\Seo\Extensions
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

    private static string $tab_name = 'Root.SEO';

    private static bool $start_closed = true;

    private static bool $use_composite_field = true;

    private static $cascade_deletes = [
        'FacebookPageImage',
        'TwitterPageImage'
    ];

    private static $db = [
        'FacebookPageType'        => 'Varchar(50)',
        'FacebookPageTitle'       => 'Varchar(255)',
        'FacebookPageDescription' => 'Text',
        'TwitterPageTitle'        => 'Varchar(255)',
        'TwitterPageDescription'  => 'Text'
    ];

    /**
     * The "creator tag" is the meta tag for Twitter to specify the creator's Twitter account. Enabled by default
     *
     * @config
     * @var bool
     */
    private static $enable_creator_tag = true;

    private static $has_one = [
        'FacebookPageImage' => Image::class,
        'TwitterPageImage'  => Image::class,
        'Creator'           => Member::class
    ];

    private static $owns = [
        'FacebookPageImage',
        'TwitterPageImage'
    ];

    /**
     * Extension point for SiteTree to merge all tags with the standard meta tags
     *
     * @param $tags
     */
    public function MetaTags(&$tags)
    {
        $tags = explode(PHP_EOL, (string) $tags);
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

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->getOwner()->ID && !$this->getOwner()->Creator()->exists() && $member = Security::getCurrentUser()) {
            $this->getOwner()->CreatorID = $member->ID;
        }
    }

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        parent::updateCMSFields($fields);

        $suppressMessaging = false;

        if (Controller::curr() instanceof HistoryViewerController) { // avoid cluttering the history comparison UI
            $suppressMessaging = true;
        }

        $openGraphFields = [
            DropdownField::create('FacebookPageType', 'Type', FacebookMetaGenerator::getValidTypes()),
            TextField::create('FacebookPageTitle', 'Title')
                ->setAttribute('placeholder', $this->getOwner()->Title)
                ->setRightTitle($suppressMessaging ? '' : 'If blank, inherits default page title')
                ->setTargetLength(45, 25, 70),
            UploadField::create('FacebookPageImage', 'Image')
                ->setRightTitle($suppressMessaging
                    ? ''
                    : 'Facebook recommends images to be 1200 x 630 pixels. ' .
                    'If no image is provided, Facebook will choose the first image that appears on the page, ' .
                    'which usually has bad results')
                ->setFolderName('seo'),
            TextareaField::create('FacebookPageDescription', 'Description')
                ->setAttribute('placeholder', $this->getOwner()->MetaDescription ?:
                    $this->getOwner()->dbObject('Content')->LimitCharacters(297))
                ->setRightTitle($suppressMessaging
                    ? ''
                    : 'If blank, inherits meta description if it exists ' .
                    'or gets the first 297 characters from content')
                ->setTargetLength(200, 160, 320),
        ];


        $fields->addFieldsToTab(
            $this->config()->get('tab_name'),
            $this->config()->get('use_composite_field') ? [
                ToggleCompositeField::create('FacebookSeoComposite', 'Open Graph', $openGraphFields)
                    ->setStartClosed($this->config()->get('start_closed')),
            ] : $openGraphFields,
            'Metadata'
        );

        $fields->addFieldsToTab(
            $this->config()->get('tab_name'),
            [
                ToggleCompositeField::create('TwitterSeoComposite', 'Twitter SEO', [
                    TextField::create('TwitterPageTitle', 'Title')
                        ->setAttribute('placeholder', $this->getOwner()->Title)
                        ->setRightTitle($suppressMessaging ? '' : 'If blank, inherits default page title')
                        ->setTargetLength(45, 25, 70),
                    UploadField::create('TwitterPageImage', 'Image')
                        ->setRightTitle($suppressMessaging ? '' : 'Must be at least 280x150 pixels')
                        ->setFolderName('seo'),
                    TextareaField::create('TwitterPageDescription', 'Description')
                        ->setAttribute('placeholder', $this->getOwner()->MetaDescription ?:
                            $this->getOwner()->dbObject('Content')->LimitCharacters(297))
                        ->setRightTitle($suppressMessaging
                            ? ''
                            : 'If blank, inherits meta description if it exists ' .
                            'or gets the first 297 characters from content')
                        ->setTargetLength(200, 160, 320),
                ])
            ],
            'Metadata'
        );
    }
}
