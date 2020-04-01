<?php

namespace QuinnInteractive\Seo\Forms;

use QuinnInteractive\Seo\Extensions\PageHealthExtension;
use QuinnInteractive\Seo\Extensions\PageSeoExtension;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\LiteralField;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Parsers\URLSegmentFilter;
use SilverStripe\View\Requirements;
use simple_html_dom\simple_html_dom;

/**
 * Class GoogleSearchPreview
 * @package QuinnInteractive\Seo\Forms
 */
class GoogleSearchPreview extends LiteralField
{

    /**
     * @var int
     */
    protected $result;
    protected $schemaComponent = 'GoogleSearchPreview';

    protected $template = self::class;

    /**
     * HealthAnalysisField constructor.
     *
     * @param string                                     $name
     * @param \SilverStripe\Forms\FormField|string       $title
     * @param \Page|PageHealthExtension|PageSeoExtension $page
     * @param simple_html_dom                            $domParser
     */
    public function __construct($name, $title, $page, simple_html_dom $domParser)
    {
        $renderedTitle = $domParser->find('title', 0);

        $body = $domParser->find('body', 0);

        if ($body) {
            foreach ($body->find('header,footer,nav') as $header) {
                $header->outertext = '';
            }
        }

        $firstParagraph = $domParser->find('p', 0);

        Requirements::javascript('quinninteractive/silverstripe-seo:dist/javascript/main.min.js');
        Requirements::css('quinninteractive/silverstripe-seo:dist/css/styles.min.css');

        parent::__construct($name, ArrayData::create([
            'Title'           => $title,
            'Page'            => $page,
            'AbsoluteLink'    => Controller::join_links(
                Director::absoluteBaseURL(),
                str_replace($page->URLSegment, '', $page->Link()),
                $this->urlSegmentHighlight($page->URLSegment, $page->FocusKeyword)
            ),
            'MetaDescription' => $page->MetaDescription ? $this->highlight(
                $page->MetaDescription,
                $page->FocusKeyword
            ) : null,
            'FirstParagraph'  => $firstParagraph ? $this->highlight(
                $firstParagraph->innertext(),
                $page->FocusKeyword
            ) : null,
            'RenderedTitle'   => $renderedTitle ? $this->highlight(
                $renderedTitle->innertext(),
                $page->FocusKeyword
            ) : null
        ])->renderWith(self::class));
    }

    /**
     * Highlights parts of the $haystack that match the focus keyword as a whole, case insensitive
     *
     * @param $haystack
     * @param $needle
     *
     * @return mixed
     */
    public function highlight($haystack, $needle)
    {
        if (!$needle) {
            return strip_tags($haystack);
        }

        return preg_replace('/\b(' . $needle . ')\b/i', '<strong>$0</strong>', strip_tags($haystack));
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

    /**
     * Highlights parts of the URLSegment that match the focus keyword as a whole, does not highlight hyphens in that
     * same match.
     *
     * @param $urlSegment
     * @param $needle
     *
     * @return mixed|string
     */
    public function urlSegmentHighlight($urlSegment, $needle)
    {
        if ($urlSegment === 'home') {
            return '/';
        }

        if (!$needle) {
            return $urlSegment;
        }

        $needle = URLSegmentFilter::create()->filter($needle);

        preg_match('/(' . $needle . ')/i', $needle, $matches);

        if (!isset($matches[1])) {
            return $urlSegment;
        }

        $needles = explode('-', $needle);
        $output  = $urlSegment;

        foreach ($needles as $needle) {
            $output = preg_replace('/(' . $needle . ')/i', '<strong>$0</strong>', $output);
        }

        return $output;
    }
}
