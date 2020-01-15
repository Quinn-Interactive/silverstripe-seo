<?php

namespace QuinnInteractive\Seo\Analysis;

use SilverStripe\View\Parsers\URLSegmentFilter;

/**
 * Class FocusKeywordUrlAnalysis
 * @package QuinnInteractive\Seo\Analysis
 */
class FocusKeywordUrlAnalysis extends Analysis
{
    const FOCUS_KEYWORD_IRRELEVANT = -2;
    const FOCUS_KEYWORD_NOT_IN_URL = 0;
    const FOCUS_KEYWORD_SUCCESS    = 1;
    const FOCUS_KEYWORD_UNSET      = -1;

    private static $hidden_levels = [
        'default'
    ];

    /**
     * @return string
     */
    public function getKeyword()
    {
        return strtolower($this->getPage()->FocusKeyword);
    }

    /**
     * @return array
     */
    public function responses()
    {
        return [
            static::FOCUS_KEYWORD_IRRELEVANT => [
                'The focus keyword is irrelevant on the home page; this message will not display',
                'default'
            ],
            static::FOCUS_KEYWORD_UNSET => [
                'The focus keyword has not been set; consider setting this to improve content analysis',
                'default'
            ],
            static::FOCUS_KEYWORD_NOT_IN_URL => [
                'The focus keyword is not in the url segment; ' .
                'consider changing this and if you do SilverStripe will automatically redirect your old URL!',
                'warning'
            ],
            static::FOCUS_KEYWORD_SUCCESS => ['The focus keyword is in the url segment; this is great!', 'success'],
        ];
    }

    /**
     * You must override this in your subclass and perform your own checks. An integer must be returned
     * that references an index of the array you return in your response() method override in your subclass.
     *
     * @return int
     */
    public function run()
    {
        if (!$this->getKeyword()) {
            return static::FOCUS_KEYWORD_UNSET;
        }

        $slug = URLSegmentFilter::create()->filter($this->getKeyword());

        if ($this->getPage()->URLSegment === 'home' && !$this->getPage()->ParentID) {
            return static::FOCUS_KEYWORD_IRRELEVANT;
        }

        if (!strstr($this->getPage()->URLSegment, $slug)) {
            return static::FOCUS_KEYWORD_NOT_IN_URL;
        }

        return static::FOCUS_KEYWORD_SUCCESS;
    }
}
