<?php

namespace Vulcan\Seo\Analysis;

use SilverStripe\View\Parsers\URLSegmentFilter;

/**
 * Class FocusKeywordUrlAnalysis
 * @package Vulcan\Seo\Analysis
 */
class FocusKeywordUrlAnalysis extends Analysis
{
    private static $hidden_levels = [
        'default'
    ];

    /**
     * You must override this in your subclass and perform your own checks. An integer must be returned
     * that references an index of the array you return in your response() method override in your subclass.
     *
     * @return int
     */
    public function run()
    {
        if (!$this->getKeyword()) {
            return 0;
        }

        $slug = URLSegmentFilter::create()->filter($this->getKeyword());

        if (!strstr($this->getPage()->URLSegment, $slug)) {
            return 1;
        }

        return 2;
    }

    /**
     * @return array
     */
    public function responses()
    {
        return [
            0 => ['The focus keyword has not been set, consider setting this to improve content analysis', 'default'],
            1 => ['The focus keyword is not in the url segment, consider changing this and if you do SilverStripe will automatically redirect your old URL!', 'warning'],
            2 => ['The focus keyword is in the url segment, this is great!', 'success'],
        ];
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return strtolower($this->getPage()->FocusKeyword);
    }
}
