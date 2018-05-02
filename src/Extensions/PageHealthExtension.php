<?php

namespace Vulcan\Seo\Extensions;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\ORM\DataExtension;
use Sunra\PhpSimple\HtmlDomParser;
use Vulcan\Seo\Analysis\Analysis;
use Vulcan\Seo\Forms\GoogleSearchPreview;
use Vulcan\Seo\Forms\HealthAnalysisField;

/**
 * Class PageHealthExtension
 * @package Vulcan\Seo\Extensions
 *
 * @property string FocusKeyword
 */
class PageHealthExtension extends DataExtension
{
    /**
     * @var string|null
     */
    protected $renderedHtml;

    private static $db = [
        'FocusKeyword' => 'Varchar(50)'
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        parent::updateCMSFields($fields);

        $fields->addFieldsToTab('Root.Main', [
            ToggleCompositeField::create(null, 'SEO Health Analysis', [
                GoogleSearchPreview::create('GoogleSearchPreview', 'Search Preview', $this->getOwner(), $this->getRenderedHtmlDomParser()),
                TextField::create('FocusKeyword', 'Set focus keyword'),
                HealthAnalysisField::create('ContentAnalysis', 'Content Analysis', $this->getOwner()),
            ])
        ], 'Metadata');
    }

    /**
     * Gets the rendered html (current version, either draft or live)
     *
     * @return string|null
     */
    public function getRenderedHtml()
    {
        // use the public URL unless an override is provided, e.g. in a Docker container setup
        if (!$this->renderedHtml) {
            $providedInternalServer = Config::inst()->get('Vulcan\Seo\Extensions\PageHealthExtension', 'internal_server_url');
            $pageURL = !empty($providedInternalServer) ?
                $providedInternalServer . $this->owner->Link() : $this->getOwner()->AbsoluteLink();
            $this->renderedHtml = file_get_contents($pageURL);
        }

        return $this->renderedHtml;
    }

    /**
     * Gets the DOM parser for the rendered html
     *
     * @return \simplehtmldom_1_5\simple_html_dom
     */
    public function getRenderedHtmlDomParser()
    {
        return HtmlDomParser::str_get_html($this->getRenderedHtml());
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

    /**
     * Override this if you have more than just `Content` (or don't have `Content` at all). Fields should
     * be in the order for which they appear for a frontend user
     *
     * @return array
     */
    public function seoContentFields()
    {
        return [
            'Content'
        ];
    }
}
