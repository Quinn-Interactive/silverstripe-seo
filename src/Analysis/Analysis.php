<?php

namespace QuinnInteractive\Seo\Analysis;

use KubAT\PhpSimple\HtmlDomParser;
use QuinnInteractive\Seo\Extensions\PageHealthExtension;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\View\ArrayData;

/**
 * Class Analysis
 * @package QuinnInteractive\Seo\Analysis
 */
abstract class Analysis
{
    use Injectable, Configurable;

    protected $domParser;

    /** @var \Page|PageHealthExtension */
    protected $page;

    /** @var int The result, set after {@link inspect()} completes successfully */
    protected $result;

    /**
     * One of: default, danger, warning or success
     *
     * @var string
     */
    protected $resultLevel;

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
     * Analysis constructor.
     *
     * @param SiteTree $page
     */
    public function __construct(SiteTree $page)
    {
        $this->setPage($page);
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

    /**
     * @return SiteTree|PageHealthExtension
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return \simple_html_dom\simple_html_dom
     */
    public function getRenderedHtmlDomParser()
    {
        if ($this->domParser) {
            return $this->domParser;
        }

        $this->domParser = HtmlDomParser::str_get_html(file_get_contents($this->getPage()->AbsoluteLink()));

        foreach ($this->domParser->find('header,footer,nav') as $item) {
            $item->outertext = '';
        }

        return $this->domParser;
    }

    /**
     * @return int
     */
    public function getResult()
    {
        return $this->result;
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
            throw new \InvalidArgumentException('Expected run() to return an integer, got '
                . gettype($result)
                . ' instead');
        }
        if (!isset($responses[$result])) {
            throw new \InvalidArgumentException(sprintf(
                'Expected %s to be a key of the array that responses() returns, except the key %s does not exist',
                $result,
                $result
            ));
        }
        if (count($responses[$result]) !== 2) {
            throw new \InvalidArgumentException(sprintf(
                'Expected the response for result %s to be an array containing two items: ' .
                'first is the message & second is the indicator status: danger, warning, success, default',
                $result
            ));
        }
        if (!in_array($responses[$result][1], $this->config()->get('indicator_levels'))) {
            throw new \InvalidArgumentException(sprintf(
                'The specified indicator (%s) in the response for key %s is not a valid level, valid levels are: %s',
                $responses[$result][1],
                $result,
                implode(', ', $this->config()->get('indicator_levels'))
            ));
        }
        $this->result      = $result;
        $this->resultLevel = $responses[$result][1];

        return ArrayData::create([
            'Analysis' => static::class,
            'Result'   => $result,
            'Response' => $responses[$result][0],
            'Level'    => $this->resultLevel,
            'Hidden'   => $this->resultLevel === 'hidden'
                ? true
                : in_array($this->resultLevel, $this->config()->get('hidden_levels'))
        ]);
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
     * You must override this in your subclass and perform your own checks. An integer must be returned
     * that references an index of the array you return in your response() method override in your subclass.
     *
     * @return int
     */
    public function run()
    {
        throw new \RuntimeException(srintf(
            'You must override the run method in %s and return an integer as a response that references '
            . 'a key in your array that your responses() override returns',
            static::class
        ));
    }

    /**
     * @param SiteTree $page
     * @return $this
     */
    public function setPage(SiteTree $page)
    {
        $this->page = $page;
        return $this;
    }
}
