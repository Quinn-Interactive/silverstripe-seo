<?php

namespace Vulcan\Seo\Forms;

use SilverStripe\Forms\LiteralField;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use simplehtmldom_1_5\simple_html_dom;

/**
 * Class GoogleSearchPreview
 * @package Vulcan\Seo\Forms
 */
class GoogleSearchPreview extends LiteralField
{
    protected $schemaComponent = 'GoogleSearchPreview';

    protected $template = self::class;

    /**
     * @var int
     */
    protected $result;

    /**
     * HealthAnalysisField constructor.
     *
     * @param string                               $name
     * @param \SilverStripe\Forms\FormField|string $title
     * @param \Page                                $page
     * @param simple_html_dom                      $domParser
     */
    public function __construct($name, $title, \Page $page, simple_html_dom $domParser)
    {
        $renderedTitle = $domParser->find('title', 0);
        $firstParagraph = $domParser->find('p', 0);

        Requirements::javascript('vulcandigital/silverstripe-seo:dist/javascript/main.min.js');
        Requirements::css('vulcandigital/silverstripe-seo:dist/css/styles.min.css');

        parent::__construct($name, ArrayData::create(['Title' => $title, 'Page' => $page, 'FirstParagraph' => $firstParagraph ? $firstParagraph->innertext() : null, 'RenderedTitle' => $renderedTitle ? $renderedTitle->innertext() : null])->renderWith(self::class));
    }

    /**
     * @param $int
     *
     * @return $this
     */
    public function setResult($int)
    {
        $this->result = $int;

        return $this;
    }
}
