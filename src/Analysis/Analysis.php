<?php

namespace Vulcan\Seo\Analysis;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\View\ArrayData;
use Sunra\PhpSimple\HtmlDomParser;
use Vulcan\Seo\Extensions\PageHealthExtension;
use Vulcan\Seo\Extensions\PageSeoExtension;
use Vulcan\Seo\Models\RenderedHtml;

/**
 * Class Analysis
 * @package Vulcan\Seo\Analysis
 */
class Analysis
{
    use Injectable, Configurable;

    /** @var \Page|PageHealthExtension */
    protected $page;

    /** @var int The result, set after {@link inspect()} completes successfully */
    protected $result;

    protected $domParser;

    /**
     * Allows you to hide certain levels (default, danger, success) from appearing in the content analysis.
     * You can specif this on a per analysis basis via YML or add the below to your own analysis instead
     *
     * @config
     * @var array
     */
    private static $hidden_levels = [];

    private static $indicator_levels = [
        'hidden',
        'default',
        'warning',
        'danger',
        'success'
    ];

    /**
     * One of: default, danger, warning or success
     *
     * @var string
     */
    protected $resultLevel;

    /**
     * Analysis constructor.
     *
     * @param \Page $page
     */
    public function __construct(\Page $page)
    {
        $this->setPage($page);
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return ArrayData
     */
    public function inspect()
    {
        $result = $this->run();

        if (!is_numeric($result)) {
            throw new \InvalidArgumentException('Expected integer for response, got ' . gettype($result) . ' instead');
        }
        if (empty($responses = $this->responses())) {
            throw new \InvalidArgumentException('Expected run() to return an integer, got ' . gettype($result) . ' instead');
        }

        if (!isset($responses[$result])) {
            throw new \InvalidArgumentException('Expected ' . $result . ' to be a key of the array that responses() returns, except the key ' . $result . ' does not exist');
        }

        if (count($responses[$result]) !== 2) {
            throw new \InvalidArgumentException('Expected the response for result ' . $result . ' to be an array containing two items, first is the message, second is the indicator status: danger, warning, success, default');
        }

        if (!in_array($responses[$result][1], $this->config()->get('indicator_levels'))) {
            throw new \InvalidArgumentException(sprintf('The specified indicator (%s) in the response for key %s is not a valid level, valid levels are: %s', $responses[$result][1], $result, implode(', ', $this->config()->get('indicator_levels'))));
        }

        $this->result = $result;
        $this->resultLevel = $responses[$result][1];

        return ArrayData::create([
            'Analysis' => static::class,
            'Result'   => $result,
            'Response' => $responses[$result][0],
            'Level'    => $this->resultLevel,
            'Hidden'   => $this->resultLevel === 'hidden' ? true : in_array($this->resultLevel, $this->config()->get('hidden_levels'))
        ]);
    }


    /**
     * You must override this in your subclass and perform your own checks. An integer must be returned
     * that references an index of the array you return in your response() method override in your subclass.
     *
     * @return int
     */
    public function run()
    {
        throw new \RuntimeException('You must override the run method in ' . static::class . ' and return an integer as a response that references a key in your array that your responses() override returns');
    }

    /**
     * All analyses must override the `responses()` method to provide response messages and the response level (which
     * is used for the indicator).
     * `run()` should return an integer that matches a key in the array that `responses()` returns, for example if
     * `run()` were to return `1`, then using the above example the message displayed would be `Hoorah!!! "Hello
     * World!" appears in the page title` with a indicator level of `success`. The available indicator levels are:
     * `default`, `danger`, `warning`, `success` which are grey, red, orange and green respectively.
     *
     * @return array
     */
    public function responses()
    {
        return [];
    }

    /**
     * @param \Page $page
     *
     * @return $this
     */
    public function setPage(\Page $page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return \Page|PageHealthExtension|PageSeoExtension
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return \simplehtmldom_1_5\simple_html_dom
     */
    public function getRenderedHtmlDomParser()
    {
        if ($this->domParser) {
            return $this->domParser;
        }

        $page = $this->getPage();

        if (!$page->RenderedHtml()->exists()) {
            $page->RenderedHtmlID = RenderedHtml::findOrMake($page)->ID;
            $page->write();
            $page->publishRecursive();
        }

        $this->domParser = HtmlDomParser::str_get_html($page->RenderedHtml()->Result);

        foreach ($this->domParser->find('header,footer,nav') as $item) {
            $item->outertext = '';
        }
        
        return $this->domParser;
    }

    /**
     * Fetches the rendered content from the dom parser. This is why it's important that your templates are semantically
     * correct. `<div>` tags should be used for layout and positioning purposes and using `<p>` tags for content is
     * semantically correct. Semantically correct pages tend to rank higher in search engines for various reasons (such
     * as how effectively crawlers parse your website etc.).
     *
     * @return string
     */
    public function getContent()
    {
        $parser = $this->getRenderedHtmlDomParser();
        $output = [];
        foreach ($parser->find('p,h1,h2,h3,h4,h5') as $item) {
            $output[] = strip_tags(html_entity_decode($item->innertext()));
        }

        $output = array_filter($output);
        return implode(' ', $output);
    }
}
