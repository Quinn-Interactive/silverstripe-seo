<?php

namespace Vulcan\Seo\Extensions;

use KubAT\PhpSimple\HtmlDomParser;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\Requirements;
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
    const EMPTY_HTML = '<p></p>';

    /**
     * @var string|null
     */
    protected $renderedHtml;

    private static $db = [
        'FocusKeyword' => 'Varchar(50)'
    ];

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
     * Gets the rendered html (current version, either draft or live)
     *
     * @return string|null
     */
    public function getRenderedHtml()
    {
        if (!$this->renderedHtml) {
            $controllerName = $this->owner->getControllerName();
            if ('SilverStripe\UserForms\Control\UserDefinedFormController' == $controllerName) {
                // remove the Form since it crashes
                $this->owner->Form = false;
            }
            Requirements::clear(); // we only want the HTML, not any of the js or css
            $this->renderedHtml = $controllerName::singleton()->render($this->owner);
            Requirements::restore(); // put the js/css requirements back when we're done
        }

        if ($this->renderedHtml === false) {
            $this->renderedHtml = self::EMPTY_HTML;
        }

        return $this->renderedHtml;
    }

    /**
     * Gets the DOM parser for the rendered html
     *
     * @return \simple_html_dom\simple_html_dom
     */
    public function getRenderedHtmlDomParser()
    {
        return HtmlDomParser::str_get_html($this->getRenderedHtml());
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

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        parent::updateCMSFields($fields);

        if ($this->owner instanceof \SilverStripe\ErrorPage\ErrorPage) {
            return;
        }

        $fields->addFieldsToTab('Root.Main', [
            ToggleCompositeField::create('SEOHealthAnalysis', 'SEO Health Analysis', [
                GoogleSearchPreview::create('GoogleSearchPreview', 'Search Preview', $this->getOwner(), $this->getRenderedHtmlDomParser()),
                TextField::create('FocusKeyword', 'Set focus keyword'),
                HealthAnalysisField::create('ContentAnalysis', 'Content Analysis', $this->getOwner()),
            ])
        ], 'Metadata');
    }
}
