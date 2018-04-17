<?php

namespace Vulcan\Seo\Analysis;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\View\ArrayData;
use Vulcan\Seo\Extensions\PageHealthExtension;

/**
 * Class Analysis
 * @package Vulcan\Seo\Analysis
 */
abstract class Analysis
{
    use Injectable, Configurable;

    /** @var \Page|PageHealthExtension */
    protected $page;

    /** @var int The result, set after {@link inspect()} completes successfully */
    protected $result;

    /**
     * Allows you to hide certain levels (default, danger, success) from appearing in the content analysis.
     * You can specif this on a per analysis basis via YML or add the below to your own analysis instead
     *
     * @config
     * @var array
     */
    private static $hidden_levels = [];

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

        $this->result = $result;
        $this->resultLevel = $responses[$result][1];

        return ArrayData::create([
            'Analysis' => static::class,
            'Result'   => $result,
            'Response' => $responses[$result][0],
            'Level'    => $this->resultLevel,
            'Hidden'   => in_array($this->resultLevel, $this->config()->get('hidden_levels'))
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
     * All analyses must override the `responses()` method to provide response messages and the response level (which is used for the indicator).
     * `run()` should return an integer that matches a key in the array that `responses()` returns, for example if `run()` were to return `1`, then using the above example
     * the message displayed would be `Hoorah!!! "Hello World!" appears in the page title` with a indicator level of `success`.
     * The available indicator levels are: `default`, `danger`, `warning`, `success` which are grey, red, orange and green respectively.
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
     * @return \Page|PageHealthExtension
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
}
