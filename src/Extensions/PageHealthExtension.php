<?php

namespace QuinnInteractive\Seo\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\ErrorPage\ErrorPage;
use KubAT\PhpSimple\HtmlDomParser;
use QuinnInteractive\Seo\Forms\GoogleSearchPreview;
use QuinnInteractive\Seo\Forms\HealthAnalysisField;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\VersionedAdmin\Controllers\CMSPageHistoryViewerController;
use SilverStripe\VersionedAdmin\Controllers\HistoryViewerController;
use SilverStripe\View\Requirements;

/**
 * @property ?string $FocusKeyword
 * @extends Extension<\Page&static>
 */
class PageHealthExtension extends Extension
{
    public const EMPTY_HTML = '<p></p>';

    private static $tab_name = 'Root.SEO';

    private static bool $move_default_meta_fields = true;

    private static bool $start_closed = true;

    /**
     * @var string|null
     */
    protected $renderedHtml;

    private static $db = [
        'FocusKeyword' => 'Varchar(50)'
    ];

    /**
     * Gets the rendered html (current version, either draft or live)
     *
     * @return string|null
     */
    public function getRenderedHtml()
    {
        if (!$this->renderedHtml) {
            $controllerName = $this->getOwner()->getControllerName();
            if ('SilverStripe\UserForms\Control\UserDefinedFormController' == $controllerName) {
                // remove the Form since it crashes
                $this->getOwner()->Form = false;
            }
            Requirements::clear(); // we only want the HTML, not any of the js or css
            $this->renderedHtml = $controllerName::singleton()->render($this->getOwner());
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
        if (
            Controller::curr() instanceof HistoryViewerController ||
            Controller::curr() instanceof CMSPageHistoryViewerController
        ) { // avoid breaking the history comparison UI
            return;
        }

        if (class_exists('\SilverStripe\ErrorPage\ErrorPage') && $this->getOwner() instanceof ErrorPage) {
            return;
        }

        $dom = $this->getRenderedHtmlDomParser();

        if ($dom) {
            $fields->removeByName(['FocusKeyword']);
            $fields->addFieldsToTab($this->getOwner()->config()->get('tab_name'), [
                ToggleCompositeField::create('SEOHealthAnalysis', 'SEO Health Analysis', [
                    GoogleSearchPreview::create(
                        'GoogleSearchPreview',
                        'Search Preview',
                        $this->getOwner(),
                        $dom
                    ),
                    TextField::create('FocusKeyword', 'Set focus keyword'),
                    HealthAnalysisField::create('ContentAnalysis', 'Content Analysis', $this->getOwner()),
                ])->setStartClosed($this->getOwner()->config()->get('start_closed'))
            ], 'Metadata');

            if ($this->getOwner()->config()->get('move_default_meta_fields')) {
                $meta = $fields->fieldByName('Root.Main.Metadata');

                if ($meta) {
                    $fields->removeByName('Metadata');
                    $fields->addFieldToTab($this->getOwner()->config()->get('tab_name'), $meta);
                }
            }
        }
    }
}
