<?php

namespace QuinnInteractive\Seo\Analysis;

/**
 * Class FocusKeywordUniqueAnalysis
 * @package QuinnInteractive\Seo\Analysis
 */
class FocusKeywordUniqueAnalysis extends Analysis
{
    const FOCUS_KEYWORD_INUSE   = 0;
    const FOCUS_KEYWORD_SUCCESS = 1;
    const FOCUS_KEYWORD_UNSET   = -1;

    /**
     * @return string
     */
    public function getKeyword()
    {
        return strtolower($this->getPage()->FocusKeyword);
    }

    /**
     *
     * @return array
     */
    public function responses()
    {
        return [
            static::FOCUS_KEYWORD_UNSET   => [
                'The focus keyword has not been set, consider setting this to improve content analysis',
                'default'
            ],
            static::FOCUS_KEYWORD_INUSE   => [
                'The focus keyword you want this page to rank for is already being used on another page; ' .
                'consider changing that if you truly want this page to rank',
                'danger'
            ],
            static::FOCUS_KEYWORD_SUCCESS => ['The focus keyword has never been used before—nice!', 'success']
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

        if (\Page::get()->filter(['FocusKeyword' => $this->getKeyword(), 'ID:not' => $this->getPage()->ID])->first()) {
            return static::FOCUS_KEYWORD_INUSE;
        }

        return static::FOCUS_KEYWORD_SUCCESS;
    }
}
