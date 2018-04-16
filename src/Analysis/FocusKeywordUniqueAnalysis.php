<?php

namespace Vulcan\Seo\Analysis;

/**
 * Class FocusKeywordUniqueAnalysis
 * @package Vulcan\Seo\Analysis
 */
class FocusKeywordUniqueAnalysis extends Analysis
{
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

        if (\Page::get()->filter(['FocusKeyword' => $this->getKeyword(), 'ID:not' => $this->getPage()->ID])->first()) {
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
            1 => ['The focus keyword you want this page to rank for is already being used on another page, consider changing that if you truly want this page to rank', 'danger'],
            2 => ['The focus keyword has never been used before, nice!', 'success']
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
